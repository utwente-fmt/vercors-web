<?php

use kartik\markdown\Markdown;
use yii\helpers\Html;

$this->title = 'news';
?>

<header class="major">
    <h2><strong>News</strong></h2>
</header>

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
