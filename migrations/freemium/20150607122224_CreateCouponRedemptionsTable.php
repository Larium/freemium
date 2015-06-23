<?php

class CreateCouponRedemptionsTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $t = $this->create_table('coupon_redemptions');
        $t->column('coupon_id', 'integer');
        $t->column('subscription_id', 'integer');
        $t->column('redeemed_on', 'date');
        $t->column('expired_on', 'date');
        $t->finish();

        $this->add_index('coupon_redemptions', 'coupon_id');
        $this->add_index('coupon_redemptions', 'subscription_id');
    }//up()

    public function down()
    {
        $this->drop_table('coupon_redemptions');
    }//down()
}
