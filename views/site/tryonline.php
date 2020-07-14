<?php
use yii\helpers\Html;
use app\components\VerificationWidget;

$this->title = 'Try VerCors Online';

?>

<h1>Try VerCors online</h1>
<p>
    Does the following program verify?
    You can also start with an <?= Html::a('existing example', ['site/examples']) ?>!
</p>

<?= VerificationWidget::widget() ?>