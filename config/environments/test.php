<?php

$config = require(__DIR__ . '/../settings/local.php');

$config['id'] = 'basic-tests';
$config['components']['mailer'] = [
    'useFileTransport' => true
];
$config['components']['assetManager'] = [
    'basePath' => __dir__ . '../../web/assets'
];
$config['components']['urlManager'] = [
    'showScriptName' => true
];
$config['components']['request']['enableCsrfValidation'] = false;

return $config;
