<?php

namespace frontend\helpers;

use backend\modules\user\components\UserComponent;
use Yii;
use yii\base\Widget;

class Menu extends Widget
{
	public function run()
	{
		/** @var UserComponent $user */
		$user = Yii::$app->user;

		$guestItems = [
			[
				'label' => 'Главная',
				'url' => '/',
			],
		];

		$userItems = [
			[
				'label' => 'Главная',
				'url' => '/',
			],
		];

		$moderatorItems = [
			[
				'label' => 'Главная',
				'url' => '/',
			],
		];

		$adminItems = [
			[
				'label' => 'Главная',
				'url' => '/',
			],
		];

		$menuItems = $guestItems;
		if ($user->can('superuser') || $user->can('admin')) {
			$menuItems = $adminItems;
		}
		if ($user->can('moderator')) {
			$menuItems = $moderatorItems;
		}
		if ($user->can('user')) {
			$menuItems = $userItems;
		}

		if (!$user->isGuest) {
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
		}

		echo $this->render('/layouts/_top-menu', compact('menuItems'));
	}
}