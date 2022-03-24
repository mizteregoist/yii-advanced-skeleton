<?php

namespace backend\controllers;

use common\models\LoginForm;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
	/**
	 * {@inheritdoc}
	 */
	public function behaviors(): array
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'actions' => ['login'],
						'allow' => true,
						'roles' => ['?'],
					],
					[
						'actions' => ['index', 'logout', 'error'],
						'allow' => true,
						'roles' => ['@'],
					],
					[
						'allow' => true,
						'roles' => ['superuser', 'developer', 'admin', 'moderator'],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'logout' => ['post'],
				],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function actions()
	{
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
		];
	}

	/**
	 * Displays homepage.
	 *
	 * @return string
	 */
	public function actionIndex(): string
	{
		if (!Yii::$app->user->can('dashboard')) {
			return $this->redirect('/');
		}

		return $this->render('index');
	}

	/**
	 * Login action.
	 *
	 * @return string|Response
	 */
	public function actionLogin()
	{
		if (!Yii::$app->user->isGuest) {
			return $this->goHome();
		}

		$this->layout = 'blank';

		$model = new LoginForm();
		if ($model->load(Yii::$app->request->post()) && $model->login()) {
			return $this->goBack();
		}

		$model->password = '';

		return $this->render('login', [
			'model' => $model,
		]);
	}

	/**
	 * Logout action.
	 *
	 * @return Response
	 */
	public function actionLogout(): Response
	{
		Yii::$app->user->logout();

		return $this->goHome();
	}
}
