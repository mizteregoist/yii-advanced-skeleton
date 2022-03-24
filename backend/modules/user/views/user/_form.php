<?php

/**
 * @var View $this
 * @var User $model
 * @var ActiveForm $form
 * @var array $groups
 */


use kartik\select2\Select2;
use yii\web\View;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\modules\user\models\User;

?>

<div class="user-form">

	<?php $form = ActiveForm::begin(); ?>

	<?= $form->field($model, 'status')->dropDownList(User::STATUS_TEXT) ?>

	<?php try {
		echo $form->field($model, 'group')->widget(Select2::class, [
			'data' => $groups,
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

	<?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Иван']) ?>

	<?= $form->field($model, 'lastname')->textInput(['maxlength' => true, 'placeholder' => 'Иванов']) ?>

	<?= $form->field($model, 'patronymic')->textInput(['maxlength' => true, 'placeholder' => 'Иванович']) ?>

	<?= $form->field($model, 'phone')->textInput(['maxlength' => true, 'placeholder' => '+7 (999) 999-99-99']) ?>

	<?= $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => 'example@webapp.local']) ?>

	<?= $form->field($model, 'password')->passwordInput() ?>

	<div class="form-group">
		<?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>
