<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%content_category}}`.
 */
class m000000_110003_create_content_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('content_category', [
            'id' => $this->primaryKey()->defaultValue("nextval('content_category_id_seq'::regclass)")->comment('ID'),
	        'sort' => $this->integer()->notNull()->defaultValue(100)->comment('Сортировка'),
	        'active' => $this->boolean()->notNull()->defaultValue(true)->comment('Активность'),
	        'name' => $this->string()->notNull()->comment('Название'),
	        'code' => $this->string()->notNull()->comment('Символьный код'),
	        'created_at' => $this->dateTime()->notNull()->comment('Время создания'),
	        'updated_at' => $this->dateTime()->notNull()->comment('Время изменения'),
        ]);
	    $this->addCommentOnTable('content_category', 'Категории контента');

	    $this->createTable('content_to_category', [
		    'content_id' => $this->integer()->notNull()->defaultValue(0)->comment('Контент'),
		    'category_id' => $this->integer()->notNull()->defaultValue(0)->comment('Категория'),
	    ]);
	    $this->addPrimaryKey('content_to_category_pri', 'content_to_category', ['content_id', 'category_id']);
	    $this->addForeignKey(
		    'content_to_category_fk_content_id',
		    'content_to_category',
		    'content_id',
		    'content',
		    'id',
		    'CASCADE'
	    );
	    $this->addForeignKey(
		    'content_to_category_fk_category_id',
		    'content_to_category',
		    'category_id',
		    'content_category',
		    'id',
		    'CASCADE'
	    );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
	    $this->dropForeignKey('content_to_category_fk_content_id', 'content_to_category');
	    $this->dropForeignKey('content_to_category_fk_category_id', 'content_to_category');
	    $this->dropPrimaryKey('content_to_category_pri', 'content_to_category');
	    $this->dropTable('content_category');
	    $this->dropTable('content_to_category');
    }
}
