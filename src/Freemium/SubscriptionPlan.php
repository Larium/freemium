<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

class SubscriptionPlan extends AbstractEntity
{
    use Rate;

    const YEARLY = 1;

    const MONTHLY = 2;

    protected $subscriptions = array();

    /**
     * Whether this plan cycles yearly or monthly
     *
     * @var integer
     * @access protected
     */
    protected $cycle;

    /**
     * The name of plan
     *
     * @var string
     * @access protected
     */
    protected $name;
}
