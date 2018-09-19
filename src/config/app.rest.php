<?php
return [
    'class' => paws\web\Application::class,
    'components' => [
        'user' => [
            'identityClass' => paws\records\User::class,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        // 'response' => [
        //     'class' => yii\web\Response::class,
        //     'on beforeSend' => function ($event) {
        //         $response = $event->sender;
        //         if ($response->data !== null && Yii::$app->request->get('suppress_response_code')) {
        //             $response->data = [
        //                 'success' => $response->isSuccessful,
        //                 'data' => $response->data,
        //             ];
        //             $response->statusCode = 200;
        //         }
        //     },
        // ],
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