<?php

/**
 * @var View $this
 * @var Permission $model
 */

use yii\web\View;
use yii\web\YiiAsset;
use yii\rbac\Permission;
use yii\helpers\Html;
use yii\widgets\DetailView;


$this->title = $model->description;
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['user/index']];
$this->params['breadcrumbs'][] = ['label' => 'Роли', 'url' => ['role/index']];
$this->params['breadcrumbs'][] = ['label' => 'Разрешения', 'url' => ['permission/index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>

<div class="permission-view">

	<h1><?= Html::encode($this->title) ?></h1>

	<p>
		<?= Html::a('Изменить', ['update', 'code' => $model->name], [
			'class' => 'btn btn-primary',
		]) ?>
		<?= Html::a('Удалить', ['delete', 'code' => $model->name], [
			'class' => 'btn btn-danger',
			'data' => [
				'confirm' => 'Вы точно хотите удалить запись?',
				'method' => 'post',
			],
		]) ?>
	</p>

	<?php try {
		echo DetailView::widget([
			'model' => $model,
			'attributes' => [
				[
					'label' => 'Тип',
					'attribute' => 'type',
				],
				[
					'label' => 'Код',
					'attribute' => 'name',
				],
				[
					'label' => 'Название',
					'attribute' => 'description',
				],
				[
					'label' => 'Создано',
					'attribute' => 'createdAt',
					'value' => function ($data) {
						return date('d.m.Y H:i:s', $data->createdAt);
					},
				],
				[
					'label' => 'Изменено',
					'attribute' => 'updatedAt',
					'value' => function ($data) {
						return date('d.m.Y H:i:s', $data->updatedAt);
					},
				],
			],
		]);
	} catch (Exception $e) {
		print_r($e);
	} ?>

</div>
