<?php

namespace backend\modules\user\models;

use Yii;
use yii\db\ActiveRecord;
use yii\rbac\Role;
use yii\base\Exception;
use yii\web\IdentityInterface;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * User model
 *
 * @property int $id
 * @property string $name
 * @property string $lastname
 * @property string $patronymic
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $verification_token
 * @property string $email
 * @property string $email_confirmed
 * @property string $email_verification_code
 * @property string $email_verification_expiration
 * @property string $phone
 * @property string $phone_confirmed
 * @property string $phone_verification_code
 * @property string $phone_verification_expiration
 * @property string $auth_key
 * @property string $access_token
 * @property int $status
 * @property int $last_login
 * @property int $created_at
 * @property int $updated_at
 * @property array $group
 * @property string $password write-only password
 * @property string $statusText
 * @property string $fullName
 */
class User extends ActiveRecord implements IdentityInterface
{
	const STATUS_DELETED = 0;
	const STATUS_PROCESS = 7;
	const STATUS_BLOCKED = 8;
	const STATUS_INACTIVE = 9;
	const STATUS_ACTIVE = 10;

	const STATUS_TEXT = [
		self::STATUS_DELETED => 'Удален',
		self::STATUS_PROCESS => 'В процессе',
		self::STATUS_BLOCKED => 'Заблокирован',
		self::STATUS_INACTIVE => 'Неактивен',
		self::STATUS_ACTIVE => 'Активен',
	];

	const PHONE_PATTERN = "/^(\+7|8)(\()([345689][\d]{2})(\))(\s)([\d]{3})(\-)([\d]{2})(\-)([\d]{2})$/";
	const PHONE_REPLACEMENT = "$3$6$8$10";
	const TRIM_PHONE_PATTERN = "/[a-z()\-\s\+]/";
	const STR_TO_PHONE_PATTERN = "/([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})/";
	const STR_TO_PHONE_NUMBER_REPLACEMENT = "+7 ($1) $2-$3-$4";
	const STR_TO_TEL_REPLACEMENT = "+7$1$2$3$4";
	const STR_TO_PHONE_REPLACEMENT = "$1$2$3$4";

	public $password;
	public $group;

	/**
	 * @inheritdoc
	 */
	public static function tableName(): string
	{
		return 'user';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(): array
	{
		$rules = [
			['status', 'default', 'value' => self::STATUS_PROCESS],
			['status', 'in',
				'range' => [
					self::STATUS_ACTIVE,
					self::STATUS_INACTIVE,
					self::STATUS_BLOCKED,
					self::STATUS_PROCESS,
					self::STATUS_DELETED,
				],
			],

			[['name', 'lastname', 'patronymic', 'username', 'email', 'phone'], 'filter', 'filter' => 'trim'],
			[['id'], 'unique', 'targetClass' => '\backend\modules\user\models\User'],
			[['email'], 'unique', 'targetClass' => '\backend\modules\user\models\User'],
			[['phone'], 'unique', 'targetClass' => '\backend\modules\user\models\User'],
			[['phone', 'phone_verification_code', 'email_verification_code'], 'integer'],
			[['name', 'lastname', 'patronymic', 'username', 'email'], 'string', 'min' => 2, 'max' => 255],
			[['email_confirmed', 'phone_confirmed'], 'boolean'],
			[['password_hash', 'password_reset_token'], 'string'],
			[['email_verification_expiration', 'phone_verification_expiration', 'last_login', 'password', 'group'], 'safe'],
			['email', 'required'],
		];

		return $rules;
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels(): array
	{
		return [
			'id' => 'ID',
			'status' => 'Статус',
			'name' => 'Имя',
			'lastname' => 'Фамилия',
			'patronymic' => 'Отчество',
			'username' => 'Логин',
			'email' => 'E-mail',
			'phone' => 'Телефон',
			'group' => 'Группа',
			'password' => 'Пароль',
			'access_token' => 'Токен',
			'created_at' => 'Создан',
			'updated_at' => 'Обновлен',
		];
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function beforeSave($insert): bool
	{
		if (parent::beforeSave($insert)) {
			if ($this->isNewRecord) {
				$this->auth_key = \Yii::$app->security->generateRandomString();
				$this->created_at = date('Y-m-d H:i:s');
			}
			$this->username = $this->email;
			if (!empty($this->group)) {
				if (is_string($this->group)) {
					$this->group = [$this->group];
				}
				$this->setGroup($this->group);
			}
			$this->updated_at = date('Y-m-d H:i:s');
			return true;
		}
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function getId()
	{
		return $this->getPrimaryKey();
	}

	/**
	 * @return int|mixed|null
	 */
	public static function getLastId()
	{
		$last = self::find()
			->select(['id'])
			->distinct(true)
			->orderBy(['id' => SORT_DESC])
			->asArray()
			->one();
		return $last['id'] ?? null;
	}

	/**
	 * @inheritdoc
	 */
	public function getAuthKey(): string
	{
		return $this->auth_key;
	}

	/**
	 * @inheritdoc
	 */
	public function validateAuthKey($authKey): bool
	{
		return $this->getAuthKey() === $authKey;
	}

	/**
	 * Validates password
	 *
	 * @param string $password password to validate
	 *
	 * @return boolean if password provided is valid for current user
	 */
	public function validatePassword(string $password): bool
	{
		return Yii::$app->security->validatePassword($password, $this->password_hash);
	}

	/**
	 * @param $username
	 * @param $password
	 * @return User|string[]
	 */
	public static function validateUser($username, $password)
	{
		$user = self::findByUsername($username);
		if (!$username or !$password or !$user) {
			return ['error_message' => 'empty parameters'];
		}
		if ($user->validatePassword($password)) {
			return $user;
		}
		return ['error_message' => 'wrong username or password'];
	}

	/**
	 * Validates password
	 *
	 * @param string $email
	 *
	 * @return boolean if password provided is valid for current user
	 */
	public function existEmail(string $email): bool
	{
		$user = self::findOne([
			'email' => $email,
			'status' => self::STATUS_ACTIVE,
		]);
		return !empty($user);
	}

	/**
	 * @inheritdoc
	 */
	public static function findIdentity($id)
	{
		return self::findOne([
			'id' => $id,
			'status' => self::STATUS_ACTIVE,
		]);
	}

	/**
	 * Finds user by username
	 *
	 * @param string $username
	 *
	 * @return static|null
	 */
	public static function findByUsername(string $username): ?User
	{
		return self::findOne([
			'username' => $username,
			'status' => self::STATUS_ACTIVE,
		]);
	}

	/**
	 * Finds user by username
	 *
	 * @param string $email
	 *
	 * @return static|null
	 */
	public static function findByEmail(string $email): ?User
	{
		return self::findOne([
			'email' => $email,
			'status' => [
				self::STATUS_ACTIVE,
				self::STATUS_PROCESS,
			],
		]);
	}

	/**
	 * @inheritdoc
	 */
	public static function findIdentityByAccessToken($token, $type = null)
	{
		return self::findOne([
			'access_token' => $token,
			'status' => self::STATUS_ACTIVE,
		]);
	}

	/**
	 * Finds user by password reset token
	 *
	 * @param string $token password reset token
	 *
	 * @return User|null
	 */
	public static function findByPasswordResetToken(string $token): ?User
	{
		if (!self::isPasswordResetTokenValid($token)) {
			return null;
		}

		return self::findOne([
			'password_reset_token' => $token,
			'status' => self::STATUS_ACTIVE,
		]);
	}

	/**
	 * Finds user by verification email token
	 *
	 * @param string $token verify email token
	 *
	 * @return User|null
	 */
	public static function findByVerificationToken(string $token): ?User
	{
		return self::findOne([
			'verification_token' => $token,
			'status' => self::STATUS_INACTIVE,
		]);
	}

	/**
	 * Finds out if password reset token is valid
	 *
	 * @param $token
	 *
	 * @return boolean
	 */
	public static function isPasswordResetTokenValid($token): bool
	{
		if (empty($token)) {
			return false;
		}

		$timestamp = (int)substr($token, strrpos($token, '_') + 1);
		$expire = Yii::$app->params['user.passwordResetTokenExpire'];
		return $timestamp + $expire >= time();
	}

	/**
	 * Generates password hash from password and sets it to the model
	 *
	 * @param string $password
	 *
	 * @throws Exception
	 */
	public function setPassword(string $password)
	{
		$this->password_hash = Yii::$app->security->generatePasswordHash($password);
	}

	/**
	 * Generates "remember me" authentication key
	 *
	 * @throws Exception
	 */
	public function generateAuthKey()
	{
		$this->auth_key = Yii::$app->security->generateRandomString();
	}

	/**
	 * Generates access token
	 *
	 * @throws Exception
	 */
	public function generateAccessToken()
	{
		$this->access_token = Yii::$app->security->generateRandomString();
	}

	/**
	 * Generates new password reset token
	 *
	 * @throws Exception
	 */
	public function generatePasswordResetToken()
	{
		$this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
	}

	/**
	 * Generates new token for email verification
	 *
	 * @throws Exception
	 */
	public function generateEmailVerificationToken()
	{
		$this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
	}

	/**
	 * Removes password reset token
	 */
	public function removePasswordResetToken()
	{
		$this->password_reset_token = null;
	}

	public function getFullName(): string
	{
		return Html::encode($this->name . ' ' . ($this->patronymic ? $this->patronymic . ' ' : '') . $this->lastname);
	}

	/**
	 * Проверяем, можем ли создать группу или разрешение с таким кодом.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public static function canCreateAuth(string $name): bool
	{
		$auth = Yii::$app->authManager;
		$role = $auth->getRole($name);
		$permission = $auth->getPermission($name);

		return empty($role) && empty($permission);
	}

	/**
	 * Список групп.
	 *
	 * @param bool $onlyName Получить только name.
	 *
	 * @return array
	 */
	public static function getGroups(bool $onlyName = false): array
	{
		$roles = ArrayHelper::getColumn(Yii::$app->authManager->getRoles(), 'description');

		if (!Yii::$app->user->identity->hasGroup('superuser')) {
			unset($roles['superuser'], $roles['developer'], $roles['system']);
		}

		return $onlyName ? array_keys($roles) : $roles;
	}

	/**
	 * Получаем группу юзера.
	 *
	 * @return array
	 */
	public function getGroup(): array
	{
		return ArrayHelper::map(Yii::$app->authManager->getRolesByUser($this->id), 'description', 'name');
	}

	/**
	 * Назначаем группу юзеру.
	 *
	 * @param array $roles
	 */
	public function setGroup(array $roles = [])
	{
		try {
			$auth = Yii::$app->authManager;
			$auth->revokeAll($this->id);
			foreach ($roles as $role) {
				if (!empty($role)) {
					$userRole = $auth->getRole($role);
					if (!empty($userRole) && $userRole instanceof Role) {
						$auth->assign($userRole, $this->id);
					}
				}
			}
		} catch (\Exception $e) {

		}
	}

	/**
	 * Состоит ли в группе.
	 *
	 * @param string|array $groups
	 *
	 * @return bool
	 */
	public function hasGroup($groups): bool
	{
		if (is_array($groups)) {
			foreach ($groups as $group) {
				if (in_array($group, $this->getGroup())) {
					return true;
				}
			}
		} else {
			return in_array($groups, $this->getGroup());
		}

		return false;
	}

	/**
	 * Проверка разрешений.
	 *
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function can(string $permission): bool
	{
		return Yii::$app->user->can($permission);
	}

	/**
	 * Получаем текст статуса юзера.
	 *
	 * @return string
	 */
	public function getStatusText(): string
	{
		return self::STATUS_TEXT[$this->status];
	}

	/**
	 * @param int $id
	 * @param bool $object Объектом или массивом.
	 *
	 * @return array|bool|User|null
	 */
	public static function getUserById(int $id, bool $object = false)
	{
		if (empty($id)) {
			return false;
		}
		$query = self::find()
			->where([
				'status' => self::STATUS_ACTIVE,
				'id' => $id,
			]);
		if (!$object) {
			$query->asArray();
		}
		return $query->one();
	}

	/**
	 * Получить пользователей по группе.
	 *
	 * @param string|array $group
	 * @param bool $object Объектом или массивом.
	 *
	 * @return array|User[]
	 */
	public static function getUsersByGroup($group, bool $object = false): array
	{
		$authAssignment = AuthAssignment::find()
			->select(['user_id'])
			->where(['item_name' => $group])
			->asArray()
			->all();
		$usersId = ArrayHelper::getColumn($authAssignment, 'user_id');
		$query = self::find()->where([
			'status' => self::STATUS_ACTIVE,
			'id' => $usersId,
		]);
		if (!$object) {
			$query->asArray();
		}
		return $query->all();
	}

	/**
	 * @return bool
	 */
	public function isActive(): bool
	{
		return $this->status == self::STATUS_ACTIVE;
	}

	/**
	 * @return bool
	 */
	public function isInactive(): bool
	{
		return $this->status == self::STATUS_INACTIVE;
	}

	/**
	 * @return bool
	 */
	public function isBlocked(): bool
	{
		return $this->status === self::STATUS_BLOCKED;
	}

	/**
	 * @return bool
	 */
	public function isProcess(): bool
	{
		return $this->status === self::STATUS_PROCESS;
	}

	/**
	 * @return bool
	 */
	public function isDeleted(): bool
	{
		return $this->status === self::STATUS_DELETED;
	}

	/**
	 * @return bool
	 */
	public function isSuperuser(): bool
	{
		return $this->hasGroup('superuser');
	}

	/**
	 * @return bool
	 */
	public function isDeveloper(): bool
	{
		return $this->hasGroup('developer');
	}

	/**
	 * @return bool
	 */
	public function isAdmin(): bool
	{
		return $this->hasGroup('admin');
	}

	/**
	 * @return bool
	 */
	public function isModerator(): bool
	{
		return $this->hasGroup('moderator');
	}

	/**
	 * @return bool
	 */
	public function isUser(): bool
	{
		return $this->hasGroup('user');
	}

	/**
	 * @return bool
	 */
	public function isSystem(): bool
	{
		return $this->hasGroup('system');
	}
}
