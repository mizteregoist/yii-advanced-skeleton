<?php

/**
 * @var View $this
 * @var RawConnection $model
 * @var ActiveForm $form
 */

use yii\db\Connection;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use backend\modules\backup\models\RawConnection;
use kartik\icons\FontAwesomeAsset;

FontAwesomeAsset::register($this);

$commands = array_keys((new Connection())->commandMap);
$dropdown = [];
foreach ($commands as $command) {
	$dropdown[$command] = $command;
}
?>

<div class="local-form">

	<?php $form = ActiveForm::begin([
		'id' => 'local-form',
		'options' => [
			'data' => [
				'pjax' => false,
			],
		],
	]); ?>

	<?= $form->field($model, 'code')->textInput() ?>

	<?= $form->field($model, 'type')->dropDownList($dropdown, [
		'class' => 'form-control',
		'prompt' => 'Выберите',
	]) ?>

	<?= $form->field($model, 'host')->textInput() ?>

	<?= $form->field($model, 'port')->input('number') ?>

	<?= $form->field($model, 'name')->textInput() ?>

	<?= $form->field($model, 'user')->textInput() ?>

	<?= $form->field($model, 'password')->textInput() ?>

	<?= $form->field($model, 'charset')->textInput() ?>

	<div class="form-group">
		<?= Html::submitButton('Сохранить', [
			'class' => 'btn btn-success',
		]) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>