<?php

namespace backend\modules\backup\controllers;

use Yii;
use yii\base\Exception;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use backend\modules\backup\models\RawConnection;

class RemoteController extends Controller
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
		$remotes = RawConnection::getRemotes();
		$models = [];
		foreach ($remotes as $code => $remote) {
			$models[] = RawConnection::toModel($code);
		}
		$dataProvider = new ArrayDataProvider([
			'id' => 'remotes',
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
		$dir = dirname(__DIR__) . '/config';
		if (!is_dir($dir)) {
			try {
				FileHelper::createDirectory($dir);
			} catch (Exception $e) {
				print_r($e);
			}
		}
		$path = "{$dir}/remote-local.php";

		$model = new RawConnection();
		$remotes = RawConnection::getRemotes();

		if ($this->request->isPost) {
			$post = $this->request->post();
			if ($model->load($post) && $model->validate()) {
				$prepared = $model->prepareConnection();
				$united = array_merge($remotes, $prepared);
				if (!empty($united)) {
					$exported = var_export($united, true);
					$exported = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $exported);
					$array = preg_split("/\r\n|\n|\r/", $exported);
					$array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
					$exported = join(PHP_EOL, array_filter(["["] + $array));
					if (file_put_contents($path, "<?php\nreturn {$exported};\n")) {
						$this->redirect(['remote/index']);
					}
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
		$remotes = RawConnection::getRemotes();
		if (!empty($remotes[$code])) {
			$dir = dirname(__DIR__) . '/config';
			if (!is_dir($dir)) {
				try {
					FileHelper::createDirectory($dir);
				} catch (Exception $e) {
					print_r($e);
				}
			}
			$path = "{$dir}/remote-local.php";

			$model = RawConnection::toModel($code);
			$beforeSave = clone $model;

			if ($this->request->isPost) {
				$post = $this->request->post();
				if (!empty($post) && $model->load($post)) {
					if (
						($beforeSave->code != $model->code && empty($locals[$model->code]))
						|| ($beforeSave->code == $model->code)
					) {
						unset($remotes[$beforeSave->code]);
						$prepared = $model->prepareConnection();
						$united = array_merge($remotes, $prepared);
						if (!empty($united)) {
							$exported = var_export($united, true);
							$exported = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $exported);
							$array = preg_split("/\r\n|\n|\r/", $exported);
							$array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
							$exported = join(PHP_EOL, array_filter(["["] + $array));
							if (file_put_contents($path, "<?php\nreturn {$exported};\n")) {
								$this->redirect(['remote/index']);
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
	 */
	public function actionDelete(string $code): Response
	{
		$remotes = RawConnection::getRemotes();
		if (!empty($remotes[$code])) {
			$dir = dirname(__DIR__) . '/config';
			if (!is_dir($dir)) {
				try {
					FileHelper::createDirectory($dir);
				} catch (Exception $e) {
					print_r($e);
				}
			}
			$path = "{$dir}/remote-local.php";

			unset($remotes[$code]);

			$exported = var_export($remotes, true);
			$exported = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $exported);
			$array = preg_split("/\r\n|\n|\r/", $exported);
			$array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
			$exported = join(PHP_EOL, array_filter(["["] + $array));
			file_put_contents($path, "<?php\nreturn {$exported};\n");
		} else {
			throw new NotFoundHttpException();
		}
		return $this->redirect(['remote/index']);
	}

	/**
	 * @param string $code
	 * @return array|RawConnection
	 * @throws NotFoundHttpException
	 */
	public function findModel(string $code)
	{
		$remotes = RawConnection::getRemotes();
		if (!empty($remotes[$code])) {
			return RawConnection::toModel($code);
		} else {
			throw new NotFoundHttpException();
		}
	}
}