<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ExampleFeature".
 *
 * @property integer $exampleid
 * @property integer $featureid
 *
 * @property Example $example
 * @property Feature $feature
 */
class ExampleFeature extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ExampleFeature';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['exampleid', 'featureid'], 'required'],
            [['exampleid', 'featureid'], 'integer'],
            [['exampleid'], 'exist', 'skipOnError' => true, 'targetClass' => Example::className(), 'targetAttribute' => ['exampleid' => 'id']],
            [['featureid'], 'exist', 'skipOnError' => true, 'targetClass' => Feature::className(), 'targetAttribute' => ['featureid' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'exampleid' => 'Exampleid',
            'featureid' => 'Featureid',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExample()
    {
        return $this->hasOne(Example::className(), ['id' => 'exampleid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeature()
    {
        return $this->hasOne(Feature::className(), ['id' => 'featureid']);
    }
}
