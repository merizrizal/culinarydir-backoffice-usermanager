<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use sycomponent\AjaxRequest;
use sycomponent\NotificationDialog;

/* @var $this yii\web\View */
/* @var $model core\models\UserLevel */
/* @var $form yii\widgets\ActiveForm */

kartik\select2\Select2Asset::register($this);
kartik\select2\ThemeKrajeeAsset::register($this);

$ajaxRequest = new AjaxRequest([
    'modelClass' => 'UserLevel',
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

endif; ?>

<?= $ajaxRequest->component() ?>

<?php
$form = ActiveForm::begin([
    'id' => 'user-level-form',
    'action' => $model->isNewRecord ? ['create'] : ['update', 'id' => $model->id],
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

    <div class="row">
        <div class="col-sm-12">
            <div class="x_panel">
                <div class="user-level-form">

                    <div class="x_title">

                        <div class="form-group">
                            <div class="row">
                                <div class="col-lg-6">
                                    <?php
                                    if (!$model->isNewRecord)
                                        echo Html::a('<i class="fa fa-upload"></i> ' . 'Create', ['create'], ['class' => 'btn btn-success']); ?>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="x_content">

                        <?= $form->field($model, 'nama_level')->textInput(['maxlength' => 32]) ?>

                        <?= $form->field($model, 'is_super_admin')->checkbox(['value' => true], false) ?>

                        <?= $form->field($model, 'keterangan')->textarea(['rows' => 2]) ?>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-lg-3"></div>
                                <div class="col-lg-6">
                                    <?php
                                    $icon = '<i class="fa fa-save"></i> ';
                                    echo Html::submitButton($model->isNewRecord ? $icon . 'Save' : $icon . 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']);
                                    echo Html::a('<i class="fa fa-times"></i> Cancel', ['index'], ['class' => 'btn btn-default']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div><!-- /.row -->

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
                                                <?php
                                                if ($value[0]['sub_program'] == '/') {
                                                    echo '(frontend)/' . $key;
                                                } else {
                                                    echo $value[0]['sub_program'] . '/' . $key;
                                                } ?>
                                            </label>
                                        </h4>
                                        <p>
                                            <?php
                                            foreach ($value as $moduleAction) {
                                                $checkBoxId = $keySubprogram . $moduleAction['nama_module'] . '-' . $moduleAction['module_action'];
                                                $checkBoxName = 'roles[' . $keySubprogram . $moduleAction['nama_module'] . $moduleAction['module_action'] . '][action]';
                                                $hiddenInputName = 'roles[' . $keySubprogram . $moduleAction['nama_module'] . $moduleAction['module_action'] . '][userAksesId]';
                                                $hiddenInputName2 = 'roles[' . $keySubprogram . $moduleAction['nama_module'] . $moduleAction['module_action'] . '][appModuleId]';
                                                $isActive = false;
                                                $userAksesId = 0;

                                                if (count($moduleAction['userAkses']) > 0) {
                                                    $userAksesId = $moduleAction['userAkses'][0]['id'];
                                                    $isActive = $moduleAction['userAkses'][0]['is_active'];
                                                }

                                                echo Html::hiddenInput($hiddenInputName, $userAksesId);
                                                echo Html::hiddenInput($hiddenInputName2, $moduleAction['id']);
                                                echo Html::checkbox($checkBoxName, $isActive, ['id' => $checkBoxId, 'value' => $moduleAction['id']]) . '&nbsp; &nbsp; ';
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

                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-3"></div>
                            <div class="col-lg-6">
                                <?php
                                $icon = '<i class="fa fa-save"></i> ';
                                echo Html::submitButton($model->isNewRecord ? $icon . 'Save' : $icon . 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']);
                                echo Html::a('<i class="fa fa-times"></i> Cancel', ['index'], ['class' => 'btn btn-default']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
ActiveForm::end(); ?>

<?php

$this->registerCssFile($this->params['assetCommon']->baseUrl . '/plugins/icheck/skins/all.css', ['depends' => 'yii\web\YiiAsset']);

$this->registerJsFile($this->params['assetCommon']->baseUrl . '/plugins/icheck/icheck.min.js', ['depends' => 'yii\web\YiiAsset']);
$this->registerJsFile($this->params['assetCommon']->baseUrl . '/plugins/masonry/dist/masonry.pkgd.min.js', ['depends' => 'yii\web\YiiAsset']);


$jscript = '
    $("#userlevel-default_action_crm").select2({
        theme: "krajee",
        placeholder: "Pilih"
    });

    $("#userlevel-default_action_cms").select2({
        theme: "krajee",
        placeholder: "Pilih"
    });

    $("#userlevel-default_action_front").select2({
        theme: "krajee",
        placeholder: "Pilih"
    });

    $("#roles").masonry({
        itemSelector: "#roles-item",
    });
';

$this->registerJs(Yii::$app->params['checkbox-radio-script']() . $jscript); ?>