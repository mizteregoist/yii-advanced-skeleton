<?php

namespace common\utils;

use Yii;

class DevUtil
{
	public static function p($data)
	{
		$user = Yii::$app->user;
		if (!$user->isGuest && ($user->identity->isDeveloper() || $user->identity->isSuperuser())) {
			if (is_bool($data)) {
				$data = ($data ? 'true' : 'false');
			}
			if (is_null($data)) {
				$data = 'null';
			}
			echo '<pre>' . print_r($data, 1) . '</pre>';
		}
	}

	/**
	 * @param $expression
	 * @param bool $return
	 * @return string|void
	 */
	public static function varExport($expression, bool $return = false)
	{
		$export = var_export($expression, true);
		$export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);
		$array = preg_split("/\r\n|\n|\r/", $export);
		$array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
		$export = join(PHP_EOL, array_filter(["["] + $array));
		if ($return) {
			return $export;
		} else {
			echo $export;
		}
	}
}