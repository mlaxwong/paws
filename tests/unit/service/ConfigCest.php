<?php
namespace paws\tests\service;

use yii\helpers\ArrayHelper;
use paws\tests\UnitTester;
use paws\service\Config;

class ConfigCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function tryToTest(UnitTester $I)
    {
    }

    public function testSetAppConfigs(UnitTester $I)
    {
        $configs = [
            ['testing'],
        ];
        $config = new Config();
        $config->setAppConfigs($configs);
        $I->assertNull($I->invokeProperty($config, '_app'));
        $I->assertEquals($configs, $I->invokeProperty($config, '_appConfigs'));
    }

    public function testGetApp(UnitTester $I)
    {
        $configArrayData = [
            'tesing' => 'this is testing',
        ];

        $path = codecept_output_dir() . '/appconfigsample.php';
        $configFileData = [
            'class' => yii\console\Application::class,
        ];
        file_put_contents($path, '<?php return ' . trim(var_export($configFileData, 1)) . ';');

        $config = new Config();
        $config->setAppConfigs(ArrayHelper::merge([$configArrayData], [$path]));

        $I->assertEquals(ArrayHelper::merge($configArrayData, $configFileData), $config->getApp());
    }

    public function testGetGeneralConfigFilePath(UnitTester $I)
    {
        $config = new Config();
        $filePath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'general.php';
        $I->seeFileFound(basename($filePath), dirname($filePath));
        $I->assertEquals($filePath, $I->invokeMethod($config, 'getGeneralConfigFilePath'));
    }

    public function testGetConfigFromFile(UnitTester $I)
    {
        $path = codecept_output_dir() . '/appconfigsample.php';
        $configData = [
            'class' => yii\console\Application::class,
        ];
        file_put_contents($path, '<?php return ' . trim(var_export($configData, 1)) . ';');

        $config = new Config();
        $I->assertEquals($configData, $I->invokeMethod($config, 'getConfigFromFile', [$path]));
        unlink($path);
    }

    public function testGetGeneral(UnitTester $I)
    {
        $config = new Config();
        $I->assertInternalType('object', $config->getGeneral());
        $I->assertEquals((object)['routeTriggerCp' => 'admin'], $config->getGeneral());
    }
}
