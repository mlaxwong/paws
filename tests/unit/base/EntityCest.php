<?php
namespace paws\tests\base;

use ReflectionClass;
use yii\db\ActiveRecord;
use Codeception\Stub;
use Paws;
use paws\tests\UnitTester;
use paws\tests\sample\ActiveRecordSample;
use paws\base\Entity;

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
        $db->createCommand('DROP TABLE {{%tempentitycestactiverecord}}')->execute();
    }

    // tests
    public function testGetReflect(UnitTester $I)
    {
        $testClass = Stub::make(Entity::class);
        $I->assertTrue($I->invokeMethod($testClass, 'getReflect') instanceof ReflectionClass);
    }

    public function testGetQueryClass(UnitTester $I)
    {
        $testClass = Stub::make(Entity::class);
        $reflect = new ReflectionClass($testClass);
        $I->assertEquals($testClass->queryNamespace . '\\' . $reflect->getShortName() . $testClass->queryClassSubfix, $testClass->getQueryClass());

        unset($testClass, $reflect);

        $namespace = 'this\\is\\testing\\namespace';
        $subfix = 'Testing';
        $testClass = Stub::make(Entity::class, [
            'queryNamespace' => $namespace,
            'queryClassSubfix' => $subfix
        ]);
        $reflect = new ReflectionClass($testClass);
        $I->assertEquals($namespace . '\\' . $reflect->getShortName() . $subfix, $testClass->getQueryClass());
    }

    public function tryToTest(UnitTester $I)
    {
        $activeRecord = new class extends ActiveRecord
        {
            public static function tableName()
            {
                return '{{%tempentitycestactiverecord}}';
            }
        };
        $activeRecord->save();
    }
}
