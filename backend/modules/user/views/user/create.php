<?php

/**
 * @var View $this
 * @var User $model
 * @var array $groups
 */

use yii\web\View;
use yii\helpers\Html;
use backend\modules\user\models\User;

$this->title = 'Создать пользователя';
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-lg-5">

            <?= $this->render('_form', [
                'model' => $model,
                'groups' => $groups,
            ]) ?>

        </div>
    </div>

</div>