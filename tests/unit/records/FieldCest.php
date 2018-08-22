<?php
namespace paws\tests\records;

use paws\tests\UnitTester;
use paws\records\Field;

class FieldCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function testLoad(UnitTester $I)
    {
        $model = new Field;

        $data = [
            'Field' => [
                'name' => 'name' . uniqid(), 
                'handle' => 'handle' . uniqid(),
            ],
        ];
        
        $I->assertFalse($model->load([]));
        $I->assertEquals(['id' => null, 'name' => null, 'handle' => null], $model->attributes);
        $I->assertTrue($model->load($data));
        $I->assertEquals(['id' => null, 'name' => $data['Field']['name'], 'handle' => $data['Field']['handle']], $model->attributes);
    }

    public function testCreate(UnitTester $I)
    {
        $model = new Field;

        $I->assertFalse($model->save());

        $handle = 'handle' . uniqid();
        $name = 'name' . uniqid();
        $model = new Field(compact('name', 'handle'));
        $I->assertTrue($model->save());
        $I->assertFalse($model->isNewRecord);
        $I->seeRecord(Field::class, ['id' => $model->id, 'name' => $name, 'handle' => $handle]);
    }
}
