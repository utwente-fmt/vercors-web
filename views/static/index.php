<?php
use kartik\markdown\Markdown;
use yii\helpers\Html;
$this->title = 'Home';
?>

<style>
abstract { 
  font-weight: bold;
  text-shadow: 2px 2px #696969;
}
</style>

<?php $this->beginBlock('banner'); ?>
<header class="major">
    <h2>Verification of Concurrent and Distributed Software</h2>
    <span class="byline"><abstract>
            VerCors is a tool for static verification of parallel programs.
            VerCors aims to verify many different concurrency constructs,
            including: heterogeneous concurrency (Java and C), GPU kernels
            using barriers and atomics (OpenCL), and compiler directives
            as used in deterministic parallelism (OpenMP). VerCors is able
            to prove data-race freedom, memory safety, and functional
            correctness of (concurrent) programs written in Java, C,
            OpenCL, OpenMP, and its own Prototypal Verification Language PVL.
        </abstract></span>
</header>
<?php $this->endBlock('banner'); ?>

<!-- NEWS -->
<div class="wrapper style2" style="background-color: #f1f1f1f1;text-align:center;">
    <section class="container">

        <header class="major" id="overview">
            <h2>Latest News</h2>
            <span class="byline"></span>
        </header>

        <p style="position:relative;text-align:center;">
        <div style="height:400px;width:76%;overflow:auto;background-color:#f1f1f1f1;scrollbar-base-color:gold;font-family:sans-serif;padding:10px;margin:auto;">
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
            <?= Html::a('More news', ['news/index'], ['class' => 'button alt']) ?>
        </div>
        </p>


    </section>
</div>

<!-- Main -->
<div id="main" class="wrapper style1">
    <section class="container">
        <table id="t01" text-align="left">
            <tr>
                <!-- The ul here is just to be able to see nice buttons -->
                <td style="text-align: left;">
                    <ul class="style">
                        <a href="wiki#installing-and-running-vercors" class="link link--dark" target="_self">
                            <span class="fa fa-wrench"></span>
                        </a>
                        <h2>Tool Installation</h2>
                        <p>
                            <span style="text-align:justify">The VerCors toolset can be installed on macOSX, Linux and Windows (via Cygwin). Follow the complete installation guidelines <a
                                        href="wiki#installing-and-running-vercors" class="url" target="_self">here</a>.</span>
                        </p>
                    </ul>
                </td>
                <td style="text-align: left;">
                    <ul class="style"><a href="publications" target="_blank"
                        class="link link--dark">
                        <span class="fa fa-cloud"></span>
                        </a>
                        <h2>Latest Publications</h2>
                        <p>A complete list of publications is listed <a href="publications" class="url" target="_self">here</a>.</p>
                    </ul>
                </td>
            </tr>
            <tr>
                <td style="text-align: left;">
                    <ul class="style"><a href="https://github.com/utwente-fmt/vercors/issues" target="_blank"
                                         class="link link--dark">
                            <span class="fa fa-cogs"></span>
                        </a>
                        <h2>Report a Bug</h2>
                        <p>For bug reports and feature requests,<br>visit <a
                                    href='https://github.com/utwente-fmt/vercors/issues' target='_blank' class='url'>https://github.com/utwente-fmt/vercors/issues</a>.
                            For questions and support, email to <a href='mailto:VERCORS@LISTS.utwente.nl'
                                                                   target='_blank' class=''>vercors@lists.utwente.nl</a>
                        </p>
                    </ul>
                </td>
                <td style="text-align: left;">
                    <ul class="style">
                        <a href="https://github.com/utwente-fmt/vercors/releases/latest" target="_blank"
                           class="link link--dark">
                            <span class="fa fa-leaf"></span>
                        </a>
                        <h2>Latest Release</h2>
                        <p>Get VerCors latest release from GitHub.
                            <a href="https://github.com/utwente-fmt/vercors/releases/latest" target="_blank"
                               class="link link--dark">
                                <button>
                                    <img src="/images/release.png" width="5%" height="5%" alt="Release"></button></p>
                        </a>
                    </ul>
                </td>
            </tr>
        </table>
    </section>
</div>
