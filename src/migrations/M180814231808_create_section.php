<?php
namespace paws\migrations;

use paws\db\Migration;
use paws\helpers\MigrationHelper;

class M180814231808_create_section extends Migration
{
    public $tableName = 'section';

    public function safeUp()
    {
        $this->createTable(MigrationHelper::prefix($this->tableName), [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(64)->notNull(),
            'handle' => $this->string()->notNull(),
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
        echo "M180814231808_create_section cannot be reverted.\n";

        return false;
    }
    */
}