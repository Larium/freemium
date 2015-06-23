<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use Doctrine\Common\Collections\ArrayCollection;

class SubscriptionPlan extends AbstractEntity implements RateInterface
{
    use Rate;

    const YEARLY = 1;

    const MONTHLY = 2;

    protected $subscriptions;

    /**
     * Coupons for this subscription plan
     *
     * @var ArrayCollection<Coupon>
     */
    protected $coupons;

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

    public function __construct()
    {
        $this->subscriptions = new ArrayCollection();
        $this->coupons       = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function rate(array $options = array())
    {
        return $this->getRate();
    }
}
