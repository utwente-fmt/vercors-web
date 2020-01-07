<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ExampleSource".
 *
 * @property integer $exampleid
 * @property integer $sourceid
 *
 * @property Example $example
 * @property Source $source
 */
class ExampleSource extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ExampleSource';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['exampleid', 'sourceid'], 'required'],
            [['exampleid', 'sourceid'], 'integer'],
            [['exampleid'], 'exist', 'skipOnError' => true, 'targetClass' => Example::className(), 'targetAttribute' => ['exampleid' => 'id']],
            [['sourceid'], 'exist', 'skipOnError' => true, 'targetClass' => Source::className(), 'targetAttribute' => ['sourceid' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'exampleid' => 'Exampleid',
            'sourceid' => 'Sourceid',
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
    public function getSource()
    {
        return $this->hasOne(Source::className(), ['id' => 'sourceid']);
    }
}
