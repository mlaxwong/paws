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
        'response' => [
            'class' => yii\web\Response::class,
            'on beforeSend' => function ($event) {
                
                $response = $event->sender;
                $data = $response->data;
                $statusCode = $response->statusCode;


                switch ($statusCode)
                {
                    case 200: // ok
                    case 201: // created
                    case 204: // deleted
                    case 304: // no changes
                    case 401: // auth fail
                    case 403: // permission fail
                    case 422: // validation fail
                        $response->data = [
                            'success' => $response->isSuccessful,
                            'status' => $response->statusCode,
                        ];

                        if ($response->isSuccessful) {
                            $response->data['data'] = $data;
                        } else {
                            $response->data['data'] = in_array($statusCode, [404, 401, 500]) ? [] : $data;
                            $response->data['message'] = isset($data['message']) ? $data['message'] : [];
                            // $response->data['code'] = $data['code'];
                        }
                        $response->statusCode = 200;
                        break;
                    case 400: // bad request
                    case 404: // not found
                    case 405: // method no allow
                    case 415: // unsupport media type
                    case 429: // too many request
                    case 500: // internal server error
                        break;
                }
            },
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