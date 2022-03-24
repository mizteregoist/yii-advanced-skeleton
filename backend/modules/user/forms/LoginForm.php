<?php

namespace backend\modules\user\forms;

use backend\modules\user\models\User;
use Yii;
use yii\base\Model;

class LoginForm extends Model
{
	public $isNewRecord;

	public $username;
	public $email;
	public $password;
	public $group;
	public $status;

	private $_user;


	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			['status', 'default', 'value' => User::STATUS_ACTIVE],
			['status', 'in', 'range' => [
				User::STATUS_ACTIVE,
				User::STATUS_INACTIVE,
				User::STATUS_BLOCKED,
				User::STATUS_DELETED
			]],

			[['name', 'lastname', 'patronymic', 'username', 'email', 'password'], 'filter', 'filter' => 'trim'],
			[['email'], 'unique', 'targetClass' => '\backend\modules\user\models\User'],
			[['username'], 'unique', 'targetClass' => '\backend\modules\user\models\User'],
			[['name', 'lastname', 'patronymic', 'username', 'email', 'password'], 'string', 'min' => 2, 'max' => 255],

			[['username', 'email', 'password', 'group'], 'required'],
			[['name', 'lastname', 'patronymic'], 'validateName'],
			['email', 'validateEmail'],
		];
	}

	public function attributeLabels(): array
	{
		return [
			'status' => 'Статус',
			'name' => 'Имя',
			'lastname' => 'Фамилия',
			'patronymic' => 'Отчество',
			'username' => 'Логин',
			'email' => 'E-mail',
			'group' => 'Группа',
			'password' => 'Пароль',
		];
	}

	/**
	 * Validates the username.
	 * This method serves as the inline validation for username.
	 *
	 * @param string $attribute the attribute currently being validated
	 * @param array $params the additional name-value pairs given in the rule
	 */
	public function validateName(string $attribute, array $params)
	{
		if (!preg_match('/^[а-яё\s-]+$/iu', $this->$attribute)) {
			$this->addError($attribute, 'Используйте русские буквы.');
		}
	}

	/**
	 * Проверка email.
	 * @param string $attribute
	 * @param array $params
	 */
	public function validateEmail(string $attribute, array $params)
	{
		if (!preg_match('/^((([0-9A-Za-z]{1}[-0-9A-z\.]{1,}[0-9A-Za-z]{1})|([0-9А-Яа-я]{1}[-0-9А-я\.]{1,}[0-9А-Яа-я]{1}))@([-A-Za-z]{1,}\.){1,2}[-A-Za-z]{2,})$/u', $this->email)) {
			$this->addError($attribute, 'Некорректный e-mail.');
		}
	}

	/**
	 * Signs user up.
	 *
	 * @return User|null the saved model or null if saving fails
	 */
	public function signup()
	{
		if (!$this->validate()) {
			return null;
		}

		$user = new User();
		$user->username = $this->username;
		$user->email = $this->email;
		$user->status = $this->status;
		$user->setPassword($this->password);
		$user->generateAuthKey();

		return $user->save() ? $user : null;
	}

	/**
	 * Logs in a user using the provided username and password.
	 *
	 * @return bool whether the user is logged in successfully
	 */
	public function login()
	{
		if ($this->validate()) {
			return Yii::$app->user->login($this->getUser(), 3600 * 24 * 30);
		}

		return false;
	}

	/**
	 * Finds user by [[username]]
	 *
	 * @return User|null
	 */
	protected function getUser()
	{
		if ($this->_user === null) {
			$this->_user = User::findOne([
				'email' => $this->email,
				'status' => User::STATUS_ACTIVE
			]);
		}

		return $this->_user;
	}
}