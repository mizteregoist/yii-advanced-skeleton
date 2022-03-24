<?php

namespace backend\modules\content\controllers;

use backend\modules\content\models\ContentClosure;
use Exception;
use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use backend\modules\content\models\Content;
use backend\modules\content\models\ContentFile;
use backend\modules\content\models\ContentSearch;

class BlockController extends Controller
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
	 * @return string
	 */
	public function actionIndex(): string
	{
		$searchModel = new ContentSearch();
		$params = Yii::$app->request->queryParams;
		$dataProvider = $searchModel->search($params, [Content::TYPES_BLOCK]);

		return $this->render('index', [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		]);
	}

	/**
	 * @param int $id
	 *
	 * @return string
	 * @throws NotFoundHttpException
	 */
	public function actionView(int $id): string
	{
		return $this->render('view', [
			'model' => $this->findModel($id),
		]);
	}

	/**
	 * @return Response|string
	 * @throws Exception
	 */
	public function actionCreate()
	{
		$model = new Content();
		$post = $this->request->post();

		if ($this->request->isPost) {
			if ($model->load($post) && $model->validate($post)) {
				$model->created_at = date('Y-m-d H:i:s');
				$model->updated_at = date('Y-m-d H:i:s');
				if (empty($model->parent_id)) {
					$model->parent_id = null;
				}
				if (empty($model->category_id)) {
					$model->category_id = null;
				}
				if ($model->save()) {
					$imageInput = UploadedFile::getInstances($model, 'imageInput');
					$fileInput = UploadedFile::getInstances($model, 'fileInput');
					$backgroundInput = UploadedFile::getInstance($model, 'backgroundInput');
					if (!empty($imageInput)) {
						ContentFile::uploadMultiple($model, ContentFile::TYPE_IMAGE, $imageInput);
					}
					if (!empty($fileInput)) {
						ContentFile::uploadMultiple($model, ContentFile::TYPE_FILE, $fileInput);
					}
					if (!empty($backgroundInput)) {
						ContentFile::upload($model, ContentFile::TYPE_BACKGROUND, $backgroundInput);
					}
					ContentClosure::__insert__($model->id, $model->parent_id);
					return $this->redirect(['update', 'id' => $model->id]);
				}
			}
		} else {
			$model->loadDefaultValues();
		}

		return $this->render('create', [
			'model' => $model,
		]);
	}

	/**
	 * @param int $id
	 *
	 * @return string
	 * @throws NotFoundHttpException|Throwable
	 */
	public function actionUpdate(int $id): string
	{
		$model = $this->findModel($id);
		$post = $this->request->post();

		if ($model->load($post) && $model->validate($post)) {
			if (empty($model->parent_id)) {
				$model->parent_id = null;
			}
			if (empty($model->category_id)) {
				$model->category_id = null;
			}
			$model->updated_at = date('Y-m-d H:i:s');
			if ($model->save()) {
				$imageInput = UploadedFile::getInstances($model, 'imageInput');
				$fileInput = UploadedFile::getInstances($model, 'fileInput');
				$backgroundInput = UploadedFile::getInstance($model, 'backgroundInput');
				if (!empty($imageInput)) {
					ContentFile::uploadMultiple($model, ContentFile::TYPE_IMAGE, $imageInput);
				}
				if (!empty($fileInput)) {
					ContentFile::uploadMultiple($model, ContentFile::TYPE_FILE, $fileInput);
				}
				if (!empty($backgroundInput)) {
					ContentFile::upload($model, ContentFile::TYPE_BACKGROUND, $backgroundInput);
				}
				ContentClosure::__update__($model->id, $model->parent_id);
			}
		}

		return $this->render('update', [
			'model' => $model,
		]);
	}

	/**
	 * @param int $id
	 *
	 * @return Response
	 * @throws NotFoundHttpException
	 * @throws StaleObjectException|Throwable
	 */
	public function actionDelete(int $id): Response
	{
		ContentClosure::__delete__([$this->findModel($id)->id]);
		$this->findModel($id)->delete();

		return $this->redirect(['index']);
	}

	/**
	 * @param int $id
	 *
	 * @return Content
	 * @throws NotFoundHttpException
	 */
	protected function findModel(int $id): Content
	{
		if (($model = Content::find()->where(['id' => $id, 'type_id' => Content::TYPES_BLOCK])->one()) !== null) {
			return $model;
		}

		throw new NotFoundHttpException('The requested page does not exist.');
	}
}
