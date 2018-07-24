<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\UserLevel */

$this->title = 'Create ' . Yii::t('app', 'User Level');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Level'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-level-create">

    <?= $this->render('_form', [
        'model' => $model,
        'modelUserAppModule' => $modelUserAppModule,
    ]) ?>

</div>