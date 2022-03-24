<?php

use backend\modules\user\models\User;
use yii\base\InvalidConfigException;
use yii\db\Migration;
use yii\db\Transaction;
use yii\db\Exception;

/**
 * Class m210830_094718_init_admin
 */
class m000000_000001_init_admin extends Migration
{
	/**
	 * {@inheritdoc}
	 * @throws Throwable
	 */
	public function up()
	{
		$transaction = $this->getDb()->beginTransaction();
		$user = \Yii::createObject([
			'class' => User::class,
			'email' => 'admin@webapp.local',
			'status' => User::STATUS_ACTIVE,
			'username' => 'admin@webapp.local',
			'password_hash' => Yii::$app->security->generatePasswordHash('A123456a'),
		]);
		if (!$user->insert(false)) {
			$transaction->rollBack();
		}
		$user->save();
		$transaction->commit();

		$auth = Yii::$app->authManager;
		$role = $auth->getRole('superuser');
		$auth->revokeAll($user->id);
		$auth->assign($role, $user->id);
	}

	/**
	 * {@inheritdoc}
	 */
	public function down()
	{
		return true;
	}
}
