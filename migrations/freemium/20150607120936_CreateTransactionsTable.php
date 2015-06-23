<?php

class CreateTransactionsTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $t = $this->create_table('transactions');
        $t->column('success', 'boolean');
        $t->column('billing_key', 'string');
        $t->column('amount', 'integer');
        $t->column('message', 'string');
        $t->column('created_at', 'datetime');
        $t->column('subsciption_id', 'integer');
        $t->finish();

        $this->add_index('transactions', 'subsciption_id');
    }//up()

    public function down()
    {
        $this->drop_table('transactions');
    }//down()
}
