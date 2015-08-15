<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium\Observer;

use SplObserver;
use SplSubject;
use DateTime;

class SubscriptionObserver implements SplObserver
{
    public function update(SplSubject $subscription)
    {
        switch (true) {
            case $subscription->isInGrace():

                # TODO: notify that subscription is set for expiration via email.
                # echo 'NOTIFY: Subscription is expiring!';

                break;
            case $subscription->isExpired():

                # TODO: notify the expiration via email.
                # echo 'NOTIFY: Subscription expired!';

                break;

            case $subscription->getLastTransactionAt()->format('Y-m-d') == (new DateTime('today'))->format('Y-m-d'):

                # TODO: notify that payment received.

                # echo 'NOTIFY: Payment received!';
                break;
        }
    }
}
