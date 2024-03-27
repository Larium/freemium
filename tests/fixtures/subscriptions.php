<?php

return [
    'Freemium\Subscription' => [
        'testDowngradeToPaid' => [
            '__construct' => ['@bob', '@premium'],
            'inTrial' => false,
            'paidThrough' => new DateTime('15 days'),
        ],
        'testRemainingAmountForYearlyPlan' => [
            '__construct' => ['@bob', '@premium'],
            'inTrial' => false,
            'paidThrough' => new DateTime('15 days'),
        ],
        'testRemainingAmountForMonthlyPlan' => [
            '__construct' => ['@bob', '@basic'],
            'inTrial' => false,
            'paidThrough' => new DateTime('15 days'),
        ],
        'testApplyCoupon' => [
            '__construct' => ['@bob', '@basic'],
            'inTrial' => false,
            'paidThrough' => new DateTime('30 days'),
        ],
        'testChargePaidSubscription' => [
            '__construct' => ['@bob', '@premium'],
            'inTrial' => false,
            'paidThrough' => new DateTime('today'),
            'startedOn' => new DateTime('30 days ago'),
        ],
        'testSetToExpire' => [
            '__construct' => ['@sally', '@premium'],
            'inTrial' => false,
            'paidThrough' => new DateTime('today'),
            'startedOn' => new DateTime('30 days ago'),
        ],
        'testExpiration' => [
            '__construct' => ['@sally', '@premium'],
            'inTrial' => false,
            'paidThrough' => new DateTime('yesterday'),
            'startedOn' => new DateTime('30 days ago'),
            'expireOn' => new DateTime('today'),
        ],
        'testInGraceSubscription' => [
            '__construct' => ['@sally', '@premium'],
            'inTrial' => false,
            'paidThrough' => new DateTime('today'),
            'startedOn' => new DateTime('30 days ago'),
        ],
        'testChangePlan' => [
            '__construct' => ['@bob', '@basic'],
            'inTrial' => false,
            'paidThrough' => new DateTime('1 days'),
        ],
        'testChangePlanNoBillingKey' => [
            '__construct' => ['@steve', '@free'],
            'inTrial' => false,
        ],
    ]
];
