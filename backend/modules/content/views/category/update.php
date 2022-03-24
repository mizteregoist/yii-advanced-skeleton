<?php

/**
 * @var View $this
 * @var ContentCategory $model
 */

use yii\helpers\Html;
use yii\web\View;
use backend\modules\content\models\ContentCategory;

$this->title = 'Изменить: ' . $model->name;
$this->params['breadcrumbs'][] = 'Контент';
$this->params['breadcrumbs'][] = ['label' => 'Категории', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="category-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
