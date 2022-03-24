<?php

namespace backend\modules\user\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "auth_assignment".
 *
 * @property string $item_name
 * @property int|null $created_at
 * @property int $user_id
 *
 * @property AuthItem $itemName
 */
class AuthAssignment extends ActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName(): string
	{
		return 'auth_assignment';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(): array
	{
		return [
			[['item_name', 'user_id'], 'required'],
			[['created_at', 'user_id'], 'default', 'value' => null],
			[['created_at', 'user_id'], 'integer'],
			[['item_name'], 'string', 'max' => 64],
			[['item_name', 'user_id'], 'unique', 'targetAttribute' => ['item_name', 'user_id']],
			[['item_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::class, 'targetAttribute' => ['item_name' => 'name']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(): array
	{
		return [
			'item_name' => 'Название группы',
			'created_at' => 'Создано',
			'user_id' => 'ID пользователя',
		];
	}

	/**
	 * Gets query for [[ItemName]].
	 *
	 * @return ActiveQuery
	 */
	public function getItemName(): ActiveQuery
	{
		return $this->hasOne(AuthItem::class, ['name' => 'item_name']);
	}

	/**
	 * {@inheritdoc}
	 * @return ActiveQuery
	 */
	public static function find(): ActiveQuery
	{
		return new AuthAssignmentQuery(get_called_class());
	}
}
