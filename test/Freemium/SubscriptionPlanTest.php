<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium;

class SubscriptionPlanTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testCycleRelativeFormat($expected, $cycle, $cycles)
    {
        $plan = new SubscriptionPlan();
        $plan->setProperties([
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
            array('1 years', SubscriptionPlan::ANNUALLY, 1),
            array('2 years', SubscriptionPlan::ANNUALLY, 2),
            array('6 months', SubscriptionPlan::BIANNUALLY, 1),
            array('12 months', SubscriptionPlan::BIANNUALLY, 2),
            array('3 months', SubscriptionPlan::QUARTERLY, 1),
            array('6 months', SubscriptionPlan::QUARTERLY, 2),
            array('1 months', SubscriptionPlan::MONTHLY, 1),
            array('2 months', SubscriptionPlan::MONTHLY, 2),
            array('2 weeks', SubscriptionPlan::FORTNIGHTLY, 1),
            array('4 weeks', SubscriptionPlan::FORTNIGHTLY, 2),
            array('1 weeks', SubscriptionPlan::WEEKLY, 1),
            array('2 weeks', SubscriptionPlan::WEEKLY, 2),
            array('1 days', SubscriptionPlan::DAILY, 1),
            array('2 days', SubscriptionPlan::DAILY, 2),
        );
    }
}
