<?php

/**
 * @var View $this
 * @var ContentCategorySearch $model
 * @var ActiveForm $form
 */


use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use backend\modules\content\models\ContentCategorySearch;
?>

<div class="category-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'sort') ?>

    <?= $form->field($model, 'active')->checkbox() ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'code') ?>

    <div class="form-group">
        <?= Html::submitButton('Поиск', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Сброс', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
