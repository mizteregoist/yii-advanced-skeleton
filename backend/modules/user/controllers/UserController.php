<?php

namespace backend\modules\user\controllers;

use common\utils\DevUtil;
use Exception;
use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use backend\modules\user\models\User;
use backend\modules\user\models\UserSearch;

class UserController extends Controller
{
	/**
	 * @inheritdoc
	 */
	public function behaviors(): array
	{
		return [
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'*' => ['post', 'get'],
					'delete' => ['post'],
				],
			],
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'actions' => ['index', 'view'],
						'allow' => true,
						'roles' => ['user_view'],
					],
					[
						'actions' => ['create'],
						'allow' => true,
						'roles' => ['user_create'],
					],
					[
						'actions' => ['update'],
						'allow' => true,
						'roles' => ['user_update'],
					],
					[
						'actions' => ['delete'],
						'allow' => true,
						'roles' => ['user_delete'],
					],
					[
						'allow' => true,
						'roles' => ['superuser', 'developer', 'admin'],
					],
				],
			],
		];
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

	/**
	 * @param $action
	 *
	 * @return bool
	 * @throws ForbiddenHttpException|BadRequestHttpException
	 */
	public function beforeAction($action): bool
	{
		if (
			!Yii::$app->user->can('dashboard')
			&& $action->id != 'error'
		) {
			throw new ForbiddenHttpException();
		}
		return parent::beforeAction($action);
	}

	/**
	 * @return string
	 */
	public function actionIndex(): string
	{
		$searchModel = new UserSearch();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);

		return $this->render('index', [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		]);
	}

	/**
	 * @param integer $id
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
	 * @return string|Response
	 * @throws Exception
	 */
	public function actionCreate()
	{
		$post = Yii::$app->request->post();
		$model = new User();
		if ($model->load($post) && $model->validate()) {
			if (!empty($model->password)) {
				$model->setPassword($model->password);
			}
			if ($model->save()) {
				if (!empty($model->group)) {
					$model->setGroup($model->group);
				}
				Yii::$app->session->setFlash('success', 'Пользователь успешно создан');
				return $this->redirect(['/user/user/index']);
			}
		}

		return $this->render('create', [
			'model' => $model,
			'groups' => User::getGroups(),
		]);
	}

	/**
	 * @param integer $id
	 * @return Response|string
	 * @throws Exception
	 */
	public function actionUpdate(int $id)
	{
		$post = Yii::$app->request->post();
		$model = $this->findModel($id);
		if ($model->load($post) && $model->validate()) {
			if (!empty($model->group)) {
				$model->setGroup($model->group);
			}
			if (!empty($model->password)) {
				$model->setPassword($model->password);
			}
			if ($model->save()) {
				Yii::$app->session->setFlash('success', 'Пользователь успешно обновлен');
				return $this->redirect(['/user/user/index']);
			}
		}

		return $this->render('update', [
			'model' => $model,
			'groups' => User::getGroups(),
		]);
	}

	/**
	 * @param int $id
	 * @return Response
	 * @throws NotFoundHttpException
	 * @throws StaleObjectException
	 */
	public function actionDelete(int $id): Response
	{
		Yii::$app->authManager->revokeAll($id);
		$this->findModel($id)->delete();
		Yii::$app->session->setFlash('success', 'Пользователь успешно удален');
		return $this->redirect(['user/index']);
	}

	/**
	 * Finds the User model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param int $id
	 * @return User
	 * @throws NotFoundHttpException
	 */
	protected function findModel(int $id): User
	{
		if (($model = User::findOne($id)) !== null) {
			$model->group = $model->getGroup();
			return $model;
		} else {
			throw new NotFoundHttpException('Запрошенная страница не найдена.');
		}
	}
}