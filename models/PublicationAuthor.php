<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "PublicationAuthor".
 *
 * @property integer $authorid
 * @property integer $publicationid
 *
 * @property Author $author
 * @property Publication $publication
 */
class PublicationAuthor extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'PublicationAuthor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['authorid', 'publicationid'], 'required'],
            [['authorid', 'publicationid'], 'integer'],
            [['authorid'], 'exist', 'skipOnError' => true, 'targetClass' => Author::className(), 'targetAttribute' => ['authorid' => 'id']],
            [['publicationid'], 'exist', 'skipOnError' => true, 'targetClass' => Publication::className(), 'targetAttribute' => ['publicationid' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'authorid' => 'Authorid',
            'publicationid' => 'Publicationid',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(Author::className(), ['id' => 'authorid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPublication()
    {
        return $this->hasOne(Publication::className(), ['id' => 'publicationid']);
    }
}
