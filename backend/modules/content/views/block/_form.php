<?php

/**
 * @var View $this
 * @var Content $model
 * @var ActiveForm $form
 */

use backend\modules\content\models\Content;
use backend\modules\content\models\ContentFile;
use common\utils\WidgetUtil;
use dosamigos\ckeditor\CKEditor;
use kartik\icons\FontAwesomeAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;
use backend\modules\content\models\ContentClosure;
use backend\modules\content\helpers\ContentHelper;

FontAwesomeAsset::register($this);

$types = Content::TYPES_LIST;
$positions = Content::TYPES_BLOCK_LIST;
$sectionsId = ArrayHelper::getColumn(Content::find()->select('id')->where([
	'active' => true,
	'type_id' => Content::TYPE_SECTION,
])->asArray()->all(), 'id');
$ancestors = ContentClosure::ancestorNodes($sectionsId);
$dropdownValues = ContentHelper::printSelect($ancestors, $model->id ?? false);

$changeFileSort = Url::to(['file/change-sort']);

$fileInputData = ContentHelper::initFileInput($model);
[$initialImagePreview, $initialImagePreviewConfig, $uploadImageExtraData] = $fileInputData[ContentFile::TYPE_IMAGE];
[$initialFilePreview, $initialFilePreviewConfig, $uploadFileExtraData] = $fileInputData[ContentFile::TYPE_FILE];
[$initialBackgroundPreview, $initialBackgroundPreviewConfig, $uploadBackgroundExtraData] = $fileInputData[ContentFile::TYPE_BACKGROUND];
?>

<div class="block-form">

	<?php $form = ActiveForm::begin([
		'id' => 'section-form',
		'options' => [
			'enctype' => 'multipart/form-data',
			'data' => [
				'pjax' => false,
			],
		],
	]); ?>

	<?= $form->field($model, 'type_id')->dropDownList($types, [
		'class' => 'form-control',
		'prompt' => 'Выберите',
	]) ?>

	<?= $form->field($model, 'position_id')->dropDownList($positions, [
		'class' => 'form-control',
		'prompt' => 'Выберите',
	]) ?>

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
						->widget(CKEditor::class, WidgetUtil::ckeditorOptions())
						->label(false);
				} catch (Exception $e) {
					print_r($e);
				} ?>
			</div>
		</div>
	</div>

	<div class="row py-1">
		<div class="col-12 py-1">
			<a class="btn btn-light"
			   data-toggle="collapse"
			   data-bs-toggle="button"
			   href="#files"
			   role="button"
			   aria-pressed="true"
			   aria-expanded="false"
			   aria-controls="files">
				Файлы
			</a>
		</div>
		<div class="col-12">
			<div class="collapse multi-collapse" id="files">
				<?= ContentHelper::fileInput($form, $model, 'imageInput', [
					'options' => ['multiple' => true],
					'pluginOptions' => [
						'initialPreview' => $initialImagePreview,
						'initialPreviewConfig' => $initialImagePreviewConfig,
						'uploadExtraData' => $uploadImageExtraData,
					],
				]); ?>

				<?= ContentHelper::fileInput($form, $model, 'backgroundInput', [
					'pluginOptions' => [
						'initialPreview' => $initialBackgroundPreview,
						'initialPreviewConfig' => $initialBackgroundPreviewConfig,
						'uploadExtraData' => $uploadBackgroundExtraData,
					],
				]); ?>
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
<script>
    function fileSorted(event, params) {
        let stack = params.stack.map(function (item) {
            return item.key
        })
        $.post("<?= $changeFileSort ?>", {
            "data": JSON.stringify(stack)
        });
    }
</script>