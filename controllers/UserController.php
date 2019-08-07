<?php

namespace backoffice\modules\usermanager\controllers;

use core\models\User;
use core\models\UserAkses;
use core\models\UserAksesAppModule;
use core\models\UserLevel;
use core\models\UserRole;
use core\models\search\UserSearch;
use yii;
use sycomponent\Tools;
use function yii\base\ArrayableTrait\toArray as array_push;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends \backoffice\controllers\BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(
            $this->getAccess(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]);
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $dataProviderUserRole = new ActiveDataProvider([
            'query' => UserRole::find()->joinWith(['userLevel'])->andWhere(['user_role.user_id' => $id]),
            'pagination' => false,
            'sort' => false
        ]);

        return $this->render('view', [
            'model' => $this->findModel($id),
            'modelUserRole' => new UserRole(),
            'dataProviderUserRole' => $dataProviderUserRole
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($save = null)
    {
        $render = 'create';

        $model = new User();
        $modelUserRole = new UserRole();
        $dataUserRole = [];

        $dataProviderUserRole = null;

        if ($model->load(($post = \Yii::$app->request->post()))) {

            if (empty($save)) {

                \Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            } else {

                $transaction = \Yii::$app->db->beginTransaction();
                $flag = false;

                $model->setPassword($model->password);

                $model->image = Tools::uploadFile('/img/user/', $model, 'image', 'username', $model->username);

                if (($flag = $model->save())) {

                    foreach ($post['UserRole']['user_level_id'] as $userLevelId) {

                        $modelUserRole = new UserRole();
                        $modelUserRole->user_id = $model->id;
                        $modelUserRole->user_level_id = $userLevelId;
                        $modelUserRole->unique_id = $model->id . '-' . $userLevelId;
                        $modelUserRole->is_active = true;

                        if (!($flag = $modelUserRole->save())) {

                            break;
                        } else {

                            array_push($dataUserRole, $modelUserRole->toArray());

                            $modelUserAkses = UserAkses::find()
                                ->andWhere(['user_level_id' => $modelUserRole->user_level_id])
                                ->asArray()->all();

                            foreach ($modelUserAkses as $dataUserAkses) {

                                $modelUserAksesAppModule = UserAksesAppModule::findOne(['unique_id' => $model->id . '-' . $dataUserAkses['user_app_module_id']]);

                                if (empty($modelUserAksesAppModule)) {

                                    $modelUserAksesAppModule = new UserAksesAppModule();
                                    $modelUserAksesAppModule->unique_id = $model->id . '-' . $dataUserAkses['user_app_module_id'];
                                    $modelUserAksesAppModule->user_id = $model->id;
                                    $modelUserAksesAppModule->user_app_module_id = $dataUserAkses['user_app_module_id'];
                                    $modelUserAksesAppModule->is_active = $dataUserAkses['is_active'];
                                    $modelUserAksesAppModule->used_by_user_role = [$modelUserRole->unique_id];
                                } else {

                                    $jsonData = $modelUserAksesAppModule->used_by_user_role;
                                    $jsonDataExist = false;

                                    foreach ($jsonData as $json) {

                                        if ($json == $modelUserRole->unique_id) {

                                            $jsonDataExist = true;
                                            break;
                                        }
                                    }

                                    if (!$jsonDataExist) {

                                        array_push($jsonData, $modelUserRole->unique_id);

                                        $modelUserAksesAppModule->used_by_user_role = $jsonData;

                                        if ($dataUserAkses['is_active'] && !$modelUserAksesAppModule->is_active) {

                                            $modelUserAksesAppModule->is_active = $dataUserAkses['is_active'];
                                        }
                                    }
                                }

                                if (!($flag = $modelUserAksesAppModule->save())) {

                                    break 2;
                                }
                            }
                        }
                    }
                }

                if ($flag) {

                    \Yii::$app->session->setFlash('status', 'success');
                    \Yii::$app->session->setFlash('message1', \Yii::t('app', 'Create Data Is Success'));
                    \Yii::$app->session->setFlash('message2', \Yii::t('app', 'Create data process is success. Data has been saved'));

                    $render = 'view';

                    $dataProviderUserRole = new ActiveDataProvider([
                        'query' => UserRole::find()->joinWith(['userLevel'])->andWhere(['user_role.user_id' => $model->id]),
                        'pagination' => false,
                        'sort' => false
                    ]);

                    $transaction->commit();
                } else {

                    $model->setIsNewRecord(true);

                    \Yii::$app->session->setFlash('status', 'danger');
                    \Yii::$app->session->setFlash('message1', \Yii::t('app', 'Create Data Is Fail'));
                    \Yii::$app->session->setFlash('message2', \Yii::t('app', 'Create data process is fail. Data fail to save'));

                    $transaction->rollBack();
                }
            }
        }

        $modelUserLevel = UserLevel::find()
            ->orderBy('nama_level')
            ->asArray()->all();

        return $this->render($render, [
            'model' => $model,
            'modelUserLevel' => $modelUserLevel,
            'modelUserRole' => $modelUserRole,
            'dataUserRole' => $dataUserRole,
            'dataProviderUserRole' => $dataProviderUserRole
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'update' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id, $save = null)
    {
        $model = $this->findModel($id);

        $dataUserRole = [];

        if ($model->load(($post = \Yii::$app->request->post()))) {

            if (empty($save)) {

                \Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            } else {

                $transaction = \Yii::$app->db->beginTransaction();
                $flag = false;

                $image = Tools::uploadFile('/img/user/', $model, 'image', 'username', $model->username);

                $model->image = !empty($image) ? $image : $model->oldAttributes['image'];

                if (($flag = $model->save())) {

                    $tempUserAkses = [];

                    foreach ($post['UserRole']['user_level_id'] as $userLevelId) {

                        $isExist = false;

                        foreach ($model->userRoles as $userRole) {

                            if (($isExist = ($userRole->unique_id == $id . '-' . $userLevelId))) {

                                $modelUserRole = $userRole;
                                break;
                            }
                        }

                        if (!$isExist) {

                            $modelUserRole = new UserRole();
                            $modelUserRole->user_id = $model->id;
                            $modelUserRole->user_level_id = $userLevelId;
                            $modelUserRole->unique_id = $id . '-' . $userLevelId;
                        }

                        $modelUserRole->is_active = true;

                        if (!($flag = $modelUserRole->save())) {

                            break;
                        } else {

                            array_push($dataUserRole, $modelUserRole->toArray());

                            $modelUserAkses = UserAkses::find()
                                ->andWhere(['user_level_id' => $userLevelId])
                                ->asArray()->all();

                            foreach ($modelUserAkses as $dataUserAkses) {

                                $isExist = false;

                                foreach ($model->userAksesAppModules as $userAksesAppModule) {

                                    if (($isExist = ($userAksesAppModule->unique_id == $id . '-' . $dataUserAkses['user_app_module_id']))) {

                                        $modelUserAksesAppModule = $userAksesAppModule;
                                        break;
                                    }
                                }

                                if (!$isExist) {

                                    $modelUserAksesAppModule = new UserAksesAppModule();
                                    $modelUserAksesAppModule->unique_id = $id . '-' . $dataUserAkses['user_app_module_id'];
                                    $modelUserAksesAppModule->user_id = $id;
                                    $modelUserAksesAppModule->user_app_module_id = $dataUserAkses['user_app_module_id'];
                                    $modelUserAksesAppModule->is_active = $dataUserAkses['is_active'];
                                    $modelUserAksesAppModule->used_by_user_role = [$modelUserRole->unique_id];
                                } else {

                                    $jsonData = $modelUserAksesAppModule->used_by_user_role;
                                    $jsonDataExist = false;

                                    if (!empty($jsonData)) {

                                        foreach ($jsonData as $json) {

                                            if ($json == $modelUserRole->unique_id) {

                                                $jsonDataExist = true;
                                                break;
                                            }
                                        }
                                    }

                                    if (!$jsonDataExist) {

                                        if (!empty($jsonData)) {

                                            array_push($jsonData, $modelUserRole->unique_id);
                                        } else {

                                            $jsonData = [$modelUserRole->unique_id];
                                        }

                                        $modelUserAksesAppModule->used_by_user_role = $jsonData;
                                    }

                                    if (empty($tempUserAkses[$dataUserAkses['user_app_module_id']])) {

                                        $tempUserAkses[$dataUserAkses['user_app_module_id']] = $dataUserAkses['is_active'];
                                    } else {

                                        $tempUserAkses[$dataUserAkses['user_app_module_id']] = $dataUserAkses['is_active'] ? $dataUserAkses['is_active'] : $tempUserAkses[$dataUserAkses['user_app_module_id']];
                                    }

                                    $modelUserAksesAppModule->is_active = $tempUserAkses[$dataUserAkses['user_app_module_id']];
                                }

                                if (!($flag = $modelUserAksesAppModule->save())) {

                                    break 2;
                                }
                            }
                        }
                    }

                    if ($flag) {

                        foreach ($model->userRoles as $existModelUserRole) {

                            $isExist = false;

                            foreach ($post['UserRole']['user_level_id'] as $userLevelId) {

                                if ($existModelUserRole->user_level_id == $userLevelId) {

                                    $isExist = true;
                                    break;
                                }
                            }

                            if (!$isExist && $existModelUserRole->is_active) {

                                $existModelUserRole->is_active = false;

                                if (!($flag = $existModelUserRole->save())) {

                                    break;
                                } else {

                                    $modelUserAkses = UserAkses::find()
                                        ->andWhere(['user_level_id' => $existModelUserRole->user_level_id])
                                        ->asArray()->all();

                                    foreach ($modelUserAkses as $dataUserAkses) {

                                        foreach ($model->userAksesAppModules as $existModelUserAksesAppModule) {

                                            if ($existModelUserAksesAppModule->unique_id == $id . '-' . $dataUserAkses['user_app_module_id']) {

                                                $jsonData = $existModelUserAksesAppModule->used_by_user_role;
                                                $indexSearch = array_search($existModelUserRole->unique_id, $jsonData);

                                                if ($indexSearch == 0 || !empty($indexSearch)) {

                                                    unset($jsonData[$indexSearch]);

                                                    $existModelUserAksesAppModule->used_by_user_role = $jsonData;
                                                }

                                                if (!($flag = $existModelUserAksesAppModule->save())) {

                                                    break 3;
                                                } else {

                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if ($flag) {

                    \Yii::$app->session->setFlash('status', 'success');
                    \Yii::$app->session->setFlash('message1', \Yii::t('app', 'Update Data Is Success'));
                    \Yii::$app->session->setFlash('message2', \Yii::t('app', 'Update data process is success. Data has been saved'));

                    $transaction->commit();
                } else {

                    \Yii::$app->session->setFlash('status', 'danger');
                    \Yii::$app->session->setFlash('message1', \Yii::t('app', 'Update Data Is Fail'));
                    \Yii::$app->session->setFlash('message2', \Yii::t('app', 'Update data process is fail. Data fail to save'));

                    $transaction->rollBack();
                }
            }
        }

        if (empty($dataUserRole)) {

            foreach ($model->userRoles as $userRole) {

                array_push($dataUserRole, $userRole->toArray());
            }
        }

        $modelUserLevel = UserLevel::find()
            ->orderBy('nama_level')
            ->asArray()->all();

        return $this->render('update', [
            'model' => $model,
            'modelUserLevel' => $modelUserLevel,
            'modelUserRole' => new UserRole(),
            'dataUserRole' => $dataUserRole
        ]);
    }

    public function actionUpdatePassword($id, $save = null)
    {
        $model = $this->findModel($id);

        if ($model->load(\Yii::$app->request->post())) {

            if (empty($save)) {

                \Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            } else {

                $model->setPassword($model->password);

                if ($model->save()) {

                    \Yii::$app->session->setFlash('status', 'success');
                    \Yii::$app->session->setFlash('message1', \Yii::t('app', 'Update Data Is Success'));
                    \Yii::$app->session->setFlash('message2', \Yii::t('app', 'Update data process is success. Data has been saved'));
                } else {

                    \Yii::$app->session->setFlash('status', 'danger');
                    \Yii::$app->session->setFlash('message1', \Yii::t('app', 'Update Data Is Fail'));
                    \Yii::$app->session->setFlash('message2', \Yii::t('app', 'Update data process is fail. Data fail to save'));
                }
            }
        }

        $model->password = '';

        return $this->render('update-password', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if (($model = $this->findModel($id)) !== false) {

            $flag = false;
            $error = '';

            try {
                $flag = $model->delete();
            } catch (yii\db\Exception $exc) {
                $error = \Yii::$app->params['errMysql'][$exc->errorInfo[1]];
            }
        }

        if ($flag) {

            \Yii::$app->session->setFlash('status', 'success');
            \Yii::$app->session->setFlash('message1', \Yii::t('app', 'Delete Is Success'));
            \Yii::$app->session->setFlash('message2', \Yii::t('app', 'Delete process is success. Data has been deleted'));
        } else {

            \Yii::$app->session->setFlash('status', 'danger');
            \Yii::$app->session->setFlash('message1', \Yii::t('app', 'Delete Is Fail'));
            \Yii::$app->session->setFlash('message2', \Yii::t('app', 'Delete process is fail. Data fail to delete' . $error));
        }

        $return = [];

        $return['url'] = \Yii::$app->urlManager->createUrl([$this->module->id . '/user/index']);

        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $return;
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
