<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%content}}`.
 */
class m000000_110002_create_content_closure_table extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->createTable('content_closure', [
			'anc' => $this->integer()->notNull()->defaultValue(0)->comment('Родитель'),
			'dsc' => $this->integer()->notNull()->defaultValue(0)->comment('Потомок'),
			'lvl' => $this->integer()->notNull()->defaultValue(0)->comment('Уровень'),
		]);
		$this->addPrimaryKey('content_closure_pri', 'content_closure', ['anc', 'dsc', 'lvl']);
		$this->addForeignKey(
			'content_closure_fk_anc',
			'content_closure',
			'anc',
			'content',
			'id',
			'CASCADE'
		);
		$this->addForeignKey(
			'content_closure_fk_dsc',
			'content_closure',
			'dsc',
			'content',
			'id',
			'CASCADE'
		);
		$this->addCommentOnTable('content_closure', 'Дерево контента');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropForeignKey('content_closure_fk_anc', 'content_closure');
		$this->dropForeignKey('content_closure_fk_dsc', 'content_closure');
		$this->dropPrimaryKey('content_closure_pri', 'content_closure');
		$this->dropTable('content_closure');
	}
}
