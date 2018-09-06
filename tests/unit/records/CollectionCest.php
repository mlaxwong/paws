<?php
namespace paws\tests\records;

use yii\helpers\ArrayHelper;
use paws\tests\UnitTester;
use paws\records\Collection;
use paws\records\CollectionType;
use paws\records\CollectionValue;
use paws\records\CollectionField;

class CollectionCest
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
        $model = new Collection;
        $I->assertEquals([
            'id',
            'collection_type_id',    
            'created_at',
            'updated_at',
        ], $model->attributes());
    }

    public function testLoad(UnitTester $I)
    {
        $model = new Collection;
        $I->assertFalse($model->load([]));
        $data = [
            'Collection' => [
                'collection_type_id' => uniqid(),
            ],
        ];
        $I->assertTrue($model->load($data));
        $I->assertEquals(ArrayHelper::merge((new Collection())->attributes, $data['Collection']), $model->attributes);
    }

    public function testCreate(UnitTester $I)
    {
        $collection = new Collection;
        $I->assertFalse($collection->save());
        unset($collection);
        
        $collectionType = new CollectionType(['name' => 'testing']);
        $I->assertTrue($collectionType->save());

        $data = [
            'collection_type_id' => $collectionType->id,
        ];
        $collection = new Collection($data);
        $I->assertEquals($collection->created_at, null);
        $I->assertEquals($collection->updated_at, null);
        $I->assertTrue($collection->save());
        $collection->refresh();
        $I->assertNotEquals($collection->created_at, null);
        $I->assertNotEquals($collection->updated_at, null);
        $I->assertEquals(ArrayHelper::merge(
            (new Collection())->attributes, 
            $data,
            ['id' => $collection->id, 'created_at' => $collection->created_at, 'updated_at' => $collection->updated_at]
        ), $collection->attributes);
        $I->assertEquals($collectionType->id, $collection->collectionType->id);

        $values = [];
        for ($i = 0; $i < 3; $i++)
        {
            $field = new CollectionField([
                'name' => uniqid(), 
                'handle' => uniqid(), 
                'config' => json_encode([uniqid()])
            ]);
            $I->assertTrue($field->save());
            $value = new CollectionValue([
                'collection_id' => $collection->id,
                'collection_field_id' => $field->id,
                'value' => 'value' . uniqid(),
            ]);
            $I->assertTrue($value->save());
            $values[] = $value;
        }
        foreach ($values as $value) $collection->link('collectionValues', $value);
        foreach ($collection->collectionValues as $i => $value) $I->assertEquals($values[$i]->id, $value->id);
    }
}
