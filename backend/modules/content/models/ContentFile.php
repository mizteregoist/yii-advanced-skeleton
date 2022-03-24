<?php

namespace backend\modules\content\models;

use common\utils\DevUtil;
use common\utils\FileUtil;
use Exception;
use frontend\helpers\ImageHelper;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\UploadedFile;

/**
 * This is the model class for table "content_file".
 *
 * @property int $id ID
 * @property int $content_id Запись
 * @property bool $active Активность
 * @property int $sort Сортировка
 * @property string $original_name Исходное название файла
 * @property string $filename Название файла
 * @property string $path Путь файла
 * @property int $type Тип файла
 * @property string $mime MIME тип
 * @property string $timestamp Время создания
 *
 * @property Content $content
 */
class ContentFile extends ActiveRecord
{
	public $file;
	public $files;
	public $delFile;

	const TYPE_IMAGE = 1;
	const TYPE_FILE = 2;
	const TYPE_BACKGROUND = 3;

	const TYPES = [
		self::TYPE_IMAGE,
		self::TYPE_FILE,
		self::TYPE_BACKGROUND,
	];

	const TYPES2TEXT = [
		self::TYPE_IMAGE => 'Картинка',
		self::TYPE_FILE => 'Файл',
		self::TYPE_BACKGROUND => 'Задний фон',
	];

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(): string
	{
		return 'content_file';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(): array
	{
		return [
			[['content_id', 'original_name', 'filename', 'type', 'mime'], 'required'],
			[['active'], 'default', 'value' => true],
			[['sort'], 'default', 'value' => 100],
			[['type'], 'default', 'value' => 1],
			[['content_id', 'sort', 'type'], 'integer'],
			[['timestamp'], 'default', 'value' => date('Y-m-d H:i:s')],
			[['timestamp', 'delFile'], 'safe'],
			[['file'], 'file'],
			[['files'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 30],
			[['active', 'delFile'], 'boolean'],
			[['original_name', 'filename', 'mime'], 'string', 'max' => 255],
			[['content_id'], 'exist', 'skipOnError' => true, 'targetClass' => Content::class, 'targetAttribute' => ['content_id' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(): array
	{
		return [
			'id' => 'ID',
			'content_id' => 'Запись',
			'active' => 'Активность',
			'sort' => 'Сортировка',
			'original_name' => 'Исходное название файла',
			'filename' => 'Название файла',
			'type' => 'Тип файла',
			'mime' => 'MIME тип',
			'timestamp' => 'Время создания',
		];
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert) : bool
	{
		if (parent::beforeSave($insert)) {
			if ($this->isNewRecord) {
				$this->timestamp = date('Y-m-d H:i:s');
			}
			return true;
		}
		return false;
	}

	/**
	 * @return string
	 */
	public function type2text() : string
	{
		return self::TYPES2TEXT[$this->type];
	}

	/**
	 * Gets query for [[Content]].
	 *
	 * @return ActiveQuery
	 */
	public function getContent(): ActiveQuery
	{
		return $this->hasOne(Content::class, ['id' => 'content_id']);
	}

	/**
	 * {@inheritdoc}
	 * @return ContentFileQuery the active query used by this AR class.
	 */
	public static function find(): ContentFileQuery
	{
		return new ContentFileQuery(get_called_class());
	}

	/**
	 * @param Content $content
	 * @param UploadedFile|null $file
	 * @param int $type
	 *
	 * @return null
	 * @throws Exception
	 */
	public function loadFile(Content $content, UploadedFile $file = null, int $type = 1)
	{
		if (!$file) {
			return null;
		}
		[$width, $height, $mime, $attr] = getimagesize($file->tempName);
		$fileData = explode('.', $file->name);
		$extension = '.' . end($fileData);
		$filename = md5("{$width}_{$height}_{$mime}_{$file->name}") . $extension;
		$directory = substr($filename, 0, 2);
		if (!is_dir(Yii::getAlias("@upload/{$directory}"))) {
			FileHelper::createDirectory(Yii::getAlias("@upload/{$directory}"));
		}
		if ($file->saveAs(Yii::getAlias("@upload/{$directory}/{$filename}"))) {
			$this->content_id = $content->id;
			$this->original_name = $file->name;
			$this->filename = $filename;
			if (empty($this->type)) {
				$this->type = $type;
			}
			$this->mime = $file->type;
			if (!$this->save()) {
				print_r($this->errors);
			}
		}
	}

	public static function upload(Content $content, int $typeId, UploadedFile $instance, bool $return = false)
	{
		try {
			$upload = Yii::getAlias('@upload');
			$nameExt = FileUtil::name2ext($instance->name);
			$md5 = md5_file($instance->tempName);
			$filenameMD5 = md5("{$instance->size}_{$md5}_{$instance->name}");
			$filename = "{$filenameMD5}.{$nameExt}";
			$substr = substr($filenameMD5, 0, 2);
			FileHelper::createDirectory("{$upload}/{$substr}");
			$path = "{$upload}/{$substr}/{$filename}";
			$save = false;
			if (!file_exists($path)) {
				$save = true;
			} else {
				$grep = preg_grep("/{$filenameMD5}/", FileUtil::fileScan("{$upload}/{$substr}"));
				$num = count($grep) + 1;
				$filename = "{$filenameMD5}_{$num}.{$nameExt}";
				$path = "{$upload}/{$substr}/{$filename}";
				if (!file_exists($path)) {
					$save = true;
				}
			}
			if ($save) {
				if ($instance->saveAs($path)) {
					$file = new ContentFile();
					$file->content_id = $content->id;
					$file->type = $typeId;
					$file->original_name = $instance->name;
					$file->filename = $filename;
					$file->mime = $instance->type;
					if ($file->save()) {
						FileUtil::linkPath($path, $file->id);
						if ($return) {
							return $file->getPath();
						}
					} else {
						print_r($file->errors);
					}
				}
			}
		} catch (\yii\base\Exception $e) {
			print_r($e);
		}

	}

	public static function uploadMultiple(Content $content, int $typeId, array $instances, bool $return = false)
	{
		$result = [];
		foreach ($instances as $instance) {
			if ($instance instanceof UploadedFile) {
				if ($return) {
					$result[] = ContentFile::upload($content, $typeId, $instance, true);
				} else {
					ContentFile::upload($content, $typeId, $instance);
				}
			}
		}
		if ($return) {
			return $result;
		}
	}

	public static function path($data, bool $absolute = true)
	{
		$tmp = sys_get_temp_dir();
		$tmpDir = "{$tmp}/content_file";
		$tmpData = FileUtil::fileScan($tmpDir);
		$upload = Yii::getAlias('@upload');
		if (is_numeric($data)) {
			$tmpPath = "{$tmpDir}/{$data}";
			if (in_array($data, $tmpData) && !file_exists($tmpPath)) {
				unlink($tmpPath);
			}
			if (file_exists($tmpPath)) {
				$path = readlink($tmpPath);
			}
			if (empty($path) || !file_exists($path)) {

			}
		}

		if ($absolute) {
			return $path;
		} else {
			return str_replace($upload, '/upload', $path);
		}
	}

	/**
	 * @return string
	 */
	public function getPath() : string
	{
		if (!empty($this->filename)) {
			$directory = substr($this->filename, 0, 2);
			return "/upload/{$directory}/{$this->filename}";
		} else {
			return '';
		}
	}

	/**
	 * @return string
	 */
	public function getAbsolutePath() : string
	{
		if (!empty($this->filename)) {
			$directory = substr($this->filename, 0, 2);
			return Url::base(true) . "/upload/{$directory}/{$this->filename}";
		} else {
			return '';
		}
	}

	/**
	 * @return string
	 */
	public function getFullPath() : string
	{
		if (!empty($this->filename)) {
			$directory = substr($this->filename, 0, 2);
			return Yii::getAlias("@upload/{$directory}/{$this->filename}");
		} else {
			return '';
		}
	}

	private function getResizedImage($w, $h, $quality = 95)
	{
		if (!empty($this->filename)) {
			$resizedPath = Yii::getAlias('@upload') . "/resize/{$w}_{$h}_{$quality}/" . substr($this->filename, 0, 2) . "/{$this->filename}";
			if (!file_exists($resizedPath)) {
				ImageHelper::resizeImage($this->path, $resizedPath, $w, $h, $quality);
			}
		}
	}

	/**
	 * @param $w
	 * @param $h
	 * @param int $quality
	 *
	 * @return string
	 */
	public function getResizedImagePath($w, $h, int $quality = 95) : string
	{
		if (!empty($this->filename)) {
			$this->getResizedImage($w, $h, $quality);
			return "/upload/resize/{$w}_{$h}_{$quality}/" . substr($this->filename, 0, 2) . "/{$this->filename}";
		} else {
			return '';
		}
	}

	/**
	 * @param $url
	 *
	 * @return bool
	 */
	public static function fileExistsByUrl($url) : bool
	{
		$headers = @get_headers($url);
		return isset($headers[0]) && stripos($headers[0], "200 OK");
	}

	/**
	 * @param $url
	 * @param array $resize
	 *
	 * @return array|false
	 * @throws InvalidConfigException
	 * @throws Exception
	 */
	public static function loadFileByUrl($url, array $resize = [])
	{
		$content = @file_get_contents($url);
		if (empty($content) || !self::fileExistsByUrl($url)) {
			return false;
		}
		[$width, $height, $type, $attr] = getimagesize($url);
		$basename = basename($url);
		$fileData = explode('.', $basename);
		$extension = '.' . end($fileData);
		$filename = md5("{$width}_{$height}_{$type}_{$basename}") . $extension;
		$directory = substr($filename, 0, 2);
		$path = Yii::getAlias("@upload/{$directory}/{$filename}");
		FileHelper::createDirectory(Yii::getAlias("@upload/{$directory}"));

		if (file_put_contents($path, $content)) {
			return [
				'original_name' => $basename,
				'filename' => $filename,
				'mime' => FileHelper::getMimeType($path),
			];
		}
		return false;
	}

	/**
	 * @param array $files
	 *
	 * @return array[]
	 */
	public static function getFileSelectData(array $files) : array
	{
		$initialPreview = $initialPreviewConfig = $uploadExtraData = [];
		/** @var ContentFile $file */
		foreach ($files as $key => $file) {
			if ($file instanceof ContentFile) {
				$initialPreview[$key] = $file->getPath();
				$initialPreviewConfig[$key] = [
					'key' => $file->id,
					'caption' => $file->original_name,
					'sort' => $file->sort,
				];
				if (file_exists($file->getFullPath()) && $fileSize = filesize($file->getFullPath())) {
					$initialPreviewConfig[$key]['size'] = $fileSize;
				}
				$uploadExtraData['id'][$key] = $file->id;
			}
		}
		return [$initialPreview, $initialPreviewConfig, $uploadExtraData];
	}
}
