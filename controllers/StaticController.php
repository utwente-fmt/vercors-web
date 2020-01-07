<?php


namespace app\controllers;


use app\models\News;
use yii\web\Controller;

class StaticController extends Controller
{
    public $layout = 'main';

    public function actionIndex() {
        $this->layout = 'root';
        return $this->render('index', [
            'news' => News::getRecent(),
        ]);
    }

    public function actionAbout() {
        return $this->render('about');
    }

    public function actionAbstraction() {
        return $this->render('abstraction');
    }

    public function actionArchitecture() {
        return $this->render('architecture');
    }

    public function actionInstallation() {
        // TODO: remove the layout override once we (hopefully) rewrite the installation guide with less conflicting CSS
        $this->layout = 'root';
        return $this->render('installation');
    }

    public function actionPublications() {
        return $this->render('publications');
    }

    public function actionTutorials() {
        return $this->render('tutorials');
    }

    public function actionLicense() {
        $this->layout = 'no_layout';
        return $this->render('license');
    }
}