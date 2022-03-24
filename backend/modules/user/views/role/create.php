<?php

/**
 * @var View $this
 * @var UserRoleForm $model
 * @var array $permissions
 */

use kartik\select2\Select2;
use yii\web\View;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\modules\user\forms\UserRoleForm;

$this->title = 'Создать роль';
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['user/index']];
$this->params['breadcrumbs'][] = ['label' => 'Роли', 'url' => ['role/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-signup">

	<h1><?= Html::encode($this->title) ?></h1>

	<div class="row">
		<div class="col-lg-5">

			<?php $form = ActiveForm::begin(); ?>

			<?= $form->field($model, 'name') ?>

			<?= $form->field($model, 'description') ?>

			<?php try {
				echo $form->field($model, 'permissions')->widget(Select2::class, [
					'data' => $permissions,
					'theme' => Select2::THEME_DEFAULT,
					'language' => 'ru',
					'options' => [
						'multiple' => true,
						'placeholder' => 'Выберите',
					],
					'pluginOptions' => [
						'allowClear' => true,
					],
				]);
			} catch (Exception $e) {
				print_r($e);
			} ?>

			<div class="form-group">
				<?= Html::submitButton('Создать', ['class' => 'btn btn-primary', 'name' => 'default-button']) ?>
				<?= Html::a('Отмена', ['role/index'], ['class' => 'btn btn-default']) ?>
			</div>

			<?php ActiveForm::end(); ?>
		</div>
	</div>
</div>