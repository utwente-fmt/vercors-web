<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Example;

/**
 * ExampleSearch represents the model behind the search form about `app\models\Example`.
 */
class ExampleSearch extends Example
{
	public $backendname;
	public $feature;
	public $source;
	public $languagename;
	
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'backendid', 'languageid', 'publicationid'], 'integer'],
            [['description', 'date', 'title', 'backendname', 'feature', 'source', 'languagename'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Example::find()->joinWith(
					['backend', 'language', 'publication']
				);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
					'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
	
	if ($this->feature > 0) {
		$query->innerJoinWith('features');
	}
				if ($this->source > 0) {
					$query->innerJoinWith('sources');
				}
				
				$dataProvider->sort->attributes['backendname'] = [
					'asc' => ['Backend.name' => SORT_ASC],
					'desc' => ['Backend.name' => SORT_DESC],
				];
				
				$dataProvider->sort->attributes['languagename'] = [
					'asc' => ['Language.name' => SORT_ASC],
					'desc' => ['Language.name' => SORT_DESC],
				];
				
        // grid filtering conditions
        $query->andFilterWhere([
            'Example.id' => $this->id,
            'backendid' => $this->backendid,
            'languageid' => $this->languageid,
            'publicationid' => $this->publicationid,
            'date' => $this->date,
        ]);

        $query->andFilterWhere(['like', 'description', $this->description]);
				$query->andFilterWhere(['like', 'Example.title', $this->title]);
				$query->andFilterWhere(['like', 'Backend.name', $this->backendname]);
				$query->andFilterWhere(['like', 'Language.name', $this->languagename]);
				$query->andFilterWhere(['like', 'featureid', $this->feature]);
				$query->andFilterWhere(['like', 'sourceid', $this->source]);
				
        return $dataProvider;
    }
}
