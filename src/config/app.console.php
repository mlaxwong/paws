<?php 
$pathBase = defined('PATH_BASE') ? constant('PATH_BASE') : dirname(__DIR__, 2);
$pathDbMigration = env('PATH_DB_MIGRATION') ? $pathBase . env('PATH_DB_MIGRATION') : $pathBase . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . 'db';
$pathRbacMigration = env('PATH_RBAC_MIGRATION') ? $pathBase . env('PATH_RBAC_MIGRATION') : $pathBase . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . 'rbac';
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
                $pathDbMigration,
                '@yii/rbac/migrations/',
                '@vendor/nterms/yii2-mailqueue/migrations/',
            ],
        ],
        'rbac-migrate' => [
            'class' => paws\console\controllers\MigrateController::class,
            'migrationTable' => '{{%migration_rbac}}',
            'templateFile' => '@paws/rbac/views/migration.php',
            'migrationPath' => [
                $pathRbacMigration
            ],
        ],
    ],
];