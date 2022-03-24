<?php

use yii\db\Migration;

class m000000_000000_init extends Migration
{
	public function safeUp()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('user', [
			'id' => $this->primaryKey()->defaultValue("nextval('user_id_seq'::regclass)")->comment('ID'),
			'status' => $this->integer()->notNull()->defaultValue(10)->comment('Статус'),
			'name' => $this->string()->null()->comment('Имя'),
			'lastname' => $this->string()->null()->defaultValue(null)->comment('Фамилия'),
			'patronymic' => $this->string()->null()->defaultValue(null)->comment('Отчество'),
			'username' => $this->string()->notNull()->unique()->comment('Логин'),
			'auth_key' => $this->string()->notNull()->comment('Ключ авторизации'),
			'access_token' => $this->string()->null()->defaultValue(null)->comment('Токен доступа'),
			'verification_token' => $this->string()->null()->defaultValue(null)->comment('Токен подтверждения'),
			'password_hash' => $this->string()->notNull()->comment('Хеш пароля'),
			'password_reset_token' => $this->string()->null()->unique()->defaultValue(null)->comment('Токен сброса пароля'),
			'email' => $this->string()->notNull()->unique()->comment('E-mail'),
			'email_confirmed' => $this->boolean()->null()->defaultValue(null)->comment('E-mail подтвержден'),
			'email_verification_code' => $this->integer()->null()->defaultValue(null)->comment('Код подтверждения E-mail'),
			'email_verification_expiration' => $this->dateTime()->null()->defaultValue(null)->comment('Срок действия кода подтверждения E-mail'),
			'phone' => $this->bigInteger()->null()->defaultValue(null)->unique()->comment('Телефон'),
			'phone_confirmed' => $this->boolean()->null()->defaultValue(null)->comment('Телефон подтвержден'),
			'phone_verification_code' => $this->integer()->null()->defaultValue(null)->comment('Код подтверждения телефона'),
			'phone_verification_expiration' => $this->dateTime()->null()->defaultValue(null)->comment('Срок действия кода подтверждения телефона'),
			'last_login' => $this->dateTime()->null()->defaultValue(null)->comment('Время последнего входа'),
			'created_at' => $this->dateTime()->notNull()->comment('Время создания'),
			'updated_at' => $this->dateTime()->notNull()->comment('Время изменения'),
		], $tableOptions);

		$this->dropColumn('auth_assignment', 'user_id');
		$this->addColumn('auth_assignment', 'user_id', $this->integer()->notNull());
		$this->addPrimaryKey('auth_assignment_pkey', 'auth_assignment', ['item_name', 'user_id']);
		$this->createIndex('idx-auth_assignment-user_id', 'auth_assignment', 'user_id');
	}

	public function safeDown()
	{
		$this->dropTable('user');
	}
}
