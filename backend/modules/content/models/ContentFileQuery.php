<?php

namespace backend\modules\content\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[ContentFile]].
 *
 * @see ContentFile
 */
class ContentFileQuery extends ActiveQuery
{
	public function active()
	{
		return $this->andWhere(['active' => true]);
	}

	/**
	 * {@inheritdoc}
	 * @return ContentFile[]|array
	 */
	public function all($db = null): array
	{
		return parent::all($db);
	}

	/**
	 * {@inheritdoc}
	 * @return ContentFile|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
