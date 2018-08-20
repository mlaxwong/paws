<?php
namespace paws\migrations;

use paws\db\Migration;
use paws\helpers\MigrationHelper;

class M180813220127_create_table_group_map extends Migration
{
    public $tableName = 'group_map';

    public function safeUp()
    {
        $this->createTable(MigrationHelper::prefix($this->tableName), [
            'id' => $this->primaryKey()->unsigned(),
            'group_id' => $this->integer(11)->unsigned(),
            'model_class' => $this->string()->notNUll(),
            'model_id' => $this->integer(11)->unsigned(),
        ]);

        $this->addForeignKey(
            MigrationHelper::fk($this->tableName, 'group_id'),
            MigrationHelper::prefix($this->tableName), 'group_id',
            MigrationHelper::prefix('group'), 'id',
            'cascade', 'cascade'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey(MigrationHelper::fk($this->tableName, 'group_id'), MigrationHelper::prefix($this->tableName));
        $this->dropTable(MigrationHelper::prefix($this->tableName));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M180813220126_create_table_group cannot be reverted.\n";

        return false;
    }
    */
}
