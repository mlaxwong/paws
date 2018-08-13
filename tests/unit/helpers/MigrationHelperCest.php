<?php
namespace paws\tests\helpers;

use paws\tests\UnitTester;
use paws\helpers\MigrationHelper;

class MigrationHelperCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function testPrefix(UnitTester $I)
    {
        $tableName = 'table_name';
        $prefixedTableName = MigrationHelper::prefix($tableName);
        $expected = '{{%' . $tableName . '}}';
        $I->assertEquals($expected, $prefixedTableName);
    }

    public function testFk(UnitTester $I)
    {
        $tableName = 'table_name';
        $column = 'column';
        $fk = MigrationHelper::fk($tableName, $column);
        $expected = 'fk_' . $tableName . '_' . $column;
        $I->assertEquals($expected, $fk);
    }
}
