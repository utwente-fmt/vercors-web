<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Language".
 *
 * @property integer $id
 * @property string $name
 *
 * @property Example[] $examples
 */
class Language extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Language';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 256],
						[['extension'], 'string', 'max' => 16],
						[['name', 'extension'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Language name',
						'extension' => 'File extension',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExamples()
    {
        return $this->hasMany(Example::className(), ['languageid' => 'id']);
    }
}
