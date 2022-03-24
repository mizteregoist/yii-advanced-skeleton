<?php

namespace backend\helpers;

use backend\modules\user\components\UserComponent;
use Yii;
use yii\helpers\Url;

class AdminPanel
{

	public static function topMenuItems(): array
	{
		/** @var UserComponent $user */
		$user = Yii::$app->user;
		$menuItems = [
			[
				'label' => 'Главная',
				'url' => Url::toRoute(['/']),
			],
			[
				'label' => 'Сайт',
				'url' => '/',
				'linkOptions' => ['target' => '_blank'],
			],
		];
		$userItems = [];
		if ($user->isSuperuser() || $user->isDeveloper() || $user->isAdmin()) {
			$userItems[] = [
				'label' => 'Роли',
				'url' => Url::toRoute(['/user/role']),
				'options' => ['class' => 'dropdown-submenu'],
			];

			$userItems[] = [
				'label' => 'Разрешения',
				'url' => Url::toRoute(['/user/permission']),
				'options' => ['class' => 'dropdown-submenu'],
			];
		}
		if ($user->can('user_view')) {
			$userItems[] = [
				'label' => 'Список пользователей',
				'url' => Url::toRoute(['/user/user']),
				'options' => ['class' => 'dropdown-submenu'],
			];

			$menuItems[] = [
				'label' => 'Пользователи',
				'url' => '#',
				'items' => $userItems,
			];
		}

		$contentItems = [];
		if ($user->can('content_create') && $user->can('content_update')) {
			$contentItems = [
				[
					'label' => 'Разделы',
					'url' => Url::toRoute(['/content/section']),
					'options' => ['class' => 'dropdown-submenu'],
				],
				[
					'label' => 'Элементы',
					'url' => Url::toRoute(['/content/element']),
					'options' => ['class' => 'dropdown-submenu'],
				],
				[
					'label' => 'Блоки',
					'url' => Url::toRoute(['/content/block']),
					'options' => ['class' => 'dropdown-submenu'],
				],
				[
					'label' => 'Категории',
					'url' => Url::toRoute(['/content/category']),
					'options' => ['class' => 'dropdown-submenu'],
				],
			];
		}

		$menuItems[] = [
			'label' => 'Контент',
			'url' => '#',
			'items' => $contentItems,
		];

		$dbItems = [];
		if ($user->isSuperuser() || $user->isDeveloper()) {
			$dbItems = [
				[
					'label' => 'Локальные БД',
					'url' => Url::toRoute(['/backup/local/index']),
					'options' => ['class' => 'dropdown-submenu'],
				],
			];
			if (YII_ENV_LOCAL) {
				$dbItems = array_merge($dbItems, [
					[
						'label' => 'Удаленные БД',
						'url' => Url::toRoute(['/backup/remote/index']),
						'options' => ['class' => 'dropdown-submenu'],
					],
					[
						'label' => 'Соответствие БД',
						'url' => Url::toRoute(['/backup/conformity/index']),
						'options' => ['class' => 'dropdown-submenu'],
					],
				]);
			}
		}

		if (!empty($dbItems)) {
			$menuItems[] = [
				'label' => 'База Данных',
				'url' => '#',
				'items' => $dbItems,
			];
		}

		if ($user->can('gii') && YII_ENV_LOCAL) {
			$menuItems[] = [
				'label' => 'Конструктор gii',
				'url' => ['../gii'],
				'linkOptions' => ['target' => '_blank'],
			];
		}

		$menuItems[] = [
			'label' => 'Выйти (' . $user->identity->username . ')',
			'url' => ['site/logout'],
		];

		return $menuItems;
	}
}