<?php

$config = require(__DIR__ . '/../settings/local.php');

$config['id'] = 'basic';
$config['bootstrap'][] = 'log';
$config['components']['cache'] = ['class' => 'yii\caching\FileCache'];
$config['components']['errorHandler'] = ['errorAction' => 'site/error'];

// This should probably be in settings/local.php.template if it were to be configured.
$config['components']['mailer'] = ['class' => 'yii\swiftmailer\Mailer', 'useFileTransport' => true];

$config['components']['log'] = [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [[
        'class' => 'yii\log\FileTarget',
        'levels' => ['error', 'warning']
    ]]
];

$config['components']['assetManager'] = [
    'bundles' => [
        'yii\web\JqueryAsset' => [
            // FIXME: we're overriding the yii jquery to an older jquery, because our template needs it.
            'sourcePath' => null,
            'basePath' => '@webroot',
            'baseUrl' => '@web',
            'js' => [
                'js/jquery.min.js'
            ]
        ]
    ]
];

$config['components']['urlManager'] = [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'enableStrictParsing' => true,
    'rules' => [
        '' => 'static/index',
        '/what/is/this/page' => 'static/abstraction',
        '/about' => 'static/about',
        '/license' => 'static/license',
        '/getting_started/installation' => 'static/installation',
        '/getting_started/overview' => 'static/architecture',
        '/getting_started/tutorials' => 'static/tutorials',
        '/news' => 'news/index',
        '/publications' => 'static/publications',

        '/try_online' => 'site/tryonline',
        '/try_online/examples' => '/site/examples',
        '/try_online/example/<id:\d+>' => 'site/example',

        '/backoffice' => 'site/backoffice',
        '/backoffice/login' => 'site/login',
        '/backoficce/logout' => 'site/logout',

        '/backoffice/news' => 'news/grid',
        '/backoffice/news/<id:\d+>' => 'news/view',
        '/backoffice/news/create' => 'news/create',
        '/backoffice/news/<id:\d+>/update' => 'news/update',
        '/backoffice/news/<id:\d+>/delete' => 'news/delete',

        '/backoffice/author' => 'author/index',
        '/backoffice/author/<id:\d+>' => 'author/view',
        '/backoffice/author/create' => 'author/create',
        '/backoffice/author/<id:\d+>/update' => 'author/update',
        '/backoffice/author/<id:\d+>/delete' => 'author/delete',

        '/backoffice/backend' => 'backend/index',
        '/backoffice/backend/<id:\d+>' => 'backend/view',
        '/backoffice/backend/create' => 'backend/create',
        '/backoffice/backend/<id:\d+>/update' => 'backend/update',
        '/backoffice/backend/<id:\d+>/delete' => 'backend/delete',

        '/backoffice/example' => 'example/index',
        '/backoffice/example/<id:\d+>' => 'example/view',
        '/backoffice/example/create' => 'example/create',
        '/backoffice/example/<id:\d+>/update' => 'example/update',
        '/backoffice/example/<id:\d+>/delete' => 'example/delete',

        '/backoffice/feature' => 'feature/index',
        '/backoffice/feature/<id:\d+>' => 'feature/view',
        '/backoffice/feature/create' => 'feature/create',
        '/backoffice/feature/<id:\d+>/update' => 'feature/update',
        '/backoffice/feature/<id:\d+>/delete' => 'feature/delete',

        '/backoffice/language' => 'language/index',
        '/backoffice/language/<id:\d+>' => 'language/view',
        '/backoffice/language/create' => 'language/create',
        '/backoffice/language/<id:\d+>/update' => 'language/update',
        '/backoffice/language/<id:\d+>/delete' => 'language/delete',

        '/backoffice/publication' => 'publication/index',
        '/backoffice/publication/<id:\d+>' => 'publication/view',
        '/backoffice/publication/create' => 'publication/create',
        '/backoffice/publication/<id:\d+>/update' => 'publication/update',
        '/backoffice/publication/<id:\d+>/delete' => 'publication/delete',

        '/backoffice/source' => 'source/index',
        '/backoffice/source/<id:\d+>' => 'source/view',
        '/backoffice/source/create' => 'source/create',
        '/backoffice/source/<id:\d+>/update' => 'source/update',
        '/backoffice/source/<id:\d+>/delete' => 'source/delete',

    ],
];

$config['modules']['markdown'] = [
    'class' => 'kartik\markdown\Module',
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
