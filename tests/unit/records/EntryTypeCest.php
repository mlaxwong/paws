<?php
namespace paws\tests\records;

use paws\tests\UnitTester;
use paws\records\Entry;
use paws\records\EntryType;
use paws\records\EntryValue;
use paws\records\Field;

class EntryTypeCest
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
        $model = new EntryType;
        $I->assertEquals(['id', 'name'], $model->attributes());
    }

    public function testLoad(UnitTester $I)
    {
        $model = new EntryType;
        $I->assertFalse($model->load([]));

        $data = ['EntryType' => ['name' => 'name' . uniqid()]];
        $I->assertTrue($model->load($data));
        $I->assertEquals(['id' => null, 'name' => $data['EntryType']['name']], $model->attributes);
    }

    public function testCreate(UnitTester $I)
    {
        // save emtpy
        $entryType = new EntryType;
        $I->assertFalse($entryType->save());
        unset($entryType);

        // create entry type
        $name = 'name' . uniqid();
        $entryType = new EntryType(compact('name'));
        $I->assertTrue($entryType->save());

        // create entries
        $entries = [];
        for ($i = 0; $i < 3; $i++)
        {
            $data = [
                'name' => 'name' . uniqid(),
                'handle' => 'handle' . uniqid(),
                'entry_type_id' => $entryType->id,
            ];
            $entry = new Entry($data);
            $I->assertTrue($entry->save());
            $entries[] = $entry;
        }
        // link all entry
        foreach ($entries as $entry) $entryType->link('entries', $entry);
        foreach ($entryType->entries as $i => $entry) $I->assertEquals($entries[$i]->id, $entry->id);

        // create fields
        $fields = [];
        for ($i = 0; $i < 3; $i++)
        {
            $data = [
                'name' => uniqid(), 
                'handle' => uniqid(), 
                'config' => json_encode([uniqid()])
            ];
            $field = new Field($data);
            $I->assertTrue($field->save());
            $fields[] = $field;
        }
        foreach ($fields as $field) $entryType->link('fields', $field);
        foreach ($entryType->fields as $i => $field) $I->assertEquals($fields[$i]->id, $field->id);
    }
}
