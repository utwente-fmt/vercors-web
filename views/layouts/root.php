<?php

/* @var $this \yii\web\View */

/* @var $content string */

use yii\helpers\Html;
use app\assets\AppAsset;
use yii\helpers\Url;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE HTML>
    <!--
        VerCors Tool --- Formal Methods and Tools Group (EWI)
        University of Twente, Enschede, The Netherlands
    -->
    <html>
    <head>
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?> · VerCors Tool FMT | UTwente </title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
        <meta name="description" content=""/>
        <meta name="keywords" content=""/>
        <?php $this->head() ?>
        <script>hljs.initHighlightingOnLoad();</script>
    </head>
    <body class="no-sidebar">
    <?php $this->beginBody() ?>
    <div id="header">
        <h1 id="logo" style="position: relative">
            <span id="hamburger"
                  onclick="document.querySelector('#nav').classList.toggle('open')"
                  class="fa fa-bars"></span>
            <?= Html::a('The VerCors Verifier', ['static/index']) ?>
        </h1>
        <nav id="nav">
            <ul>
                <li>
                    <a href="">Getting Started</a>
                    <ul>
                        <li><?= Html::a('Introduction', ['site/wiki', '#' => 'introduction']) ?></li>
                        <li><?= Html::a('Installation Guide', ['site/wiki', '#' => 'installing-and-running-vercors']) ?></li>
                        <li><?= Html::a('Tutorial', ['site/wiki']) ?></li>
                        <li>
                            <a href="https://github.com/utwente-fmt/vercors/issues" target="_blank">
                                Issue Tracker
                                <span class="fa fa-external-link" style="font-size: 10pt"></span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li><?= Html::a('Showcases', ['site/examples']) ?></li>
                <li><?= Html::a('News', ['news/index']) ?></li>
                <li><?= Html::a('Publications', ['static/publications']) ?></li>
                <li>
                    <?= Html::a('About', ['static/about']) ?>
                    <ul>
                        <li><?= Html::a('VerCors Team', ['static/about', '#' => 'Team']) ?></li>
                        <li><?= Html::a('Contact', ['static/about', '#' => 'Contact']) ?></li>
                        <li><?= Html::a('Credits', ['static/about', '#' => 'Credits']) ?></li>
                        <li><?= Html::a('License', ['static/license']) ?></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <div class="container">
            <div id="banner">
                <div class="container">
                    <section>
                        <?php if(isset($this->blocks['banner'])) { ?><?= $this->blocks['banner'] ?><?php } ?>
                        <p>
                            <a href="https://github.com/utwente-fmt/vercors" class="button alt" target="_blank">
                                View on Github
                            </a>
                            <a href="/try_online" class="button alt">Try VerCors Online</a>
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <?= $content ?>

    <div id="footer">
        <div class="row">
            <a href="https://fmt.ewi.utwente.nl" target="_blank"><img src="/images/FMT logo.png" alt=""></a>
            <div class="copyright">
                <a href="https://fmt.ewi.utwente.nl/research/projects/view/vercors/" target="_blank">Copyright <code>&copy</code> The VerCors Project 2007-2020</a>
                | <a href="https://fmt.ewi.utwente.nl" target="_blank">FMT - University of Twente</a>
                | <?= Html::a('About Us', ['static/about']) ?>
                | <?= Html::a('Backoffice', ['site/backoffice']) ?>
            </div>
        </div>

    </div>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>