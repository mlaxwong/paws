<?php 
namespace paws\service;

use stdClass;
use yii\base\Component;
use yii\helpers\ArrayHelper;

class Config extends Component
{
    public $mode = 'dev';

    protected $_appConfigs = [];
    protected $_app = null;
    protected $_general = null;

    public function setAppConfigs(array $appConfigs)
    {
        $this->_appConfigs = $appConfigs;
        $this->_app = null;
    }

    public function getApp()
    {
        if ($this->_app === null)
        {
            $configs = [];
            foreach ($this->_appConfigs as $appConfig)
            {
                $configs[] = is_array($appConfig) ? $appConfig : $this->getConfigFromFile($appConfig);
            }

            $this->_app = call_user_func_array([ArrayHelper::class, 'merge'], $configs);
        }
        return $this->_app;
    }

    public function getGeneral()
    {
        if ($this->_general === null)
        {
            $this->_general = (object) $this->getConfigFromFile($this->getGeneralConfigFilePath());
        }
        return $this->_general;
    }

    protected function getGeneralConfigFilePath()
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'general.php';
    }

    protected function getConfigFromFile($path)
    {
        if (!file_exists($path)) return [];
        
        if (!is_array($config = @include($path))) return [];

        return $config;
    }
}