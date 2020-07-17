<?php
namespace app\components;

use app\models\Language;
use yii\base\Widget;

class VerificationWidget extends Widget
{
    public $initialCode;
    public $initialLanguage;
    public $hide;
    public $initialCodeOnHide;

    public function init() {
        parent::init();

        if($this->hide === null) {
            $this->hide = false;
        }

        if($this->initialCode === null) {
            $this->initialCode = '// Write your code here';
        }

        if($this->initialCodeOnHide === null) {
            $this->initialCodeOnHide = $this->initialCode;
        }

        if($this->initialLanguage === null) {
            $this->initialLanguage = 'java';
        }
    }

    public function run() {
        return $this->render('verification_widget', [
            'languages' => Language::find()->all(),
            'initialCode' => $this->initialCode,
            'initialLanguage' => $this->initialLanguage,
            'hide' => $this->hide,
            'initialCodeOnHide' => $this->initialCodeOnHide,
        ]);
    }
}
?>