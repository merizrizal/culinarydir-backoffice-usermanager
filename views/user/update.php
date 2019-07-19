<?php

/* @var $this yii\web\View */
/* @var $model core\models\User */
/* @var $modelUserLevel core\models\UserLevel */
/* @var $modelUserRole core\models\UserRole */
/* @var $dataUserRole Array */

$this->title = 'Update ' . Yii::t('app', 'User') . ': ' . ' ' . $model->full_name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Management'), 'url' => ['user/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->full_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update'; ?>

<div class="user-update">

    <?= $this->render('_form', [
        'model' => $model,
        'modelUserLevel' => $modelUserLevel,
        'modelUserRole' => $modelUserRole,
        'dataUserRole' => $dataUserRole
    ]) ?>

</div>
