<?php

/**
 * @var View $this
 * @var ContentCategorySearch $searchModel
 * @var ActiveDataProvider $dataProvider
 */

use backend\modules\content\models\ContentCategory;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\web\View;
use backend\modules\content\models\ContentCategorySearch;

$this->title = 'Категории';
$this->params['breadcrumbs'][] = 'Контент';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-index">

	<h1><?= Html::encode($this->title) ?></h1>

	<p><?= Html::a('Создать категорию', ['create'], ['class' => 'btn btn-success']) ?></p>

	<?php //= $this->render('_search', ['model' => $searchModel]); ?>

	<?php try {
		echo GridView::widget([
			'dataProvider' => $dataProvider,
			'filterModel' => $searchModel,
			'tableOptions' => [
				'class' => 'table table-striped table-hover table-sm',
			],
			'options' => [
				'class' => 'table-responsive',
			],
			'columns' => [
				['class' => 'yii\grid\SerialColumn'],

				'id',
				'sort',
				'active:boolean',
				'name',
				'code',

				[
					'class' => 'yii\grid\ActionColumn',
					'template' => '{view} {update} {delete}',
					'urlCreator' => function ($action, ContentCategory $model, $key, $index, $column) {
						return Url::toRoute([$action, 'id' => $model->id]);
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
