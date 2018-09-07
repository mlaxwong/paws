<?php 
return [
    'id' => 'paws',
    'name' => 'Paws CMS',
    'basePath' => dirname(__DIR__),
    'runtimePath' => '@runtime',
    // 'controllerNamespace' => 'paws\controllers',
    'components' => [
        'records' => paws\service\Record::class
    ]
];