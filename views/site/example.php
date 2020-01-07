<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\DetailView;
use f4soft\codemirror\CodeMirror;

$this->title = $model->title;
?>

<div class="example-view">
    <h1><?= Html::encode($this->title) ?></h1>
    <p><?= Html::encode($model->description) ?></p>

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

            'link',
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
    <div class="row">
        <div class="col-md-6">
            <?= Codemirror::widget([
                'name' => 'examplecode',
                'value' => $model->examplecode(),
                'clientOptions' => [
                    'lineNumbers' => true,
                    'matchBrackets' => true,
                    'indentUnit' => 2,
                    'indentWithTabs' => true
                ]
            ]) ?>
            <?php $this->registerJs("editor.on('change', function () {
        editor.save();
    })"); ?>
        </div>
        <div class="col-md-6">
            <div>
                <?= Html::hiddenInput('language', $model->language->extension) ?>

                <?= Html::button('Verify this!', [
                    'class' => 'btn btn-success',
                    'id' => 'verifythis'
                ]) ?>
            </div>
            <div id="verification-progress" style="margin: 12pt 0 12pt 0; font-style: italic">
                Note, verification may take a while and has a time-out of 20 seconds.
            </div>
            <pre id="verification-log"></pre>
        </div>
    </div>
</div>