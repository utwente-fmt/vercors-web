<?php


namespace app\assets;


use yii\web\AssetBundle;

class InstallationAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/scribbler-global.css',
        'css/scribbler-landing.css',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css',
        'https://fonts.googleapis.com/css?family=Nunito+Sans:300,400,600,700,800,900',
        'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/railscasts.min.css',
    ];

    public $js = [
        'js/scribbler.js',
        'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js',
    ];
}