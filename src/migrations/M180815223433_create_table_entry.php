<?php
namespace paws\migrations;

use paws\db\Migration;
use paws\helpers\MigrationHelper;

class M180815223433_create_table_entry extends Migration
{
    public $tableName = 'entry';

    public function safeUp()
    {
        $this->createTable(MigrationHelper::prefix($this->tableName), [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(256)->noNull(),
            'handle'=> $this->string(64)->notNUll(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable(MigrationHelper::prefix($this->tableName));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M180815223433_create_table_entry cannot be reverted.\n";

        return false;
    }
    */
}
