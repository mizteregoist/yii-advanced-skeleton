<?php

use yii\base\ActionEvent;

$params = array_merge(
	require __DIR__ . '/../../common/config/params.php',
	require __DIR__ . '/../../common/config/params-local.php',
	require __DIR__ . '/params.php',
	require __DIR__ . '/params-local.php'
);

return [
	'id' => 'app-backend',
	'basePath' => dirname(__DIR__),
	'controllerNamespace' => 'backend\controllers',
	'bootstrap' => ['log', 'debug'],
	'language' => 'ru',
	'aliases' => [
		'@bower' => '@vendor/bower-asset',
		'@npm' => '@vendor/npm-asset',
	],
	'modules' => [
		'user' => [
			'class' => 'backend\modules\user\Module',
		],
		'content' => [
			'class' => 'backend\modules\content\Module',
		],
		'backup' => [
			'class' => 'backend\modules\backup\Module',
		],
		'rbac' => [
			'class' => 'yii2mod\rbac\Module',
		],
		'gii' => [
			'class' => 'yii\gii\Module',
			'allowedIPs' => [],
		],
		'debug' => [
			'class' => 'yii\debug\Module',
			'traceLine' => '<a href="phpstorm://open?url=file://{file}&line={line}">{file}:{line}</a>',
			'allowedIPs' => [],
		],
	],
	'components' => [
		'request' => [
			'baseUrl' => '/admin',
			'csrfParam' => '_csrf-backend',
			'csrfCookie' => [
				'httpOnly' => true,
				'path' => '/admin',
			],
			'enableCsrfValidation' => true,
			'parsers' => [
				'application/json' => 'yii\web\JsonParser',
			],
		],
		'user' => [
			'class' => 'backend\modules\user\components\UserComponent',
			'identityClass' => 'backend\modules\user\models\User',
			'enableAutoLogin' => true,
			'identityCookie' => [
				'name' => '_identity_user',
				'httpOnly' => true,
				'domain' => $_SERVER['HTTP_HOST'],
			],
		],
		'log' => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'errorHandler' => [
			'errorAction' => 'site/error',
		],
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'suffix' => '/',
			'rules' => [
				'' => 'site/index',
				'<action:(login|logout)>' => 'site/<action>',
				'<module:(debug|backup|user|content)>/<controller:[\wd-]+>/<action:[\wd-]+>/<id:[\d]+>' => '<module>/<controller>/<action>',
				'<module:(debug|backup|user|content)>/<controller:[\wd-]+>/<action:[\wd-]+>/<code:[\d\-_a-zA-Z]+>' => '<module>/<controller>/<action>',
				'<module:(debug|backup|user|content)>/<controller:[\wd-]+>/<action:[\wd-]+>' => '<module>/<controller>/<action>',
				'<module:(debug|backup|user|content)>/<controller:[\wd-]+>' => '<module>/<controller>/index',
				'<controller:[\wd-]+>/<action:[\wd-]+>/<id:[\d]+>' => '<controller>/<action>',
				'<controller:[\wd-]+>/<action:[\wd-]+>/<code:[\d\-_a-zA-Z]+>' => '<controller>/<action>',
				'<controller:[\wd-]+>/<action:[\wd-]+>' => '<controller>/<action>',
				'<controller:[\wd-]+>' => '<controller>/index',
				'<module:[\wd-]+>/<controller:[\wd-]+>/<action:[\wd-]+>/<id:[\d]+>' => '<module>/<controller>/<action>',
				'<module:[\wd-]+>/<controller:[\wd-]+>/<action:[\wd-]+>/<code:[\d\-_a-zA-Z]+>' => '<module>/<controller>/<action>',
				'<module:[\wd-]+>/<controller:[\wd-]+>/<action:[\wd-]+>' => '<module>/<controller>/<action>',
				'<module:[\wd-]+>/<controller:[\wd-]+>' => '<module>/<controller>/index',
			],
		],
		'assetManager' => [
			'basePath' => '@webroot/assets',
			'baseUrl' => '@web/assets',
			'linkAssets' => false,
			'appendTimestamp' => true,
			'bundles' => [
				'dosamigos\multiselect\MultiSelectAsset' => [
					'depends' => ['yii\bootstrap4\BootstrapPluginAsset'],
				],
			],
		],
		'authManager' => [
			'class' => 'yii\rbac\DbManager',
		],
	],
	'on beforeAction' => function (ActionEvent $event) {
		$url = Yii::$app->request->absoluteUrl;
		$uri = parse_url($url);
		$path = trim($uri['path'], '/');
		$pos = strripos($path, 'index');
		if ($pos != false && strlen($path) - strlen('index') == $pos && $event->action->id == 'index') {
			$url = preg_replace("/(\/index)/", '', $url);
			Yii::$app->response->redirect($url);
		}
		if (Yii::$app->user->can('yii_debug')) {
			Yii::$app->getModule('debug')->allowedIPs = ['*'];
		}
		if (Yii::$app->user->can('gii') && YII_ENV_LOCAL) {
			Yii::$app->getModule('gii')->allowedIPs = ['*'];
		}
	},
	'params' => $params,
];
