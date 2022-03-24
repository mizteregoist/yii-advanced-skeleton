<?php
return [
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
	'aliases' => [
		'@bower' => '@vendor/bower-asset',
		'@npm' => '@vendor/npm-asset',
	],
	'components' => [
		'authManager' => [
			'class' => 'yii\rbac\DbManager',
		],
		'cache' => [
			'class' => 'yii\caching\FileCache',
			'defaultDuration' => 60 * 60 * 8,
		],
		'i18n' => [
			'translations' => [
				'yii2mod.rbac' => [
					'class' => 'yii\i18n\PhpMessageSource',
					'basePath' => '@yii2mod/rbac/messages',
				],
			],
		],
		'assetManager' => [
			'linkAssets' => false,
			'appendTimestamp' => true,
		],
		'formatter' => [
			'class' => '\yii\i18n\Formatter',
			'nullDisplay' => '&nbsp;',
			'thousandSeparator' => ' ',
			'locale' => 'ru-RU',
			'defaultTimeZone' => 'Europe/Moscow',
			'dateFormat' => 'dd.MM.yyyy',
			'datetimeFormat' => 'dd.MM.yyyy, HH:mm:ss',
			'timeFormat' => 'HH:mm:ss',
		],
		'monolog' => [
			'class' => '\Mero\Monolog\MonologComponent',
			'channels' => [
				'main' => [
					'handler' => [
						[
							'type' => 'stream',
							'path' => '@frontend/runtime/logs/' . date('Y-m-d') . '/main.log',
							'level' => 'debug',
						],
					],
					'processor' => [],
				],
				'auth' => [
					'handler' => [
						[
							'type' => 'stream',
							'path' => '@frontend/runtime/logs/' . date('Y-m-d') . '/auth.log',
							'level' => 'debug',
						],
					],
					'processor' => [],
				],
			],
		],
	],
	'language' => 'ru-RU',
	'sourceLanguage' => 'ru-RU',
	'timeZone' => 'Europe/Moscow',
	'name' => 'Yii2',
];
