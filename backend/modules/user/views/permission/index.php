<?php

/**
 * @var View $this
 * @var array $filter
 * @var UserSearch $searchModel
 * @var ActiveDataProvider $dataProvider
 */

use rmrevin\yii\fontawesome\FAS;
use yii\rbac\Permission;
use yii\web\View;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\helpers\Html;
use backend\modules\user\models\UserSearch;

$this->title = 'Разрешения';
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['user/index']];
$this->params['breadcrumbs'][] = ['label' => 'Роли', 'url' => ['role/index']];

if (!empty($filter)) {
	$this->params['breadcrumbs'][] = [
		'label' => Yii::$app->authManager->getRole($filter)->description,
		'url' => ['role/update', 'code' => Yii::$app->authManager->getRole($filter)->name],
	];
}
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="permission-index">

	<h1><?= Html::encode($this->title) ?></h1>

	<p>
		<?php if (empty($filter)) { ?>
			<?= Html::a('Создать разрешение', ['permission/create'], ['class' => 'btn btn-success']) ?>
		<?php } else { ?>
			<?= Html::a('Изменить', ['role/update', 'code' => $filter], ['class' => 'btn btn-primary']) ?>
			<?= Html::a('Все', ['permission/index'], ['class' => 'btn btn-default']) ?>
		<?php } ?>
	</p>

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
					'urlCreator' => function ($action, Permission $model, $key, $index, $column) {
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
	} ?>

</div>