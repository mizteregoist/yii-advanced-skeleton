<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%content}}`.
 */
class m000000_110000_create_content_table extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->createTable('content', [
			'id' => $this->primaryKey()->defaultValue("nextval('content_id_seq'::regclass)")->comment('ID'),
			'type_id' => $this->integer()->notNull()->comment('Тип'),
			'position_id' => $this->integer()->notNull()->comment('Расположение'),
			'sort' => $this->integer()->notNull()->defaultValue(100)->comment('Сортировка'),
			'active' => $this->boolean()->notNull()->defaultValue(true)->comment('Активность'),
			'name' => $this->string()->notNull()->comment('Название'),
			'code' => $this->string()->notNull()->comment('Символьный код'),
			'title' => $this->string()->null()->comment('Заголовок страницы'),
			'description' => $this->string()->null()->comment('Описание'),
			'href' => $this->string()->null()->comment('Ссылка'),
			'link' => $this->string()->null()->comment('Текст ссылки'),
			'content' => $this->text()->comment('Контент'),
			'created_at' => $this->dateTime()->notNull()->comment('Время создания'),
			'updated_at' => $this->dateTime()->notNull()->comment('Время изменения'),
		]);
		$this->addCommentOnTable('content', 'Контент');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropTable('content');
	}
}
