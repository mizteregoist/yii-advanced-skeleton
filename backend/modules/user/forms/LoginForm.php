<?php

namespace backend\modules\user\forms;

use Yii;
use yii\base\Model;
use backend\modules\user\models\User;

/**
 * Login form
 * @property string $username
 * @property string $password
 * @property bool $rememberMe
 *
 * @property User $_user
 */
class LoginForm extends Model
{
	public $username;
	public $password;
	public $rememberMe = true;

	private $_user;


	/**
	 * {@inheritdoc}
	 */
	public function rules(): array
	{
		return [
			[['username', 'password'], 'string', 'min' => 2, 'max' => 255],
			[['username', 'password'], 'filter', 'filter' => 'trim'],
			[['username', 'password'], 'required'],
			['rememberMe', 'boolean'],
			['password', 'validatePassword'],
			['username', 'validateEmail'],
		];
	}

	public function attributeLabels(): array
	{
		return [
			'username' => 'E-mail',
			'password' => 'Пароль',
		];
	}

	/**
	 * Validates the password.
	 * This method serves as the inline validation for password.
	 *
	 * @param string $attribute the attribute currently being validated
	 * @param array $params the additional name-value pairs given in the rule
	 */
	public function validatePassword($attribute, $params)
	{
		if (!$this->hasErrors()) {
			$user = $this->getUser();
			if (!$user || !$user->validatePassword($this->password)) {
				$this->addError($attribute, 'Некорректный e-mail или пароль');
			}
		}
	}

	/**
	 * Проверка email.
	 * @param string $attribute the attribute currently being validated
	 * @param array $params the additional name-value pairs given in the rule
	 */
	public function validateEmail($attribute, $params)
	{
		if (!preg_match('/^((([0-9A-Za-z]{1}[-0-9A-z\.]{1,}[0-9A-Za-z]{1})|([0-9А-Яа-я]{1}[-0-9А-я\.]{1,}[0-9А-Яа-я]{1}))@([-A-Za-z]{1,}\.){1,2}[-A-Za-z]{2,})$/u', $this->username)) {
			$this->addError($attribute, 'Некорректный e-mail');
		}
	}

	/**
	 * Logs in a user using the provided email and password.
	 *
	 * @return bool whether the user is logged in successfully
	 */
	public function login(): bool
	{
		if ($this->validate()) {
			return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
		}

		return false;
	}

	/**
	 * Finds user by [[email]]
	 *
	 * @return User|null
	 */
	protected function getUser(): ?User
	{
		if ($this->_user === null) {
			$this->_user = User::findOne([
				'email' => $this->username,
				'status' => User::STATUS_ACTIVE,
			]);
		}

		return $this->_user;
	}
}