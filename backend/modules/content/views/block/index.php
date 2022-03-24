<?php

/**
 * @var View $this
 * @var string $title
 * @var ContentSearch $searchModel
 * @var ActiveDataProvider $dataProvider
 */

use backend\modules\content\helpers\ContentHelper;
use backend\modules\content\models\ContentClosure;
use backend\modules\content\models\Content;
use backend\modules\content\models\ContentSearch;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = 'Блоки';
$this->params['breadcrumbs'][] = ['label' => 'Контент'];
$this->params['breadcrumbs'][] = $this->title;

$sectionsId = ArrayHelper::getColumn(Content::find()->select('id')->where([
	'active' => true,
	'type_id' => Content::TYPE_SECTION,
])->asArray()->all(), 'id');
$ancestors = ContentClosure::ancestorNodes($sectionsId);
$dropdownValues = ContentHelper::printSelect($ancestors);
?>
<div class="block-index">
	<h1><?= Html::encode($this->title) ?></h1>

	<p><?= Html::a('Создать', ['create'], ['class' => 'btn btn-success']) ?></p>

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

				[
					'attribute' => 'id',
					'content' => function (Content $content) {
						return Html::a(
							$content->id,
							Url::toRoute(['section/update', 'id' => $content->id])
						);
					},
				],
				[
					'attribute' => 'type_id',
					'content' => function (Content $content) {
						return $content->getTypeText();
					},
					'filter' => Html::activeDropDownList($searchModel, 'type_id', Content::TYPES_BLOCK_LIST, [
						'class' => 'form-control',
						'prompt' => 'Выберите',
					]),
					'visible' => !in_array($this->context->action->id, ['elements', 'sections']),
				],
				[
					'attribute' => 'position_id',
					'content' => function (Content $content) {
						return $content->getPositionText();
					},
					'filter' => Html::activeDropDownList($searchModel, 'position_id', Content::POSITIONS_LIST, [
						'class' => 'form-control',
						'prompt' => 'Выберите',
					]),
				],
				[
					'attribute' => 'parent_id',
					'content' => function (Content $content) {
						if (is_null($content->parentId)) {
							return 'Верхний уровень';
						} elseif (!empty($content->parent)) {
							return $content->parent->name;
						}
					},
					'filter' => Html::activeDropDownList($searchModel, 'parent_id', $dropdownValues, [
						'class' => 'form-control',
						'prompt' => 'Выберите',
					]),
				],
				'active:boolean',
				'sort',
				'name',
				'code',

				[
					'class' => 'yii\grid\ActionColumn',
					'template' => '{view} {update} {delete}',
					'urlCreator' => function ($action, Content $model, $key, $index, $column) {
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
