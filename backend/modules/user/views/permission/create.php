<?php

/**
 * @var View $this
 * @var UserRoleForm $model
 */

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\modules\user\forms\UserRoleForm;

$this->title = 'Создать разрешение';
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['user/index']];
$this->params['breadcrumbs'][] = ['label' => 'Роли', 'url' => ['role/index']];
$this->params['breadcrumbs'][] = ['label' => 'Разрешения', 'url' => ['permission/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="permission-create">

	<h1><?= Html::encode($this->title) ?></h1>

	<div class="row">
		<div class="col-lg-5">

			<?php $form = ActiveForm::begin(); ?>

			<?= $form->field($model, 'name') ?>

			<?= $form->field($model, 'description') ?>

			<div class="form-group">
				<?= Html::submitButton('Создать', ['class' => 'btn btn-primary', 'name' => 'default-button']) ?>
				<?= Html::a('Отмена', ['permission/index'], ['class' => 'btn btn-default']) ?>
			</div>

			<?php ActiveForm::end(); ?>
		</div>
	</div>
</div>