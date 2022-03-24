<?php

namespace backend\modules\backup\controllers;

use Yii;
use yii\base\Exception;
use yii\data\ArrayDataProvider;
use yii\db\Connection;
use yii\filters\AccessControl;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use backend\modules\backup\models\Conformity;
use backend\modules\backup\models\RawConnection;

class ConformityController extends Controller
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
		$conformity = Conformity::getData();
		$models = [];
		foreach ($conformity as $remote => $local) {
			$models[] = new Conformity(['remote' => $remote, 'local' => $local]);
		}
		$dataProvider = new ArrayDataProvider([
			'id' => 'conformity',
			'allModels' => $models,
		]);
		return $this->render('index', [
			'dataProvider' => $dataProvider,
			'message' => null,
		]);
	}

	/**
	 * @param string $code
	 * @return string|void
	 * @throws \Exception
	 */
	public function actionSync(string $code)
	{
		$locals = RawConnection::getLocals();
		$conformity = Conformity::getData();
		$models = [];
		foreach ($conformity as $remote => $local) {
			$models[] = new Conformity(['remote' => $remote, 'local' => $local]);
		}
		$dataProvider = new ArrayDataProvider([
			'id' => 'conformity',
			'allModels' => $models,
		]);
		if (!empty($conformity[$code]) && $this->request->isPost) {
			$remote = RawConnection::toModel($code);
			$local = RawConnection::toModel($conformity[$code], $locals);
			$filename = "{$local->name}.sql";
			$projectPath = "./data/db/backup/{$filename}";
			$dockerPath = "/home/backup/{$filename}";
			if ($remote->type != $local->type) {
				throw new \Exception('DB connection type is different');
			}
			try {
				$connection = new Connection($remote->getConnection());
				$connection->open();
				if ($connection->isActive) {
					$dumpCommand = [];
					switch ($local->type) {
						case 'mysql':
							$remotePort = empty($remote->port) ? 3306 : $remote->port;
							$dumpCommand[] = "docker exec localserver_mysql_1 /usr/bin/mysqldump --user={$remote->user} --password={$remote->password} --host={$remote->host} --port={$remotePort} --add-drop-table --skip-comments {$remote->name} > {$projectPath}";
							$dumpCommand[] = "docker exec localserver_mysql_1 /bin/bash -c \"mysql --user={$local->user} --password={$local->password} {$local->name} < {$dockerPath}\"";
							break;
						case 'pgsql':
							$remotePort = empty($remote->port) ? 5432 : $remote->port;
							$localPort = empty($local->port) ? 5432 : $local->port;
							$dumpCommand[] = "docker exec localserver_pgsql_1 pg_dump --dbname=postgresql://{$remote->user}:{$remote->password}@{$remote->host}:{$remotePort}/{$remote->name} -f{$projectPath}";
							$dumpCommand[] = "docker exec localserver_pgsql_1 dropdb --username={$local->user} {$local->name} -w";
							$dumpCommand[] = "docker exec localserver_pgsql_1 createdb --username={$local->user} {$local->name} -w";
							$dumpCommand[] = "docker exec localserver_pgsql_1 psql --dbname=postgresql://{$local->user}:{$local->password}@{$local->host}:{$localPort}/{$local->name} -f{$dockerPath}";
							break;
					}
					$html = "";
					foreach ($dumpCommand as $item) {
						$html .= "{$item}<br>";
					}
					return $this->renderAjax('index', [
						'dataProvider' => $dataProvider,
						'message' => $html,
					]);
				}
				$connection->close();
			} catch (\yii\db\Exception $e) {

			}
		}
	}

	/**
	 * @return string|Response
	 */
	public function actionCreate()
	{
		$model = new Conformity();
		$dir = dirname(__DIR__) . '/config';
		if (!is_dir($dir)) {
			try {
				FileHelper::createDirectory($dir);
			} catch (Exception $e) {
				print_r($e);
			}
		}
		$path = "{$dir}/conformity-local.php";
		$conformity = Conformity::getData();

		if ($this->request->isPost) {
			$post = $this->request->post();
			if ($model->load($post) && $model->validate()) {
				$united = array_merge($conformity, [$model->remote => $model->local]);
				if (!empty($united)) {
					$exported = var_export($united, true);
					$exported = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $exported);
					$array = preg_split("/\r\n|\n|\r/", $exported);
					$array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
					$exported = join(PHP_EOL, array_filter(["["] + $array));
					if (file_put_contents($path, "<?php\nreturn {$exported};\n")) {
						return $this->redirect(['conformity/index']);
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
	 * @return Response|string
	 * @throws NotFoundHttpException
	 */
	public function actionUpdate(string $code)
	{
		$model = new Conformity();
		$dir = dirname(__DIR__) . '/config';
		if (!is_dir($dir)) {
			try {
				FileHelper::createDirectory($dir);
			} catch (Exception $e) {
				print_r($e);
			}
		}
		$path = "{$dir}/conformity-local.php";
		$conformity = Conformity::getData();
		if (!empty($conformity[$code])) {
			$model->remote = $code;
			$model->local = $conformity[$code];

			if ($this->request->isPost) {
				$post = $this->request->post();
				if ($model->load($post) && $model->validate()) {
					$united = array_merge($conformity, [$model->remote => $model->local]);
					if (!empty($united)) {
						$exported = var_export($united, true);
						$exported = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $exported);
						$array = preg_split("/\r\n|\n|\r/", $exported);
						$array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
						$exported = join(PHP_EOL, array_filter(["["] + $array));
						if (file_put_contents($path, "<?php\nreturn {$exported};\n")) {
							return $this->redirect(['conformity/index']);
						}
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
		$dir = dirname(__DIR__) . '/config';
		if (!is_dir($dir)) {
			try {
				FileHelper::createDirectory($dir);
			} catch (Exception $e) {
				print_r($e);
			}
		}
		$path = "{$dir}/conformity-local.php";
		$conformity = Conformity::getData();
		if (!empty($conformity[$code])) {
			unset($conformity[$code]);

			$exported = var_export($conformity, true);
			$exported = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $exported);
			$array = preg_split("/\r\n|\n|\r/", $exported);
			$array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
			$exported = join(PHP_EOL, array_filter(["["] + $array));
			if (file_put_contents($path, "<?php\nreturn {$exported};\n")) {
				return $this->redirect(['conformity/index']);
			}
		} else {
			throw new NotFoundHttpException();
		}
		return $this->redirect(['remote/index']);
	}
}