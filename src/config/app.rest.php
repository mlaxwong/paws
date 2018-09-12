<?php
return [
    'class' => paws\web\Application::class,
    'components' => [
        'user' => [
            'identityClass' => paws\records\User::class,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'request' => [
            'enableCsrfValidation' => false, 
            'parsers' => [
                'application/json' => yii\web\JsonParser::class,
            ]
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,      
        ],
    ],
];