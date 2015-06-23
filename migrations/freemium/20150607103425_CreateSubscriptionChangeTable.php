<?php

class CreateSubscriptionChangeTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $t = $this->create_table('subscription_changes');
        $t->column('subsciption_id', 'integer');
        $t->column('original_subscription_plan_id', 'integer');
        $t->column('original_rate', 'integer');
        $t->column('new_subscription_plan_id', 'integer');
        $t->column('new_rate', 'integer');
        $t->column('reason', 'integer', array('null'=>false));
        $t->column('created_at', 'datetime');
        $t->finish();

        $this->add_index('subscription_changes', 'subsciption_id');
        $this->add_index('subscription_changes', 'original_subscription_plan_id');
        $this->add_index('subscription_changes', 'new_subscription_plan_id');
        $this->add_index('subscription_changes', 'reason');
    }//up()

    public function down()
    {
        $this->drop_table('subscription_changes');
    }//down()
}
