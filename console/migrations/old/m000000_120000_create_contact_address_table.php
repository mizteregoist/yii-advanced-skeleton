<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%contact_address}}`.
 */
class m000000_120000_create_contact_address_table extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->createTable('contact_address', [
			'id' => $this->primaryKey()->defaultValue("nextval('content_type_id_seq'::regclass)")->comment('ID'),
			'sort' => $this->integer()->notNull()->defaultValue(100)->comment('Сортировка'),
			'active' => $this->boolean()->notNull()->defaultValue(true)->comment('Активность'),
			'name' => $this->string()->null()->comment('Название'),
			'code' => $this->string()->null()->comment('Символьный код'),
			'description' => $this->text()->null()->comment('Описание'),
			'timezone' => $this->string()->null()->defaultValue('+3:00')->comment('Часовой пояс'),
			'address' => $this->string()->null()->comment('Адрес'),
			'postal_code' => $this->string()->null()->comment('Индекс'),
			'country' => $this->string()->null()->comment('Страна'),
			'region' => $this->string()->null()->comment('Регион'),
			'area' => $this->string()->null()->comment('Район'),
			'city' => $this->string()->null()->comment('Город'),
			'settlement' => $this->string()->null()->comment('Населенный пункт'),
			'street' => $this->string()->null()->comment('Улица'),
			'house' => $this->string()->notNull()->comment('Дом'),
			'building' => $this->string()->null()->comment('Корпус/Строение'),
			'flat' => $this->string()->null()->comment('Квартира/Офис'),
			'lat' => $this->string()->null()->comment('Широта'),
			'lon' => $this->string()->null()->comment('Долгота'),
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropTable('contact_address');
	}
}
