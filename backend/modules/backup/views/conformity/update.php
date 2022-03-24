<?php

/**
 * @var View $this
 * @var Conformity $model
 */

use yii\web\View;
use yii\helpers\Html;
use backend\modules\backup\models\Conformity;

$this->title = 'Изменение соответствия';
$this->params['breadcrumbs'][] = ['label' => 'Соответствие БД', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="conformity-update">

	<h1><?= Html::encode($this->title) ?></h1>

	<?= $this->render('_form', [
		'model' => $model,
	]) ?>

</div>
