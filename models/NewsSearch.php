<?php


namespace app\models;


use yii\base\Model;
use yii\data\ActiveDataProvider;

class NewsSearch extends News
{
    public function rules() {
        return [
            [['id'], 'integer'],
            [['date'], 'safe'],
            [['title'], 'safe']
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params) {
        $q = News::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $q
        ]);

        $this->load($params);

        if(!$this->validate()) {
            return $dataProvider;
        }

        $q->andFilterWhere([
            'id' => $this->id,
        ]);

        $q->andFilterWhere(['like', 'title', $this->title]);
        $q->andFilterWhere(['like', 'date', $this->date]);

        return $dataProvider;
    }
}