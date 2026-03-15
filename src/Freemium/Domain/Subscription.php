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
     *
     * @var Subscribable
     */
    private Subscribable $subscribable;

    /**
     * The previous subsciption plan when subscription plan is changed.
     *
     * @var SubscriptionPlan
     */
    private $originalPlan;

    /**
     * When the subscription currently expires, assuming no further payment.
     * For manual billing, this also determines when the next payment is due.
     *
     * @var DateTimeImmutable|null
     */
    private ?DateTimeImmutable $paidThrough = null;

    /**
     * When subscription started?
     *
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $startedOn;

    /**
     * When the last gateway transaction was for this account?
     * This is used by your gateway to find "new" transactions.
     *
     * @var DateTimeImmutable|null
     */
    private ?DateTimeImmutable $lastTransactionAt = null;

    /**
     * Is subscription in trial?
     *
     * @var bool
     */
    private bool $inTrial = false;

    /**
     * When this subscription will cancel (or has canceled).
     *
     * @var DateTimeImmutable|null
     */
    private ?DateTimeImmutable $cancelAt = null;

    private ?StateMachine $stateMachine = null;

    private Money $rate;

    private SubscriptionStatus $status = SubscriptionStatus::ACTIVE;

    private Clock $clock;

    private int $daysTrial;

    private int $daysGrace;

    public function __construct(
        string $token,
        Subscribable $subscribable,
        /** Which service plan this subscription is for. Affects how payment is interpreted.*/
        private SubscriptionPlan $subscriptionPlan,
        ?Clock $clock = null,
        int $daysTrial = 0,
        int $daysGrace = 0
    ) {
        $this->token = $token;
        $this->subscribable = $subscribable;
        $this->clock = $clock ?? new SystemClock();
        $this->daysTrial = $daysTrial;
        $this->daysGrace = $daysGrace;
        $this->calculateForPlan($subscriptionPlan);
    }

    public function getDaysTrial(): int
    {
        return $this->daysTrial;
    }

    public function getDaysGrace(): int
    {
        return $this->daysGrace;
    }

    private function today(): DateTimeImmutable
    {
        return $this->clock->now()->setTime(0, 0, 0);
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
    public function setSubscriptionPlan(SubscriptionPlan $plan): ?SubscriptionChange
    {
        if ($this->subscriptionPlan !== null
            && $this->subscriptionPlan->getName() === $plan->getName()
        ) {
            return null;
        }

        $this->originalPlan = $this->subscriptionPlan;
        $this->subscriptionPlan = $plan;

        return $this->calculateForPlan($plan);
    }

    private function calculateForPlan(SubscriptionPlan $plan): SubscriptionChange
    {
        $this->rate = $plan->getRate();
        $this->startedOn = $this->today();

        if ($this->isPaid() && $this->subscribable->getBillingKey() === null) {
            throw new DomainException(
                'Can not create paid subscription without a billing key. Subscription: ' . $this->token . ', customer: ' . $this->subscribable->getCustomerId() . '.'
            );
        }

        $this->applyPaidThrough(new RateCalculator());

        return new SubscriptionChange(
            $this,
            $this->getSubscriptionReason(),
            $this->originalPlan
        );
    }

    private function applyPaidThrough(RateCalculator $rateCalculator): void
    {
        $notPaidSubscription = new PaidThrough\NotPaidSubscriptionCalculator($this);
        $newPaidSubscription = new PaidThrough\NewPaidSubscriptionCalculator($this);
        $creditRemainingValue = new PaidThrough\CreditRemainingValueCalculator($this, $rateCalculator);
        $default = new PaidThrough\DefaultCalculator($this);

        $notPaidSubscription->setSuccessor($newPaidSubscription);
        $newPaidSubscription->setSuccessor($creditRemainingValue);
        $creditRemainingValue->setSuccessor($default);

        $state = $notPaidSubscription->calculate();

        $this->paidThrough = $state->getPaidThrough();
        $this->cancelAt = $state->getExpireOn() ?: $this->cancelAt;
        $this->inTrial = $state->isInTrial();
    }

    private function getSubscriptionReason(): SubscriptionChangeReason
    {
        if ($this->originalPlan === null) {
            return SubscriptionChangeReason::REASON_NEW; # Fresh subscription.
        }

        if ($this->originalPlan->getRate()->greater($this->subscriptionPlan->getRate())) {
            return $this->isCancellationDue()
                ? SubscriptionChangeReason::REASON_EXPIRE # Even Free plan may expire after a certain amount of time.
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
     *
     * @return Money Amount in minor units; zero when getOriginalPlan() is null
     */
    public function remainingAmount(): Money
    {
        $originalPlan = $this->getOriginalPlan();
        if ($originalPlan === null) {
            return Money::zero($this->getRate()->getCurrency());
        }

        $dailyRate = (new RateCalculator())->dailyRate($originalPlan);

        return $dailyRate->multiply((string) $this->getRemainingDays());
    }

    /**
     * Gets the remaining days for the next payment cycle.
     * A negative number doesn' t  mean that subscription has
     * expired. Maybe it is in grace.
     *
     * @return int
     */
    public function getRemainingDays(): int
    {
        if ($this->getPaidThrough() === null) {
            return 0;
        }

        $interval = $this->today()->diff($this->getPaidThrough());

        return $interval->invert == 1 ? (-1 * $interval->days) : $interval->days;
    }

    /**
     * Returns remaining days of grace.
     * if under grace through today, returns zero
     *
     * @return int
     */
    public function getRemainingDaysOfGrace(): int
    {
        if ($this->cancelAt === null) {
            return 0;
        }

        return (int) ($this->cancelAt->diff($this->today())->days);
    }

    /**
     * Checks if current Subscription is in grace.
     *
     * @return bool
     */
    public function isInGrace(): bool
    {
        return $this->getRemainingDaysOfGrace() > 0;
    }

    /**
     * Sets the Subscription to expire after applying the grace period.
     *
     * If paid through date is in future then grace days will apply to that
     * date.
     *
     * This will not run in Subscriptions that already have an expired date.
     *
     * @return void
     */
    public function markPastDue(): void
    {
        if (null === $this->cancelAt) {
            $this->stateMachine()->apply(SubscriptionStateMachine::TRANSITION_PAST_DUE);
            $base = $this->getPaidThrough() ?? $this->today();
            $max = $base > $this->today() ? $base : $this->today();
            $this->cancelAt = $max->modify($this->getDaysGrace() . ' days');
        }
    }

    /**
     * Cancel a Subscription.
     *
     * Sets cancel date to today and status to CANCELED.
     */
    public function cancel(): void
    {
        $this->stateMachine()->apply(SubscriptionStateMachine::TRANSITION_CANCEL);
        $this->cancelAt = $this->today();
    }

    /**
     * Checks if cancellation is due (cancel date has passed).
     */
    public function isCancellationDue(): bool
    {
        if ($this->cancelAt === null) {
            return false;
        }

        return $this->cancelAt >= $this->paidThrough
            && $this->cancelAt <= $this->today();
    }

    /**
     * Current Subscription received a succesful payment.
     */
    public function receivePayment(): void
    {
        $this->stateMachine()->apply(SubscriptionStateMachine::TRANSITION_PAY);
        $this->cancelAt = null;
        $this->inTrial = false;
        $relative_format = $this->getSubscriptionPlan()->getCycleRelativeFormat();
        $this->paidThrough ??= $this->today();
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
     *
     * @return bool
     */
    public function isInTrial(): bool
    {
        return $this->inTrial;
    }

    /**
     * Get subscribable.
     *
     * @return Subscribable
     */
    public function getSubscribable(): Subscribable
    {
        return $this->subscribable;
    }

    /**
     * Get subscription plan.
     *
     * @return SubscriptionPlan
     */
    public function getSubscriptionPlan(): SubscriptionPlan
    {
        return $this->subscriptionPlan;
    }

    /**
     * Get started on.
     *
     * @return DateTimeImmutable
     */
    public function getStartedOn(): DateTimeImmutable
    {
        return $this->startedOn;
    }

    /**
     * Get paid through.
     *
     * @return DateTimeImmutable|null
     */
    public function getPaidThrough(): ?DateTimeImmutable
    {
        return $this->paidThrough;
    }

    public function createTransaction(string $transactionToken, ?string $idempotencyKey = null, ?Coupon $activeCoupon = null): Transaction
    {
        $this->lastTransactionAt = $this->today();

        return new Transaction($transactionToken, $this->billingAmount($activeCoupon), $idempotencyKey);
    }

    /**
     * Get last transaction date for this subscription.
     *
     * @return DateTimeImmutable|null
     */
    public function getLastTransactionAt(): ?DateTimeImmutable
    {
        return $this->lastTransactionAt;
    }

    /**
     * Get cancel at.
     *
     * @return DateTimeImmutable|null
     */
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
