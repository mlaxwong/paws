<?php 
use yii\helpers\ArrayHelper;
use paws\service\Config;

// Helpers
// -----------------------------------------------------------------------------
require dirname(__DIR__) . '/helpers/env.php';

// Constants
// -----------------------------------------------------------------------------

// Paths
defined('PATH_BASE') or define('PATH_BASE', env('PATH_BASE') ?: dirname(__DIR__, 3));
defined('PATH_VENDOR') or define('PATH_VENDOR', env('PATH_VENDOR') ?: PATH_BASE . DIRECTORY_SEPARATOR . 'vendor');
defined('PATH_PAWS_SRC') or define('PATH_PAWS_SRC', env('PATH_PAWS_SRC') ?: PATH_VENDOR . DIRECTORY_SEPARATOR . 'mlaxwong' . DIRECTORY_SEPARATOR . 'paws' . DIRECTORY_SEPARATOR . 'src');

// Environment
defined('ENVIRONMENT') or define('ENVIRONMENT', env('ENVIRONMENT') ?: 'prod');

// Validate environment
if (ENVIRONMENT !== 'prod' && ENVIRONMENT !== 'dev')
{
    throw new \Exception('ENVIRONMENT must be "prod" or "dev".');
}

// Application type
defined('APP_TYPE') or define('APP_TYPE', 'web');

// Validate app type
if (APP_TYPE !== 'web' && APP_TYPE !== 'console')
{
    throw new \Exception('APP_TYPE must be "web" or "console".');
}

// Dev mode
$devMode = ENVIRONMENT == 'dev' || APP_TYPE == 'console';
if ($devMode) {
    ini_set('display_errors', 1);
    defined('YII_DEBUG') || define('YII_DEBUG', true);
    defined('YII_ENV') || define('YII_ENV', 'dev');
} else {
    ini_set('display_errors', 0);
    defined('YII_DEBUG') || define('YII_DEBUG', false);
    defined('YII_ENV') || define('YII_ENV', 'prod');
}

// Config
// -----------------------------------------------------------------------------

// config service
$config = new Config();
$config->mode = ENVIRONMENT;

// Setup
// -----------------------------------------------------------------------------

// Paws CMS
require PATH_PAWS_SRC . DIRECTORY_SEPARATOR . 'Yii.php';
require PATH_PAWS_SRC . DIRECTORY_SEPARATOR . 'Paws.php';

// Set aliases
Paws::setAlias('@paws', PATH_PAWS_SRC);
Paws::setAlias('@runtime', PATH_BASE . DIRECTORY_SEPARATOR . 'runtime');

// Application config
$config->appConfigs = [
    ['components' => ['config' => $config]],
    [
        'vendorPath' => PATH_VENDOR,
        'env' => ENVIRONMENT
    ], 
    PATH_PAWS_SRC . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.php',
    PATH_PAWS_SRC . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.' . APP_TYPE . '.php',
    PATH_BASE . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.php',
    PATH_BASE . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.' . APP_TYPE . '.php',
];

// Build application
return Paws::createObject($config->app);