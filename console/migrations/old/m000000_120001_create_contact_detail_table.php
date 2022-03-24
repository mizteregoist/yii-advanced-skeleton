<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%contact_detail}}`.
 */
class m000000_120001_create_contact_detail_table extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->createTable('contact_detail', [
			'id' => $this->primaryKey()->defaultValue("nextval('content_type_id_seq'::regclass)")->comment('ID'),
			'contact_address_id' => $this->integer()->notNull()->comment('ID адреса'),
			'sort' => $this->integer()->notNull()->defaultValue(100)->comment('Сортировка'),
			'active' => $this->boolean()->notNull()->defaultValue(true)->comment('Активность'),
			'name' => $this->string()->notNull()->comment('Название'),
			'code' => $this->string()->notNull()->comment('Символьный код'),
			'description' => $this->text()->null()->comment('Описание'),
			'phone' => $this->string()->null()->comment('Телефон'),
			'phone_additional' => $this->string()->null()->comment('Телефон (дополнительный)'),
			'email' => $this->string()->null()->comment('E-mail'),
			'email_additional' => $this->string()->null()->comment('E-mail (дополнительный)'),
			'calls_from' => $this->time()->null()->comment('Звонить с'),
			'calls_to' => $this->time()->null()->comment('Звонить до'),
		]);
		$this->addForeignKey(
			'fk_contact_address_id',
			'contact_detail',
			'contact_address_id',
			'contact_address',
			'id',
			'CASCADE'
		);
		$this->addCommentOnTable('contact_detail', 'Контактные данные');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropForeignKey('fk_contact_address_id', 'contact_detail');
		$this->dropTable('contact_detail');
	}
}
