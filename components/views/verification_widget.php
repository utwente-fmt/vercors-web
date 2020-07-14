<?php
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\ActiveForm;
use f4soft\codemirror\CodeMirror;
?>

<div>
    <div class="example-form">
        <label class="control-label" for="example-backendid">Language:</label>
        <?= Html::dropDownList('lang', $initialLanguage, ArrayHelper::map($languages, 'extension', 'name'), [
            'class' => 'form-control',
        ]) ?>
    </div>

    <div style="margin-top: 12pt">
        <?= Codemirror::widget([
            'name' => 'examplecode',
            'value' => $initialCode,
            'clientOptions' => [
                'lineNumbers' => true,
                'matchBrackets' => true,
                'indentUnit' => 2,
                'indentWithTabs' => true
            ]
        ]) ?>
        <?php $this->registerJs("editor.on('change', function () { editor.save(); })"); ?>
    </div>
    <div>
        <?= Html::button('Verify this!', [
            'class' => 'btn btn-success verifythis',
        ]) ?>
    </div>
    <div class="verification-progress" style="margin: 12pt 0 12pt 0; font-style: italic; display: none">
        Note, verification may take a while and has a time-out of 20 seconds.
    </div>
    <pre class="verification-log" style="display: none"></pre>
</div>