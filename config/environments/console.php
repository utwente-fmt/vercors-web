<?php

$config = require(__DIR__ . '/../settings/local.php');

$config['id'] = 'basic-console';
$config['bootstrap'][] = 'log';
$config['controllerNamespace'] = 'app\commands';
$config['components']['cache'] = [
    'class' => 'yii\caching\FileCache'
];
$config['components']['log'] = [
    'targets' => [[
        'class' => 'yii\log\FileTarget',
        'levels' => ['error', 'warning']
    ]]
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
