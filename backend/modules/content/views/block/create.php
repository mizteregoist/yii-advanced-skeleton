<?php

/**
 * @var View $this
 * @var Content $model
 */

use backend\modules\content\models\Content;
use yii\helpers\Html;
use yii\web\View;

$this->title = 'Создать';
$this->params['breadcrumbs'][] = ['label' => 'Контент'];
$this->params['breadcrumbs'][] = ['label' => 'Блоки', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Создание';
?>
<div class="block-create">

	<h1><?= Html::encode($this->title) ?></h1>

	<?= $this->render('_form', [
		'model' => $model,
	]) ?>

</div>
