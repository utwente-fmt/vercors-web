<?php

namespace app\controllers;

use app\models\News;
use Yii;
use app\models\ExampleSearch;
use app\models\Example;
use app\models\Feature;
use app\models\Language;
use app\models\Source;
use app\models\LoginForm;
use yii\filters\AccessControl;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\web\Controller;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'backoffice'],
                'rules' => [
                    [
                        'actions' => ['logout', 'backoffice'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ]
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionExamples()
    {
        $searchModel = new ExampleSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('example_list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'features' => Feature::find()->orderBy('name')->all(),
            'sources' => Source::find()->orderBy('name')->all()
        ]);
    }

    private function configureEditor()
    {
        $this->getView()->registerJsFile(
            '@web/js/vercorsonline.js',
            ['depends' => [\yii\web\JqueryAsset::className()]]
        );
    }

    public function actionExample($id)
    {
        $this->configureEditor();

        return $this->render('example', [
            'model' => $this->findExample($id),
        ]);
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionBackoffice() {
        return $this->render('backoffice', []);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionTryonline()
    {
        $this->configureEditor();

        return $this->render('tryonline');
    }

    protected function findExample($id)
    {
        if (($model = Example::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionWiki() {
        $this->configureEditor();
        return $this->render('wiki');
    }
}
