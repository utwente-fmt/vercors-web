<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Author".
 *
 * @property integer $id
 * @property string $firstname
 * @property string $lastname
 *
 * @property PublicationAuthor[] $publicationAuthors
 * @property Publication[] $publications
 */
class Author extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Author';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['firstname', 'lastname'], 'string', 'max' => 256],
						[['firstname', 'lastname'], 'required'],
        ];
    }
		
		public function fullname() {
			return $this->firstname . ' ' . $this->lastname;
		}

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'firstname' => 'First name',
            'lastname' => 'Last name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPublicationAuthors()
    {
        return $this->hasMany(PublicationAuthor::className(), ['authorid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPublications()
    {
        return $this->hasMany(Publication::className(), ['id' => 'publicationid'])->viaTable('PublicationAuthor', ['authorid' => 'id']);
    }
}
