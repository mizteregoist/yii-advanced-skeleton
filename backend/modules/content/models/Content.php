<?php

namespace backend\modules\content\models;

use common\utils\TextUtil;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseInflector;

/**
 * This is the model class for table "content".
 *
 * @property int $id ID
 * @property int $type_id Тип
 * @property int $position_id Расположение
 * @property int $sort Сортировка
 * @property bool $active Активность
 * @property string $name Название
 * @property string $code Символьный код
 * @property string|null $title Заголовок страницы
 * @property string|null $description Описание
 * @property string|null $href Ссылка
 * @property string|null $link Текст ссылки
 * @property string $content Контент
 * @property string $created_at Время создания
 * @property string $updated_at Время изменения
 *
 * @property ContentFile[] $contentFiles=
 *
 * @property ContentCategory[] $categories
 * @property ContentToCategory[] $contentToCategories
 *
 * @property ContentClosure $node
 * @property ContentClosure[] $ancestors
 * @property ContentClosure[] $descendants
 * @property ContentClosure[] $parents
 * @property ContentClosure[] $child
 *
 * @property int $parent_id
 * @property array $category_id
 * @property Content|null $parent
 * @property int|null $parentId
 */
class Content extends ActiveRecord
{
	const TYPE_SECTION = 0;
	const TYPE_ELEMENT = 1;
	const TYPE_SMALL_BLOCK = 2;
	const TYPE_BIG_BLOCK = 3;

	const TYPES = [
		self::TYPE_SECTION,
		self::TYPE_ELEMENT,
		self::TYPE_SMALL_BLOCK,
		self::TYPE_BIG_BLOCK,
	];
	const TYPES_BLOCK = [
		self::TYPE_SMALL_BLOCK,
		self::TYPE_BIG_BLOCK,
	];
	const TYPES_LIST = [
		self::TYPE_SECTION => 'Раздел',
		self::TYPE_ELEMENT => 'Элемент',
		self::TYPE_SMALL_BLOCK => 'Маленький блок',
		self::TYPE_BIG_BLOCK => 'Большой блок',
	];
	const TYPES_BLOCK_LIST = [
		self::TYPE_SMALL_BLOCK => 'Маленький блок',
		self::TYPE_BIG_BLOCK => 'Большой блок',
	];

	const POSITION_DEFAULT = 0;
	const POSITION_MAIN = 1;
	const POSITION_SECTION = 2;
	const POSITION_DETAIL = 3;

	const POSITIONS = [
		self::POSITION_DEFAULT,
		self::POSITION_MAIN,
		self::POSITION_SECTION,
		self::POSITION_DETAIL,
	];
	const POSITIONS_LIST = [
		self::POSITION_DEFAULT => 'По умолчанию',
		self::POSITION_MAIN => 'Главная страница',
		self::POSITION_SECTION => 'Страница раздела',
		self::POSITION_DETAIL => 'Детальная страница',
	];

	public $imageInput;
	public $fileInput;
	public $backgroundInput;
	public $parent_id;
	public $category_id;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(): string
	{
		return 'content';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(): array
	{
		return [
			['type_id', 'in', 'range' => self::TYPES],
			['position_id', 'in', 'range' => self::POSITIONS],

			[['type_id', 'position_id', 'name'], 'required'],
			[['type_id', 'position_id'], 'default', 'value' => null],

			[['sort'], 'default', 'value' => 1],
			[['active'], 'default', 'value' => true],

			[['type_id', 'position_id', 'sort'], 'integer'],
			[['active'], 'boolean'],
			[['content'], 'string'],
			[['code'], 'unique', 'targetClass' => self::class],
			[['name', 'code', 'title', 'description', 'href', 'link'], 'string', 'max' => 255],
			[['created_at', 'updated_at', 'parent_id', 'category_id'], 'safe'],

			[['imageInput'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg', 'maxFiles' => 30],
			[['fileInput'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 30],
			[['backgroundInput'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg'],

			['code', 'validateCode'],
		];
	}

	public function validateCode($attribute, $params)
	{
		if (!preg_match('/^[a-z0-9-]+$/', $this->$attribute)) {
			$this->addError($attribute, 'Используйте только английские буквы в нижнем регистре, цифры и символ -');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(): array
	{
		return [
			'id' => 'ID',

			'type_id' => 'Тип',
			'position_id' => 'Расположение',

			'sort' => 'Сортировка',
			'active' => 'Активность',

			'name' => 'Название',
			'code' => 'Символьный код',

			'title' => 'Заголовок страницы',
			'description' => 'Описание страницы',
			'keywords' => 'Ключевые слова',

			'href' => 'Ссылка',
			'link' => 'Текст ссылки',

			'content' => 'Контент',

			'created_at' => 'Время создания',
			'updated_at' => 'Время изменения',

			'parent_id' => 'Раздел',
			'category_id' => 'Категории',

			'imageInput' => 'Изображения',
			'fileInput' => 'Файлы',
			'backgroundInput' => 'Задний фон',
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
	 * Gets query for [[ContentFiles]].
	 *
	 * @return ActiveQuery
	 */
	public function getContentFiles(): ActiveQuery
	{
		return $this->hasMany(ContentFile::class, ['content_id' => 'id']);
	}

	/**
	 * Gets query for [[ContentClosures]].
	 *
	 * @return ActiveQuery
	 */
	public function getNode(): ActiveQuery
	{
		return $this->hasOne(ContentClosure::class, ['dsc' => 'id'])
			->onCondition(['anc' => $this->id, 'lvl' => 0]);
	}

	/**
	 * Gets query for [[ContentClosures]].
	 *
	 * @return ActiveQuery
	 */
	public function getAncestors(): ActiveQuery
	{
		return $this->hasMany(ContentClosure::class, ['dsc' => 'id'])->onCondition(['>', 'lvl', 0]);
	}

	/**
	 * Gets query for [[ContentClosures0]].
	 *
	 * @return ActiveQuery
	 */
	public function getDescendants(): ActiveQuery
	{
		$closure = ContentClosure::tableName();
		return $this->hasMany(ContentClosure::class, ['anc' => 'id'])
			->leftJoin("{$closure} AS p", "p.dsc = {$closure}.dsc AND p.lvl = 1")
			->onCondition(['>', "{$closure}.lvl", 0]);
	}

	/**
	 * Gets query for [[ContentClosures]].
	 *
	 * @return ActiveQuery
	 */
	public function getParents(): ActiveQuery
	{
		return $this->hasMany(ContentClosure::class, ['dsc' => 'id'])->onCondition(['lvl' => 1]);
	}

	/**
	 * Gets query for [[ContentClosures]].
	 *
	 * @return ActiveQuery
	 */
	public function getChild(): ActiveQuery
	{
		return $this->hasMany(ContentClosure::class, ['anc' => 'id'])->onCondition(['lvl' => 1]);
	}

	/**
	 * Gets query for [[ContentToCategories]].
	 *
	 * @return ActiveQuery
	 */
	public function getContentToCategories(): ActiveQuery
	{
		return $this->hasMany(ContentToCategory::class, ['content_id' => 'id']);
	}

	/**
	 * Gets query for [[Categories]].
	 *
	 * @return ActiveQuery
	 * @throws InvalidConfigException
	 */
	public function getCategories(): ActiveQuery
	{
		return $this->hasMany(ContentCategory::class, ['id' => 'category_id'])->viaTable('content_to_category', ['content_id' => 'id']);
	}

	/**
	 * {@inheritdoc}
	 * @return ContentQuery the active query used by this AR class.
	 */
	public static function find(): ContentQuery
	{
		return new ContentQuery(get_called_class());
	}

	/**
	 * @return string
	 */
	public function getTypeText(): string
	{
		return self::TYPES_LIST[$this->type_id];
	}

	/**
	 * @return string
	 */
	public function getPositionText(): string
	{
		return self::POSITIONS_LIST[$this->type_id];
	}

	/**
	 * @return array|ActiveRecord|null
	 */
	public function getParent()
	{
		$parents = ContentClosure::parents([$this->id]);
		if (count($parents) == 1 && $parents[0]['anc'] != $this->id) {
			return Content::find()->where(['id' => $parents[0]['anc']])->one();
		}
		return null;
	}

	/**
	 * @return int|null
	 */
	public function getParentId(): ?int
	{
		$parents = ContentClosure::parents([$this->id]);
		if (count($parents) == 1 && $parents[0]['anc'] != $this->id) {
			return $parents[0]['anc'];
		}
		return null;
	}

	/**
	 * @return array
	 */
	public function getCategoryId(): array
	{
		return ArrayHelper::getColumn($this->categories ?? [], 'id');
	}
}
