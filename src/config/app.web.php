<?php 
return [
    'class' => paws\web\Application::class,
    'layoutPath' => '@app/views/_layouts',
    // 'defaultRoute' => 'dashboard',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [
        $this->general->routeTriggerCp => [
            'class' => paws\Module::class,
            'layoutPath' => '@app/views/_layouts',
        ],
    ],
    'components' => [
        'view' => [
            'class' => paws\web\View::class,
            'renderers' => [
                'twig' => [
                    'class' => paws\twig\ViewRenderer::class,
                    'cachePath' => '@runtime/Twig/cache',
                    'lexerOptions' => [],
                    // Array of twig options:
                    'options' => YII_DEBUG ? [
                        'debug' => true,
                        'auto_reload' => true,
                    ] : [],
                    'extensions' => YII_DEBUG ? ['\Twig_Extension_Debug'] : [],
                    'globals' => [
                        'html' => ['class' => yii\helpers\Html::class],
                    ],
                    'functions' => [
                        't' => 'Paws::t',
                    ],
                    'uses' => ['yii\bootstrap'],
                ],
            ],
        ],
        'assetManager' => [
            'class' => 'yii\web\AssetManager',
            'converter' => [
                'class' => lucidtaz\yii2scssphp\ScssAssetConverter::class,
                // 'formatter' => YII_DEBUG ? lucidtaz\yii2scssphp\ScssAssetConverter::FORMAT_NESTED : lucidtaz\yii2scssphp\ScssAssetConverter::FORMAT_COMPRESSED,
                // 'sourceMap' => lucidtaz\yii2scssphp\ScssAssetConverter::SOURCE_MAP_INLINE,
                // 'sourceMapOptions' => [
                //     'sourceMapBasepath' => '/',
                //     'sourceRoot'        => '/',
                // ],
            ],
            'linkAssets' => true,
        ],
        'request' => [
            'class' => yii\web\Request::class,
            'cookieValidationKey' => 'HAu3bCrCtjvFceGXIl9ecqDG1_h0FA5X',
        ],
        'urlManager' => [
            'class' => yii\web\UrlManager::class,
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            // 'enableStrictParsing' => false,
            // 'ruleConfig' => ['class' => paws\web\UrlRule::class],
        ],
        'errorHandler' => [
            'class' => yii\web\ErrorHandler::class,
            // 'errorAction' => 'templates/error'
        ]
    ],
];