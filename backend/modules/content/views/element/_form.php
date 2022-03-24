<?php

/**
 * @var View $this
 * @var Content $model
 * @var ActiveForm $form
 */

use backend\modules\content\models\Content;
use backend\modules\content\models\ContentCategory;
use backend\modules\content\models\ContentFile;
use dosamigos\ckeditor\CKEditor;
use kartik\icons\FontAwesomeAsset;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;
use backend\modules\content\models\ContentClosure;
use backend\modules\content\helpers\ContentHelper;

// Trick for select2 values
if (!empty($model->getCategoryId())) {
	$model->category_id = $model->getCategoryId();
}

FontAwesomeAsset::register($this);

$sectionsId = ArrayHelper::getColumn(Content::find()->select('id')->section()->active()->asArray()->all(), 'id');
$ancestors = ContentClosure::ancestorNodes($sectionsId);
$dropdownValues = ContentHelper::printSelect($ancestors, $model->id ?? false);
$categories = ArrayHelper::map(ContentCategory::find()
	->where(['active' => true])
	->select(['id', 'name'])
	->orderBy(['sort' => SORT_DESC])
	->asArray()
	->all(), 'id', 'name');

$changeFileSort = Url::to(['file/change-sort']);

$fileInputData = ContentHelper::initFileInput($model);
[$initialImagePreview, $initialImagePreviewConfig, $uploadImageExtraData] = $fileInputData[ContentFile::TYPE_IMAGE];
[$initialFilePreview, $initialFilePreviewConfig, $uploadFileExtraData] = $fileInputData[ContentFile::TYPE_FILE];
[$initialBackgroundPreview, $initialBackgroundPreviewConfig, $uploadBackgroundExtraData] = $fileInputData[ContentFile::TYPE_BACKGROUND];
?>

<div class="element-form">

	<?php $form = ActiveForm::begin([
		'id' => 'element-form',
		'options' => [
			'enctype' => 'multipart/form-data',
			'data' => [
				'pjax' => false,
			],
		],
	]); ?>

	<?= Html::activeHiddenInput($model, 'type_id', ['value' => Content::TYPE_ELEMENT]) ?>

	<?= Html::activeHiddenInput($model, 'position_id', ['value' => Content::POSITION_DEFAULT]) ?>

	<?= $form->field($model, 'parent_id')->dropDownList($dropdownValues, [
		'class' => 'form-control',
		'prompt' => 'Выберите',
		'value' => $model->parentId,
	]) ?>

	<?php try {
		echo $form->field($model, 'category_id')->widget(Select2::class, [
			'data' => $categories,
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

				<?= ContentHelper::fileInput($form, $model, 'fileInput', [
					'options' => [
						'multiple' => true,
						'accept' => '*',
					],
					'pluginOptions' => [
						'initialPreview' => $initialFilePreview,
						'initialPreviewConfig' => $initialFilePreviewConfig,
						'uploadExtraData' => $uploadFileExtraData,
						'allowedFileExtensions' => ['pdf', 'doc', 'docx'],
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