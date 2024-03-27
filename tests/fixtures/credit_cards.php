<?php

return [
    'AktiveMerchant\Billing\CreditCard' => [
        'bogus_card' => [
            '__construct' => [
                [
                    'first_name' => 'Bob',
                    'last_name'  => 'Smith',
                    'month'      => '12',
                    'year'       => date('Y', strtotime('1 year')),
                    'type'       => 'bogus',
                    'number'     => '1',
                ]
            ]
        ],
        'bogus_card_fail' => [
            '__construct' => [
                [
                    'first_name' => 'John',
                    'last_name'  => 'Doe',
                    'month'      => '10',
                    'year'       => date('Y', strtotime('1 year')),
                    'type'       => 'bogus',
                    'number'     => '0',
                ]
            ]
        ],
        'bogus_card_exception' => [
            '__construct' => [
                [
                    'first_name' => 'John',
                    'last_name'  => 'Doe',
                    'month'      => '10',
                    'year'       => date('Y', strtotime('1 year')),
                    'type'       => 'bogus',
                    'number'     => '2',
                ]
            ]
        ],
    ]
];
