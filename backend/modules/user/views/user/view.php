<?php

/**
 * @var View $this
 * @var User $model
 */

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;
use backend\modules\user\models\User;


$this->title = $model->fullName;
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>

<div class="user-view">

	<h1><?= Html::encode($this->title) ?></h1>

	<p>
		<?= Html::a('Изменить', ['user/update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
		<?= Html::a('Удалить', ['user/delete', 'id' => $model->id], [
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
				'id',
				[
					'attribute' => 'status',
					'value' => $model->statusText,
				],
				'name',
				'lastname',
				'patronymic',
				'username',
				'email:email',
				'email_confirmed',
				'phone',
				'phone_confirmed',
				[
					'attribute' => 'group',
					'format' => 'raw',
					'value' => function (User $model) {
						$str = '<ul>';
						foreach ($model->getGroup() as $name => $role) {
							$str .= Html::tag('li', Html::a($name, Url::toRoute(['role/view', 'code' => $role])));
						}
						$str .= '</ul>';
						return $str;
					},
				],
				'created_at:datetime',
				'updated_at:datetime',
				'last_login:datetime',
			],
		]);
	} catch (Exception $e) {
		print_r($e);
	} ?>

</div>
