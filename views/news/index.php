<?php

use kartik\markdown\Markdown;
use yii\helpers\Html;

$this->title = 'news';
?>

<h2 class="spacing" style="color: #3ac984">News</h2>

<div class="blog-posts">
    <?php foreach($news as $item) { ?>
        <div class="blog-post spacing">
            <h3><a href=""><?= Html::encode($item->title) ?></a></h3>
            <p class="summary">
                <span class="date"><?= Html::encode($item->getDate()) ?></span>
            </p>
            <?= Markdown::convert($item->content) ?>
        </div>
    <?php } ?>
</div>
