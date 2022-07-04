<?php

namespace backend\modules\content\helpers;

use backend\modules\content\models\Content;
use backend\modules\content\models\ContentFile;
use common\utils\DevUtil;
use kartik\widgets\FileInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveField;
use yii\widgets\ActiveForm;

class ContentHelper
{
	/**
	 * @param Content $model
	 * @param int $type
	 * @return array
	 */
	public static function initFileInput(Content $model, int $type = 0): array
	{
		$images = $files = $background = [];
		foreach ($model->contentFiles ?? [] as $item) {
			if (!empty($item->getFullPath()) && !empty($item->getPath())) {
				switch ($item->type) {
					case ContentFile::TYPE_IMAGE:
						$images[] = $item;
						break;
					case ContentFile::TYPE_FILE:
						$files[] = $item;
						break;
					case ContentFile::TYPE_BACKGROUND:
						$background[] = $item;
						break;
				}
			}
		}
		usort($images, function (ContentFile $a, ContentFile $b) {
			return $a->sort >= $b->sort ? 1 : -1;
		});
		usort($files, function (ContentFile $a, ContentFile $b) {
			return $a->sort >= $b->sort ? 1 : -1;
		});
		usort($background, function (ContentFile $a, ContentFile $b) {
			return $a->sort >= $b->sort ? 1 : -1;
		});
		[$initialImagePreview, $initialImagePreviewConfig, $uploadImageExtraData] = ContentFile::getFileSelectData($images);
		[$initialFilePreview, $initialFilePreviewConfig, $uploadFileExtraData] = ContentFile::getFileSelectData($files);
		[$initialBackgroundPreview, $initialBackgroundPreviewConfig, $uploadBackgroundExtraData] = ContentFile::getFileSelectData($background);

		if (!empty($model->id)) {
			$uploadImageExtraData['content_id'] = $model->id;
			$uploadFileExtraData['content_id'] = $model->id;
			$uploadBackgroundExtraData['content_id'] = $model->id;
		}
		$uploadImageExtraData['type'] = ContentFile::TYPE_IMAGE;
		$uploadFileExtraData['type'] = ContentFile::TYPE_FILE;
		$uploadBackgroundExtraData['type'] = ContentFile::TYPE_BACKGROUND;

		$result[ContentFile::TYPE_IMAGE] = [$initialImagePreview, $initialImagePreviewConfig, $uploadImageExtraData];
		$result[ContentFile::TYPE_FILE] = [$initialFilePreview, $initialFilePreviewConfig, $uploadFileExtraData];
		$result[ContentFile::TYPE_BACKGROUND] = [$initialBackgroundPreview, $initialBackgroundPreviewConfig, $uploadBackgroundExtraData];

		if ($type > 0) {
			return $result[$type];
		} else {
			return $result;
		}
	}


	/**
	 * @param ActiveForm $form
	 * @param Content $model
	 * @param string $attribute
	 * @param array $options
	 * @return string|ActiveField
	 */
	public static function fileInput(ActiveForm $form, Content $model, string $attribute, array $options = [])
	{
		$options = ArrayHelper::merge([
			'pluginLoading' => false,
			'readonly' => false,
			'disabled' => false,
			'options' => [
				'multiple' => false,
				'accept' => 'image/*',
			],
			'pluginOptions' => [
				'overwriteInitial' => false,
				'initialPreviewAsData' => true,
				'initialPreview' => [],
				'initialPreviewConfig' => [],
				'uploadExtraData' => [],

				'uploadUrl' => Url::to(['file/upload-file']),
				'deleteUrl' => Url::to(['file/delete-file']),

				'previewFileType' => 'any',
				'allowedFileExtensions' => ['jpg', 'jpeg', 'png'],

				'maxFileCount' => 30,
				'maxFileSize' => 5120,

				'showBrowse' => true,
				'browseOnZoneClick' => false,
				'showUpload' => false,
				'showPreview' => true,
				'showCaption' => true,
				'showRemove' => false,
				'showPause' => false,
				'showCancel' => false,
				'showClose' => false,
				'showUploadStats' => false,
				'showUploadedThumbs' => false,

				'browseClass' => 'btn btn-success',
				'uploadClass' => 'btn btn-info',
				'removeClass' => 'btn btn-danger',

				'fileActionSettings' => [
					'showZoom' => true,
					'showDrag' => !$model->isNewRecord,
					'showUpload' => !$model->isNewRecord,
					'showDownload' => !$model->isNewRecord,
					'showRemove' => true,

				],
			],
			'pluginEvents' => [
				"filesorted" => "function(event, params) { return fileSorted(event, params); }",
			],
		], $options);
		try {
			return $form->field($model, $attribute)->widget(FileInput::class, $options);
		} catch (\Exception $e) {
			return "";
		}
	}

	public static function makeTree(array $nodes): array
	{
		$tree = [];
		foreach ($nodes as $node) {
			$parentId = $node['parent_id'] ?? 0;
			$childId = $node['child_id'] ?? 0;
			$anc = $node['anc'];
			$dsc = $node['dsc'];
			$lvl = $node['lvl'];
			$isNode = ($anc == $dsc && $lvl == 0);
			$isChild = (!empty($parentId) && $anc != $parentId);

			if ($isNode) {
				$tree[$dsc]['id'] = $dsc;
				$tree[$dsc]['parent_id'] = $parentId;
				if (!empty($childId)) {
					$tree[$dsc]['child'][$childId] = [];
				}
			}

			if (!$isNode) {
				$tree[$dsc]['id'] = $dsc;
				$tree[$dsc]['parent_id'] = $parentId;
				if (!empty($childId)) {
					$tree[$dsc]['child'][$childId] = [];
				}

				$tree[$parentId]['child'][$dsc]['id'] = $dsc;
				$tree[$parentId]['child'][$dsc]['parent_id'] = $parentId;
				if (!empty($childId)) {
					$tree[$parentId]['child'][$dsc]['child'][$childId] = [];
				}
			}
		}

		$levels = [];
		foreach ($tree as $id => $item) {
			/**
			 * Берем на верхнем уровне тоько разделы
			 * и строим ближайшие уровни
			 */
			$parentId = $item['parent_id'] ?? $item['id'];
			if ($id != $parentId) {
				$levels[$id] = $parentId;
			}
		}
		krsort($levels);

		foreach ($levels as $id => $parent) {
			if (!empty($tree[$id]) && $parent != 0) {
				/**
				 * Если в массиве есть раздел с текущим Ид
				 * и у массива родителя нет текущего раздела
				 */
				$tree[$parent]['child'][$id] = $tree[$id];
				uasort($tree[$parent]['child'], function ($a, $b) {
					return ($a['section'] == $b['section']) ? 0 : 1;
				});
				unset($tree[$id]);
				/**
				 * Удаляем из массива ветку с текущим Ид раздела
				 */
			}
		}
		return $tree;
	}

	/**
	 * @param array $ancestors
	 * @param int|null $modelId
	 * @return array
	 */
	public static function printSelect(array $ancestors, int $modelId = null): array
	{
		$tree = ContentHelper::makeTree($ancestors);
		$result = [];
		$recursive = function ($items, $level = 0) use (&$recursive, &$result, $modelId) {
			foreach ($items as $item) {
				if (!empty($item)) {
					$prefix = "- ";
					if ((!empty($modelId) && $modelId != $item['id']) || empty($modelId)) {
						$content = Content::find()->select('name')->where(['id' => $item['id']])->asArray()->one();
						$result[$item['id']] = str_repeat($prefix, $level + 1) . $content['name'] ?? '';
					}
					if (!empty($item['child']) && is_array($item['child'])) {
						$recursive($item['child'], $level + 1);
					}
				}
			}
		};
		$recursive($tree);
		return $result;
	}
}