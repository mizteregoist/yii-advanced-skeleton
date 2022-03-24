<?php

namespace common\utils;

use backend\modules\user\models\User;
use Yii;

class TextUtil
{
	/**
	 * @param $text
	 * @param int $maxLength
	 * @param bool $toLowCase
	 * @param bool $trim
	 *
	 * @return false|string|string[]
	 */
	public static function transliteration($text, int $maxLength = 1000, bool $toLowCase = true, bool $trim = true)
	{
		$dictionary = [
			'й' => 'i',
			'ц' => 'c',
			'у' => 'u',
			'к' => 'k',
			'е' => 'e',
			'н' => 'n',
			'г' => 'g',
			'ш' => 'sh',
			'щ' => 'shch',
			'з' => 'z',
			'х' => 'h',
			'ъ' => '',
			'ф' => 'f',
			'ы' => 'y',
			'в' => 'v',
			'а' => 'a',
			'п' => 'p',
			'р' => 'r',
			'о' => 'o',
			'л' => 'l',
			'д' => 'd',
			'ж' => 'zh',
			'э' => 'e',
			'ё' => 'e',
			'я' => 'ya',
			'ч' => 'ch',
			'с' => 's',
			'м' => 'm',
			'и' => 'i',
			'т' => 't',
			'ь' => '',
			'б' => 'b',
			'ю' => 'yu',

			'Й' => 'I',
			'Ц' => 'C',
			'У' => 'U',
			'К' => 'K',
			'Е' => 'E',
			'Н' => 'N',
			'Г' => 'G',
			'Ш' => 'SH',
			'Щ' => 'SHCH',
			'З' => 'Z',
			'Х' => 'H',
			'Ъ' => '',
			'Ф' => 'F',
			'Ы' => 'Y',
			'В' => 'V',
			'А' => 'A',
			'П' => 'P',
			'Р' => 'R',
			'О' => 'O',
			'Л' => 'L',
			'Д' => 'D',
			'Ж' => 'ZH',
			'Э' => 'E',
			'Ё' => 'E',
			'Я' => 'YA',
			'Ч' => 'CH',
			'С' => 'S',
			'М' => 'M',
			'И' => 'I',
			'Т' => 'T',
			'Ь' => '',
			'Б' => 'B',
			'Ю' => 'YU',

			'\-' => '-',
			'\s' => '-',
			'\+' => '-',

			'[^a-zA-Z0-9\-]' => '',

			'[-]{2,}' => '-',
		];

		foreach ($dictionary as $from => $to) {
			$text = mb_ereg_replace($from, $to, $text);
		}

		$text = mb_substr($text, 0, $maxLength, Yii::$app->charset);
		if ($toLowCase) {
			$text = mb_strtolower($text, Yii::$app->charset);
		}

		return $trim ? trim($text, " \t\n\r\0\x0B\-") : $text;
	}

	/**
	 * Обрезает строку до нужного размера.
	 * @param string $string Строка
	 * @param int $symbols Число символов
	 * @return string
	 */
	public static function short(string $string, int $symbols = 100): string
	{
		if (iconv_strlen($string, 'utf-8') > $symbols) {
			$string = rtrim(mb_strimwidth(strip_tags($string), 0, $symbols), '!,.- ') . '...';
		}
		return $string;
	}

	/**
	 * @param integer $n
	 * @param array $forms [0 => значений, 1 => значение, 2 => значения]
	 * @param bool $withN
	 *
	 * @return mixed
	 */
	public static function plural(int $n, array $forms, bool $withN = false)
	{
		$result = $n % 10 == 1 && $n % 100 != 11 ? $forms[1] : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? $forms[2] : $forms[0]);
		if ($withN) {
			$result = $n . ' ' . $result;
		}
		return $result;
	}

	/**
	 * Убирает из строки все кроме цифр
	 *
	 * @param $string
	 *
	 * @return string
	 */
	public static function phoneTrim($string): string
	{
		$string = trim(preg_replace(User::TRIM_PHONE_PATTERN, '', $string));
		if (strlen($string) == 10) {
			$string = preg_replace(
				User::STR_TO_PHONE_PATTERN,
				User::STR_TO_PHONE_REPLACEMENT,
				$string
			);
		}
		if (strlen($string) == 11) {
			$string = substr($string, 1, 11);
			$string = preg_replace(
				User::STR_TO_PHONE_PATTERN,
				User::STR_TO_PHONE_REPLACEMENT,
				$string
			);
		}
		return $string;
	}

	public static function str2phone($string)
	{
		$result = '';
		$trimmed = self::phoneTrim($string);
		if (strlen($trimmed) == 10) {
			$result = preg_replace(
				User::STR_TO_PHONE_PATTERN,
				User::STR_TO_PHONE_NUMBER_REPLACEMENT,
				$trimmed
			);
		}
		return $result;
	}

	public static function str2tel($string)
	{
		$result = '';
		$trimmed = self::phoneTrim($string);
		if (strlen($trimmed) == 10) {
			$result = preg_replace(
				User::STR_TO_PHONE_PATTERN,
				User::STR_TO_TEL_REPLACEMENT,
				$trimmed
			);
		}
		return $result;
	}
}