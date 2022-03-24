<?php

namespace backend\modules\content\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Content]].
 *
 * @see Content
 */
class ContentQuery extends ActiveQuery
{
	public function active(): ContentQuery
	{
		return $this->andWhere(['active' => true]);
	}

	public function section(): ContentQuery
	{
		return $this->andWhere(['type_id' => Content::TYPE_SECTION]);
	}

	public function element(): ContentQuery
	{
		return $this->andWhere(['type_id' => Content::TYPE_ELEMENT]);
	}

	public function smallBlock(): ContentQuery
	{
		return $this->andWhere(['type_id' => Content::TYPE_SMALL_BLOCK]);
	}

	public function bigBlock(): ContentQuery
	{
		return $this->andWhere(['type_id' => Content::TYPE_BIG_BLOCK]);
	}

	/**
	 * {@inheritdoc}
	 * @return Content[]|array
	 */
	public function all($db = null): array
	{
		return parent::all($db);
	}

	/**
	 * {@inheritdoc}
	 * @return Content|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
