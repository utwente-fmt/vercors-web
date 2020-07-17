<?php
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use f4soft\codemirror\CodeMirror;
?>

<div class="verification-container">
    <?php if($hide) { ?>
        <pre style="position: relative; margin-bottom: 0" class="verification-text"><?= Html::encode($initialCodeOnHide) ?><span class="code-buttons"><span style="display: none" class="fa fa-times-circle code-close-button plain-close"></span><span class="fa fa-pencil code-edit-button"></span><span class="fa fa-play code-run-button"></span></span></pre>
    <?php } ?>
    <div class="verification-widget verification-non-plain" style="<?= $hide ? 'display: none' : '' ?>">
        <div style="position: relative">
            <textarea name="examplecode" rows="20" <?= $hide ? '' : 'data-code-mirror="true"' ?>><?= Html::encode($initialCode) ?></textarea>
            <span class="code-buttons">
                <span class="fa fa-times-circle code-close-button"></span>
                <span class="fa fa-play code-run-button"></span>
            </span>
        </div>

        <div style="background-color: #dddddd; padding: 0.4ex 1ex">
            <label class="control-label" for="example-backendid">Language:</label>
            <?= Html::dropDownList('lang', $initialLanguage, ArrayHelper::map($languages, 'extension', 'name'), [
                'class' => 'form-control',
            ]) ?>
        </div>
    </div>
    <div class="verification-progress verification-non-plain" style="display: none; background-color: #dddddd; padding: 0.4ex 1ex" >
        <span class="fa"></span>
        <span class="verification-progress-text"></span>
    </div>
    <pre class="verification-log verification-non-plain" style="display: none"></pre>
</div>