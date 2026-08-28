<?php

declare(strict_types=1);

namespace Freemium\Domain;

use DateTimeImmutable;
use DomainException;
use Larium\StateMachine\Stateful;
use Larium\StateMachine\StateMachine;

class Subscription implements Stateful
{
    public const TOKEN_PREFIX = 'sub_';

    private readonly string $token;

    /**
     * The model in your system that has the subscription.
     * Probably a User.
     */
    private Subscribable $subscribable;

    /**
     * The previous subsciption plan when subscription plan is changed.
     */
    private ?SubscriptionPlan $originalPlan = null;

    /**
     * When the subscription currently expires, assuming no further payment.
     * For manual billing, this also determines when the next payment is due.
     */
    private ?DateTimeImmutable $paidThrough = null;

    /**
     * When subscription started?
     */
    private DateTimeImmutable $startedOn;

    /**
     * When the last gateway transaction was for this account?
     * This is used by your gateway to find "new" transactions.
     */
    private ?DateTimeImmutable $lastTransactionAt = null;

    /**
     * Is subscription in trial?
     */
    private bool $inTrial = false;

    /**
     * When this subscription will cancel (or has canceled).
     */
    private ?DateTimeImmutable $cancelAt = null;

    private ?StateMachine $stateMachine = null;

    private Money $rate;

    private SubscriptionStatus $status = SubscriptionStatus::ACTIVE;

    private ?DateTimeImmutable $trialStartedOn = null;

    private ?DateTimeImmutable $trialEndsOn = null;

    private ?DateTimeImmutable $graceStartedOn = null;

    private ?DateTimeImmutable $graceEndsOn = null;

    /** Which service plan this subscription is for. Affects how payment is interpreted. */
    private SubscriptionPlan $subscriptionPlan;

    public function __construct(
        string $token,
        Subscribable $subscribable,
        SubscriptionPlan $subscriptionPlan,
        DateTimeImmutable $on,
    ) {
        $this->token = $token;
        $this->subscribable = $subscribable;
        $this->subscriptionPlan = $subscriptionPlan;
        $this->calculateForPlan($subscriptionPlan, $on);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Sets a SubscriptionPlan to current Subscription.
     *
     * Calculates rate, started date, paid through date. Returns a SubscriptionChange for audit when plan actually changed.
     *
     * @return SubscriptionChange|null The change record, or null if same plan
     */
    public function setSubscriptionPlan(SubscriptionPlan $plan, DateTimeImmutable $on): ?SubscriptionChange
    {
        if ($this->subscriptionPlan->getName() === $plan->getName()) {
            return null;
        }

        $this->originalPlan = $this->subscriptionPlan;
        $this->subscriptionPlan = $plan;

        return $this->calculateForPlan($plan, $on);
    }

    private function calculateForPlan(SubscriptionPlan $plan, DateTimeImmutable $on): SubscriptionChange
    {
        $this->rate = $plan->getRate();
        $this->startedOn = $on;

        if ($this->isPaid() && $this->subscribable->getBillingKey() === null) {
            throw new DomainException(
                'Can not create paid subscription without a billing key. Subscription: ' . $this->token . ', customer: ' . $this->subscribable->getCustomerId() . '.'
            );
        }

        $this->applyPaidThrough(new RateCalculator(), $on);

        return new SubscriptionChange(
            $this,
            $this->getSubscriptionReason($on),
            $this->originalPlan
        );
    }

    private function applyPaidThrough(RateCalculator $rateCalculator, DateTimeImmutable $on): void
    {
        $notPaidSubscription = new PaidThrough\NotPaidSubscriptionCalculator($this, $on);
        $newPaidSubscription = new PaidThrough\NewPaidSubscriptionCalculator($this, $on);
        $creditRemainingValue = new PaidThrough\CreditRemainingValueCalculator($this, $rateCalculator, $on);
        $default = new PaidThrough\DefaultCalculator($this, $on);

        $notPaidSubscription->setSuccessor($newPaidSubscription);
        $newPaidSubscription->setSuccessor($creditRemainingValue);
        $creditRemainingValue->setSuccessor($default);

        $state = $notPaidSubscription->calculate();

        $this->paidThrough = $state->getPaidThrough();
        $this->cancelAt = $state->getExpireOn() ?: $this->cancelAt;
        $this->inTrial = $state->isInTrial();
        if ($this->inTrial) {
            $this->trialEndsOn = $state->getTrialEndsOn();
            $this->trialStartedOn ??= $on;
        }
    }

    private function getSubscriptionReason(DateTimeImmutable $on): SubscriptionChangeReason
    {
        if ($this->originalPlan === null) {
            return SubscriptionChangeReason::REASON_NEW;
        }

        if ($this->originalPlan->getRate()->greater($this->subscriptionPlan->getRate())) {
            return $this->isCancellationDue($on)
                ? SubscriptionChangeReason::REASON_EXPIRE
                : SubscriptionChangeReason::REASON_DOWNGRADE;
        }

        return SubscriptionChangeReason::REASON_UPGRADE;
    }

    public function getRate(): Money
    {
        return $this->rate;
    }

    public function isPaid(): bool
    {
        return $this->rate->greater(Money::zero($this->rate->getCurrency()));
    }

    /**
     * The amount to charge the gateway for one billing cycle (plan rate with coupon applied at cycle level).
     */
    public function billingAmount(?Coupon $activeCoupon = null): Money
    {
        $value = $this->subscriptionPlan->getRate();
        if ($activeCoupon !== null) {
            $value = $activeCoupon->getDiscount($value);
        }

        return $value;
    }

    /**
     * Returns the remaining monetary value for conversion (e.g. on plan change).
     * When there is no original plan (new subscription), no conversion is needed and zero is returned.
     */
    public function remainingAmount(DateTimeImmutable $on): Money
    {
        $originalPlan = $this->getOriginalPlan();
        if ($originalPlan === null) {
            return Money::zero($this->getRate()->getCurrency());
        }

        $dailyRate = (new RateCalculator())->dailyRate($originalPlan);

        return $dailyRate->multiply((string) $this->getRemainingDays($on));
    }

    /**
     * Gets the remaining days for the next payment cycle.
     * A negative number doesn' t  mean that subscription has
     * expired. Maybe it is in grace.
     */
    public function getRemainingDays(DateTimeImmutable $on): int
    {
        if ($this->getPaidThrough() === null) {
            return 0;
        }

        $interval = $on->diff($this->getPaidThrough());

        return $interval->invert == 1 ? (-1 * $interval->days) : $interval->days;
    }

    /**
     * Returns remaining days of grace.
     * if under grace through today, returns zero
     */
    public function getRemainingDaysOfGrace(DateTimeImmutable $on): int
    {
        if ($this->cancelAt === null) {
            return 0;
        }

        if ($on >= $this->cancelAt) {
            return 0;
        }

        return (int) ($on->diff($this->cancelAt)->days);
    }

    /**
     * Checks if current Subscription is in grace.
     */
    public function isInGrace(DateTimeImmutable $on): bool
    {
        return $this->getRemainingDaysOfGrace($on) > 0;
    }

    /**
     * Sets the Subscription to expire after applying the grace period.
     *
     * If paid through date is in future then grace days will apply to that
     * date.
     *
     * This will not run in Subscriptions that already have an expired date.
     */
    public function markPastDue(DateTimeImmutable $on): void
    {
        if (null === $this->cancelAt) {
            $this->stateMachine()->apply(SubscriptionStateMachine::TRANSITION_PAST_DUE);
            $base = $this->getPaidThrough() ?? $on;
            $max = $base > $on ? $base : $on;
            $this->graceStartedOn = $on;
            $this->graceEndsOn = $max->modify($this->getSubscriptionPlan()->getGraceDays() . ' days');
            $this->cancelAt = $this->graceEndsOn;
        }
    }

    /**
     * Cancel a Subscription.
     *
     * Sets cancel date to today and status to CANCELED.
     */
    public function cancel(DateTimeImmutable $on): void
    {
        $this->stateMachine()->apply(SubscriptionStateMachine::TRANSITION_CANCEL);
        $this->cancelAt = $on;
        $this->graceEndsOn = $on;
    }

    /**
     * Checks if cancellation is due (cancel date has passed).
     */
    public function isCancellationDue(DateTimeImmutable $on): bool
    {
        if ($this->cancelAt === null) {
            return false;
        }

        return $this->cancelAt >= $this->paidThrough
            && $this->cancelAt <= $on;
    }

    /**
     * Current Subscription received a succesful payment.
     */
    public function receivePayment(DateTimeImmutable $on): void
    {
        $this->stateMachine()->apply(SubscriptionStateMachine::TRANSITION_PAY);
        $this->cancelAt = null;
        $this->graceStartedOn = null;
        $this->graceEndsOn = null;
        $this->inTrial = false;
        $this->trialStartedOn = null;
        $this->trialEndsOn = null;
        $relative_format = $this->getSubscriptionPlan()->getCycleRelativeFormat();
        $this->paidThrough ??= $on;
        $this->paidThrough = $this->paidThrough->modify($relative_format);
    }

    private function stateMachine(): StateMachine
    {
        if ($this->stateMachine === null) {
            $this->stateMachine = SubscriptionStateMachine::create($this);
        }

        return $this->stateMachine;
    }

    /**
     * Checks if subscription is in trial period.
     */
    public function isInTrial(): bool
    {
        return $this->inTrial;
    }

    public function getSubscribable(): Subscribable
    {
        return $this->subscribable;
    }

    /** Which service plan this subscription is for. Affects how payment is interpreted. */
    public function getSubscriptionPlan(): SubscriptionPlan
    {
        return $this->subscriptionPlan;
    }

    public function getStartedOn(): DateTimeImmutable
    {
        return $this->startedOn;
    }

    public function getPaidThrough(): ?DateTimeImmutable
    {
        return $this->paidThrough;
    }

    public function getTrialStartedOn(): ?DateTimeImmutable
    {
        return $this->trialStartedOn;
    }

    public function getTrialEndsOn(): ?DateTimeImmutable
    {
        return $this->trialEndsOn;
    }

    public function getGraceStartedOn(): ?DateTimeImmutable
    {
        return $this->graceStartedOn;
    }

    public function getGraceEndsOn(): ?DateTimeImmutable
    {
        return $this->graceEndsOn;
    }

    public function createTransaction(
        string $transactionToken,
        DateTimeImmutable $on,
        ?string $idempotencyKey = null,
        ?Coupon $activeCoupon = null,
    ): Transaction {
        $this->lastTransactionAt = $on;

        return new Transaction($transactionToken, $this->billingAmount($activeCoupon), $idempotencyKey, $this->token);
    }

    public function getLastTransactionAt(): ?DateTimeImmutable
    {
        return $this->lastTransactionAt;
    }

    public function getCancelAt(): ?DateTimeImmutable
    {
        return $this->cancelAt;
    }

    public function getStatus(): SubscriptionStatus
    {
        return $this->status;
    }

    public function getOriginalPlan(): ?SubscriptionPlan
    {
        return $this->originalPlan;
    }

    public function getFiniteState(): ?string
    {
        return $this->status->value;
    }

    public function setFiniteState(string $state): void
    {
        $this->status = SubscriptionStatus::from($state);
    }
}
