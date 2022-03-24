<?php

namespace backend\modules\user\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * UserSearch represents the model behind the search form about `backend\modules\user\models\User`.
 */
class UserSearch extends User
{
	public $group;

	/**
	 * @inheritdoc
	 */
	public function rules(): array
	{
		return [
			[['name', 'lastname', 'patronymic', 'username', 'email'], 'filter', 'filter' => 'trim'],

			[['id', 'status'], 'integer'],
			[['group', 'auth_key', 'password_hash', 'password_reset_token'], 'safe'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function scenarios(): array
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
	public function search(array $params): ActiveDataProvider
	{
		$query = User::find();

		$query->leftJoin('auth_assignment', '{{auth_assignment.user_id}} = {{user.id}}')->distinct();

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

		// grid filtering conditions
		$query->andFilterWhere([
			'user.id' => $this->id,
			'user.status' => $this->status,
			'auth_assignment.item_name' => $params['UserSearch']['group'] ?? null
		]);

		$query
			->andFilterWhere(['ilike', 'user.username', $this->username])
			->andFilterWhere(['ilike', 'user.name', $this->name])
			->andFilterWhere(['ilike', 'user.lastname', $this->lastname])
			->andFilterWhere(['ilike', 'user.patronymic', $this->patronymic])
			->andFilterWhere(['like', 'user.email', $this->email]);

		if (!Yii::$app->user->identity->hasGroup('superuser')) {
			$query->andFilterWhere(['not', ['auth_assignment.item_name' => 'superuser']]);
		}

		// По умолчанию показываем сверху свежие данные.
		if (empty($params['sort'])) {
			$query->orderBy(['created_at' => SORT_ASC]);
		}

		return $dataProvider;
	}
}