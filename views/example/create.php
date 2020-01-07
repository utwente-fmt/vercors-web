<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Example */

$this->title = 'Add a new example';
$this->params['breadcrumbs'][] = ['label' => 'Examples', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="example-create">

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
