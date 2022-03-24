<?php

namespace backend\modules\user\controllers;

use Exception;
use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\rbac\Permission;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use backend\modules\user\models\User;
use backend\modules\user\forms\UserRoleForm;

class PermissionController extends Controller
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
		$get = Yii::$app->request->get();
		$auth = Yii::$app->authManager;
		$filter = $get['filter'] ?? null;
		$permission = $filter ? array_values($auth->getPermissionsByRole($filter)) : array_values($auth->getPermissions());
		$dataProvider = new ArrayDataProvider([
			'models' => $permission,
		]);

		return $this->render('index', [
			'dataProvider' => $dataProvider,
			'filter' => $filter,
		]);
	}

	/**
	 * @param string $code
	 * @return string
	 * @throws NotFoundHttpException
	 */
	public function actionView(string $code): string
	{
		$model = $this->findModel($code);
		return $this->render('view', [
			'model' => $model,
		]);
	}

	/**
	 * @return string|Response
	 * @throws Exception
	 */
	public function actionCreate()
	{
		$post = Yii::$app->request->post();
		$model = new UserRoleForm();
		if ($model->load($post)) {
			if (User::canCreateAuth($model->name)) {
				$auth = Yii::$app->authManager;
				$newPermission = $auth->createPermission($model->name);
				$newPermission->description = $model->description;
				$auth->add($newPermission);

				Yii::$app->session->setFlash('success', 'Разрешение успешно создано');
				return $this->redirect(['permission/index']);
			} else {
				Yii::$app->session->setFlash('warning', 'Данный код уже используется');
			}
		}

		return $this->render('create', [
			'model' => $model,
			'type' => 'new',
		]);
	}

	/**
	 * @param string $code
	 * @return string|Response
	 * @throws NotFoundHttpException
	 */
	public function actionUpdate(string $code)
	{
		$model = new UserRoleForm();
		$auth = Yii::$app->authManager;
		$permission = $this->findModel($code);

		if ($model->load(Yii::$app->request->post())) {
			$permissionOldName = $permission->name;
			if ($permissionOldName != $model->name && !User::canCreateAuth($model->name)) {
				Yii::$app->session->setFlash('warning', 'Данный код уже используется');
			} else {
				$permission->name = $model->name;
				$permission->description = $model->description;
				$auth->update($permissionOldName, $permission);

				Yii::$app->session->setFlash('success', 'Разрешение успешно изменено');
				return $this->redirect(['permission/index']);
			}
		}

		$model->name = $permission->name;
		$model->description = $permission->description;

		return $this->render('create', [
			'model' => $model,
			'type' => '',
		]);
	}

	/**
	 * @param string $code
	 * @return Response
	 * @throws NotFoundHttpException
	 */
	public function actionDelete(string $code): Response
	{
		$auth = Yii::$app->authManager;
		$permission = $this->findModel($code);
		$auth->remove($permission);

		Yii::$app->session->setFlash('success', 'Разрешение успешно удалено');

		return $this->redirect(['permission/index']);
	}

	/**
	 * Finds the User model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param string $alias
	 * @return Permission
	 * @throws NotFoundHttpException
	 */
	protected function findModel(string $alias): Permission
	{
		$auth = Yii::$app->authManager;
		$permission = $auth->getPermission($alias);

		if (!$permission) {
			throw new NotFoundHttpException('Запрошенная страница не найдена.');
		}
		return $permission;
	}
}