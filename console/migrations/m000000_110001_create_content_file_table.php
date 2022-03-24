<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%content_file}}`.
 */
class m000000_110001_create_content_file_table extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->createTable('content_file', [
			'id' => $this->primaryKey()->defaultValue("nextval('content_file_id_seq'::regclass)")->comment('ID'),
			'content_id' => $this->integer()->notNull()->comment('Запись'),
			'active' => $this->boolean()->notNull()->defaultValue(true)->comment('Активность'),
			'sort' => $this->integer()->notNull()->comment('Сортировка'),
			'original_name' => $this->string()->notNull()->comment('Исходное название файла'),
			'filename' => $this->string()->notNull()->comment('Название файла'),
			'type' => $this->integer()->notNull()->comment('Тип файла'),
			'mime' => $this->string()->notNull()->comment('MIME тип'),
			'timestamp' => $this->dateTime()->notNull()->comment('Время создания'),
		]);
		$this->addForeignKey('fk_content_id', 'content_file', 'content_id', 'content', 'id', 'CASCADE');
		$this->addCommentOnTable('content_file', 'Файлы');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropForeignKey('fk_content_id', 'content_file');
		$this->dropTable('content_file');
	}
}
