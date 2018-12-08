<?php 
namespace paws\tests\records;

use paws\tests\UnitTester;
use paws\records\Token;
use paws\records\User;

class TokenCest
{
    public function _before(UnitTester $I)
    {
        User::deleteAll();
    }

    // tests
    public function tryToTest(UnitTester $I)
    {
    }

    public function testCreateAndGetInstance(UnitTester $I)
    {
        $I->haveRecord(User::class, [
            'id' => 1,
            'username' => 'name' . uniqid(),
            'email' => 'testing@mail.com',
            'auth_key' => uniqid(),
            'password_hash' => uniqid(),
        ]);

        $user = User::findOne(1);
        $token = Token::create($user, 'testing', ['key' => 'value']);
        $I->assertInstanceOf(Token::class, $token);

        $gettedToken = Token::getInstance($user, 'testing', $token->token_key);
        $I->assertInstanceOf(Token::class, $gettedToken);
    }
}
