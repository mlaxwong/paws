<?php 
$pathBase = defined('PATH_BASE') ? constant('PATH_BASE') : dirname(__DIR__, 2);
$migrationPath = env('PATH_MIGRATION') ? env('PATH_MIGRATION') : $pathBase . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . 'db';
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
            'migrationPath' => [
                $migrationPath,
                '@yii/rbac/migrations/',
            ],
        ],
        // 'rbac-migrate' => [
        //     'class' => paws\console\controllers\MigrateController::class,
        //     'migrationTable' => '{{%migration_rbac}}',
        //     'templateFile' => '@paws/rbac/views/migration.php',
        //     'migrationPath' => [
        //         $pathBase . DIRECTORY_SEPARATOR . 'migrations/rbac',
        //     ],
        // ],
    ],
];