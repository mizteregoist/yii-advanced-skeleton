<?php

namespace backend\modules\content\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\modules\content\models\ContentCategory;

class ContentCategorySearch extends ContentCategory
{
	/**
	 * {@inheritdoc}
	 */
	public function rules(): array
	{
		return [
			[['id', 'sort'], 'integer'],
			[['active'], 'boolean'],
			[['name', 'code', 'created_at', 'updated_at'], 'safe'],
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
	 * @param array $params
	 *
	 * @return ActiveDataProvider
	 */
	public function search(array $params): ActiveDataProvider
	{
		$query = ContentCategory::find();

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		$this->load($params);

		if (!$this->validate()) {
			$query->where('0=1');
			return $dataProvider;
		}

		$query->andFilterWhere([
			'id' => $this->id,
			'sort' => $this->sort,
			'active' => $this->active,
		]);

		$query->andFilterWhere(['ilike', 'name', $this->name])
			->andFilterWhere(['ilike', 'code', $this->code]);

		return $dataProvider;
	}
}
