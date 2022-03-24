<?php

/**
 * @var View $this
 * @var ContentCategory $model
 */

use yii\web\View;
use yii\web\YiiAsset;
use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\modules\content\models\ContentCategory;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Контент'];
$this->params['breadcrumbs'][] = ['label' => 'Категории', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>
<div class="category-view">

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
				'sort',
				'active:boolean',
				'name',
				'code',
				'created_at',
				'updated_at',
			],
		]);
	} catch (Exception $e) {
		print_r($e);
	} ?>

</div>
