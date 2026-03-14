<?php

return [
    'Freemium\Domain\Subscription' => [
        'testDowngradeToPaid' => [
            '__construct' => ['sub_testDowngradeToPaid', '@bob', '@premium'],
            'inTrial' => false,
            'paidThrough' => new DateTime('15 days'),
        ],
        'testRemainingAmountForYearlyPlan' => [
            '__construct' => ['sub_testRemainingAmountForYearlyPlan', '@bob', '@premium'],
            'inTrial' => false,
            'paidThrough' => new DateTime('15 days'),
        ],
        'testRemainingAmountForMonthlyPlan' => [
            '__construct' => ['sub_testRemainingAmountForMonthlyPlan', '@bob', '@basic'],
            'inTrial' => false,
            'paidThrough' => new DateTime('15 days'),
        ],
        'testApplyCoupon' => [
            '__construct' => ['sub_testApplyCoupon', '@bob', '@basic'],
            'inTrial' => false,
            'paidThrough' => new DateTime('30 days'),
        ],
        'testChargePaidSubscription' => [
            '__construct' => ['sub_testChargePaidSubscription', '@bob', '@premium'],
            'inTrial' => false,
            'paidThrough' => new DateTime('today'),
            'startedOn' => new DateTime('30 days ago'),
        ],
        'testSetToExpire' => [
            '__construct' => ['sub_testSetToExpire', '@sally', '@premium'],
            'inTrial' => false,
            'paidThrough' => new DateTime('today'),
            'startedOn' => new DateTime('30 days ago'),
        ],
        'testExpiration' => [
            '__construct' => ['sub_testExpiration', '@sally', '@premium'],
            'inTrial' => false,
            'paidThrough' => new DateTime('yesterday'),
            'startedOn' => new DateTime('30 days ago'),
            'expireOn' => new DateTime('today'),
        ],
        'testInGraceSubscription' => [
            '__construct' => ['sub_testInGraceSubscription', '@sally', '@premium'],
            'inTrial' => false,
            'paidThrough' => new DateTime('today'),
            'startedOn' => new DateTime('30 days ago'),
        ],
        'testChangePlan' => [
            '__construct' => ['sub_testChangePlan', '@bob', '@basic'],
            'inTrial' => false,
            'paidThrough' => new DateTime('1 days'),
        ],
        'testChangePlanNoBillingKey' => [
            '__construct' => ['sub_testChangePlanNoBillingKey', '@steve', '@free'],
            'inTrial' => false,
        ],
    ]
];
