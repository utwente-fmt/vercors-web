<?php

namespace app\controllers;

use Yii;
use app\models\Author;
use app\models\Publication;
use app\models\PublicationSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PublicationController implements the CRUD actions for Publication model.
 */
class PublicationController extends Controller
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
     * Lists all Publication models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PublicationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Publication model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }
		
		private function linkAuthors($model) {
			$model->unlinkAll('authors', true);
			$ids = Yii::$app->request->post()['Publication']['authors'];
			if (is_array($ids)) {
				foreach ($ids as $id) {
					$model->link('authors', Author::findOne($id));
				}
			}
		}

    /**
     * Creates a new Publication model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
			$model = new Publication();
			
			if ($model->load(Yii::$app->request->post())) {
				if ($model->save()) {
					$this->linkAuthors($model);
					return $this->redirect(['view', 'id' => $model->id]);
				}
			}

      return $this->render('create', [
				'authors' => Author::find()->orderby('lastname,firstname')->all(),
        'model' => $model,
      ]);
    }

    /**
     * Updates an existing Publication model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
			$model = $this->findModel($id);

			if ($model->load(Yii::$app->request->post()) && $model->save()) {
				$this->linkAuthors($model);
				return $this->redirect(['view', 'id' => $model->id]);
			} else {
				return $this->render('update', [
					'authors' => Author::find()->orderby('lastname,firstname')->all(),
					'model' => $model,
				]);
			}
    }

    /**
     * Deletes an existing Publication model.
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
     * Finds the Publication model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Publication the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Publication::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
