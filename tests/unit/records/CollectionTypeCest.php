<?php
namespace paws\tests\records;

use paws\tests\UnitTester;
use paws\records\Collection;
use paws\records\CollectionType;
use paws\records\CollectionValue;
use paws\records\CollectionField;

class CollectionTypeCest
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
        $model = new CollectionType;
        $I->assertEquals(['id', 'name', 'handle'], $model->attributes());
    }

    public function testLoad(UnitTester $I)
    {
        $model = new CollectionType;
        $I->assertFalse($model->load([]));

        $data = ['CollectionType' => ['name' => 'name' . uniqid(), 'handle' => 'handle' . uniqid()]];
        $I->assertTrue($model->load($data));
        $I->assertEquals(['id' => null, 'name' => $data['CollectionType']['name'], 'handle' => $data['CollectionType']['handle']], $model->attributes);
    }

    public function testCreate(UnitTester $I)
    {
        // save emtpy
        $collectionType = new CollectionType;
        $I->assertFalse($collectionType->save());
        unset($collectionType);

        // create collection type
        $name = 'name' . uniqid();
        $handle = 'handle' . uniqid();
        $collectionType = new CollectionType(compact('name', 'handle'));
        $I->assertTrue($collectionType->save());

        // create collections
        $collections = [];
        for ($i = 0; $i < 3; $i++)
        {
            $data = [
                'collection_type_id' => $collectionType->id,
            ];
            $collection = new Collection($data);
            $I->assertTrue($collection->save());
            $collections[] = $collection;
        }
        // link all collection
        foreach ($collections as $collection) $collectionType->link('collections', $collection);
        foreach ($collectionType->collections as $i => $collection) $I->assertEquals($collections[$i]->id, $collection->id);

        // create fields
        $fields = [];
        for ($i = 0; $i < 3; $i++)
        {
            $data = [
                'name' => uniqid(), 
                'handle' => uniqid(), 
                'config' => json_encode([uniqid()])
            ];
            $field = new CollectionField($data);
            $I->assertTrue($field->save());
            $fields[] = $field;
        }
        foreach ($fields as $field) $collectionType->link('fields', $field);
        foreach ($collectionType->fields as $i => $field) $I->assertEquals($fields[$i]->id, $field->id);
    }
}
