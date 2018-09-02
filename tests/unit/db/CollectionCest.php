<?php
namespace paws\tests\db;

use yii\db\ActiveRecordInterface;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use Paws;
use paws\tests\UnitTester;
use paws\db\Collection;
use paws\db\RecordInterface;
use paws\db\CollectionInterface;
use paws\records\Entry;
use paws\records\EntryType;

class CollectionCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function testCreate(UnitTester $I)
    {
        $collection = new Collection;
    }

    public function testInstanceOfInterfaces(UnitTester $I)
    {
        $collection = new Collection;
        $interfaces = [
            ActiveRecordInterface::class,
            RecordInterface::class,
            CollectionInterface::class,
        ];
        foreach ($interfaces as $interface) $I->assertInstanceOf($interface, $collection);
    }

    public function testCollectionRecord(UnitTester $I)
    {
        // default
        $I->assertEquals('paws\\records\\Collection', Collection::collectionRecord());

        // extended
        $testClass = new class extends Collection {};
        $I->assertEquals('paws\\records\\' . Inflector::camelize(StringHelper::basename(get_class($testClass))), $testClass::collectionRecord());

        // customize
        $testClass = new class extends Collection {
            public static function collectionRecord()
            {
                return 'this\\is\\testing\\Class';
            }
        };
        $I->assertEquals('this\\is\\testing\\Class', $testClass::collectionRecord());
    }

    public function testCollectionTypeRecord(UnitTester $I) 
    {
        // default
        $I->assertEquals(Collection::collectionRecord() . 'Type', Collection::collectionTypeRecord());

        // extended
        $testClass = new class extends Collection {};
        $I->assertEquals('paws\\records\\' . Inflector::camelize(StringHelper::basename(get_class($testClass))) . 'Type', $testClass::collectionTypeRecord());

        // customize
        $testClass = new class extends Collection {
            public static function collectionRecord()
            {
                return 'this\\is\\testing\\Class';
            }
        };
        $I->assertEquals('this\\is\\testing\\ClassType', $testClass::collectionTypeRecord());
        $testClass = new class extends Collection {
            public static function collectionRecord()
            {
                return 'this\\is\\testing\\Class';
            }
            public static function collectionTypeRecord()
            {
                return 'custom\\collection\\type\\Record';
            }
        };
        $I->assertEquals('custom\\collection\\type\\Record', $testClass::collectionTypeRecord());
    }

    public function testCollectionValueRecord(UnitTester $I) 
    {
        // default
        $I->assertEquals(Collection::collectionRecord() . 'Value', Collection::collectionValueRecord());

        // extended
        $testClass = new class extends Collection {};
        $I->assertEquals('paws\\records\\' . Inflector::camelize(StringHelper::basename(get_class($testClass))) . 'Value', $testClass::collectionValueRecord());

        // customize
        $testClass = new class extends Collection {
            public static function collectionRecord()
            {
                return 'this\\is\\testing\\Class';
            }
        };
        $I->assertEquals('this\\is\\testing\\ClassValue', $testClass::collectionValueRecord());
        $testClass = new class extends Collection {
            public static function collectionRecord()
            {
                return 'this\\is\\testing\\Class';
            }
            public static function collectionValueRecord()
            {
                return 'custom\\collection\\type\\Record';
            }
        };
        $I->assertEquals('custom\\collection\\type\\Record', $testClass::collectionValueRecord());
    }

    public function testTypeAttribute(UnitTester $I)
    {
        // default
        $I->assertEquals('collection_type_id', Collection::typeAttribute());

        // extended
        $testClass = new class extends Collection {};
        $I->assertEquals(Inflector::camel2id(StringHelper::basename(get_class($testClass)), '_') . '_type_id', $testClass::typeAttribute());
    }

    public function testGetType(UnitTester $I)
    {
        $I->haveRecord(EntryType::class, [
            'id' => 1,
            'name' => 'testing'
        ]);
        $testClass = new class extends Collection 
        {
            public $typeId = 1;
            public static function collectionRecord()  { return Entry::class; } 
        };
        $I->assertNull($I->invokeProperty($testClass, '_type'));
        $I->assertEquals(EntryType::findOne(1), $testClass->getType());
        $I->assertNotNull($I->invokeProperty($testClass, '_type'));
    }

    public function testPrimaryKey(UnitTester $I)
    {
        $testClass = new class extends Collection
        {
            public static function collectionRecord() { return Entry::class; }
        };
        $I->assertEquals(Entry::primaryKey(), $testClass::primaryKey());
    }

    public function testGetDb(UnitTester $I)
    {
        $testClass = new class extends Collection
        {
            public static function collectionRecord() { return Entry::class; }
        };
        $I->assertEquals(Entry::getDb(), $testClass::getDb());
    }
}
