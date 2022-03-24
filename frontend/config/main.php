<?php

use yii\base\ActionEvent;

$params = array_merge(
	require __DIR__ . '/../../common/config/params.php',
	require __DIR__ . '/../../common/config/params-local.php',
	require __DIR__ . '/params.php',
	require __DIR__ . '/params-local.php'
);

$https = (($_SERVER['HTTPS'] ?? '') == 'on' ? 'https' : 'http');

return [
	'id' => 'app-frontend',
	'basePath' => dirname(__DIR__),
	'bootstrap' => ['log'],
	'controllerNamespace' => 'frontend\controllers',
	'aliases' => [
		'@bower' => '@vendor/bower-asset',
		'@npm' => '@vendor/npm-asset',
	],
	'modules' => [
		'debug' => [
			'class' => 'yii\debug\Module',
			'traceLine' => '<a href="phpstorm://open?url=file://{file}&line={line}">{file}:{line}</a>',
			'allowedIPs' => [],
		],
	],
	'components' => [
		'request' => [
			'baseUrl' => '',
			'csrfParam' => '_csrf-frontend',
			'csrfCookie' => [
				'httpOnly' => true,
				'path' => '/',
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
					'except' => [
						'yii\debug\Module*',
					],
				],
				// Фиксирование ошибок 404 на почту.
				[
					'class' => 'yii\log\EmailTarget',
					'mailer' => 'mailer',
					'levels' => ['error'],
					'categories' => [
						'yii\web\HttpException:404',
					],
					'message' => [
						'from' => $params['error400Email'],
						'to' => $params['supportEmail'],
						'subject' => "{$https}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']} Ошибка 404 на сайте {$_SERVER['HTTP_HOST']}",
					],
				],
				// Фиксирование остальных ошибок на почту.
				[
					'class' => 'yii\log\EmailTarget',
					'mailer' => 'mailer',
					'levels' => ['error'],
					'except' => [
						'yii\web\HttpException:404',
					],
					'message' => [
						'from' => $params['error500Email'],
						'to' => $params['supportEmail'],
						'subject' => "{$https}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']} Ошибка 50* на сайте {$_SERVER['HTTP_HOST']}",
					],
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
				'<module:(debug)>/<controller:[\wd-]+>/<action:[\wd-]+>/<id:[\d]+>' => '<module>/<controller>/<action>',
				'<module:(debug)>/<controller:[\wd-]+>/<action:[\wd-]+>/<code:[\d\-_a-zA-Z]+>' => '<module>/<controller>/<action>',
				'<module:(debug)>/<controller:[\wd-]+>/<action:[\wd-]+>' => '<module>/<controller>/<action>',
				'<module:(debug)>/<controller:[\wd-]+>' => '<module>/<controller>/index',
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
