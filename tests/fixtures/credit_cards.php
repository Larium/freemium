<?php

return array(
    'AktiveMerchant\Billing\CreditCard' => array(
        'bogus_card' => array(
            '__construct' => array(
                array(
                    'first_name' => 'Bob',
                    'last_name'  => 'Smith',
                    'month'      => '12',
                    'year'       => date('Y', strtotime('1 year')),
                    'type'       => 'bogus',
                    'number'     => '1',
                )
            )
        )
    )
);
