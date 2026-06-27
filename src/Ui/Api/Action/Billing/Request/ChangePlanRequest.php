<?php

declare(strict_types=1);

namespace Larium\Ui\Api\Action\Billing\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class ChangePlanRequest
{
    #[Assert\NotBlank]
    public string $planName = '';
}
