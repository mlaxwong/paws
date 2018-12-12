<?php namespace paws\tests\behaviors;

use yii\db\QueryBuilder;
use yii\db\ActiveRecord;
use Paws;
use paws\tests\UnitTester;

class RelationLinkerBehaviorCest
{
    public function _before(UnitTester $I)
    {
        foreach ($this->getTables() as $tableName => $columns)
        {
            $sql = (new QueryBuilder(Paws::$app->db))->createTable($tableName, $columns);
            Paws::$app->db->createCommand($sql)->execute();
        }
    }

    public function _after(UnitTester $I)
    {
        foreach ($this->getTables() as $tableName => $columns)
        {
            $sql = (new QueryBuilder(Paws::$app->db))->dropTable($tableName);
            Paws::$app->db->createCommand($sql)->execute();
        }
    }

    // tests
    public function tryToTest(UnitTester $I)
    {
        $example0 = $this->getGetExample0ActiveRecord();
        $example0->save();
    }

    protected function getTables()
    {
        return [
            '{{%relation_linker_behavior_example_0}}' => [
                'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
                'name' => 'VARCHAR(255) NULL DEFAULT NULL',
                'example_0_id' => 'int(10) unsigned NULL DEFAULT NULL',
                'PRIMARY KEY (`id`)',
            ],
            '{{%relation_linker_behavior_example_1}}' => [
                'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
                'name' => 'VARCHAR(255) NULL DEFAULT NULL',
                'PRIMARY KEY (`id`)',
            ],
            '{{%relation_linker_behavior_example_map}}' => [
                'example_1_id' => 'int(10) unsigned NULL DEFAULT NULL',
                'example_2_id' => 'int(10) unsigned NULL DEFAULT NULL',
            ],
            '{{%relation_linker_behavior_example_2}}' => [
                'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
                'name' => 'VARCHAR(255) NULL DEFAULT NULL',
                'PRIMARY KEY (`id`)',
            ],
        ];
    }

    protected function getGetExample0ActiveRecord()
    {
        return new class extends ActiveRecord
        {
            public static function tableName()
            {
                return '{{%relation_linker_behavior_example_0}}';
            }
        };
    }
}
