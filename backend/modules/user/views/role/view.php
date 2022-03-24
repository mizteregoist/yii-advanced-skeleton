<?php

/**
 * @var View $this
 * @var Role $model
 */

use yii\web\View;
use yii\web\YiiAsset;
use yii\rbac\Role;
use yii\helpers\Html;
use yii\widgets\DetailView;


$this->title = $model->description;
$this->params['breadcrumbs'][] = ['label' => 'Роли', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>

<div class="user-view">

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
					'label' => 'Создан',
					'attribute' => 'createdAt',
					'value' => function ($data) {
						return date('d.m.Y H:i:s', $data->createdAt);
					},
				],
				[
					'label' => 'Изменен',
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
