<?php

namespace backend\modules\user\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "auth_rule".
 *
 * @property string $name
 * @property resource|null $data
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property AuthItem[] $authItems
 */
class AuthRule extends ActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName(): string
	{
		return 'auth_rule';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(): array
	{
		return [
			[['name'], 'required'],
			[['data'], 'string'],
			[['created_at', 'updated_at'], 'default', 'value' => null],
			[['created_at', 'updated_at'], 'integer'],
			[['name'], 'string', 'max' => 64],
			[['name'], 'unique'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(): array
	{
		return [
			'name' => 'Название',
			'data' => 'Данные',
			'created_at' => 'Создано',
			'updated_at' => 'Обновлено',
		];
	}

	/**
	 * Gets query for [[AuthItems]].
	 *
	 * @return ActiveQuery
	 */
	public function getAuthItems(): ActiveQuery
	{
		return $this->hasMany(AuthItem::class, ['rule_name' => 'name']);
	}

	/**
	 * {@inheritdoc}
	 * @return ActiveQuery
	 */
	public static function find(): ActiveQuery
	{
		return new AuthRuleQuery(get_called_class());
	}
}
