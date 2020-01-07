<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Example */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Examples', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="example-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

		<h3>General information</h3>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
						['class' => 'yii\grid\DataColumn', 'attribute' => 'publicationid', 'filter' => false, 'value' => function ($data) {
							return $data->publication === NULL ? 'none' : $data->publication->displayName();
						}],
						
						['class' => 'yii\grid\DataColumn', 'attribute' => 'backendid', 'filter' => false, 'value' => function ($data) {
							return $data->backend->name;
						}],
						
						['class' => 'yii\grid\DataColumn', 'attribute' => 'languageid', 'filter' => false, 'value' => function ($data) {
							return $data->language->name;
						}],
						
						['class' => 'yii\grid\DataColumn', 'attribute' => 'features', 'filter' => false, 'value' => function ($data) {
							return $data->featuresText();
						}],
						
						['class' => 'yii\grid\DataColumn', 'attribute' => 'sources', 'filter' => false, 'value' => function ($data) {
							return $data->sourcesText();
						}],
						
            'description:ntext',
						'link',
						['class' => 'yii\grid\DataColumn', 'attribute' => 'doesverify', 'filter' => false, 'value' => function ($data) {
							return $data->doesverify ? 'Yes' : 'No';
						}],
            'date',
        ],
    ]) ?>

		<h3>Statistical information</h3>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
					['class' => 'yii\grid\DataColumn', 'attribute' => 'linesofcode', 'filter' => false, 'value' => function ($data) {
						return $data->linesofcodeText();
					}],
					['class' => 'yii\grid\DataColumn', 'attribute' => 'linesofspec', 'filter' => false, 'value' => function ($data) {
						return $data->linesofspecText();
					}],
					['class' => 'yii\grid\DataColumn', 'attribute' => 'computationtime', 'filter' => false, 'value' => function ($data) {
						return $data->computationtimeText();
					}],
        ],
    ]) ?>
		
		<h3>Example code</h3>
		<pre><code class="java"><?=Html::encode($model->examplecode()) ?></code></pre>
</div>
