<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use sycomponent\AjaxRequest;
use sycomponent\ModalDialog;
use sycomponent\NotificationDialog;

/* @var $this yii\web\View */
/* @var $model core\models\User */

$ajaxRequest = new AjaxRequest([
    'modelClass' => 'User',
]);

$ajaxRequest->view();

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

$this->title = $model->full_name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Management'), 'url' => ['user/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title; ?>

<?= $ajaxRequest->component() ?>

<div class="user-view">

    <div class="row">
        <div class="col-sm-12">
            <div class="x_panel">

                <div class="x_content">

                    <?= Html::a('<i class="fa fa-upload"></i> Create',
                        ['create'],
                        [
                            'class' => 'btn btn-success',
                            'style' => 'color:white'
                        ]) ?>

                    <?= Html::a('<i class="fa fa-pencil-alt"></i> Edit',
                        ['update', 'id' => $model->id],
                        [
                            'class' => 'btn btn-primary',
                            'style' => 'color:white'
                        ]) ?>

                    <?= Html::a('<i class="fa fa-trash-alt"></i> Delete',
                        ['delete', 'id' => $model->id],
                        [
                            'id' => 'delete',
                            'class' => 'btn btn-danger',
                            'style' => 'color:white',
                            'data-not-ajax' => 1,
                            'model-id' => $model->email,
                            'model-name' => $model->full_name,
                        ]) ?>

                    <?= Html::a('<i class="fa fa-wrench"></i> Update Password',
                        ['update-password', 'id' => $model->id],
                        [
                            'class' => 'btn btn-default',
                        ]) ?>

                    <?= Html::a('<i class="fa fa-times"></i> Cancel',
                        ['index'],
                        [
                            'class' => 'btn btn-default',
                        ]) ?>

                    <div class="clearfix" style="margin-top: 15px"></div>

                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => [
                            'class' => 'table'
                        ],
                        'attributes' => [
                            'userLevel.nama_level',
                            'email',
                            'username',
                            'full_name',
                            [
                                'attribute' => 'image',
                                'format' => 'raw',
                                'value' => Html::img(Yii::getAlias('@uploadsUrl') . $model->thumb('/img/user/', 'image', 200, 200), ['class'=>'img-thumbnail']),
                            ],
                            [
                                'attribute' => 'not_active',
                                'format' => 'raw',
                                'value' => Html::checkbox('not_active', $model->not_active, ['value' => $model->not_active, 'disabled' => 'disabled']),
                            ],
                        ],
                    ]) ?>

                </div>

            </div>
        </div>
    </div>

</div>

<?php

$modalDialog = new ModalDialog([
    'clickedComponent' => 'a#delete',
    'modelAttributeId' => 'model-id',
    'modelAttributeName' => 'model-name',
]);

$modalDialog->theScript(false);

echo $modalDialog->renderDialog();

$this->registerCssFile($this->params['assetCommon']->baseUrl . '/plugins/icheck/skins/all.css', ['depends' => 'yii\web\YiiAsset']);

$this->registerJsFile($this->params['assetCommon']->baseUrl . '/plugins/icheck/icheck.min.js', ['depends' => 'yii\web\YiiAsset']);

$jscript = Yii::$app->params['checkbox-radio-script']()
    . '$(".iCheck-helper").parent().removeClass("disabled");
';

$this->registerJs($jscript);

?>