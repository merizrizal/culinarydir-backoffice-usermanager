<?php

/* @var $this yii\web\View */
/* @var $model core\models\User */
/* @var $modelUserLevel core\models\UserLevel */
/* @var $modelUserRole core\models\UserRole */
/* @var $dataUserRole Array */

$this->title = 'Create ' . Yii::t('app', 'User');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Management'), 'url' => ['user/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title; ?>

<div class="user-create">

    <?= $this->render('_form', [
        'model' => $model,
        'modelUserLevel' => $modelUserLevel,
        'modelUserRole' => $modelUserRole,
        'dataUserRole' => $dataUserRole
    ]) ?>

</div>