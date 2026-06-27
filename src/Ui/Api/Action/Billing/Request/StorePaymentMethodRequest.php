<?php

declare(strict_types=1);

namespace Larium\Ui\Api\Action\Billing\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class StorePaymentMethodRequest
{
    #[Assert\NotBlank]
    public string $number = '';

    #[Assert\NotBlank]
    public string $month = '';

    #[Assert\NotBlank]
    public string $year = '';

    #[Assert\NotBlank]
    public string $verificationValue = '';
}
