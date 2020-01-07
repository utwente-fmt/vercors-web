<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Backend */

$this->title = 'Create Backend';
$this->params['breadcrumbs'][] = ['label' => 'Backends', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="backend-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
