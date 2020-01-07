<?php

use yii\helpers\Html;

$this->title = 'Backoffice';

?>
<p>
    Editable models:
</p>
<ul>
    <li><?= Html::a('Authors', ['author/index']) ?></li>
    <li><?= Html::a('Backends', ['backend/index']) ?></li>
    <li><?= Html::a('Examples', ['example/index']) ?></li>
    <li><?= Html::a('Features', ['feature/index']) ?></li>
    <li><?= Html::a('Languages', ['language/index']) ?></li>
    <li><?= Html::a('News Items', ['news/grid']) ?></li>
    <li><?= Html::a('Publications', ['publication/index']) ?></li>
    <li><?= Html::a('Sources', ['source/index']) ?></li>
</ul>
