<?php
return [
    'class' => paws\web\Application::class,
    'components' => [
        'assetManager' => [
            'class' => 'yii\web\AssetManager',
            'linkAssets' => true,
            'appendTimestamp' => YII_ENV_DEV,
        ],
    ],
];