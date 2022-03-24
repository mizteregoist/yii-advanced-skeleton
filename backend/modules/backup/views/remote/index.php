<?php

/**
 * @var View $this
 * @var ArrayDataProvider $dataProvider
 */

use yii\web\View;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use backend\modules\backup\models\RawConnection;

$this->title = 'Удаленные БД';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="remote-index">

	<h1><?= Html::encode($this->title) ?></h1>

	<p><?= Html::a('Добавить БД', ['create'], ['class' => 'btn btn-success']) ?></p>

	<?php Pjax::begin([
		'enablePushState' => true,
		'enableReplaceState' => false,
	]); ?>
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

				'code',
				'type',
				'host',
				'port',
				'name',
				'user',
				'charset',

				[
					'attribute' => 'status',
					'content' => function (RawConnection $model) {
						$status = $model->getStatus();
						return "[{$status['code']}] {$status['message']}";
					},
				],

				[
					'class' => 'yii\grid\ActionColumn',
					'template' => '{update} {delete}',
					'urlCreator' => function ($action, RawConnection $model, $key, $index, $column) {
						return Url::toRoute([$action, 'code' => $model->code]);
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
