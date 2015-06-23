<?php

class CreateCouponsTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $t = $this->create_table('coupons');
        $t->column('description', 'string');
        $t->column('discount_percentage', 'integer');
        $t->column('discount_flat', 'integer');
        $t->column('redemption_key', 'string');
        $t->column('redemption_limit', 'integer');
        $t->column('redemption_expiration', 'date');
        $t->column('duration_in_months', 'integer');
        $t->finish();

        $t = $this->create_table('coupons_subscription_plans', ['id'=>false]);
        $t->column('coupon_id', 'integer');
        $t->column('subscription_plan_id', 'integer');
        $t->finish();

        $this->add_index('coupons_subscription_plans', 'coupon_id');
        $this->add_index('coupons_subscription_plans', 'subscription_plan_id');
    }//up()

    public function down()
    {
        $this->drop_table('coupons');
        $this->drop_table('coupons_subscription_plans');
    }//down()
}
