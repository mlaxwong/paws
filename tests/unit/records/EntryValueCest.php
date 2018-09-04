<?php
namespace paws\tests\records;

use yii\helpers\ArrayHelper;
use paws\tests\UnitTester;
use paws\records\EntryValue;
use paws\records\Field;
use paws\records\Entry;
use paws\records\EntryType;

class EntryValueCest
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
        $model = new EntryValue;
        $I->assertEquals(['id', 'entry_id', 'field_id', 'value'], $model->attributes());
    }

    public function testLoad(UnitTester $I)
    {
        $model = new EntryValue;
        $I->assertFalse($model->load([]));
        $data = [
            'EntryValue' => [
                'entry_id' => rand(1, 10),
                'field_id' => rand(1, 10),
                'value' => 'value' . uniqid(),
            ],
        ];
        $I->assertTrue($model->load($data));
        $I->assertEquals(ArrayHelper::merge((new EntryValue)->attributes, $data['EntryValue']), $model->attributes);
    }

    public function testSave(UnitTester $I)
    {
        $entryValue = new EntryValue;
        $I->assertFalse($entryValue->save());
        unset($entryValue);

        $field = new Field(['name' => 'testing', 'handle' => 'testing']);
        $I->assertTrue($field->save());

        $name = 'name' . uniqid();
        $entryType = new EntryType(compact('name'));
        $I->assertTrue($entryType->save());

        $entry = new Entry([
            'entry_type_id' => $entryType->id,
        ]);
        $I->assertTrue($entry->save());

        $data = [
            'entry_id' => $entry->id,
            'field_id' => $field->id,
            'value' => 'value' . uniqid(),
        ];
        $entryValue = new EntryValue($data);
        $I->assertTrue($entryValue->save());
        $I->assertEquals(ArrayHelper::merge($data, ['id' => $entryValue->id]), $entryValue->attributes);
        $I->assertEquals($field->id, $entryValue->field->id);
        $I->assertEquals($entry->id, $entryValue->entry->id);
    }
}
