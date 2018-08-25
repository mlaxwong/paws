<?php
namespace paws\tests\behaviors;

use yii\db\ActiveRecord;
use yii\db\Expression;
use Codeception\Stub;
use Paws;
use paws\tests\UnitTester;
use paws\behaviors\TimestampBehavior;

class TimestampBehaviorCest
{
    public function _before(UnitTester $I)
    {
        $db = Paws::$app->db;
        $db->createCommand('
        CREATE TABLE IF NOT EXISTS {{%testsbehaviorstimestampbehaviorcest_activerecod}} (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        )
        ')->execute();
    }

    public function _after(UnitTester $I)
    {
        $db = Paws::$app->db;
        $db->createCommand('DROP TABLE {{%testsbehaviorstimestampbehaviorcest_activerecod}}')->execute();
    }

    // tests
    public function testAttach(UnitTester $I)
    {
        $testClass = new class extends ActiveRecord
        {
            public static function tableName()
            {
                return '{{%testsbehaviorstimestampbehaviorcest_activerecod}}';
            }
        };
        $testClass->attachBehavior('timestamp', TimestampBehavior::class);
        $I->assertNull($testClass->created_at);
        $I->assertNull($testClass->updated_at);
        $I->assertTrue($testClass->save());
        $testClass->refresh();
        $I->assertNotNull($testClass->created_at);
        $I->assertNotNull($testClass->updated_at);
    }

    public function testGetValue(UnitTester $I)
    {
        $behavior = Stub::make(TimestampBehavior::class);
        $I->assertInstanceOf(Expression::class, $I->invokeMethod($behavior, 'getValue', [null]));
        $I->assertEquals(new Expression('NOW()'), $I->invokeMethod($behavior, 'getValue', [null]));

        unset($behavior);

        $behavior = Stub::make(TimestampBehavior::class, [
            'value' => 1234
        ]);
        $I->assertEquals(1234, $I->invokeMethod($behavior, 'getValue', [null]));
    }
}
