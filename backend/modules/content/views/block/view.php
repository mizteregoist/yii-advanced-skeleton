<?php

/**
 * @var View $this
 * @var Content $model
 */

use backend\modules\content\models\Content;
use yii\helpers\Html;
use yii\web\View;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Контент'];
$this->params['breadcrumbs'][] = ['label' => 'Блоки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>
<div class="block-view">
	<h1><?= Html::encode($this->title) ?></h1>
	<p>
		<?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
		<?= Html::a('Удалить', ['delete', 'id' => $model->id], [
			'class' => 'btn btn-danger',
			'data' => [
				'confirm' => 'Are you sure you want to delete this item?',
				'method' => 'post',
			],
		]) ?>
	</p>

	<?php try {
		echo DetailView::widget([
			'model' => $model,
			'attributes' => [
				'id',
				[
					'attribute' => 'type_id',
					'value' => function (Content $content) {
						return $content->getTypeText();
					},
				],
				[
					'attribute' => 'position_id',
					'value' => function (Content $content) {
						return $content->getPositionText();
					},
				],
				[
					'attribute' => 'parent_id',
					'value' => function (Content $content) {
						if (is_null($content->parent_id)) {
							return 'Верхний уровень';
						} elseif (!empty($content->parent)) {
							return $content->parent->name;
						}
					},
				],
				'active:boolean',
				'sort',
				'name',
				'code',
				'title',
				'description',
				'content:html',
				'created_at',
				'updated_at',
			],
		]);
	} catch (Exception $e) {
		print_r($e);
	} ?>
</div>
