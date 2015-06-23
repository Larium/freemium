<?php

class CreateSubscriptionPlansTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $t = $this->create_table('subscription_plans');
        $t->column('name', 'string');
        $t->column('cycle', 'integer');
        $t->column('rate', 'integer');

        $t->finish();
    }//up()

    public function down()
    {
        $this->drop_table('subscription_plans');
    }//down()
}
