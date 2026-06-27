<?php

declare(strict_types=1);

namespace Larium\Ui\Api\Action\Billing\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateSubscriptionRequest
{
    #[Assert\NotBlank]
    public string $customerId = '';

    #[Assert\NotBlank]
    public string $planName = '';

    #[Assert\PositiveOrZero]
    public ?int $daysTrial = 0;

    #[Assert\PositiveOrZero]
    public ?int $daysGrace = 0;
}
