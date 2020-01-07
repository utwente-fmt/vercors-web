<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Example */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="example-form">

    <?php $form = ActiveForm::begin(); ?>

		<?= $form->field($model, 'backendid')->dropDownList(ArrayHelper::map($backends, 'id', 'name')) ?>

    <?= $form->field($model, 'languageid')->dropDownList(ArrayHelper::map($languages, 'id', 'name')) ?>

    <?= $form->field($model, 'publicationid')->dropDownList(ArrayHelper::map($articles, 'id', function ($article) { 
			return $article->displayName();
		}), ['prompt' => 'None']) ?>
		
		<?= $form->field($model, 'title')->textInput() ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
		
		<?= $form->field($model, 'link')->textInput() ?>
		
		<?= $form->field($model, 'linesofcode')->textInput() ?>
		
		<?= $form->field($model, 'linesofspec')->textInput() ?>
		
		<?= $form->field($model, 'computationtime')->textInput() ?>
		
		<?= $form->field($model, 'doesverify')->DropDownList(['1' => 'Yes', '0' => 'No']) ?>
		
		<?= $form->field($model, 'features')->checkboxList(ArrayHelper::map($features, 'id', 'name'), ['separator' => '<br />']) ?>
		
		<?= $form->field($model, 'sources')->checkboxList(ArrayHelper::map($sources, 'id', 'name'), ['separator' => '<br />']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
