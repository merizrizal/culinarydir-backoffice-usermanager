<?php

/* @var $this yii\web\View */
/* @var $model core\models\UserLevel */

$this->title = 'Create ' . Yii::t('app', 'User Level');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Management'), 'url' => ['user/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Level'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-level-create">

    <?= $this->render('_form', [
        'model' => $model,
        'modelUserAppModule' => $modelUserAppModule,
    ]) ?>

</div>