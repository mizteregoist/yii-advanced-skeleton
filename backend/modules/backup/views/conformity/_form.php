<?php

/**
 * @var View $this
 * @var Conformity $model
 * @var ActiveForm $form
 */

use yii\bootstrap4\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use backend\modules\backup\models\Conformity;
use backend\modules\backup\models\RawConnection;
use kartik\icons\FontAwesomeAsset;

FontAwesomeAsset::register($this);

$remotes = RawConnection::getRemotes(true);
$locals = RawConnection::getLocals(true);
?>

<div class="conformity-form">

	<?php $form = ActiveForm::begin([
		'id' => 'conformity-form',
		'options' => [
			'data' => [
				'pjax' => false,
			],
		],
	]); ?>

	<?= $form->field($model, 'remote')->dropDownList($remotes, [
		'class' => 'form-control',
		'prompt' => 'Выберите',
	]) ?>

	<?= $form->field($model, 'local')->dropDownList($locals, [
		'class' => 'form-control',
		'prompt' => 'Выберите',
	]) ?>

	<div class="form-group">
		<?= Html::submitButton('Сохранить', [
			'class' => 'btn btn-success',
		]) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>