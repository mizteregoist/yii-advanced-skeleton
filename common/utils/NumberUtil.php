<?php

namespace common\utils;

class NumberUtil
{
	public static function format($number, int $decimals = null): string
	{
		$explode = explode('.', $number);
		if (!empty($explode[1]) && $explode[1] > 0) {
			if (!empty($decimals)) {
				return number_format($number, $decimals, '.', ' ');
			} else {
				return number_format($number, strlen($explode[1]), '.', ' ');
			}
		} else {
			if (!empty($decimals)) {
				return number_format($number, $decimals, '.', ' ');
			} else {
				return number_format($number, 0, '.', ' ');
			}
		}
	}
}