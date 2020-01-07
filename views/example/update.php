<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Example */

$this->title = 'Update: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Examples', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="example-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
			'articles' => $articles,
			'backends' => $backends,
			'features' => $features,
			'sources' => $sources,
			'languages' => $languages,
      'model' => $model,
    ]) ?>

</div>
