<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%contact_address_schedule}}`.
 */
class m000000_120002_create_contact_address_schedule_table extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->createTable('contact_address_schedule', [
			'contact_address_id' => $this->integer()->notNull()->comment('ID адреса'),
			'day_of_week' => $this->string()->notNull()->comment('День недели'),
			'from' => $this->time()->notNull()->comment('С'),
			'to' => $this->time()->notNull()->comment('До'),
			'break_from' => $this->time()->null()->comment('Перерыв с'),
			'break_to' => $this->time()->null()->comment('Перерыв до'),
		]);
		$this->addForeignKey(
			'fk_contact_address_id',
			'contact_address_schedule',
			'contact_address_id',
			'contact_address',
			'id',
			'CASCADE'
		);
		$this->addPrimaryKey(
			'pk_contact_address_schedule',
			'contact_address_schedule',
			['contact_address_id', 'day_of_week'],
		);
		$this->addCommentOnTable('contact_address_schedule', 'Режим работы');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropForeignKey('fk_contact_address_id', 'contact_address_schedule');
		$this->dropPrimaryKey('pk_contact_address_schedule', 'contact_address_schedule');
		$this->dropTable('contact_address_schedule');
	}
}
