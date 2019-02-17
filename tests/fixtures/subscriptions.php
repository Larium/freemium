<?php

return array(
    'Freemium\Subscription' => array(
        'testDowngradeToPaid' => array(
            '__construct' => ['@bob', '@premium'],
            'in_trial' => false,
            'paid_through' => new DateTime('15 days'),
        ),
        'testRemainingAmountForYearlyPlan' => array(
            '__construct' => ['@bob', '@premium'],
            'in_trial' => false,
            'paid_through' => new DateTime('15 days'),
        ),
        'testRemainingAmountForMonthlyPlan' => array(
            '__construct' => ['@bob', '@basic'],
            'in_trial' => false,
            'paid_through' => new DateTime('15 days'),
        ),
        'testApplyCoupon' => array(
            '__construct' => ['@bob', '@basic'],
            'in_trial' => false,
            'paid_through' => new DateTime('30 days'),
        ),
        'testChargePaidSubscription' => array(
            '__construct' => ['@bob', '@premium'],
            'in_trial' => false,
            'paid_through' => new DateTime('today'),
            'started_on' => new DateTime('30 days ago'),
        ),
        'testSetToExpire' => array(
            '__construct' => ['@sally', '@premium'],
            'in_trial' => false,
            'paid_through' => new DateTime('today'),
            'started_on' => new DateTime('30 days ago'),
        ),
        'testExpiration' => array(
            '__construct' => ['@sally', '@premium'],
            'in_trial' => false,
            'paid_through' => new DateTime('yesterday'),
            'started_on' => new DateTime('30 days ago'),
            'expire_on' => new DateTime('today'),
        ),
        'testInGraceSubscription' => array(
            '__construct' => ['@sally', '@premium'],
            'in_trial' => false,
            'paid_through' => new DateTime('today'),
            'started_on' => new DateTime('30 days ago'),
        ),
        'testChangePlan' => array(
            '__construct' => ['@bob', '@basic'],
            'in_trial' => false,
            'paid_through' => new DateTime('1 days'),
        ),
        'testChangePlanNoBillingKey' => array(
            '__construct' => ['@steve', '@free'],
            'in_trial' => false,
        ),
    )
);
