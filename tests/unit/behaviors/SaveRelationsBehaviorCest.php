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
    public function testCreateByArray(UnitTester $I)
    {
        $model = $this->getGetExample0ActiveRecord();
        $model->name = '112';
        $model->child = ['name' => 'tes'];
        $model->children = [
            ['name' => '123'],
            ['name' => '456'],
        ];
        $I->assertTrue($model->save());
        $I->assertEquals('112', $model->name);
        $I->assertEquals('tes', $model->child->name);
        $I->assertEquals(['123', '456'], ArrayHelper::getColumn($model->children, 'name'));
    }

    public function testCreateByJson(UnitTester $I)
    {
        $model = $this->getGetExample0ActiveRecord();
        $model->name = '112';
        $model->child = json_encode(['name' => 'tes']);
        $model->children = json_encode([
            ['name' => '123'],
            ['name' => '456'],
        ]);
        $I->assertTrue($model->save());
        $I->assertEquals('112', $model->name);
        $I->assertEquals('tes', $model->child->name);
        $I->assertEquals(['123', '456'], ArrayHelper::getColumn($model->children, 'name'));
    }

    public function testCreateById(UnitTester $I)
    {
        $child1 = $this->getGetExample0ActiveRecord();
        $child1->name = 'tes';
        $I->assertTrue($child1->save());

        $child2 = $this->getGetExample0ActiveRecord();
        $child2->name = '123';
        $I->assertTrue($child2->save());

        $child3 = $this->getGetExample0ActiveRecord();
        $child3->name = '456';
        $I->assertTrue($child3->save());

        $model = $this->getGetExample0ActiveRecord();
        $model->name = '112';
        $model->child = $child1->id;
        $model->children = [$child2->id, $child3->id];
        $I->assertTrue($model->save());
        $I->assertEquals('112', $model->name);
        $I->assertEquals('tes', $model->child->name);
        $I->assertEquals(['123', '456'], ArrayHelper::getColumn($model->children, 'name'));
    }

    public function testDefaultValue(UnitTester $I)
    {
        $model = $this->getGetExample1ActiveRecord();
        $model->name = '112';
        $model->child = json_encode(['name' => 'tes']);
        $model->children = [
            ['name' => '123'],
            ['name' => '456'],
        ];
        $I->assertTrue($model->save());
        foreach ($model->children as $child) $I->assertEquals('this is default value', $child->description);
    }

    protected function getTables()
    {
        return [
            '{{%save_relations_behavior_example_0}}' => [
                'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
                'name' => 'VARCHAR(256) NULL DEFAULT NULL',
                'description' => 'VARCHAR(516) NULL DEFAULT NULL',
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

    protected function getGetExample1ActiveRecord()
    {
        return new class extends ActiveRecord
        {
            public function behaviors()
            {
                return [
                    'saveRelations' => [
                        'class' => SaveRelationsBehavior::class,
                        'relations' => [
                            'child' => [
                                'defaultValues' => [
                                    'description' => 'this is default value',  
                                ],
                            ], 
                            'children' => [
                                'defaultValues' => [
                                    'description' => 'this is default value',  
                                ],
                            ],
                        ],
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
                    [['description'], 'string'],
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
