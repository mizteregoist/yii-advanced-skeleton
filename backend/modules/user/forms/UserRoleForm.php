<?php

namespace backend\modules\user\forms;

use Exception;
use Yii;
use yii\base\Model;
use yii\rbac\Role;

/**
 * Class UserRoleForm
 * @package backend\models
 *
 * @property string $name
 * @property string $description
 */
class UserRoleForm extends Model
{
	public $name;
	public $description;
	public $permissions;

	/**
	 * @inheritdoc
	 */
	public function rules(): array
	{
		return [
			['name', 'unique', 'targetClass' => '\backend\modules\user\models\AuthItem'],

			[['name', 'description'], 'filter', 'filter' => 'trim'],
			[['name', 'description'], 'string', 'min' => 3, 'max' => 50],
			[['name'], 'match', 'pattern' => '/^[a-z_]*$/i'],
			[['name', 'description'], 'required'],
			[['permissions'], 'safe'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels(): array
	{
		return [
			'name' => 'Код',
			'description' => 'Название',
			'permissions' => 'Разрешения',
		];
	}

	/**
	 * @throws Exception
	 */
	public function createRole()
	{
		$auth = Yii::$app->authManager;
		$newRole = $auth->createRole($this->name);
		$newRole->description = $this->description;
		$auth->add($newRole);
	}

	/**
	 * @param Role $role
	 * @throws Exception
	 */
	public function updateRole(Role $role)
	{
		$roleName = $role->name;
		$role->name = $this->name;
		$role->description = $this->description;
		Yii::$app->authManager->update($roleName, $role);
	}

	/**
	 * @return array
	 */
	public function getPermissions(): array
	{
		return array_keys(Yii::$app->authManager->getPermissionsByRole($this->name));
	}

	/**
	 * @param array $permissions
	 * @throws \yii\base\Exception
	 */
	public function setPermissions(array $permissions = [])
	{
		$auth = Yii::$app->authManager;
		$role = $auth->getRole($this->name);
		$auth->removeChildren($role);
		foreach ($permissions as $permission) {
			$auth->addChild($role, $auth->getPermission($permission));
		}
	}
}