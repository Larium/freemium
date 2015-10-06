<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

use Model\SubscriptionPlan;

class SubscriptionPlanTest extends \PHPUnit_Framework_TestCase
{
    use Helper;

    /**
     * @dataProvider dataProvider
     */
    public function testCycleRelativeFormat($expected, $cycle, $cycles)
    {
        $plan = $this->build_subscription_plan([
            'cycle' => $cycle,
            'rate'  => 5000,
            'name'  => 'basic'
        ]);

        $r = $plan->getCycleRelativeFormat($cycles);

        $this->assertEquals($expected, $r);
    }

    public function dataProvider()
    {
        return array(
            array('1 years', SubscriptionPlanInterface::ANNUALLY, 1),
            array('2 years', SubscriptionPlanInterface::ANNUALLY, 2),
            array('6 months', SubscriptionPlanInterface::BIANNUALLY, 1),
            array('12 months', SubscriptionPlanInterface::BIANNUALLY, 2),
            array('3 months', SubscriptionPlanInterface::QUARTERLY, 1),
            array('6 months', SubscriptionPlanInterface::QUARTERLY, 2),
            array('1 months', SubscriptionPlanInterface::MONTHLY, 1),
            array('2 months', SubscriptionPlanInterface::MONTHLY, 2),
            array('2 weeks', SubscriptionPlanInterface::FORTNIGHTLY, 1),
            array('4 weeks', SubscriptionPlanInterface::FORTNIGHTLY, 2),
            array('1 weeks', SubscriptionPlanInterface::WEEKLY, 1),
            array('2 weeks', SubscriptionPlanInterface::WEEKLY, 2),
            array('1 days', SubscriptionPlanInterface::DAILY, 1),
            array('2 days', SubscriptionPlanInterface::DAILY, 2),
        );
    }
}
