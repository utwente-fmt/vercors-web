<?php

namespace app\controllers;

use Yii;
use app\models\Backend;
use app\models\Example;
use app\models\ExampleSearch;
use app\models\Feature;
use app\models\Source;
use app\models\Language;
use app\models\Publication;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ExampleController implements the CRUD actions for Example model.
 */
class ExampleController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
					'access' => [
						'class' => \yii\filters\AccessControl::className(),
						'rules' => [
							[ 'allow' => true, 'roles' => ['@'] ]
						]
					],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Example models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ExampleSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Example model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

		private function linkFeatures($model) {
			$model->unlinkAll('features', true);
			$ids = Yii::$app->request->post()['Example']['features'];
			if (is_array($ids)) {
				foreach ($ids as $id) {
					$model->link('features', Feature::findOne($id));
				}
			}
		}

		private function linkSources($model) {
			$model->unlinkAll('sources', true);
			$ids = Yii::$app->request->post()['Example']['sources'];
			if (is_array($ids)) {
				foreach ($ids as $id) {
					$model->link('sources', Source::findOne($id));
				}
			}
		}
		
    /**
     * Creates a new Example model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
			$model = new Example();
			
			if ($model->load(Yii::$app->request->post())) {
				$model->date = date("Y-m-d"); // today
				if ($model->save()) {
					$this->linkFeatures($model);
					$this->linkSources($model);
					return $this->redirect(['view', 'id' => $model->id]);
				}
			}
			
      return $this->render('create', [
				'articles' => Publication::find()->orderby('conference,year')->all(),
				'backends' => Backend::find()->orderby('name')->all(),
				'features' => Feature::find()->orderby('name')->all(),
				'sources' => Source::find()->orderby('name')->all(),
				'languages' => Language::find()->orderby('name')->all(),
				'model' => $model,
      ]);
    }

    /**
     * Updates an existing Example model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
					$this->linkFeatures($model);
					$this->linkSources($model);
          return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
							'articles' => Publication::find()->orderby('conference,year')->all(),
							'backends' => Backend::find()->orderby('name')->all(),
							'features' => Feature::find()->orderby('name')->all(),
							'sources' => Source::find()->orderby('name')->all(),
							'languages' => Language::find()->orderby('name')->all(),
              'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Example model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Example model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Example the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Example::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
