<?php

/* @var $this yii\web\View */
/* @var $model core\models\User */
/* @var $modelUserLevel core\models\UserLevel */

$this->title = 'Create ' . Yii::t('app', 'User');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Management'), 'url' => ['user/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title; ?>

<div class="user-create">

    <?= $this->render('_form', [
        'model' => $model,
        'modelUserLevel' => $modelUserLevel
    ]) ?>

</div>