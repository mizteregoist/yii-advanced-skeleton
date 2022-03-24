<?php

namespace console\controllers;

use Exception;
use Yii;
use yii\console\Controller;
use yii\rbac\Permission;
use yii\rbac\Role;

class RbacController extends Controller
{
	public function actionInit()
	{
		$auth = Yii::$app->authManager;

		$roles = [
			[
				'name' => 'superuser',
				'description' => 'Суперпользователь',
			],
			[
				'name' => 'developer',
				'description' => 'Разработчик',
			],
			[
				'name' => 'admin',
				'description' => 'Администратор',
			],
			[
				'name' => 'moderator',
				'description' => 'Модератор',
			],
			[
				'name' => 'user',
				'description' => 'Пользователь',
			],
			[
				'name' => 'system',
				'description' => 'Система',
			],
		];
		$permissions = $superuserPermissions = $developerPermissions = [
			[
				'name' => 'yii_debug',
				'description' => 'Yii debug',
			],
			[
				'name' => 'log',
				'description' => 'Просмотр логов',
			],
			[
				'name' => 'gii',
				'description' => 'Конструктор gii',
			],
			[
				'name' => 'dashboard',
				'description' => 'Панель администрирования',
			],
			[
				'name' => 'cache',
				'description' => 'Работа с кешем',
			],
			[
				'name' => 'alert',
				'description' => 'Сервисные сообщения',
			],
			[
				'name' => 'notification',
				'description' => 'Оповещения',
			],
			[
				'name' => 'user_view',
				'description' => 'Просмотр пользователей',
			],
			[
				'name' => 'user_create',
				'description' => 'Создание пользователей',
			],
			[
				'name' => 'user_update',
				'description' => 'Изменение пользователей',
			],
			[
				'name' => 'user_delete',
				'description' => 'Удаление пользователей',
			],
			[
				'name' => 'content_view',
				'description' => 'Просмотр контента',
			],
			[
				'name' => 'content_create',
				'description' => 'Создание контента',
			],
			[
				'name' => 'content_update',
				'description' => 'Изменение контента',
			],
			[
				'name' => 'content_delete',
				'description' => 'Удаление контента',
			],
		];
		$adminPermissions = [
			[
				'name' => 'dashboard',
				'description' => 'Панель администрирования',
			],
			[
				'name' => 'cache',
				'description' => 'Работа с кешем',
			],
			[
				'name' => 'alert',
				'description' => 'Сервисные сообщения',
			],
			[
				'name' => 'notification',
				'description' => 'Оповещения',
			],
			[
				'name' => 'user_view',
				'description' => 'Просмотр пользователей',
			],
			[
				'name' => 'user_create',
				'description' => 'Создание пользователей',
			],
			[
				'name' => 'user_update',
				'description' => 'Изменение пользователей',
			],
			[
				'name' => 'user_delete',
				'description' => 'Удаление пользователей',
			],
			[
				'name' => 'content_view',
				'description' => 'Просмотр контента',
			],
			[
				'name' => 'content_create',
				'description' => 'Создание контента',
			],
			[
				'name' => 'content_update',
				'description' => 'Изменение контента',
			],
			[
				'name' => 'content_delete',
				'description' => 'Удаление контента',
			],
		];
		$moderatorPermissions = [
			[
				'name' => 'dashboard',
				'description' => 'Панель администрирования',
			],
			[
				'name' => 'cache',
				'description' => 'Работа с кешем',
			],
			[
				'name' => 'alert',
				'description' => 'Сервисные сообщения',
			],
			[
				'name' => 'notification',
				'description' => 'Оповещения',
			],
			[
				'name' => 'user_view',
				'description' => 'Просмотр пользователей',
			],
			[
				'name' => 'content_view',
				'description' => 'Просмотр контента',
			],
			[
				'name' => 'content_create',
				'description' => 'Создание контента',
			],
			[
				'name' => 'content_update',
				'description' => 'Изменение контента',
			],
		];
		$userPermissions = [
			[
				'name' => 'notification',
				'description' => 'Оповещения',
			],
			[
				'name' => 'content_view',
				'description' => 'Просмотр контента',
			],
		];

		foreach ($roles as $role) {
			try {
				$existRole = $auth->getRole($role['name']);
				if (empty($existRole)) {
					$newRole = $auth->createRole($role['name']);
					$newRole->description = $role['description'];
					$auth->add($newRole);
				}
			} catch (Exception $e) {
				return print_r($e);
			}
		}

		foreach ($permissions as $permission) {
			try {
				$existPermission = $auth->getPermission($permission['name']);
				if (empty($existPermission)) {
					$newPermission = $auth->createPermission($permission['name']);
					$newPermission->description = $permission['description'];
					$auth->add($newPermission);
				}
			} catch (Exception $e) {
				return print_r($e);
			}
		}

		$existRoles = [
			$auth->getRole('superuser'),
			$auth->getRole('developer'),
			$auth->getRole('admin'),
			$auth->getRole('moderator'),
			$auth->getRole('user'),
		];

		if (!empty($existRoles)) {
			foreach ($existRoles as $role) {
				if ($role instanceof Role) {
					$permissionsName = "{$role->name}Permissions";
					if (isset(${$permissionsName})) {
						$auth->removeChildren($role);
						$rolePermissions = ${$permissionsName};
						foreach ($rolePermissions as $permission) {
							try {
								$existPermission = $auth->getPermission($permission['name']);
								if ($existPermission instanceof Permission) {
									if (!$auth->hasChild($role, $existPermission)) {
										$auth->addChild($role, $existPermission);
									}
								}
							} catch (Exception $e) {
								return print_r($e);
							}
						}
					}
				}
			}
		}
		return 'Done';
	}
}