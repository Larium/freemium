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
     * How much this plan costs, in cents
     *
     * @var integer
     * @access protected
     */
    protected $rate_cents;

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

    public function getRate()
    {
        return $this->rate_cents;
    }
}
