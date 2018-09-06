<?php
namespace paws\migrations;

use paws\db\Migration;
use paws\helpers\MigrationHelper;

class M180822132240_create_table_collection_value extends Migration
{
    public $tableName = 'collection_value';

    public function safeUp()
    {
        $this->createTable(MigrationHelper::prefix($this->tableName), [
            'id' => $this->primaryKey()->unsigned(),
            'collection_id' => $this->integer(11)->unsigned(),
            'collection_field_id' => $this->integer(11)->unsigned(),
            'value' => $this->text()->defaultValue(null),
        ]);

        $this->addForeignKey(
            MigrationHelper::fk($this->tableName, 'collection_id'),
            MigrationHelper::prefix($this->tableName), 'collection_id',
            MigrationHelper::prefix('collection'), 'id',
            'cascade', 'cascade'
        );

        $this->addForeignKey(
            MigrationHelper::fk($this->tableName, 'collection_field_id'),
            MigrationHelper::prefix($this->tableName), 'collection_field_id',
            MigrationHelper::prefix('collection_field'), 'id',
            'cascade', 'cascade'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey(MigrationHelper::fk($this->tableName, 'collection_field_id'), MigrationHelper::prefix($this->tableName));
        $this->dropForeignKey(MigrationHelper::fk($this->tableName, 'collection_id'), MigrationHelper::prefix($this->tableName));
        $this->dropTable(MigrationHelper::prefix($this->tableName));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M180822132240_create_table_collection_value cannot be reverted.\n";

        return false;
    }
    */
}
