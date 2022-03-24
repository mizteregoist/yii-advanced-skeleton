<?php

namespace backend\modules\backup\models;

use Exception;
use yii\base\Model;
use yii\helpers\FileHelper;

class Conformity extends Model
{
	public $remote;
	public $local;

	/**
	 * {@inheritdoc}
	 */
	public function rules(): array
	{
		return [
			[['remote', 'local'], 'required'],
			[['remote', 'local'], 'string'],
			[['remote', 'local'], 'trim'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(): array
	{
		return [
			'remote' => 'Удаленная база',
			'local' => 'Локальная база',
			'actions' => 'Действия',
		];
	}

	/**
	 * @return array
	 */
	public static function getData(): array
	{
		$result = [];
		try {
			$dir = dirname(__DIR__) . '/config';
			if (!is_dir($dir)) {
				FileHelper::createDirectory($dir);
			}
			$path = "{$dir}/conformity-local.php";
			if (!file_exists($path)) {
				file_put_contents($path, "<?php\nreturn [];\n");
			}
			$result = require $path;
		} catch (Exception $e) {
			print_r($e);
		}
		return $result;
	}
}