<?php
namespace paws\tests\records;

use yii\helpers\ArrayHelper;
use yii\db\ActiveRecord;
use paws\tests\UnitTester;
use paws\records\CollectionField;
use paws\records\CollectionType;

class CollectionFieldCest
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
        $model = new CollectionField;
        $I->assertEquals(['id', 'name', 'handle', 'config'], $model->attributes());
    }

    public function testLoad(UnitTester $I)
    {
        $model = new CollectionField;

        $data = [
            'CollectionField' => [
                'name' => 'name' . uniqid(), 
                'handle' => 'handle' . uniqid(),
                'config' => json_encode([uniqid(), uniqid(), uniqid()]),
            ],
        ];
        
        $I->assertFalse($model->load([]));
        $I->assertTrue($model->load($data));
        $I->assertEquals([
            'id' => null, 
            'name' => $data['CollectionField']['name'], 
            'handle' => $data['CollectionField']['handle'],
            'config' => $data['CollectionField']['config'],
        ], $model->attributes);
    }

    public function testCreate(UnitTester $I)
    {
        $field = new CollectionField;

        $I->assertFalse($field->save());

        $handle = 'handle' . uniqid();
        $name = 'name' . uniqid();
        $config = json_encode([uniqid(), uniqid(), uniqid()]);

        $data = [
            'handle' => 'handle' . uniqid(),
            'name' => 'name' . uniqid(),
            'config' => json_encode([uniqid(), uniqid(), uniqid()]),
        ];
        $field = new CollectionField($data);
        $I->assertTrue($field->save());
        $I->assertFalse($field->isNewRecord);
        $I->seeRecord(CollectionField::class, ArrayHelper::merge($data, ['id' => $field->id]));
    }

    public function testGetCollectionTypes(UnitTester $I)
    {
        $I->haveRecord(CollectionField::class, [
            'id' => 1,
            'handle' => 'handle' . uniqid(),
            'name' => 'name' . uniqid(),
            'config' => json_encode([uniqid(), uniqid(), uniqid()]),
        ]);
        
        $ids = [];
        for ($i = 1; $i <= 3; $i++)
        {
            $I->haveRecord(CollectionType::class, [
                'id' => $i,
                'name' => 'name' . uniqid(),
                'handle' => 'handle' . uniqid(),
            ]);
            
            $maping = new Class extends ActiveRecord
            {
                public static function tableName()
                {
                    return '{{%collection_type_field_map}}';
                }
            };

            $I->haveRecord(get_class($maping), [
                'collection_type_id' => $i,
                'collection_field_id' => 1,
            ]);

            $ids[] = $i;
        }

        $field = CollectionField::findOne(1);
        foreach ($field->collectionTypes as $i => $collectionType) $I->assertEquals($ids[$i], $collectionType->id);
    }
}
