<?php
namespace paws\tests\records;

use yii\helpers\ArrayHelper;
use paws\tests\UnitTester;
use paws\records\CollectionValue;
use paws\records\CollectionField;
use paws\records\Collection;
use paws\records\CollectionType;

class CollectionValueCest
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
        $model = new CollectionValue;
        $I->assertEquals(['id', 'collection_id', 'collection_field_id', 'value'], $model->attributes());
    }

    public function testLoad(UnitTester $I)
    {
        $model = new CollectionValue;
        $I->assertFalse($model->load([]));
        $data = [
            'CollectionValue' => [
                'collection_id' => rand(1, 10),
                'collection_field_id' => rand(1, 10),
                'value' => 'value' . uniqid(),
            ],
        ];
        $I->assertTrue($model->load($data));
        $I->assertEquals(ArrayHelper::merge((new CollectionValue)->attributes, $data['CollectionValue']), $model->attributes);
    }

    public function testSave(UnitTester $I)
    {
        $collectionValue = new CollectionValue;
        $I->assertFalse($collectionValue->save());
        unset($collectionValue);

        $field = new CollectionField(['name' => 'testing', 'handle' => 'testing']);
        $I->assertTrue($field->save());

        $name = 'name' . uniqid();
        $handle = 'handle' . uniqid();
        $collectionType = new CollectionType(compact('name', 'handle'));
        $I->assertTrue($collectionType->save());

        $collection = new Collection([
            'collection_type_id' => $collectionType->id,
        ]);
        $I->assertTrue($collection->save());

        $data = [
            'collection_id' => $collection->id,
            'collection_field_id' => $field->id,
            'value' => 'value' . uniqid(),
        ];
        $collectionValue = new CollectionValue($data);
        $I->assertTrue($collectionValue->save());
        $I->assertEquals(ArrayHelper::merge($data, ['id' => $collectionValue->id]), $collectionValue->attributes);
        $I->assertEquals($field->id, $collectionValue->field->id);
        $I->assertEquals($collection->id, $collectionValue->collection->id);
    }
}
