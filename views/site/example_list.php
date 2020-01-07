<?php

use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = 'VerCors Example Database';
?>

<h2>Example database</h2>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        'id',
        'title',

        ['attribute' => 'feature', 'label' => 'Verification features', 'filter' => ArrayHelper::map($features, 'id', 'name'), 'value' => function ($data) {
            return $data->featuresText();
        }],
        ['attribute' => 'source', 'label' => 'Example source', 'filter' => ArrayHelper::map($sources, 'id', 'name'), 'value' => function ($data) {
            return $data->sourcesText();
        }],

        ['attribute' => 'languagename', 'label' => 'Language', 'value' => 'language.name'],

        ['label' => 'More information', 'format' => 'raw', 'value' => function ($data) {
            return Html::a('more info', [
                '/site/example', 'id' => $data->id
            ]);
        }]
    ],
]); ?>