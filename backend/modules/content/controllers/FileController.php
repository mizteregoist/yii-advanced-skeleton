<?php

namespace backend\modules\content\controllers;

use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;
use backend\modules\content\models\Content;
use backend\modules\content\models\ContentFile;

class FileController extends Controller
{
	/**
	 * {@inheritdoc}
	 */
	public function behaviors(): array
	{
		return array_merge(
			parent::behaviors(),
			[
				'verbs' => [
					'class' => VerbFilter::class,
					'actions' => [
						'*' => ['post', 'get'],
						'delete' => ['post'],
					],
				],
			]
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function actions(): array
	{
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
		];
	}

	public function beforeAction($action)
	{
		if (!Yii::$app->user->can('dashboard') && !in_array($action->id, ['login', 'error'])) {
			return $this->redirect(['/site/login']);
		}
		return true;
	}

	/**
	 * @return Response|array|string|mixed
	 */
	public function actionUploadFile()
	{
		Yii::$app->response->format = Response::FORMAT_JSON;
		$post = Yii::$app->request->post();
		$result['files'] = [];
		if (!empty($post)) {
			if (!empty($post['content_id'])) {
				$model = Content::findOne($post['content_id']);
				if (!empty($model) && !empty($post['type'])) {
					switch ($post['type']) {
						case ContentFile::TYPE_IMAGE:
							$instances = UploadedFile::getInstances($model, 'imageInput');
							if (!empty($instances)) {
								$result['files'] = ContentFile::uploadMultiple($model, ContentFile::TYPE_IMAGE, $instances, true);
							}
							break;
						case ContentFile::TYPE_FILE:
							$instances = UploadedFile::getInstances($model, 'fileInput');
							if (!empty($instances)) {
								$result['files'] = ContentFile::uploadMultiple($model, ContentFile::TYPE_FILE, $instances, true);
							}
							break;
						case ContentFile::TYPE_BACKGROUND:
							$instance = UploadedFile::getInstance($model, 'backgroundInput');
							if (!empty($instance)) {
								$result['files'][] = ContentFile::upload($model, ContentFile::TYPE_BACKGROUND, $instance, true);
							}
							break;
					}
				}
			}

		}
		return $result;
	}

	/**
	 * @return bool
	 */
	public function actionDeleteFile(): bool
	{
		Yii::$app->response->format = Response::FORMAT_JSON;
		$get = Yii::$app->request->get();
		$post = Yii::$app->request->post();
		if (!empty($post) && !empty($post['key'])) {
			$image = ContentFile::findOne($post['key']);
			try {
				if (file_exists($image->getFullPath())) {
					if (!unlink($image->getFullPath())) {
						return false;
					}
				}
				if ($image->delete()) {
					return true;
				} else {
					return false;
				}
			} catch (StaleObjectException|Throwable $e) {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	public function actionChangeSort(): bool
	{
		Yii::$app->response->format = Response::FORMAT_JSON;
		$post = Yii::$app->request->post();
		$result = false;
		if (!empty($post['data'])) {
			$data = Json::decode($post['data']);
			if (!empty($data)) {
				foreach ($data as $sort => $id) {
					$file = ContentFile::findOne($id);
					if (!empty($file) && $file instanceof ContentFile) {
						$file->sort = $sort;
						if ($file->save()) {
							$result = true;
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 * @param int $id
	 *
	 * @return ContentFile|null
	 */
	protected function findModel(int $id): ?ContentFile
	{
		if (($model = ContentFile::findOne($id)) !== null) {
			return $model;
		}

		return null;
	}
}