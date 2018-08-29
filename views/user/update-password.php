<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use sycomponent\AjaxRequest;
use sycomponent\NotificationDialog;

/* @var $this yii\web\View */
/* @var $model core\models\User */
/* @var $form yii\widgets\ActiveForm */

kartik\select2\Select2Asset::register($this);
kartik\select2\ThemeKrajeeAsset::register($this);

$ajaxRequest = new AjaxRequest([
    'modelClass' => 'User',
]);

$ajaxRequest->form();

$status = Yii::$app->session->getFlash('status');
$message1 = Yii::$app->session->getFlash('message1');
$message2 = Yii::$app->session->getFlash('message2');

if ($status !== null) :
    $notif = new NotificationDialog([
        'status' => $status,
        'message1' => $message1,
        'message2' => $message2,
    ]);

    $notif->theScript();
    echo $notif->renderDialog();

endif;

$this->title = 'Update ' . Yii::t('app', 'User Password') . ': ' . ' ' . $model->full_name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Management'), 'url' => ['user/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->full_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update ' . Yii::t('app', 'User Password'); ?>

<?= $ajaxRequest->component() ?>

<div class="user-update">

    <div class="row">
        <div class="col-sm-12">
            <div class="x_panel">
                <div class="user-form">

                    <?php $form = ActiveForm::begin([
                            'id' => 'user-form',
                            'action' => ['update-password', 'id' => $model->id],
                            'options' => [

                            ],
                            'fieldConfig' => [
                                'parts' => [
                                    '{inputClass}' => 'col-lg-12'
                                ],
                                'template' => '
                                    <div class="row">
                                        <div class="col-lg-3">
                                            {label}
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="{inputClass}">
                                                {input}
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            {error}
                                        </div>
                                    </div>',
                            ]
                    ]); ?>

                        <div class="x_title">

                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <?php
                                        if (!$model->isNewRecord)
                                            echo Html::a('<i class="fa fa-upload"></i>&nbsp;&nbsp;&nbsp;' . 'Create', ['create'], ['class' => 'btn btn-success']); ?>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="x_content">

                            <?= $form->field($model, 'password')->passwordInput(['maxlength' => 64]) ?>

                            <div class="form-group">
                                <div class="row">
                                    <div class="col-lg-3"></div>
                                    <div class="col-lg-6">
                                        <?php
                                        $icon = '<i class="fa fa-save"></i> ';
                                        echo Html::submitButton($model->isNewRecord ? $icon . 'Save' : $icon . 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']);
                                        echo '&nbsp;&nbsp;&nbsp;';
                                        echo Html::a('<i class="fa fa-times"></i> Cancel', ['index'], ['class' => 'btn btn-default']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php ActiveForm::end(); ?>

                </div>
            </div>
        </div>
    </div><!-- /.row -->

</div>

<?php

$jscript = '
    $("#user-user_level_id").select2({
        theme: "krajee",
        placeholder: "Pilih"
    });

    $("#user-user_level_id").prop("disabled", true);
';

$this->registerJs($jscript); ?>
