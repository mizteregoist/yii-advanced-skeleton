<?php

namespace backend\modules\user\controllers;

use Exception;
use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\rbac\Role;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use backend\modules\user\models\User;
use backend\modules\user\forms\UserRoleForm;

class RoleController extends Controller
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
		$roles = array_values(Yii::$app->authManager->getRoles());
		$dataProvider = new ArrayDataProvider([
			'allModels' => $roles,
		]);
		return $this->render('index', [
			'dataProvider' => $dataProvider,
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
		$auth = Yii::$app->authManager;
		$permissions = ArrayHelper::map($auth->getPermissions(), 'name', 'description');
		if ($model->load($post) && $model->validate()) {

			$model->createRole();
			$model->setPermissions($model->permissions);

			Yii::$app->session->setFlash('success', 'Роль успешно создана');
			return $this->redirect(['role/index']);
		}
		return $this->render('create', [
			'model' => $model,
			'permissions' => $permissions
		]);
	}

	/**
	 * @param string $code
	 * @return string|Response
	 * @throws NotFoundHttpException
	 * @throws Exception
	 */
	public function actionUpdate(string $code)
	{
		$post = Yii::$app->request->post();
		$model = new UserRoleForm();
		$auth = Yii::$app->authManager;
		$role = $this->findModel($code);
		$permissions = ArrayHelper::map($auth->getPermissions(), 'name', 'description');

		if ($model->load($post)) {
			if ($role->name != $model->name && !User::canCreateAuth($model->name)) {
				Yii::$app->session->setFlash('warning', 'Данный код уже используется');
			} else {
				$model->updateRole($role);
				$model->setPermissions($model->permissions);

				Yii::$app->session->setFlash('success', 'Роль успешно изменена');
				return $this->redirect(['role/index']);
			}
		}

		$model->name = $role->name;
		$model->description = $role->description;
		$model->permissions = ArrayHelper::getColumn($auth->getPermissionsByRole($model->name), 'name');

		return $this->render('update', [
			'model' => $model,
			'permissions' => $permissions,
		]);
	}

	/**
	 * Удаление группы.
	 * @param string $code
	 * @return Response
	 * @throws NotFoundHttpException
	 */
	public function actionDelete(string $code): Response
	{
		$auth = Yii::$app->authManager;
		$role = $this->findModel($code);
		$auth->remove($role);

		Yii::$app->session->setFlash('success', 'Роль успешно удалена');
		return $this->redirect(['role/index']);
	}

	/**
	 * Finds the User model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param string $code
	 * @return Role
	 * @throws NotFoundHttpException
	 */
	protected function findModel(string $code): Role
	{
		$auth = Yii::$app->authManager;
		$role = $auth->getRole($code);

		if (!$role) {
			throw new NotFoundHttpException('Запрошенная страница не найдена.');
		}
		return $role;
	}
}