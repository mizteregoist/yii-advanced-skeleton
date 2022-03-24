<?php

namespace backend\modules\content\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class ContentSearch extends Content
{
	/**
	 * {@inheritdoc}
	 */
	public function rules(): array
	{
		return [
			[['type_id', 'position_id', 'sort'], 'integer'],
			[['active'], 'boolean'],
			[['content'], 'string'],
			[['created_at', 'updated_at'], 'safe'],
			[['name', 'code', 'title', 'description'], 'string'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function scenarios(): array
	{
		return Model::scenarios();
	}

	/**
	 * Creates data provider instance with search query applied
	 *
	 * @param array $params
	 * @param array $types
	 * @return ActiveDataProvider
	 */
	public function search(array $params, array $types = []): ActiveDataProvider
	{
		$query = Content::find();

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		$this->load($params);

		if (!$this->validate()) {
			return $dataProvider;
		}

		$query->andFilterWhere([
			'id' => $this->id,
			'sort' => $this->sort,
			'active' => $this->active,
			'type_id' => $this->type_id,
			'position_id' => $this->position_id,
		]);

		$query->andWhere(['!=', 'id', 0]);

		if (!empty($types)) {
			$query->andFilterWhere(['type_id' => $types]);
		}

		$query->andFilterWhere(['like', 'name', $this->name])
			->andFilterWhere(['like', 'code', $this->code])
			->andFilterWhere(['like', 'code', $this->title])
			->orderBy([
				'position_id' => SORT_ASC,
				'type_id' => SORT_DESC,
				'sort' => SORT_ASC,
			]);

		return $dataProvider;
	}
}
