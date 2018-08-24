<?php
namespace paws\tests\records;

use yii\helpers\ArrayHelper;
use paws\tests\UnitTester;
use paws\records\Entry;
use paws\records\EntryType;
use paws\records\EntryValue;
use paws\records\Field;

class EntryCest
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
        $model = new Entry;
        $I->assertEquals([
            'id',
            'entry_type_id',
            'name',
            'handle',
            'created_at',
            'updated_at',
        ], $model->attributes());
    }

    public function testLoad(UnitTester $I)
    {
        $model = new Entry;
        $I->assertFalse($model->load([]));
        $data = [
            'Entry' => [
                'name' => 'name' . uniqid(),
                'handle' => 'handle' . uniqid(),
                'entry_type_id' => uniqid(),
            ],
        ];
        $I->assertTrue($model->load($data));
        $I->assertEquals(ArrayHelper::merge((new Entry())->attributes, $data['Entry']), $model->attributes);
    }

    public function testCreate(UnitTester $I)
    {
        $entry = new Entry;
        $I->assertFalse($entry->save());
        unset($entry);
        
        $entryType = new EntryType(['name' => 'testing']);
        $I->assertTrue($entryType->save());

        $data = [
            'name' => 'name' . uniqid(),
            'handle' => 'handle' . uniqid(),
            'entry_type_id' => $entryType->id,
        ];
        $entry = new Entry($data);
        $I->assertEquals($entry->created_at, null);
        $I->assertEquals($entry->updated_at, null);
        $I->assertTrue($entry->save());
        $entry->refresh();
        $I->assertNotEquals($entry->created_at, null);
        $I->assertNotEquals($entry->updated_at, null);
        $I->assertEquals(ArrayHelper::merge(
            (new Entry())->attributes, 
            $data,
            ['id' => $entry->id, 'created_at' => $entry->created_at, 'updated_at' => $entry->updated_at]
        ), $entry->attributes);
        $I->assertEquals($entryType->id, $entry->entryType->id);

        $values = [];
        for ($i = 0; $i < 3; $i++)
        {
            $field = new Field([
                'name' => uniqid(), 
                'handle' => uniqid(), 
                'config' => json_encode([uniqid()])
            ]);
            $I->assertTrue($field->save());
            $value = new EntryValue([
                'entry_id' => $entry->id,
                'field_id' => $field->id,
                'value' => 'value' . uniqid(),
            ]);
            $I->assertTrue($value->save());
            $values[] = $value;
        }
        foreach ($values as $value) $entry->link('entryValues', $value);
        foreach ($entry->entryValues as $i => $value) $I->assertEquals($values[$i]->id, $value->id);
    }
}
