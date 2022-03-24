<?php

namespace backend\modules\user\components;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;
use yii\web\IdentityInterface;
use yii\web\User;

/**
 * Class UserComponent
 * @package common\components
 *
 * @property-read null|array $identityAndDurationFromCookie
 */
class UserComponent extends User
{
	/**
	 * @return void
	 * @throws InvalidConfigException
	 */
	public function init()
	{
		$this->enableAutoLogin = true;
		$this->getIdentityAndDurationFromCookie();
		parent::init();
	}

	/**
	 * Очистка сессии при блокировке пользователя.
	 * @return array|null
	 */
	protected function getIdentityAndDurationFromCookie(): ?array
	{
		$value = Yii::$app->getRequest()->getCookies()->getValue($this->identityCookie['name']);
		if ($value === null) {
			return null;
		}
		$data = json_decode($value, true);
		if (is_array($data) && count($data) == 3) {
			[$id, $authKey, $duration] = $data;
			/* @var $class IdentityInterface */
			$class = $this->identityClass;
			$identity = $class::findIdentity($id);
			if ($identity !== null) {
				if (!$identity instanceof IdentityInterface) {
					throw new InvalidValueException('$class::findIdentity() must return an object implementing IdentityInterface.');
				} elseif (!$identity->validateAuthKey($authKey)) {
					if (Yii::$app->session->has($this->idParam)) {
						Yii::$app->session->remove($this->idParam);
						$this->removeIdentityCookie();
					}
				} else {
					return ['identity' => $identity, 'duration' => $duration];
				}
			}
		}
		$this->removeIdentityCookie();
		return null;
	}

	/**
	 * Проверка разрешений.
	 * @param $permissionName
	 * @param array $params
	 * @param bool $allowCaching
	 * @return bool
	 */
	public function can($permissionName, $params = [], $allowCaching = true): bool
	{
		$permissionName = (array)$permissionName;

		foreach ($permissionName as $permission) {
			if (parent::can($permission, $params, $allowCaching)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function isSuperuser(): bool
	{
		return !$this->isGuest && $this->identity->hasGroup('superuser');
	}

	/**
	 * @return bool
	 */
	public function isDeveloper(): bool
	{
		return !$this->isGuest && $this->identity->hasGroup('developer');
	}

	/**
	 * @return bool
	 */
	public function isAdmin(): bool
	{
		return !$this->isGuest && $this->identity->hasGroup('admin');
	}

	/**
	 * @return bool
	 */
	public function isModerator(): bool
	{
		return !$this->isGuest && $this->identity->hasGroup('moderator');
	}

	/**
	 * @return bool
	 */
	public function isUser(): bool
	{
		return !$this->isGuest && $this->identity->hasGroup('user');
	}

	/**
	 * @return bool
	 */
	public function isSystem(): bool
	{
		return !$this->isGuest && $this->identity->hasGroup('system');
	}
}