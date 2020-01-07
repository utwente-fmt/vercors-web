<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Publication".
 *
 * @property integer $id
 * @property string $title
 * @property string $conference
 * @property string $year
 * @property string $url
 *
 * @property Example[] $examples
 * @property PublicationAuthor[] $publicationAuthors
 * @property Author[] $authors
 */
class Publication extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Publication';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['year'], 'required'],
            [['year'], 'safe'],
            [['title', 'conference'], 'string', 'max' => 256],
						[['title', 'conference'], 'required'],
            [['url'], 'string', 'max' => 512],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Article name',
            'conference' => 'Conference name',
            'year' => 'Year',
            'url' => 'URL',
        ];
    }
		
		public function authorsText() {
			$parts = [];
			foreach ($this->authors as $author) {
				$parts[] = $author->fullname();
			}
			asort($parts);
			return implode(', ', $parts);
		}
		
		public function shortDisplayName() {
			return $this->conference . ' ' . $this->year;
		}
		
		public function displayName() {
			return $this->shortDisplayName() . ': ' . $this->title;
		}

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExamples()
    {
        return $this->hasMany(Example::className(), ['publicationid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPublicationAuthors()
    {
        return $this->hasMany(PublicationAuthor::className(), ['publicationid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthors()
    {
        return $this->hasMany(Author::className(), ['id' => 'authorid'])->viaTable('PublicationAuthor', ['publicationid' => 'id']);
    }
}
