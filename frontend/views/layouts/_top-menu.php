<?php
/**
 * @var View $this
 * @var array $menuItems
 */

use yii\web\View;
use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;

NavBar::begin([
	'brandLabel' => Yii::$app->name,
	'brandUrl' => Yii::$app->homeUrl,
	'options' => [
		'class' => 'navbar-inverse navbar-expand-md navbar-dark bg-dark fixed-top',
	],
]);
if (!Yii::$app->user->isGuest) {
	echo Nav::widget([
		'options' => ['class' => 'navbar-nav navbar-right'],
		'items' => $menuItems,
	]);
}
NavBar::end();
