<?php

/**
 * @var View $this
 * @var Content $model
 * @var ActiveForm $form
 */

use backend\modules\content\models\Content;
use dosamigos\ckeditor\CKEditor;
use kartik\icons\FontAwesomeAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use backend\modules\content\models\ContentClosure;
use backend\modules\content\helpers\ContentHelper;

FontAwesomeAsset::register($this);

$sectionsId = ArrayHelper::getColumn(Content::find()->select('id')->section()->active()->asArray()->all(), 'id');
$ancestors = ContentClosure::ancestorNodes($sectionsId);
$dropdownValues = ContentHelper::printSelect($ancestors, $model->id ?? false);
?>

<div class="section-form">

	<?php $form = ActiveForm::begin([
		'id' => 'section-form',
		'options' => [
			'enctype' => 'multipart/form-data',
			'data' => [
				'pjax' => false,
			],
		],
	]); ?>

	<?= Html::activeHiddenInput($model, 'type_id', ['value' => Content::TYPE_SECTION]) ?>

	<?= Html::activeHiddenInput($model, 'position_id', ['value' => Content::POSITION_DEFAULT]) ?>

	<?= $form->field($model, 'parent_id')->dropDownList($dropdownValues, [
		'class' => 'form-control',
		'prompt' => 'Выберите',
		'value' => $model->parentId,
	]) ?>

	<?= $form->field($model, 'sort')->input('number', ['maxlength' => true]) ?>

	<?= $form->field($model, 'active')->checkbox(['checked' => true]) ?>

	<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

	<?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

	<?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

	<?= $form->field($model, 'description')->textarea(['maxlength' => true]) ?>

	<div class="row py-1">
		<div class="col-12 py-1">
			<a class="btn btn-light"
			   data-toggle="collapse"
			   data-bs-toggle="button"
			   href="#content"
			   role="button"
			   aria-pressed="true"
			   aria-expanded="false"
			   aria-controls="content">
				<?= $model->getAttributeLabel('content') ?>
			</a>
		</div>
		<div class="col-12">
			<div class="collapse multi-collapse" id="content">
				<?php try {
					echo $form->field($model, 'content')
						->widget(CKEditor::class, ContentHelper::$editorOptions)
						->label(false);
				} catch (Exception $e) {
					print_r($e);
				} ?>
			</div>
		</div>
	</div>

	<div class="form-group">
		<?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', [
			'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary',
		]) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>