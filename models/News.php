<?php


namespace app\models;


use yii\db\ActiveRecord;

class News extends ActiveRecord
{
    public static function tableName() {
        return 'News';
    }

    public function rules() {
        return [
            [['date'], 'date', 'format' => 'yyyy-M-d'],
            [['title'], 'string'],
            [['content'], 'string'],
        ];
    }

    public static function getRecent($count = 5) {
        return static::find()->orderBy(['date' => SORT_DESC])->limit($count)->all();
    }

    public function getDate() {
        return (new \DateTime($this->date))->format('F j, Y');
    }

    public function afterFind()
    {
        $this->date = (new \DateTime($this->date))->format('Y-m-d');
        parent::afterFind();
    }
}