<?php

/**
 * @var View $this
 * @var string $content
 */

use backend\assets\AppAsset;
use backend\helpers\AdminPanel;
use yii\helpers\Html;
use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\bootstrap4\Breadcrumbs;
use yii\helpers\Url;
use yii\web\View;
use common\widgets\Alert;

AppAsset::register($this);
Url::remember();
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
	<meta charset="<?= Yii::$app->charset ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php $this->registerCsrfMetaTags() ?>
	<title><?= Html::encode($this->title) ?></title>
	<?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?php
NavBar::begin([
	'brandLabel' => Yii::$app->name,
	'brandUrl' => Yii::$app->homeUrl,
	'options' => [
		'class' => 'site-header sticky-top py-1 navbar-inverse navbar-expand-lg navbar-dark bg-dark',
	],
]);
if (!Yii::$app->user->isGuest) {
	try {
		echo Nav::widget([
			'options' => ['class' => 'navbar-nav navbar-right'],
			'items' => AdminPanel::topMenuItems(),
		]);
	} catch (Exception $e) {
		print_r($e);
	}
}
NavBar::end();
?>

<main role="main" class="container">
	<?php try {
		echo Breadcrumbs::widget([
			'links' => $this->params['breadcrumbs'] ?? [],
		]);
		echo Alert::widget();
	} catch (Exception $e) {
		print_r($e);
	} ?>
	<?= $content ?>
</main>

<footer class="footer">
	<div class="container">
		<p class="float-left text-muted">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>
	</div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
