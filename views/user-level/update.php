<?php

/* @var $this yii\web\View */
/* @var $model core\models\UserLevel */
/* @var $modelUserAppModule core\models\UserAppModule */

$this->title = 'Update ' . Yii::t('app', 'User Level') . ': ' . ' ' . $model->nama_level;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Management'), 'url' => ['user/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Level'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->nama_level, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="user-level-update">

    <?= $this->render('_form', [
        'model' => $model,
        'modelUserAppModule' => $modelUserAppModule,
    ]) ?>

</div>
