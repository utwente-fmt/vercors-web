<?php


namespace app\controllers;


use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class CustomController extends Controller
{
    public function actionPage($path) {
        $base = $this->viewPath;
        $trailingSlash = substr($path, -1) === '/';
        $path = $base . DIRECTORY_SEPARATOR . FileHelper::normalizePath($path);

        if($trailingSlash && is_dir($path)) {
            $path .= DIRECTORY_SEPARATOR . 'index.html';
        }

        if(is_file($path)) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            \Yii::$app->response->headers->set('Content-Type', FileHelper::getMimeTypeByExtension($path));
            return file_get_contents($path);
        } else {
            throw new NotFoundHttpException();
        }
    }
}