<?php

namespace backend\modules\backup\models;

use Exception;
use ReflectionClass;
use Yii;
use yii\base\Model;
use yii\db\Connection;
use yii\helpers\FileHelper;

/**
 * @property string $code
 * @property string $type
 * @property string $host
 * @property string $port
 * @property string $name
 * @property string $user
 * @property string $password
 * @property-read array $status
 * @property-read array $connection
 * @property string $charset
 */
class RawConnection extends Model
{
	public $code;
	public $type;
	public $host;
	public $port;
	public $name;
	public $user;
	public $password;
	public $charset;

	/**
	 * {@inheritdoc}
	 */
	public function rules(): array
	{
		return [
			[['code', 'type', 'host', 'name', 'user', 'password'], 'required'],
			[['code', 'type', 'host', 'port', 'name', 'user', 'password', 'charset'], 'string'],
			[['code', 'type', 'host', 'port', 'name', 'user', 'password', 'charset'], 'trim'],
			[['charset'], 'default', 'value' => 'utf8'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(): array
	{
		return [
			'code' => 'Символьный код',
			'type' => 'Тип соединения',
			'host' => 'Хост',
			'port' => 'Порт',
			'name' => 'Название БД',
			'user' => 'Пользователь',
			'password' => 'Пароль',
			'charset' => 'Кодировка',
			'status' => 'Состояние',
		];
	}

	/**
	 * @return array
	 */
	public function getStatus(): array
	{
		try {
			$connection = new Connection($this->getConnection());
			$connection->open();
			if ($connection->isActive) {
				$result = [
					'code' => 200,
					'message' => 'Ok',
				];
			} else {
				$result = [
					'code' => 500,
					'message' => 'No connection',
				];
			}
			$connection->close();
		} catch (\yii\db\Exception $e) {
			$result = [
				'code' => $e->getCode(),
				'message' => $e->getMessage(),
			];
		}
		return $result;
	}

	/**
	 * @return array
	 */
	public function getConnection(): array
	{
		$host = !empty($this->host) ? "host={$this->host};" : '';
		$port = !empty($this->port) ? "port={$this->port};" : '';
		$name = !empty($this->name) ? "dbname={$this->name};" : '';
		$charset = !empty($this->charset) ? "charset={$this->charset};" : '';
		$dsn = "{$this->type}:{$host}{$port}{$name}{$charset}";

		return [
			'dsn' => $dsn,
			'username' => $this->user,
			'password' => $this->password,
		];
	}

	/**
	 * @return array[]
	 */
	public function prepareConnection(): array
	{
		$host = !empty($this->host) ? "host={$this->host};" : '';
		$port = !empty($this->port) ? "port={$this->port};" : '';
		$name = !empty($this->name) ? "dbname={$this->name};" : '';
		$charset = !empty($this->charset) ? "charset={$this->charset};" : '';
		$dsn = "{$this->type}:{$host}{$port}{$name}{$charset}";

		return [
			$this->code => [
				'dsn' => $dsn,
				'username' => $this->user,
				'password' => $this->password,
			],
		];
	}

	/**
	 * @return array[]
	 */
	public function prepareLocalConnection(): array
	{
		$host = !empty($this->host) ? "host={$this->host};" : '';
		$port = !empty($this->port) ? "port={$this->port};" : '';
		$name = !empty($this->name) ? "dbname={$this->name};" : '';
		$dsn = "{$this->type}:{$host}{$port}{$name}";
		$result[$this->code]['class'] = Connection::class;
		$result[$this->code]['dsn'] = $dsn;
		$result[$this->code]['username'] = $this->user;
		$result[$this->code]['password'] = $this->password;
		if (!empty($this->charset)) {
			$result[$this->code]['charset'] = $this->charset;
		}

		return $result;
	}

	/**
	 * @param bool $forSelect
	 * @return array
	 */
	public static function getRemotes(bool $forSelect = false): array
	{
		$result = [];
		try {
			$dir = dirname(__DIR__) . '/config';
			if (!is_dir($dir)) {
				FileHelper::createDirectory($dir);
			}
			$path = "{$dir}/remote-local.php";
			if (!file_exists($path)) {
				file_put_contents($path, "<?php\nreturn [];\n");
			}
			$data = require $path;
			if ($forSelect) {
				foreach ($data as $key => $item) {
					if (!empty($item['dsn'])) {
						$result[$key] = "[{$key}] {$item['dsn']}";
					}
				}
			} else {
				$result = $data;
			}
		} catch (Exception $e) {
			print_r($e);
		}
		return $result;
	}

	/**
	 * @param bool $forSelect
	 * @return array
	 */
	public static function getLocals(bool $forSelect = false): array
	{
		$path = Yii::getAlias('@common/config/main-local.php');
		$data = require $path;
		$result = [];
		foreach ($data['components'] ?? [] as $code => $component) {
			if (!empty($component['class']) && $component['class'] == Connection::class) {
				if ($forSelect) {
					if (!empty($component['dsn'])) {
						$result[$code] = "[{$code}] {$component['dsn']}";
					}
				} else {
					$result[$code] = $component;
				}
			}
		}
		return $result;
	}

	/**
	 * @param string $code
	 * @param array $connection
	 * @return array|RawConnection
	 */
	public static function toModel(string $code, array $connection = [])
	{
		$model = new self();
		$class = (new ReflectionClass($model))->getShortName();
		$remotes = self::getRemotes();
		$connection = !empty($connection[$code]) ? $connection[$code] : [];
		$connection = (empty($connection) && !empty($remotes[$code])) ? $remotes[$code] : $connection;
		$prepared[$class]['code'] = $code;
		$prepared[$class]['user'] = $connection['username'];
		$prepared[$class]['password'] = $connection['password'];
		$exploded = array_filter(explode(':', $connection['dsn']));
		$commands = array_keys((new Connection())->commandMap);
		if (in_array($exploded[0], $commands)) {
			$prepared[$class]['type'] = $exploded[0];
		}
		if (!empty($exploded[1]) && preg_match('/;/', $exploded[1])) {
			$exploded = array_filter(explode(';', $exploded[1]));
			if (!empty($exploded)) {
				foreach ($exploded as $item) {
					$data = explode('=', $item);
					if (!empty($data[0]) && !empty($data[1])) {
						switch ($data[0]) {
							case 'host':
								$prepared[$class]['host'] = $data[1];
								break;
							case 'port':
								$prepared[$class]['port'] = $data[1];
								break;
							case 'dbname':
								$prepared[$class]['name'] = $data[1];
								break;
							case 'charset':
								$prepared[$class]['charset'] = $data[1];
								break;
						}
					}
				}
			}
		}
		if (empty($prepared[$class]['charset']) && !empty($connection['charset'])) {
			$prepared[$class]['charset'] = $connection['charset'];
		}

		if ($model->load($prepared) && $model->validate()) {
			return $model;
		} else {
			return $model->errors;
		}
	}
}