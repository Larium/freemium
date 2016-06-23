<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

return array(
    'Freemium\Subscription' => array(
        'testDowngradeToPaid' => array(
            'subscription_plan' => '@premium',
            'subscribable' => '@bob',
            'in_trial' => false,
            'billing_key' => 1,
            'credit_card' => '@bogus_card',
            'paid_through' => new DateTime('15 days'),
        ),
        'testRemainingAmountForYearlyPlan' => array(
            'subscription_plan' => '@premium',
            'subscribable' => '@bob',
            'in_trial' => false,
            'billing_key' => 1,
            'credit_card' => '@bogus_card',
            'paid_through' => new DateTime('15 days'),
        ),
        'testRemainingAmountForMonthlyPlan' => array(
            'subscription_plan' => '@basic',
            'subscribable' => '@bob',
            'in_trial' => false,
            'billing_key' => 1,
            'credit_card' => '@bogus_card',
            'paid_through' => new DateTime('15 days'),
        ),
        'testApplyCoupon' => array(
            'subscription_plan' => '@basic',
            'subscribable' => '@bob',
            'in_trial' => false,
            'billing_key' => 1,
            'credit_card' => '@bogus_card',
            'paid_through' => new DateTime('30 days'),
        ),
        'testChargePaidSubscription' => array(
            'subscription_plan' => '@premium',
            'subscribable' => '@bob',
            'in_trial' => false,
            'billing_key' => 1,
            'credit_card' => '@bogus_card',
            'paid_through' => new DateTime('today'),
            'started_on' => new DateTime('30 days ago'),
        ),
        'testSetToExpire' => array(
            'subscription_plan' => '@premium',
            'subscribable' => '@bob',
            'in_trial' => false,
            'billing_key' => 0,
            'credit_card' => '@bogus_card',
            'paid_through' => new DateTime('today'),
            'started_on' => new DateTime('30 days ago'),
        ),
        'testExpiration' => array(
            'subscription_plan' => '@premium',
            'subscribable' => '@bob',
            'in_trial' => false,
            'billing_key' => 0,
            'credit_card' => '@bogus_card',
            'paid_through' => new DateTime('yesterday'),
            'started_on' => new DateTime('30 days ago'),
            'expire_on' => new DateTime('today'),
        ),
    )
);
