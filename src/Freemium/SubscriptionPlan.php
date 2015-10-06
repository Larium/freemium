<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use Doctrine\Common\Collections\ArrayCollection;

trait SubscriptionPlan
{
    use Rate;

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
     */
    protected $cycle;

    /**
     * The name of plan
     *
     * @var string
     */
    protected $name;

    public static $cycles = array(
        self::ANNUALLY      => 'years',
        self::BIANNUALLY    => '6 months',
        self::QUARTERLY     => '3 months',
        self::MONTHLY       => 'months',
        self::FORTNIGHTLY   => '2 weeks',
        self::WEEKLY        => 'weeks',
        self::DAILY         => 'days',
    );

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
        switch ($this->cycle) {
            case self::ANNUALLY:
                return $this->getRate() / 12;
            default:
                return $this->getRate();
                break;
        }
    }

    public function getCycleRelativeFormat($cycles)
    {
        $format = static::$cycles[$this->getCycle()];
        if (preg_match('/^(\d)+\s\w+/', $format, $m)) {
            $multiply = $m[1];
            $period = trim(str_replace($multiply, null, $format));
            return ($multiply * $cycles) . " {$period}";
        }
        return "{$cycles} {$format}";
    }

    /**
     * Get name.
     *
     * @return name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get cycle.
     *
     * @return cycle.
     */
    public function getCycle()
    {
        return $this->cycle;
    }
}
