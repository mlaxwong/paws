<?php 
return [
    'id' => 'paws',
    'name' => 'Paws CMS',
    'basePath' => dirname(__DIR__),
    'runtimePath' => '@runtime',
    // 'controllerNamespace' => 'paws\controllers',
    'components' => [
        'authManager' => [
            'class' => yii\rbac\DbManager::class,
        ],
        'records' => paws\service\Record::class,
        'models' => paws\service\Model::class,
    ]
];