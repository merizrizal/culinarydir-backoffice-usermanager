<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use sycomponent\AjaxRequest;
use sycomponent\ModalDialog;
use sycomponent\NotificationDialog;

/* @var $this yii\web\View */
/* @var $model core\models\UserLevel */
/* @var $modelUserAppModule core\models\UserAppModule */
/* @var $keySubprogram string */
/* @var $userAksesId int */

$ajaxRequest = new AjaxRequest([
    'modelClass' => 'UserLevel',
]);

$ajaxRequest->view();

$status = Yii::$app->session->getFlash('status');
$message1 = Yii::$app->session->getFlash('message1');
$message2 = Yii::$app->session->getFlash('message2');

if ($status !== null) {

    $notif = new NotificationDialog([
        'status' => $status,
        'message1' => $message1,
        'message2' => $message2,
    ]);

    $notif->theScript();
    echo $notif->renderDialog();
}

$this->title = $model->nama_level;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Management'), 'url' => ['user/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Level'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo $ajaxRequest->component(); ?>

<div class="user-level-view">

    <div class="row">
        <div class="col-sm-12">
            <div class="x_panel">

                <div class="x_content">

                    <?= Html::a('<i class="fa fa-upload"></i> Create', ['create'], [
                        'class' => 'btn btn-success',
                        'style' => 'color:white'
                    ]) ?>

                    <?= Html::a('<i class="fa fa-pencil-alt"></i> Edit', ['update', 'id' => $model->id], [
                        'class' => 'btn btn-primary',
                        'style' => 'color:white'
                    ]) ?>

                    <?= Html::a('<i class="fa fa-trash-alt"></i> Delete', ['delete', 'id' => $model->id], [
                        'id' => 'delete',
                        'class' => 'btn btn-danger',
                        'style' => 'color:white',
                        'data-not-ajax' => 1,
                        'model-id' => $model->id,
                        'model-name' => $model->nama_level,
                    ]) ?>

                    <?= Html::a('<i class="fa fa-times"></i> Cancel', ['index'], ['class' => 'btn btn-default']) ?>

                    <div class="clearfix" style="margin-top: 15px"></div>

                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => [
                            'class' => 'table'
                        ],
                        'attributes' => [
                            'id',
                            'nama_level',
                            [
                                'attribute' => 'is_super_admin',
                                'format' => 'raw',
                                'value' => Html::checkbox('is_super_admin', $model->is_super_admin, ['value' => $model->is_super_admin, 'disabled' => 'disabled']),
                            ],
                            'keterangan:ntext',
                            [
                                'attribute' => 'app_akses',
                                'format' => 'raw',
                                'value' => function ($model) {

                                    $result = '<div class="row">';

                                    foreach ($model->app_akses['app_name'] as $i => $dataAppName) {

                                        $result .=
                                            '<div class="col-xs-3">
                                                <strong>' . $dataAppName . '</strong>
                                            </div>'
                                        ;

                                        if ($i % 2 == 0 && $i != 0) {

                                            $result .= '<div class="clearfix"></div>';
                                        }
                                    }

                                    return '</div></div>' . $result;
                                }
                            ]
                        ],
                    ]) ?>

                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Roles</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <div class="row" id="roles">

                        <?php
                        foreach ($modelUserAppModule as $keySubprogram => $subprogram):

                            foreach ($subprogram as $key => $value): ?>

                                <div class="col-xs-6 col-sm-4 col-md-4 col-lg-3" id="roles-item">
                                    <div class="tile-stats">
                                        <h4 style="margin: 10px">
                                            <label>
                                                <?= $value[0]['sub_program'] . '/' . $key ?>
                                            </label>
                                        </h4>

                                        <p>
                                            <?php
                                            foreach ($value as $moduleAction) {

                                                $checkBoxId = $moduleAction['nama_module'] . '-' . $moduleAction['module_action'];
                                                $checkBoxName = 'roles[' . $moduleAction['nama_module'] . $moduleAction['module_action'] . '][action]';
                                                $isActive = false;
                                                $userAksesId = 0;

                                                if (count($moduleAction['userAkses']) > 0) {

                                                    $userAksesId = $moduleAction['userAkses'][0]['id'];
                                                    $isActive = $moduleAction['userAkses'][0]['is_active'];
                                                }

                                                echo Html::checkbox($checkBoxName, $isActive, ['id' => $checkBoxId, 'value' => $moduleAction['id'], 'disabled' => 'disabled']) . '&nbsp; &nbsp; ';
                                                echo Html::label($moduleAction['module_action'], $checkBoxId);
                                                echo '<br>';
                                            } ?>
                                        </p>
                                    </div>
                                </div>

                            <?php
                            endforeach;
                        endforeach; ?>

                    </div>
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
$this->registerJsFile($this->params['assetCommon']->baseUrl . '/plugins/masonry/dist/masonry.pkgd.min.js', ['depends' => 'yii\web\YiiAsset']);

$jscript = Yii::$app->params['checkbox-radio-script']() . '
    $(".iCheck-helper").parent().removeClass("disabled");

    $("#roles").masonry({
        itemSelector: "#roles-item",
    });
';

$this->registerJs($jscript); ?>