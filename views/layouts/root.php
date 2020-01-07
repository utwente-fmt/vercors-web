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
        VerCors Tool --- Formal Methods and Tool Group (EWI)
        University of Twente, Enschede, The Netherlands
    -->
    <html>
    <head>
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?> · VerCors Tool FMT | UTwente </title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
        <meta name="description" content=""/>
        <meta name="keywords" content=""/>
        <?php $this->head() ?>
        <script>hljs.initHighlightingOnLoad();</script>
    </head>
    <body class="no-sidebar">
    <?php $this->beginBody() ?>
    <div id="skel-layers-wrapper">
        <div id="header">
            <div class="container">

                <!-- Logo -->
                <h1><?= Html::a('The VerCors Verifier', ['static/index'], ['id' => 'logo']) ?></h1>

                <!-- Nav -->
                <nav id="nav">
                    <ul>
                        <li>
                            <a href="">Getting Started</a>
                            <ul>
                                <li><?= Html::a('Installation Guide', ['static/installation'], ['target' => '_blank']) ?></li>
                                <li><?= Html::a('Tool Overview', ['static/architecture'], ['target' => '_self']) ?></li>
                                <li><?= Html::a('Tutorials', ['static/tutorials'], ['target' => '_blank']) ?></li>
                                <!--
                                <li><a href="https://github.com/utwente-fmt/vercors/tree/master/examples" target="_blank">Examples</a></li>
                                -->
                                <li>
                                    <a href="https://github.com/utwente-fmt/vercors/wiki" target="_blank">VerCors with
                                        IDEs</a>

                                </li>
                                <li><a href="https://github.com/utwente-fmt/vercors/issues" target="_blank">Issue
                                        Tracker</a></li>
                            </ul>
                        </li>
                        <li><?= Html::a('Showcases', ['site/examples'], ['target' => '_blank']) ?></li>
                        <li><?= Html::a('News', ['news/index']) ?></li>
                        <li><?= Html::a('Publications', ['static/publications']) ?></li>
                        <li><?= Html::a('About', ['static/about']) ?>
                            <ul>
                                <li><?= Html::a('VerCors Team', ['static/about', '#' => 'Team']) ?></li>
                                <li><?= Html::a('Contact', ['static/about', '#' => 'Contact']) ?></li>
                                <li><?= Html::a('Credits', ['static/about', '#' => 'Credits']) ?></li>
                                <li><?= Html::a('License', ['static/license']) ?></li>
                            </ul>
                        </li>
                    </ul>
                </nav>

                <!-- Banner -->
                <div id="banner">
                    <div class="container">
                        <section>
                            <?php if(isset($this->blocks['banner'])) { ?><?= $this->blocks['banner'] ?><?php } ?>
                            <a href="https://github.com/utwente-fmt/vercors" class="button alt" target="_blank">View on
                                Github</a>
                            <a href="/try_online" class="button alt">Try VerCors Online</a>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fake Banner for small screens -->
    <div id="fakeheader">
        <section style="text-align: center; padding:5%; color:#eee; background-image: url(../../web/images/Vercors_Original.jpg); background-repeat: no-repeat; background-size: cover; box-shadow: inset 0 0 0 1000px rgba(0,0,0,.2);">
            <?php if(isset($this->blocks['banner'])) { ?><?= $this->blocks['banner'] ?><?php } ?>
            <a href="https://github.com/utwente-fmt/vercors" class="button alt" target="_blank">View on Github</a>
        </section>
    </div>

    <?= $content ?>

    <div id="footer">

        <!-- Copyright -->
        <div class="row">
            <a href="https://fmt.ewi.utwente.nl" target="_blank"><img src="/images/FMT logo.png" alt=""></a>
            <div class="copyright">
                <a href="https://fmt.ewi.utwente.nl/research/projects/view/vercors/" target="_blank">Copyright <code>&copy</code> The VerCors Project 2007-2019</a>
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