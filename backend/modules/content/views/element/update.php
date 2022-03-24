<?php

/**
 * @var View $this
 * @var Content $model
 */

use backend\modules\content\models\Content;
use yii\helpers\Html;
use yii\web\View;

$this->title = 'Изменить: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Контент'];
$this->params['breadcrumbs'][] = ['label' => 'Элементы', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="element-update">

	<h1><?= Html::encode($this->title) ?></h1>

	<?= $this->render('_form', [
		'model' => $model,
	]) ?>

</div>
