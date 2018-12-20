<?php namespace paws\tests\helpers;

use paws\tests\UnitTester;
use paws\helpers\StringHelper;

class StringHelperCest
{
    public function _before(UnitTester $I)
    {
    }

    // tests
    public function testStrtrWithArrayParams(UnitTester $I)
    {
        $message = 'My address is {address1} {address2}.';
        $params = [
            'address1' => 'address one', 
            'address2' => 'address two', 
        ];
        $I->assertEquals('My address is address one address two.', StringHelper::strtr($message, $params));
    }

    public function testStrtrWithObjectParams(UnitTester $I)
    {
        $message = 'My address is {address1} {address2}.';
        $object = new class 
        {
            public $address1 = 'address one';
            public $address2 = 'address two';
        };
        $I->assertEquals('My address is address one address two.', StringHelper::strtr($message, $object));
    }

}
