<?php

$params = array_merge(
	require __DIR__ . '/../../common/config/params.php',
	require __DIR__ . '/../../common/config/params-local.php',
	require __DIR__ . '/params.php',
	require __DIR__ . '/params-local.php'
);

return [
	'id' => 'app-console',
	'basePath' => dirname(__DIR__),
	'bootstrap' => ['log'],
	'controllerNamespace' => 'console\controllers',
	'aliases' => [
		'@bower' => '@vendor/bower-asset',
		'@npm' => '@vendor/npm-asset',
	],
	'controllerMap' => [
		'fixture' => [
			'class' => 'yii\console\controllers\FixtureController',
			'namespace' => 'common\fixtures',
		],
	],
	'components' => [
		'log' => [
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
				[
					'class' => 'yii\log\EmailTarget',
					'mailer' => 'mailer',
					'levels' => ['error'],
					'message' => [
						'from' => $params['error500Email'],
						'to' => $params['supportEmail'],
						'subject' => implode(' ', $_SERVER['argv'] ?? []) . " Ошибка на сайте (console)",
					],
				],
			],
		],
	],
	'params' => $params,
];
