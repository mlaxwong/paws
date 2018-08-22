<?php
$pathBase = defined('PATH_BASE') ? constant('PATH_BASE') : dirname(__DIR__, 2);

return [
    'class' => yii\console\Application::class,
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
            'dsn' => env('DB_DNS'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'tablePrefix' => env('DB_PREFIX'),
            'charset' => env('DB_CHARSET'),
            'enableSchemaCache' => !YII_DEBUG,
        ]
    ]
];