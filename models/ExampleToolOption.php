<?php


namespace app\models;


use yii\db\ActiveRecord;

class ExampleToolOption extends ActiveRecord
{
    public static function tableName() {
        return 'ExampleToolOption';
    }
}