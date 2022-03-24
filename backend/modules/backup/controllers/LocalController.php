<?php

namespace backend\modules\backup\controllers;

use Exception;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use backend\modules\backup\models\RawConnection;

class LocalController extends Controller
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
				'access' => [
					'class' => AccessControl::class,
					'rules' => [
						[
							'allow' => true,
							'roles' => ['superuser', 'developer'],
						],
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

	/**
	 * @param $action
	 * @return bool|Response
	 */
	public function beforeAction($action)
	{
		if (!Yii::$app->user->can('dashboard') && !in_array($action->id, ['login', 'error'])) {
			return $this->redirect(['/site/login']);
		}
		return true;
	}

	/**
	 * @param $action
	 * @param $result
	 * @return mixed
	 */
	public function afterAction($action, $result)
	{
		clearstatcache();
		return parent::afterAction($action, $result);
	}

	/**
	 * @return string
	 */
	public function actionIndex(): string
	{
		$locals = RawConnection::getLocals();
		$models = [];
		foreach ($locals as $code => $local) {
			$models[] = RawConnection::toModel($code, $locals);
		}
		$dataProvider = new ArrayDataProvider([
			'id' => 'locals',
			'allModels' => $models,
		]);
		return $this->render('index', [
			'dataProvider' => $dataProvider,
		]);
	}

	/**
	 * @return string
	 */
	public function actionCreate(): string
	{
		$model = new RawConnection();
		$path = Yii::getAlias('@common/config/main-local.php');
		$data = require $path;

		$locals = RawConnection::getLocals();

		if ($this->request->isPost) {
			$post = $this->request->post();
			if ($model->load($post) && $model->validate()) {
				if (empty($locals[$model->code])) {
					$prepared = $model->prepareLocalConnection();
					$united = array_merge($locals, $prepared);
					$united = array_merge($data['components'], $united);
					ksort($united);
					$data['components'] = $united;

					$exported = var_export($data, true);
					$exported = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $exported);
					$array = preg_split("/\r\n|\n|\r/", $exported);
					$array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
					$exported = join(PHP_EOL, array_filter(["["] + $array));
					try {
						if (file_put_contents($path, "<?php\nreturn {$exported};\n")) {
							$this->redirect(['local/index']);
						}
					} catch (Exception $e) {
						foreach ($model->attributes as $attribute => $value) {
							$model->addError($attribute, 'Ошибка записи файла');
						}
					}

				} else {
					$model->addError('code', 'База данных с таким символьным кодом уже существует');
				}
			}
		}
		return $this->render('create', [
			'model' => $model,
		]);
	}

	/**
	 * @param string $code
	 * @return string
	 * @throws NotFoundHttpException
	 */
	public function actionUpdate(string $code): string
	{
		$model = new RawConnection();
		$path = Yii::getAlias('@common/config/main-local.php');
		$data = require $path;

		$locals = RawConnection::getLocals();
		if (!empty($locals[$code])) {
			$model = RawConnection::toModel($code, $locals);
			$beforeSave = clone $model;
			if ($this->request->isPost) {
				$post = $this->request->post();
				if ($model->load($post) && $model->validate()) {
					if (
						($beforeSave->code != $model->code && empty($locals[$model->code]))
						|| ($beforeSave->code == $model->code)
					) {
						if ($beforeSave->code == 'db') {
							$model->addError('code', 'Базу данных с таким символьным кодом нельзя переименовывать');
						} else {
							unset($locals[$beforeSave->code]);
							unset($data['components'][$beforeSave->code]);
							$prepared = $model->prepareLocalConnection();
							$united = array_merge($locals, $prepared);
							$united = array_merge($data['components'], $united);
							ksort($united);
							$data['components'] = $united;

							$exported = var_export($data, true);
							$exported = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $exported);
							$array = preg_split("/\r\n|\n|\r/", $exported);
							$array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
							$exported = join(PHP_EOL, array_filter(["["] + $array));
							try {
								if (file_put_contents($path, "<?php\nreturn {$exported};\n")) {
									$this->redirect(['local/index']);
								}
							} catch (Exception $e) {
								foreach ($model->attributes as $attribute => $value) {
									$model->addError($attribute, 'Ошибка записи файла');
								}
							}
						}
					} else {
						$model->addError('code', 'База данных с таким символьным кодом уже существует');
					}
				}
			}

			return $this->render('update', [
				'model' => $model,
			]);
		} else {
			throw new NotFoundHttpException();
		}
	}

	/**
	 * @param string $code
	 * @return Response
	 * @throws NotFoundHttpException
	 * @throws ForbiddenHttpException
	 */
	public function actionDelete(string $code): Response
	{
		$model = new RawConnection();
		$path = Yii::getAlias('@common/config/main-local.php');
		$data = require $path;

		$locals = RawConnection::getLocals();
		if (!empty($locals[$code]) && !empty($data['components'][$code])) {
			if ($code == 'db') {
				throw new ForbiddenHttpException('Нельзя удалять основную базу данных');
			} else {
				unset($data['components'][$code]);

				$exported = var_export($data, true);
				$exported = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $exported);
				$array = preg_split("/\r\n|\n|\r/", $exported);
				$array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
				$exported = join(PHP_EOL, array_filter(["["] + $array));
				file_put_contents($path, "<?php\nreturn {$exported};\n");
			}
		} else {
			throw new NotFoundHttpException();
		}
		return $this->redirect(['local/index']);
	}

	/**
	 * @param string $code
	 * @return array|RawConnection
	 * @throws NotFoundHttpException
	 */
	public function findModel(string $code)
	{
		$locals = RawConnection::getLocals();
		if (!empty($locals[$code])) {
			return RawConnection::toModel($code, $locals);
		} else {
			throw new NotFoundHttpException();
		}
	}
}