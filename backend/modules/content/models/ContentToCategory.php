<?php

namespace backend\modules\content\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "content_to_category".
 *
 * @property int $content_id Контент
 * @property int $category_id Категория
 *
 * @property ContentCategory $category
 * @property Content $content
 */
class ContentToCategory extends ActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName(): string
	{
		return 'content_to_category';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(): array
	{
		return [
			[['content_id', 'category_id'], 'required'],
			[['content_id', 'category_id'], 'default', 'value' => null],
			[['content_id', 'category_id'], 'integer'],
			[['content_id', 'category_id'], 'unique', 'targetAttribute' => ['content_id', 'category_id']],
			[['content_id'], 'exist', 'skipOnError' => true, 'targetClass' => Content::class, 'targetAttribute' => ['content_id' => 'id']],
			[['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContentCategory::class, 'targetAttribute' => ['category_id' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(): array
	{
		return [
			'content_id' => 'Контент',
			'category_id' => 'Категория',
		];
	}

	/**
	 * Gets query for [[Category]].
	 *
	 * @return ActiveQuery
	 */
	public function getCategory(): ActiveQuery
	{
		return $this->hasOne(ContentCategory::class, ['id' => 'category_id']);
	}

	/**
	 * Gets query for [[Content]].
	 *
	 * @return ActiveQuery
	 */
	public function getContent(): ActiveQuery
	{
		return $this->hasOne(Content::class, ['id' => 'content_id']);
	}
}
