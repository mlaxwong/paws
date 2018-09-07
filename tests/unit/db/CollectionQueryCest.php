<?php
namespace paws\tests\db;

use yii\db\QueryBuilder;
use yii\db\Query;
use yii\db\ActiveRecord;
use paws\tests\UnitTester;
use paws\db\Collection as DbCollection;
use paws\db\CollectionQuery;
use paws\records\Collection;
use paws\records\CollectionType;
use paws\records\CollectionValue;
use paws\records\CollectionField;

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
        $I->haveRecord(CollectionType::class, [
            'id' => 1,
            'name' => 'Article',
            'handle' => 'article',
        ]);
        $testClass = new class extends DbCollection { public static function collectionRecord() { return Collection::class; } };
        $activeQuery = new CollectionQuery($testClass);
        $I->assertNull($I->invokeProperty($activeQuery, '_type'));
        $activeQuery->type(1);
        $I->assertNotNull($I->invokeProperty($activeQuery, '_type'));
        $I->assertInstanceOf($testClass::collectionTypeRecord(), $I->invokeProperty($activeQuery, '_type'));
    }

    public function testPrepare(UnitTester $I)
    {
        $mappingTable = new class extends ActiveRecord { public static function tableName() { return '{{%collection_type_field_map}}'; } };
        $attributes = ['title', 'content'];
        $I->haveRecord(CollectionType::class, [
            'id' => 1,
            'name' => 'Article',
            'handle' => 'article',
        ]);
        foreach ($attributes as $index => $attribute)
        {
            $I->haveRecord(CollectionField::class, [
                'id' => $index + 1,
                'name' => $attribute,
                'handle' => $attribute,
            ]);
            $I->haveRecord(get_class($mappingTable), [
                'collection_type_id' => 1,
                'collection_field_id' => $index + 1,
            ]);
        }
        $testClass = new class extends DbCollection 
        { 
            public static function collectionRecord() { return Collection::class; } 
            public static function collectionFieldRecord() { return CollectionField::class; } 
        };
        $collectionClass = $testClass::collectionRecord();
        $activeQuery = new CollectionQuery($testClass);
        $activeQuery->type(1);
        $I->assertInstanceOf(Query::class, $query = $activeQuery->prepare(new QueryBuilder($collectionClass::getDB())));

        $select = [];
        $sql = 'SELECT ';
        $baseAttributes = $testClass->getBaseAttributes();

        foreach ($testClass->getBaseAttributes() as $baseAttribute) $select[] = '`' . $collectionClass::getTableSchema()->name . '`.`' . $baseAttribute . '`';
        
        foreach ($attributes as $index => $attribute) $select[] = "(SELECT `value` FROM `" . CollectionValue::getTableSchema()->name . "` WHERE `" . $testClass::fkFieldId() . "` = '" . ($index + 1) . "' AND `" . $testClass::fkCollectionId() . "` = `" . $collectionClass::getTableSchema()->name . "`.id) AS `" . $attribute . "`";
        
        $sql .= implode(', ', $select);
        $sql .= ' FROM `' .  $collectionClass::getTableSchema()->name . '`';

        $I->assertEquals($sql, $query->createCommand()->rawSql);
    }
}
