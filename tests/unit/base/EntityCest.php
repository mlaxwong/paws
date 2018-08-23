<?php
namespace paws\tests\base;

use ReflectionClass;
use yii\db\ActiveRecord;
use yii\db\ActiveQueryInterface;
use Codeception\Stub;
use Paws;
use paws\tests\UnitTester;
use paws\tests\sample\ActiveRecordSample;
use paws\base\Entity;
use paws\entities\query\EntityQuery;

class EntityCest
{
    public function _before(UnitTester $I)
    {
        $db = Paws::$app->db;
        $db->createCommand('
            CREATE TABLE IF NOT EXISTS {{%tempentitycestactiverecord}} (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
            )
        ')->execute();
    }

    public function _after(UnitTester $I)
    {
        $db = Paws::$app->db;
        $db->createCommand('DROP TABLE {{%tempentitycestactiverecord}}')->execute();
    }

    // tests
    public function testGetQueryClass(UnitTester $I)
    {
        $I->assertEquals(EntityQuery::class, Entity::getQueryClass());
    }

    public function testFind(UnitTester $I)
    {
        $I->assertTrue(Entity::find() instanceof ActiveQueryInterface);
    }

    // public function testGetQueryClass(UnitTester $I)
    // {
    //     // sample active record
    //     $activeRecord = new class extends ActiveRecord
    //     {
    //         public static function tableName()
    //         {
    //             return '{{%tempentitycestactiverecord}}';
    //         }
    //     };
        
    //     // test default
    //     $testClass = Stub::make(Entity::class, ['recordClass' => $activeRecord]);
    //     $reflect = new ReflectionClass($testClass);
    //     $I->assertEquals($testClass->queryNamespace . '\\' . $reflect->getShortName() . $testClass->queryClassSubfix, $testClass->getQueryClass());

    //     unset($testClass, $reflect);

    //     // test custom
    //     $namespace = 'this\\is\\testing\\namespace';
    //     $subfix = 'Testing';
    //     $testClass = Stub::make(Entity::class, [
    //         'queryNamespace' => $namespace,
    //         'queryClassSubfix' => $subfix,
    //         'recordClass' => $activeRecord,
    //     ]);
    //     $reflect = new ReflectionClass($testClass);
    //     $I->assertEquals($namespace . '\\' . $reflect->getShortName() . $subfix, $testClass->getQueryClass());
    // }

    // public function testGetFind(UnitTester $I)
    // {
    //     // sample active record
    //     $activeRecord = new class extends ActiveRecord
    //     {
    //         public static function tableName()
    //         {
    //             return '{{%tempentitycestactiverecord}}';
    //         }
    //     };

    //     $testClass = Stub::make(Entity::class, ['recordClass' => $activeRecord]);
    //     $I->assertTrue($testClass::find() instanceof ActiveQueryInterface);
    // }
}
