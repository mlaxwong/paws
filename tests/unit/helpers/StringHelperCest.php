<?php namespace paws\tests\helpers;

use yii\db\QueryBuilder;
use yii\db\ActiveRecord;
use Paws;
use paws\tests\UnitTester;
use paws\helpers\StringHelper;

class StringHelperCest
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
    public function testStrtrWithArrayParams(UnitTester $I)
    {
        $message = 'My address is {address1} {address2}.';
        $params = [
            'address1' => 'address one', 
            'address2' => 'address two', 
        ];
        $I->assertEquals('My address is address one address two.', StringHelper::strtr($message, $params));
    }

    public function testStrtrWithObjectParams(UnitTester $I)
    {
        $message = 'My address is {address1} {address2}.';
        $object = new class 
        {
            public $address1 = 'address one';
            public $address2 = 'address two';
        };
        $I->assertEquals('My address is address one address two.', StringHelper::strtr($message, $object));
    }

    public function testStrtrWithActiveRecord(UnitTester $I)
    {
        $message = '{name} - {description}';
        $model = $this->getGetExample0ActiveRecord();
        $model->name = 'this is name';
        $model->description = 'this is description';
        $I->assertTrue($model->save());
        $I->assertEquals('this is name - this is description', StringHelper::strtr($message, $model));
    }

    public function testStrtrWithActiveRecordRelation(UnitTester $I)
    {
        $message = '{child.name} - {child.description}';
        $childModel = $this->getGetExample0ActiveRecord();
        $childModel->name = 'this is child name';
        $childModel->description = 'this is child description';
        $I->assertTrue($childModel->save());

        $model = $this->getGetExample0ActiveRecord();
        $model->name = 'this is name';
        $model->description = 'this is description';
        $model->example_0_id = $childModel->id;
        $I->assertTrue($model->save());
        
        $I->assertEquals('this is child name - this is child description', StringHelper::strtr($message, $model));
    }

    public function testStrtrWithExtraString(UnitTester $I)
    {
        $childModel = $this->getGetExample0ActiveRecord();
        $childModel->name = 'this is child name';
        $childModel->description = 'this is child description';
        $I->assertTrue($childModel->save());

        $model = $this->getGetExample0ActiveRecord();
        $model->name = 'this is name';
        $model->description = '';
        $model->example_0_id = $childModel->id;
        $I->assertTrue($model->save());

        $messages = [
            '{abc} {def}' => ' ',
            '{name}' => 'this is name',
            '{name| - }{description|.}' => 'this is name - ',
            '{description|.}{name| - }' => 'this is name - ',
            '{child.name| - }{child.description}' => 'this is child name - this is child description',
        ];

        foreach ($messages as $message => $expected)
        {
            $I->assertEquals($expected, StringHelper::strtr($message, $model));
        }
        
    }

    protected function getTables()
    {
        return [
            '{{%string_helper_example_0}}' => [
                'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
                'name' => 'VARCHAR(256) NULL DEFAULT NULL',
                'description' => 'VARCHAR(516) NULL DEFAULT NULL',
                'example_0_id' => 'int(10) unsigned NULL DEFAULT NULL',
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
                return '{{%string_helper_example_0}}';
            }

            public function rules()
            {
                return [
                    [['name'], 'required'],
                    [['name', 'description'], 'string'],
                    [['example_0_id'], 'integer'],
                ];
            }

            public function getChild()
            {
                return $this->hasOne(self::class, ['id' => 'example_0_id']);
            }

            public function formName()
            {
                return 'save_relations_behavior_example_0';
            }
        };
    }
}
