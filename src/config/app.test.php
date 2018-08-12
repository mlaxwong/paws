<?php
$pathBase = defined('PATH_BASE') ? constant('PATH_BASE') : dirname(__DIR__, 2);

return [
    'class' => paws\console\Application::class,
    'controllerNamespace' => 'paws\console\controllers',
    'controllerMap' => [
        'migrate' => [
            'class' => paws\console\controllers\MigrateController::class,
            'templateFile' => '@paws/db/views/migration.php',
            'migrationNamespaces' => [
                "paws\migrations",
            ],
            'migrationPath' => $pathBase . DIRECTORY_SEPARATOR . 'migrations',
        ],
    ],
    'components' => [
        'db' => [
            'class' => yii\db\Connection::class,
            'dsn' => 'mysql:host=localhost;port=3306;dbname=test',
            'username' => 'root',
            'password' => '',
            'tablePrefix' => '',
            'charset' => 'utf8',
            'enableSchemaCache' => false,
        ]
    ],
];