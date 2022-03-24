<?php

/**
 * @var View $this
 * @var ArrayDataProvider $dataProvider
 * @var string|null $message
 */

use yii\web\View;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use backend\modules\backup\models\Conformity;

$this->title = 'Соответствие БД';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="conformity-index">

	<h1><?= Html::encode($this->title) ?></h1>

	<p><?= Html::a('Создать соответствие', ['create'], ['class' => 'btn btn-success']) ?></p>

	<?php Pjax::begin([
		'enablePushState' => true,
		'enableReplaceState' => false,
	]); ?>
	<?php if (!empty($message)) { ?>
		<div id="sync-status" class="alert-success alert alert-dismissible" role="alert">
			<p>Откройте директорию с localserver в терминале, и выполните там следующие комманды по очереди:</p>
			<?= $message ?>
			<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span></button>
		</div>
	<?php } ?>
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

				'remote',
				'local',

				[
					'attribute' => 'actions',
					'content' => function (Conformity $model) {
						$uri = Url::toRoute(['conformity/sync', 'code' => $model->remote]);
						return Html::a('Синхронизировать', $uri, [
							'data' => [
								'method' => 'post',
								'pjax' => 1,
							],
						]);
					},
				],

				[
					'class' => 'yii\grid\ActionColumn',
					'template' => '{update} {delete}',
					'urlCreator' => function ($action, Conformity $model, $key, $index, $column) {
						return Url::toRoute([$action, 'code' => $model->remote]);
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
	<?php Pjax::end(); ?>

</div>
