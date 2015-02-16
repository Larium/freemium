<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use Exception;

trait Rate
{
    protected $rate;

    public function getDailyRate(array $options = array())
    {
        return $this->getYearlyRate($options) / 365;
    }

    public function getMonthlyRate(array $options = array())
    {
        try {
            return $this->rate($options);
        } catch (Exception $e) {
            return $this->rate;
        }
    }

    public function getYearlyRate(array $options = array())
    {
        try {
            return $this->rate($options) * 12;
        } catch (Exception $e) {
            return $this->rate;
        }
    }

    public function isPaid()
    {
        if ($this->rate) {
            return $this->rate > 0;
        }

        return false;
    }
}
