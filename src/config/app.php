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
        'mailqueue' => [
            'class' => nterms\mailqueue\MailQueue::class,
            'table' => '{{%mail_queue}}',
            'mailsPerRound' => 10,
			'maxAttempts' => 3,
            // 'transport' => [
			// 	'class' => 'Swift_SmtpTransport',
			// 	'host' => 'smtp.gmail.com',
			// 	'username' => '',
			// 	'password' => '',
			// 	'port' => '587',
			// 	'encryption' => 'tls',
			// ],
        ],
        'records' => paws\service\Record::class,
        'models' => paws\service\Model::class,
    ]
];