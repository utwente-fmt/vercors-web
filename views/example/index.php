<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ExampleSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Example database';
$this->params['breadcrumbs'][] = 'Examples';
?>
<div class="example-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Add a new example', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
						'title',
						
						['class' => 'yii\grid\DataColumn', 'attribute' => 'publicationid', 'filter' => false, 'value' => function ($data) {
							return $data->publication === NULL ? 'none' : $data->publication->shortDisplayName();
						}],
						
						['class' => 'yii\grid\DataColumn', 'attribute' => 'backendid', 'filter' => false, 'value' => function ($data) {
							return $data->backend->name;
						}],
						
						['class' => 'yii\grid\DataColumn', 'attribute' => 'languageid', 'filter' => false, 'value' => function ($data) {
							return $data->language->name;
						}],
						
            //'publicationid',
            //'description:ntext',
            // 'date',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
