<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use Model\SubscriptionPlan;
use Freemium\SubscriptionPlanInterface as Plan;

class SubscriptionPlanTest extends \PHPUnit_Framework_TestCase
{
    use Helper;

    /**
     * @dataProvider dataProvider
     */
    public function testCycleRelativeFormat($expected, $period, $frequency)
    {
        $plan = $this->build_subscription_plan([
            'period' => $period,
            'frequency' => $frequency,
            'rate'  => 5000,
            'name'  => 'basic'
        ]);

        $r = $plan->getCycleRelativeFormat();

        $this->assertEquals($expected, $r);
    }

    public function dataProvider()
    {
        return array(
            array('1 years', Plan::PERIOD_YEAR, 1),
            array('2 years', Plan::PERIOD_YEAR, 2),
            array('6 months', Plan::PERIOD_MONTH, 6),
            array('12 months', Plan::PERIOD_MONTH, 12),
            array('3 months', Plan::PERIOD_MONTH, 3),
            array('1 months', Plan::PERIOD_MONTH, 1),
            array('2 months', Plan::PERIOD_MONTH, 2),
            array('2 weeks', Plan::PERIOD_WEEK, 2),
            array('4 weeks', Plan::PERIOD_WEEK, 4),
            array('1 weeks', Plan::PERIOD_WEEK, 1),
            array('2 weeks', Plan::PERIOD_WEEK, 2),
            array('1 days', Plan::PERIOD_DAY, 1),
            array('2 days', Plan::PERIOD_DAY, 2),
        );
    }
}
