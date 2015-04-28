<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium\Observer;

use SplObserver;
use SplSubject;

class SubscriptionObserver implements SplObserver
{
    public function update(SplSubject $subscription)
    {
        switch (true) {
            case $subscription->isInGrace():

                # TODO: notify that subscription is set for expiration via email.

                break;
            case $subscription->isExpired():

                # TODO: notify the expiration via email.

                break;

            default:

                break;
        }
    }
}
