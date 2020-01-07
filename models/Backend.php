<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Backend".
 *
 * @property integer $id
 * @property string $name
 *
 * @property Example[] $examples
 */
class Backend extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Backend';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 256],
						['name', 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Backend name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExamples()
    {
        return $this->hasMany(Example::className(), ['backendid' => 'id']);
    }
}
