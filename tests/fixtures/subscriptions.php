<?php

return [
    'Freemium\Domain\Subscription' => [
        'testDowngradeToPaid' => [
            '__construct' => ['sub_testDowngradeToPaid', '@bob', '@premium', new \DateTimeImmutable('today')],
            'inTrial' => false,
            'paidThrough' => new \DateTimeImmutable('15 days'),
        ],
        'testRemainingAmountForYearlyPlan' => [
            '__construct' => ['sub_testRemainingAmountForYearlyPlan', '@bob', '@premium', new \DateTimeImmutable('today')],
            'inTrial' => false,
            'paidThrough' => new \DateTimeImmutable('15 days'),
        ],
        'testRemainingAmountForMonthlyPlan' => [
            '__construct' => ['sub_testRemainingAmountForMonthlyPlan', '@bob', '@basic', new \DateTimeImmutable('today')],
            'inTrial' => false,
            'paidThrough' => new \DateTimeImmutable('15 days'),
        ],
        'testApplyCoupon' => [
            '__construct' => ['sub_testApplyCoupon', '@bob', '@basic', new \DateTimeImmutable('today')],
            'inTrial' => false,
            'paidThrough' => new \DateTimeImmutable('30 days'),
        ],
        'testChargePaidSubscription' => [
            '__construct' => ['sub_testChargePaidSubscription', '@bob', '@premium', new \DateTimeImmutable('today')],
            'inTrial' => false,
            'paidThrough' => new \DateTimeImmutable('today'),
            'startedOn' => new \DateTimeImmutable('30 days ago'),
        ],
        'testSetToExpire' => [
            '__construct' => ['sub_testSetToExpire', '@sally', '@premium', new \DateTimeImmutable('today')],
            'inTrial' => false,
            'paidThrough' => new \DateTimeImmutable('today'),
            'startedOn' => new \DateTimeImmutable('30 days ago'),
        ],
        'testExpiration' => [
            '__construct' => ['sub_testExpiration', '@sally', '@premium', new \DateTimeImmutable('today')],
            'inTrial' => false,
            'paidThrough' => new \DateTimeImmutable('yesterday'),
            'startedOn' => new \DateTimeImmutable('30 days ago'),
            'cancelAt' => new \DateTimeImmutable('today'),
        ],
        'testInGraceSubscription' => [
            '__construct' => ['sub_testInGraceSubscription', '@sally', '@premium', new \DateTimeImmutable('today')],
            'inTrial' => false,
            'paidThrough' => new \DateTimeImmutable('today'),
            'startedOn' => new \DateTimeImmutable('30 days ago'),
        ],
        'testChangePlan' => [
            '__construct' => ['sub_testChangePlan', '@bob', '@basic', new \DateTimeImmutable('today')],
            'inTrial' => false,
            'paidThrough' => new \DateTimeImmutable('1 days'),
        ],
        'testChangePlanNoBillingKey' => [
            '__construct' => ['sub_testChangePlanNoBillingKey', '@steve', '@free', new \DateTimeImmutable('today')],
            'inTrial' => false,
        ],
    ],
];
