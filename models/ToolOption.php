<?php


namespace app\models;


use yii\db\ActiveRecord;

class ToolOption extends ActiveRecord
{
    public static function tableName() {
        return 'ToolOption';
    }
}