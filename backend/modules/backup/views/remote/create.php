<?php

/**
 * @var View $this
 * @var RawConnection $model
 */

use yii\web\View;
use yii\helpers\Html;
use backend\modules\backup\models\RawConnection;

$this->title = 'Добавление';
$this->params['breadcrumbs'][] = ['label' => 'Удаленные БД', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="remote-create">

	<h1><?= Html::encode($this->title) ?></h1>

	<?= $this->render('_form', [
		'model' => $model,
	]) ?>

</div>
