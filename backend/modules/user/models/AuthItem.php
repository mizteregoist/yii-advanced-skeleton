<?php

namespace backend\modules\user\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "auth_item".
 *
 * @property string $name
 * @property int $type
 * @property string|null $description
 * @property string|null $rule_name
 * @property resource|null $data
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property AuthAssignment[] $authAssignments
 * @property AuthItemChild[] $authItemChildren
 * @property AuthItemChild[] $authItemChildren0
 * @property AuthItem[] $children
 * @property AuthItem[] $parents
 * @property AuthRule $ruleName
 */
class AuthItem extends ActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName(): string
	{
		return 'auth_item';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(): array
	{
		return [
			[['name', 'type'], 'required'],
			[['type', 'created_at', 'updated_at'], 'default', 'value' => null],
			[['type', 'created_at', 'updated_at'], 'integer'],
			[['description', 'data'], 'string'],
			[['name', 'rule_name'], 'string', 'max' => 64],
			[['name'], 'unique'],
			[['rule_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthRule::class, 'targetAttribute' => ['rule_name' => 'name']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(): array
	{
		return [
			'name' => 'Название',
			'type' => 'Тип',
			'description' => 'Описание',
			'rule_name' => 'Название правила',
			'data' => 'Данные',
			'created_at' => 'Создано',
			'updated_at' => 'Обновлено',
		];
	}

	/**
	 * Gets query for [[AuthAssignments]].
	 *
	 * @return ActiveQuery
	 */
	public function getAuthAssignments(): ActiveQuery
	{
		return $this->hasMany(AuthAssignment::class, ['item_name' => 'name']);
	}

	/**
	 * Gets query for [[AuthItemChildren]].
	 *
	 * @return ActiveQuery
	 */
	public function getAuthItemParent(): ActiveQuery
	{
		return $this->hasMany(AuthItemChild::class, ['parent' => 'name']);
	}

	/**
	 * Gets query for [[AuthItemChildren0]].
	 *
	 * @return ActiveQuery
	 */
	public function getAuthItemChildren(): ActiveQuery
	{
		return $this->hasMany(AuthItemChild::class, ['child' => 'name']);
	}

	/**
	 * Gets query for [[Children]].
	 *
	 * @return ActiveQuery
	 * @throws InvalidConfigException
	 */
	public function getChildren(): ActiveQuery
	{
		return $this->hasMany(AuthItem::class, ['name' => 'child'])->viaTable('auth_item_child', ['parent' => 'name']);
	}

	/**
	 * Gets query for [[Parents]].
	 *
	 * @return ActiveQuery
	 * @throws InvalidConfigException
	 */
	public function getParents(): ActiveQuery
	{
		return $this->hasMany(AuthItem::class, ['name' => 'parent'])->viaTable('auth_item_child', ['child' => 'name']);
	}

	/**
	 * Gets query for [[RuleName]].
	 *
	 * @return ActiveQuery
	 */
	public function getRuleName(): ActiveQuery
	{
		return $this->hasOne(AuthRule::class, ['name' => 'rule_name']);
	}

	/**
	 * {@inheritdoc}
	 * @return ActiveQuery
	 */
	public static function find(): ActiveQuery
	{
		return new AuthItemQuery(get_called_class());
	}
}
