<?php
$config = [
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
return yii\helpers\ArrayHelper::merge(require (__DIR__ . DIRECTORY_SEPARATOR . 'app.php'), $config);