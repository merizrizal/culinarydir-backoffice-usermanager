<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use sycomponent\AjaxRequest;
use sycomponent\NotificationDialog;

/* @var $this yii\web\View */
/* @var $model core\models\User */
/* @var $modelUserLevel core\models\UserLevel */
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

if ($status !== null) {

    $notif = new NotificationDialog([
        'status' => $status,
        'message1' => $message1,
        'message2' => $message2,
    ]);

    $notif->theScript();
    echo $notif->renderDialog();
}

echo $ajaxRequest->component(); ?>

<div class="row">
    <div class="col-sm-12">
        <div class="x_panel">
            <div class="user-form">

                <?php
                $form = ActiveForm::begin([
                    'id' => 'user-form',
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

                    <div class="x_title">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-lg-6">

                                    <?php
                                    if (!$model->isNewRecord) {

                                        echo Html::a('<i class="fa fa-upload"></i> Create', ['create'], ['class' => 'btn btn-success']);
                                    } ?>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="x_content">

                    	<?= $form->field($model, 'user_level_id')->checkboxList(
                	        ArrayHelper::map(
                    	        $modelUserLevel,
                    	        'id',
                    	        function($data) {

                    	            return $data['nama_level'];
                    	        }
                	        ),
                	        [
                	            'item' => function ($index, $label, $name, $checked, $value) use ($modelUserLevel) {

                    	            $checkboxes = '
                                        <div class="row">
                                            <div class="col-xs-12 col">
                                                <label>' .
                                                    Html::checkbox($name, $checked, [
                                                        'value' => $value,
                                                        'class' => 'user-level icheck',
                                                    ]) . ' ' . $label .
                                                '</label>
                                            </div>
                                        </div>
                                    ';

                                    $userLevelCount = count($modelUserLevel);

                                    $index++;

                                    if ($index === 1) {

                                        return '<div class="col-xs-6">' . $checkboxes;
                                    } else if ($index === $userLevelCount) {

                                        return $checkboxes . '</div>';
                                    } else if (($index % ceil($userLevelCount / 2)) === 0) {

                                        return $checkboxes . '</div><div class="col-xs-6">';
                                    } else {

                                        return $checkboxes;
                                    }
                	            }
                	        ]) ?>

                        <?= $form->field($model, 'email', [
                            'enableAjaxValidation' => true
                        ])->textInput(['maxlength' => true]) ?>

                        <?= $form->field($model, 'username', [
                            'enableAjaxValidation' => true
                        ])->textInput(['maxlength' => true]) ?>

                        <?= $model->isNewRecord ? $form->field($model, 'password')->passwordInput(['maxlength' => true]) : '' ?>

                        <?= $form->field($model, 'full_name')->textInput(['maxlength' => true]) ?>

                        <?= $form->field($model, 'image')->widget(FileInput::classname(), [
                            'options' => [
                                'accept' => 'image/*'
                            ],
                            'pluginOptions' => [
                                'initialPreview' => [
                                    Html::img(Yii::getAlias('@uploadsUrl') . $model->thumb('/img/user/', 'image', 200, 200), ['class'=>'file-preview-image']),
                                ],
                                'showRemove' => false,
                                'showUpload' => false,
                            ]
                        ]); ?>

                        <?= $form->field($model, 'not_active')->checkbox(['value' => true], false) ?>

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

                <?php
                ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>

<?php
$this->registerCssFile($this->params['assetCommon']->baseUrl . '/plugins/icheck/skins/all.css', ['depends' => 'yii\web\YiiAsset']);

$this->registerJsFile($this->params['assetCommon']->baseUrl . '/plugins/icheck/icheck.min.js', ['depends' => 'yii\web\YiiAsset']);

$this->registerJs(Yii::$app->params['checkbox-radio-script']()); ?>