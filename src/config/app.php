<?php 
return [
    'id' => 'paws',
    'name' => 'Paws CMS',
    'basePath' => dirname(__DIR__),
    'runtimePath' => '@runtime',
    // 'controllerNamespace' => 'paws\controllers',
    'components' => [
        'record' => paws\service\Record::class
    ]
];