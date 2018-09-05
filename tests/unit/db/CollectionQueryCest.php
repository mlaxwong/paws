<?php
namespace paws\tests\db;

use yii\db\QueryBuilder;
use yii\db\Query;
use yii\db\ActiveRecord;
use paws\tests\UnitTester;
use paws\db\Collection;
use paws\db\CollectionQuery;
use paws\records\Entry;
use paws\records\EntryType;
use paws\records\EntryValue;
use paws\records\Field;

class CollectionQueryCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function testType(UnitTester $I)
    {
        $I->haveRecord(EntryType::class, [
            'id' => 1,
            'name' => 'Article',
        ]);
        $testClass = new class extends Collection { public static function collectionRecord() { return Entry::class; } };
        $activeQuery = new CollectionQuery($testClass);
        $I->assertNull($I->invokeProperty($activeQuery, '_type'));
        $activeQuery->type(1);
        $I->assertNotNull($I->invokeProperty($activeQuery, '_type'));
        $I->assertInstanceOf($testClass::collectionTypeRecord(), $I->invokeProperty($activeQuery, '_type'));
    }

    public function testPrepare(UnitTester $I)
    {
        $mappingTable = new class extends ActiveRecord { public static function tableName() { return '{{%entry_type_field_map}}'; } };
        $attributes = ['title', 'content'];
        $I->haveRecord(EntryType::class, [
            'id' => 1,
            'name' => 'Article',
        ]);
        foreach ($attributes as $index => $attribute)
        {
            $I->haveRecord(Field::class, [
                'id' => $index + 1,
                'name' => $attribute,
                'handle' => $attribute,
            ]);
            $I->haveRecord(get_class($mappingTable), [
                'entry_type_id' => 1,
                'field_id' => $index + 1,
            ]);
        }
        $testClass = new class extends Collection 
        { 
            public static function collectionRecord() { return Entry::class; } 
            public static function collectionFieldRecord() { return Field::class; } 
        };
        $collectionClass = $testClass::collectionRecord();
        $activeQuery = new CollectionQuery($testClass);
        $activeQuery->type(1);
        $I->assertInstanceOf(Query::class, $query = $activeQuery->prepare(new QueryBuilder($collectionClass::getDB())));

        $select = [];
        $sql = 'SELECT ';
        $baseAttributes = $testClass->getBaseAttributes();

        foreach ($testClass->getBaseAttributes() as $baseAttribute) $select[] = '`' . $collectionClass::getTableSchema()->name . '`.`' . $baseAttribute . '`';
        
        foreach ($attributes as $index => $attribute) $select[] = "(SELECT `value` FROM `" . EntryValue::getTableSchema()->name . "` WHERE `" . $testClass::fkFieldId() . "` = '" . ($index + 1) . "' AND `" . $testClass::fkCollectionId() . "` = `" . $collectionClass::getTableSchema()->name . "`.id) AS `" . $attribute . "`";
        
        $sql .= implode(', ', $select);
        $sql .= ' FROM `' .  $collectionClass::getTableSchema()->name . '`';

        $I->assertEquals($sql, $query->createCommand()->rawSql);
    }
}
