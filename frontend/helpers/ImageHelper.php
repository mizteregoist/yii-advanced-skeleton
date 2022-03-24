<?php

namespace frontend\helpers;

use Yii;
use yii\imagine\Image;

class ImageHelper
{
	/**
	 * @param $id
	 * @param null $class
	 * @param bool $checkExists
	 *
	 * @return string
	 */
	public static function showSvg($id, $class = null, bool $checkExists = false): string
	{
		$fileUri = '/images/svg/map.svg';
		$fileName = Yii::getAlias('@frontend/web' . $fileUri);

		if ($checkExists) {
			$doc = new \DOMDocument();
			$doc->load($fileName);

			$xpath = new \DOMXPath($doc);
			$rootNamespace = $doc->lookupNamespaceUri($doc->namespaceURI);
			$xpath->registerNamespace('svg', $rootNamespace);

			$nodes = $xpath->query("//svg:symbol[@id='" . $id . "']");

			if (!$nodes || $nodes->length == 0) {
				return '';
			}
		}
		ob_start();
		?>
		<svg class="<?= $class ?? $id ?>" role="img">
			<use xmlns:xlink="http://www.w3.org/1999/xlink"
			     xlink:href="<?= $fileUri ?>?v=<?= filemtime($fileName) ?>#<?= $id ?>"></use>
		</svg>
		<?php
		return ob_get_clean();
	}

	/**
	 * Создает фото (превью) нужного размера.
	 *
	 * @param string $imagePath
	 * @param string $resizedFile
	 * @param int $w
	 * @param int $h
	 * @param int $quality
	 */
	public static function resizeImage($imagePath, $resizedFile, $w, $h, $quality)
	{
		Image::thumbnail($imagePath, $w, $h)->save($resizedFile, ['quality' => $quality]);
	}
}