<?php

/**
 * @var View $this
 * @var RawConnection $model
 */

use yii\web\View;
use yii\helpers\Html;
use backend\modules\backup\models\RawConnection;

$this->title = "Изменить {$model->code}";
$this->params['breadcrumbs'][] = ['label' => 'Локальные БД', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="local-update">

	<h1><?= Html::encode($this->title) ?></h1>

	<?= $this->render('_form', [
		'model' => $model,
	]) ?>

</div>
