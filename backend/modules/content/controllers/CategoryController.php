<?php

namespace backend\modules\content\controllers;

use Yii;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use backend\modules\content\models\ContentCategory;
use backend\modules\content\models\ContentCategorySearch;

class CategoryController extends Controller
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
		$searchModel = new ContentCategorySearch();
		$params = Yii::$app->request->queryParams;
		$dataProvider = $searchModel->search($params);

		return $this->render('index', [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		]);
	}

	/**
	 * @param int $id
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
	 */
	public function actionCreate()
	{
		$model = new ContentCategory();
		$post = $this->request->post();

		if ($this->request->isPost) {
			if ($model->load($post) && $model->validate($post)) {
				$model->created_at = date('Y-m-d H:i:s');
				$model->updated_at = date('Y-m-d H:i:s');
				if ($model->save()) {
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
	 * @return Response|string
	 * @throws NotFoundHttpException
	 */
	public function actionUpdate(int $id)
	{
		$model = $this->findModel($id);
		$post = $this->request->post();

		if ($this->request->isPost) {
			if ($model->load($post) && $model->validate($post)) {
				if ($model->save()) {
					return $this->redirect(['view', 'id' => $model->id]);
				}
			}
		}

		return $this->render('update', [
			'model' => $model,
		]);
	}

	/**
	 * @param int $id
	 * @return Response
	 * @throws NotFoundHttpException|StaleObjectException
	 */
	public function actionDelete(int $id): Response
	{
		$this->findModel($id)->delete();

		return $this->redirect(['index']);
	}

	/**
	 * @param int $id
	 * @return ContentCategory
	 * @throws NotFoundHttpException
	 */
	protected function findModel(int $id): ContentCategory
	{
		if (($model = ContentCategory::findOne(['id' => $id])) !== null) {
			return $model;
		}

		throw new NotFoundHttpException('The requested page does not exist.');
	}
}
