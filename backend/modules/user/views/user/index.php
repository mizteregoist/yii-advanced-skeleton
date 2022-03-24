<?php

/**
 * @var View $this
 * @var UserSearch $searchModel
 * @var ActiveDataProvider $dataProvider
 */

use yii\web\View;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use backend\modules\user\models\User;
use backend\modules\user\models\UserSearch;

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;

$roles = ArrayHelper::map(Yii::$app->authManager->getRoles(), 'name', 'description');
?>

<div class="user-index">

	<h1><?= Html::encode($this->title) ?></h1>

	<p>
		<?= Html::a('Создать пользователя', ['create'], ['class' => 'btn btn-success']) ?>
		<?php if (!empty(Yii::$app->request->get())) { ?>
			<?= Html::a('Сбросить фильтр', ['index'], ['class' => 'btn btn-info']) ?>
		<?php } ?>
	</p>

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
				[
					'attribute' => 'status',
					'filter' => User::STATUS_TEXT,
					'content' => function ($data) {
						/** @var User $data */
						return $data->statusText;
					},
				],
				'name',
				'lastname',
				'email:email',
				[
					'attribute' => 'group',
					'filter' => $roles,
					'content' => function (User $data) {
						$str = '<ul>';
						foreach ($data->getGroup() as $name => $role) {
							$str .= Html::tag('li', Html::a($name, Url::toRoute(['role/view', 'code' => $role])));
						}
						$str .= '</ul>';
						return $str;
					},
				],
				'created_at:datetime',
				[
					'class' => 'yii\grid\ActionColumn',
					'template' => '{view} {update} {delete}',
					'urlCreator' => function ($action, User $model, $key, $index, $column) {
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
