<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

class Coupon extends AbstractEntity
{
    /**
     * description
     *
     * @var string
     * @access protected
     */
    protected $description;

    /**
     * discount_percentage
     *
     * @var integer
     * @access protected
     */
    protected $discount_percentage;

    /**
     * redemption_key
     *
     * @var string
     * @access protected
     */
    protected $redemption_key;

    /**
     * redemption_limit
     *
     * @var integer
     * @access protected
     */
    protected $redemption_limit;

    /**
     * redemption_expiration
     *
     * @var DateTime
     * @access protected
     */
    protected $redemption_expiration;

    /**
     * duration_in_months
     *
     * @var integer
     * @access protected
     */
    protected $duration_in_months;
}
