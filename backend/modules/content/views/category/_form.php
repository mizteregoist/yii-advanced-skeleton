<?php

/**
 * @var View $this
 * @var ContentCategory $model
 * @var ActiveForm $form
 */

use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use backend\modules\content\models\ContentCategory;

?>

<div class="category-form">

	<?php $form = ActiveForm::begin([
		'id' => 'category-form',
		'options' => [
			'data' => [
				'pjax' => false,
			],
		],
	]); ?>

	<?= $form->field($model, 'sort')->input('number', ['maxlength' => true]) ?>

	<?= $form->field($model, 'active')->checkbox(['checked' => true]) ?>

	<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

	<?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

	<div class="form-group">
		<?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', [
			'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary',
		]) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>
