<?php

namespace backend\modules\content\models;

use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\utils\TextUtil;

/**
 * This is the model class for table "content_category".
 *
 * @property int $id ID
 * @property int $sort Сортировка
 * @property bool $active Активность
 * @property string $name Название
 * @property string $code Символьный код
 * @property string $created_at Время создания
 * @property string $updated_at Время изменения
 *
 * @property ContentToCategory[] $contentToCategories
 * @property Content[] $contents
 */
class ContentCategory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'content_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
	        [['name'], 'required'],
            [['sort'], 'default', 'value' => 1],
	        [['active'], 'default', 'value' => true],

            [['sort'], 'integer'],
            [['active'], 'boolean'],
            [['name', 'code'], 'string', 'max' => 255],
	        [['created_at', 'updated_at'], 'safe'],

	        [['code'], 'unique', 'targetClass' => self::class],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'sort' => 'Сортировка',
            'active' => 'Активность',
            'name' => 'Название',
            'code' => 'Символьный код',
            'created_at' => 'Время создания',
            'updated_at' => 'Время изменения',
        ];
    }

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert): bool
	{
		if (parent::beforeSave($insert)) {
			if ($this->isNewRecord) {
				$this->created_at = date('Y-m-d H:i:s');
			}
			$this->updated_at = date('Y-m-d H:i:s');
			if (empty($this->code)) {
				$this->code = rand(1000, 9999) . '-' . TextUtil::transliteration($this->name);
			}
			return true;
		}
		return false;
	}

    /**
     * Gets query for [[ContentToCategories]].
     *
     * @return ActiveQuery
     */
    public function getContentToCategories(): ActiveQuery
    {
        return $this->hasMany(ContentToCategory::class, ['category_id' => 'id']);
    }

	/**
	 * Gets query for [[Contents]].
	 *
	 * @return ActiveQuery
	 * @throws InvalidConfigException
	 */
    public function getContents(): ActiveQuery
    {
        return $this->hasMany(Content::class, ['id' => 'content_id'])->viaTable('content_to_category', ['category_id' => 'id']);
    }
}
