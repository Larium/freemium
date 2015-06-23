<?php

class CreateSubscriptionTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $t = $this->create_table('subscriptions');
        $t->column('subscribable_id', 'integer');
        $t->column('subscribable_type', 'string');
        $t->column('subscription_plan_id', 'integer');
        $t->column('paid_through', 'date');
        $t->column('started_on', 'date');
        $t->column('billing_key', 'string');
        $t->column('last_transaction_at', 'datetime');
        $t->column('in_trial', 'boolean');
        $t->column('expire_on', 'date');
        $t->finish();

        $this->add_index('subscriptions', 'subscribable_id');
        $this->add_index('subscriptions', 'subscribable_type');
        $this->add_index('subscriptions', 'subscription_plan_id');
        $this->add_index('subscriptions', 'paid_through');
        $this->add_index('subscriptions', 'expire_on');
        $this->add_index('subscriptions', 'billing_key');
    }//up()

    public function down()
    {
        $this->drop_table('subscriptions');
    }//down()
}
