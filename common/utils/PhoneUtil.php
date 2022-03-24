<?php

namespace common\utils;

class PhoneUtil
{
	/**
	 * Получить номер вида +7(999) 999-99-99
	 *
	 * @param string $phone
	 *
	 * @return string|string[]|null
	 */
	public static function getPhoneWithoutSpace(string $phone)
	{
		$number = preg_replace('/\D+/', '', $phone);
		if (strlen($number) === 10) {
			$result = preg_replace('/(\d{3})(\d{3})(\d{2})(\d{2})/', "+7($1) $2-$3-$4", $number);
		} elseif (strlen($number) === 11) {
			$result = preg_replace('/(\d)(\d{3})(\d{3})(\d{2})(\d{2})/', "+7($2) $3-$4-$5", $number);
		} else {
			$result = null;
		}
		return $result;
	}

	/**
	 * Получить номер вида +7 (999) 999-99-99
	 *
	 * @param string $phone
	 *
	 * @return string|string[]|null
	 */
	public static function getPhoneWithSpace(string $phone)
	{
		$number = preg_replace('/\D+/', '', $phone);
		if (strlen($number) === 10) {
			$result = preg_replace('/(\d{3})(\d{3})(\d{2})(\d{2})/', "+7 ($1) $2-$3-$4", $number);
		} elseif (strlen($number) === 11) {
			$result = preg_replace('/(\d)(\d{3})(\d{3})(\d{2})(\d{2})/', "+7 ($2) $3-$4-$5", $number);
		} else {
			$result = null;
		}
		return $result;
	}
}