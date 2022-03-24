<?php

/**
 * @var View $this
 * @var UserSearch $searchModel
 * @var ArrayDataProvider $dataProvider
 */

use rmrevin\yii\fontawesome\FAS;
use yii\rbac\Role;
use yii\web\View;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Url;
use yii\helpers\Html;
use backend\modules\user\models\User;
use backend\modules\user\models\UserSearch;

$this->title = 'Роли';
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['user/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="role-index">

	<h1><?= Html::encode($this->title) ?></h1>

	<p><?= Html::a('Создать роль', ['create'], ['class' => 'btn btn-success']) ?></p>

	<?php try {
		echo GridView::widget([
			'dataProvider' => $dataProvider,
			'tableOptions' => [
				'class' => 'table table-striped table-hover table-sm',
			],
			'options' => [
				'class' => 'table-responsive',
			],
			'columns' => [
				['class' => 'yii\grid\SerialColumn'],
				[
					'label' => 'Код',
					'attribute' => 'name',
				],
				[
					'label' => 'Название',
					'attribute' => 'description',
				],
				[
					'label' => 'Разрешений',
					'content' => function ($data) {
						$permissions = Yii::$app->authManager->getPermissionsByRole($data->name);
						return Html::a(count($permissions), Url::toRoute(['permission/index', 'filter' => $data->name]));
					},
				],
				[
					'label' => 'Пользователей',
					'content' => function ($data) {
						$users = Yii::$app->authManager->getUserIdsByRole($data->name);
						foreach ($users as $user) {
							if (!User::find()->where(['id' => $user])->one()) {
								Yii::$app->authManager->revokeAll($user);
							}
						}
						return Html::a(count($users), Url::toRoute(['user/index', 'UserSearch[group]' => $data->name]));
					},
				],
				[
					'label' => 'Создан',
					'content' => function ($data) {
						return date('d.m.Y H:i:s', $data->createdAt);
					},
				],
				[
					'label' => 'Обновлен',
					'content' => function ($data) {
						return date('d.m.Y H:i:s', $data->updatedAt);
					},
				],
				[
					'class' => 'yii\grid\ActionColumn',
					'template' => '{view} {update} {delete}',
					'urlCreator' => function ($action, Role $model, $key, $index, $column) {
						return Url::toRoute([$action, 'code' => $model->name]);
					},
					'headerOptions' => [
						'class' => 'col-1',
					],
					'contentOptions' => [
						'class' => 'd-flex justify-content-around align-items-center',
					],
				],
			],
		]);
	} catch (Exception $e) {
		print_r($e);
	}
	?>

</div>