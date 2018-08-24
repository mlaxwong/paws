<?php
namespace paws\tests\records;

use yii\helpers\ArrayHelper;
use yii\db\ActiveRecord;
use paws\tests\UnitTester;
use paws\records\Field;
use paws\records\EntryType;

class FieldCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function testAttributes(UnitTester $I)
    {
        $model = new Field;
        $I->assertEquals(['id', 'name', 'handle', 'config'], $model->attributes());
    }

    public function testLoad(UnitTester $I)
    {
        $model = new Field;

        $data = [
            'Field' => [
                'name' => 'name' . uniqid(), 
                'handle' => 'handle' . uniqid(),
                'config' => json_encode([uniqid(), uniqid(), uniqid()]),
            ],
        ];
        
        $I->assertFalse($model->load([]));
        $I->assertTrue($model->load($data));
        $I->assertEquals([
            'id' => null, 
            'name' => $data['Field']['name'], 
            'handle' => $data['Field']['handle'],
            'config' => $data['Field']['config'],
        ], $model->attributes);
    }

    public function testCreate(UnitTester $I)
    {
        $field = new Field;

        $I->assertFalse($field->save());

        $handle = 'handle' . uniqid();
        $name = 'name' . uniqid();
        $config = json_encode([uniqid(), uniqid(), uniqid()]);

        $data = [
            'handle' => 'handle' . uniqid(),
            'name' => 'name' . uniqid(),
            'config' => json_encode([uniqid(), uniqid(), uniqid()]),
        ];
        $field = new Field($data);
        $I->assertTrue($field->save());
        $I->assertFalse($field->isNewRecord);
        $I->seeRecord(Field::class, ArrayHelper::merge($data, ['id' => $field->id]));
    }

    public function testGetEntryTypes(UnitTester $I)
    {
        $I->haveRecord(Field::class, [
            'id' => 1,
            'handle' => 'handle' . uniqid(),
            'name' => 'name' . uniqid(),
            'config' => json_encode([uniqid(), uniqid(), uniqid()]),
        ]);
        
        $ids = [];
        for ($i = 1; $i <= 3; $i++)
        {
            $I->haveRecord(EntryType::class, [
                'id' => $i,
                'name' => 'name' . uniqid(),
            ]);
            
            $maping = new Class extends ActiveRecord
            {
                public static function tableName()
                {
                    return '{{%entry_type_field_map}}';
                }
            };

            $I->haveRecord(get_class($maping), [
                'entry_type_id' => $i,
                'field_id' => 1,
            ]);

            $ids[] = $i;
        }

        $field = Field::findOne(1);
        foreach ($field->entryTypes as $i => $entryType) $I->assertEquals($ids[$i], $entryType->id);
    }
}
