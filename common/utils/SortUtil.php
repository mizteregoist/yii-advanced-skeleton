<?php

namespace common\utils;

class SortUtil
{
	/**
	 * Сортировка массива по нужному ключу.
	 * Еще есть ArrayHelper::multisort();
	 *
	 * @param string $sort Ключ
	 * @param array $array Массив
	 * @param int $orderBy Направление сортировки, по умолчанию А-Я.
	 */
	public static function sortBy(string $sort, array &$array, $orderBy = SORT_ASC)
	{
		uasort($array, function ($a, $b) use ($sort, $orderBy) {
			if ($orderBy === SORT_ASC) {
				if ($a[$sort] > $b[$sort]) return 1;
			}
			if ($orderBy === SORT_DESC) {
				if ($a[$sort] < $b[$sort]) return 1;
			}
		});
	}

	/**
	 * Сортировка по алфавиту + языку.
	 * @param array $array Исходный массив
	 * @param false $byKey Сортировка по ключу. По умолчанию по значению.
	 */
	public static function sortByLocale(array &$array, $byKey = false)
	{
		$func = $byKey ? 'uksort' : 'uasort';
		$func($array, function ($a, $b) {
			if (ord($a) > 122 && ord($b) > 122) {
				return (int)($a > $b);
			}
			if (ord($a) > 122 || ord($b) > 122) {
				return (int)($a < $b);
			}
			return 0;
		});
	}
}