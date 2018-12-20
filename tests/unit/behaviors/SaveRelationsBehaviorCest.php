<?php namespace paws\tests\behaviors;

use yii\db\QueryBuilder;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use Paws;
use paws\tests\UnitTester;
use paws\behaviors\SaveRelationsBehavior;

class SaveRelationsBehaviorCest
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
        $model1 = $this->getGetExample0ActiveRecord();
        $model1->name = '112';
        $model1->child = ['name' => 'tes'];
        $model1->children = [
            ['name' => '123'],
            ['name' => '456'],
        ];
        $I->assertTrue($model1->save());
        $I->assertEquals('112', $model1->name);
        $I->assertEquals('tes', $model1->child->name);
        $I->assertEquals(['123', '456'], ArrayHelper::getColumn($model1->children, 'name'));
    }

    protected function getTables()
    {
        return [
            '{{%save_relations_behavior_example_0}}' => [
                'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
                'name' => 'VARCHAR(255) NULL DEFAULT NULL',
                'example_0_id' => 'int(10) unsigned NULL DEFAULT NULL',
                'PRIMARY KEY (`id`)',
            ],
            '{{%save_relations_behavior_example_0_map}}' => [
                'example_0_parent_id' => 'int(10) unsigned NULL DEFAULT NULL',
                'example_0_child_id' => 'int(10) unsigned NULL DEFAULT NULL',
            ],
            '{{%save_relations_behavior_example_1}}' => [
                'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
                'name' => 'VARCHAR(255) NULL DEFAULT NULL',
                'PRIMARY KEY (`id`)',
            ],
            '{{%save_relations_behavior_example_map}}' => [
                'example_1_id' => 'int(10) unsigned NULL DEFAULT NULL',
                'example_2_id' => 'int(10) unsigned NULL DEFAULT NULL',
            ],
            '{{%save_relations_behavior_example_2}}' => [
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
            public function behaviors()
            {
                return [
                    'saveRelations' => [
                        'class' => SaveRelationsBehavior::class,
                        'relations' => ['child', 'children'],
                    ],
                ];  
            }

            public static function tableName()
            {
                return '{{%save_relations_behavior_example_0}}';
            }

            public function rules()
            {
                return [
                    [['name'], 'required'],
                    [['name'], 'string', 'max' => 3],
                    [['example_0_id'], 'integer'],
                ];
            }

            public function getChild()
            {
                return $this->hasOne(self::class, ['id' => 'example_0_id']);
            }


            public function getChildren()
            {
                return $this->hasMany(self::class, ['id' => 'example_0_child_id'])->viaTable('{{%save_relations_behavior_example_0_map}}', ['example_0_parent_id' => 'id']);
            }

            public function formName()
            {
                return 'test';
            }
        };
    }
}
