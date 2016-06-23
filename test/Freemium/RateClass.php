<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

class RateClass implements RateInterface
{
    use Rate;

    public function __construct($rate = null)
    {
        $this->rate = null === $rate ? 1000 : $rate; # 10 dollars
    }

    public function rate(array $options = array())
    {
        return $this->rate;
    }
}
