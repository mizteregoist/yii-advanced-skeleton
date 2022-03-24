<?php

namespace backend\modules\user\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[AuthRule]].
 *
 * @see AuthRule
 */
class AuthRuleQuery extends ActiveQuery
{
	/**
	 * {@inheritdoc}
	 * @return ActiveQuery[]|array
	 */
	public function all($db = null): array
	{
		return parent::all($db);
	}

	/**
	 * {@inheritdoc}
	 * @return ActiveQuery|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}
