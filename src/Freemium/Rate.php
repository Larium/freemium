<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use Exception;

trait Rate
{
    protected $rate;

    /**
     * Gets the daily cost in cents.
     * For available options @see Freemium\RateInterface::rate method.
     *
     * @param array $options
     * @access public
     * @return integer
     */
    public function getDailyRate(array $options = array())
    {
        return (int) floor($this->getYearlyRate($options) / 365);
    }

    /**
     * Gets the monthly cost in cents.
     * For available options @see Freemium\RateInterface::rate method.
     *
     * @param array $options
     * @access public
     * @return integer
     */
    public function getMonthlyRate(array $options = array())
    {
        try {
            return $this->rate($options);
        } catch (Exception $e) {
            return $this->rate;
        }
    }

    /**
     * Gets the yearly cost in cents.
     * For available options @see Freemium\RateInterface::rate method.
     *
     * @param array $options
     * @access public
     * @return integer
     */
    public function getYearlyRate(array $options = array())
    {
        try {
            return $this->rate($options) * 12;
        } catch (Exception $e) {
            return $this->rate;
        }
    }

    /**
     * Chack if object can be paid or not.
     *
     * @access public
     * @return boolean
     */
    public function isPaid()
    {
        if ($this->rate) {
            return $this->rate > 0;
        }

        return false;
    }
}
