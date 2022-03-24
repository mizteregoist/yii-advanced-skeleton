<?php

/**
 * @var View $this
 * @var ContentCategory $model
 */

use backend\modules\content\models\ContentCategory;
use yii\helpers\Html;
use yii\web\View;

$this->title = 'Создать категорию';
$this->params['breadcrumbs'][] = 'Контент';
$this->params['breadcrumbs'][] = ['label' => 'Категории', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Создание';
?>
<div class="category-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
