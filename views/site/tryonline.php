<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\DetailView;
use f4soft\codemirror\CodeMirror;

$this->title = 'Try VerCors Online';

?>

<h1>Try VerCors online</h1>
<p>
    Does the following program verify?
    You can also start with an <?= Html::a('existing example', ['site/examples']) ?>!
</p>

<div class="example-form">
    <label class="control-label" for="example-backendid">Language</label>
    <?= Html::dropDownList('lang', '1', ArrayHelper::map($languages, 'extension', 'extension'), ['class' => 'form-control']) ?>
    <?php $this->registerJs("$('select[name=lang]').on('change', function () {
        $('input[name=language]').val($(this).val());
    })"); ?>
</div>

<div style="margin-top: 12pt">
    <label class="control-label" for="example-backendid">Code</label>
    <?= Codemirror::widget([
        'name' => 'examplecode',
        'value' => '// start here...',
        'clientOptions' => [
            'lineNumbers' => true,
            'matchBrackets' => true,
            'indentUnit' => 2,
            'indentWithTabs' => true
        ]
    ]) ?>

    <?php $this->registerJs("editor.on('change', function () {
editor.save();
})"); ?>
</div>
<div id="verification-progress" style="margin: 12pt 0 12pt 0; font-style: italic">
    Note, verification may take a while and has a time-out of 20 seconds.
</div>
<pre id="verification-log"></pre>
<div>
    <?= Html::hiddenInput('language', 'pvl') ?>

    <?= Html::button('Verify this!', [
        'class' => 'btn btn-success',
        'id' => 'verifythis'
    ]) ?>
</div>