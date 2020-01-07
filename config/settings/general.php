<?php
return [
    'basePath' => dirname(dirname(__DIR__)),
    'language' => 'en-US',
    'params' => [
        'adminEmail' => 'admin@example.com'
    ],
    'bootstrap' => [],
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'charset' => 'utf8'
        ],
        'request' => [
            'class' => 'yii\web\Request',
        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\User',
        ]
    ],
    'modules' => []
];