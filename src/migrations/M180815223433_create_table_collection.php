<?php
namespace paws\migrations;

use paws\db\Migration;
use paws\helpers\MigrationHelper;

class M180815223433_create_table_collection extends Migration
{
    public $tableName = 'collection';

    public function safeUp()
    {
        $this->createTable(MigrationHelper::prefix($this->tableName), [
            'id' => $this->primaryKey()->unsigned(),
            'collection_type_id' => $this->integer(11)->unsigned(),
            // 'name' => $this->string(256)->notNull(),
            // 'handle' => $this->string(256)->notNull(),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->addForeignKey(
            MigrationHelper::fk($this->tableName, 'collection_type_id'),
            MigrationHelper::prefix($this->tableName), 'collection_type_id',
            MigrationHelper::prefix('collection_type'), 'id',
            'cascade', 'cascade'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey(MigrationHelper::fk($this->tableName, 'collection_type_id'), MigrationHelper::prefix($this->tableName));
        $this->dropTable(MigrationHelper::prefix($this->tableName));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M180815223433_create_table_collection cannot be reverted.\n";

        return false;
    }
    */
}
