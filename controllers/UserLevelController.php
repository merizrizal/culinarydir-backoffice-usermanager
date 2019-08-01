<?php

namespace backoffice\modules\usermanager\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;
use core\models\UserLevel;
use core\models\search\UserLevelSearch;
use core\models\UserAppModule;
use core\models\UserAkses;
use core\models\UserAksesAppModule;

/**
 * UserLevelController implements the CRUD actions for UserLevel model.
 */
class UserLevelController extends \backoffice\controllers\BaseController
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
     * Lists all UserLevel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserLevelSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single UserLevel model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $modelUserAppModule = UserAppModule::find()
            ->joinWith([
                'userAkses' => function ($query) use ($id) {

                    $query->onCondition(['user_akses.user_level_id' => $id]);
                },
            ])->asArray()->all();

        $dataUserAppModule = [];

        foreach ($modelUserAppModule as $value) {

            $dataUserAppModule[$value['sub_program']][$value['nama_module']][] = $value;
        }

        return $this->render('view', [
            'model' => $this->findModel($id),
            'modelUserAppModule' => $dataUserAppModule,
        ]);
    }

    /**
     * Creates a new UserLevel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($save = null)
    {
        $render = 'create';

        $model = new UserLevel();

        if ($model->load(\Yii::$app->request->post()) && (($post = \Yii::$app->request->post()))) {

            if (empty($save)) {

                \Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            } else {

                $transaction = \Yii::$app->db->beginTransaction();
                $flag = false;

                if (($flag = $model->save())) {

                    foreach ($post['roles'] as $value) {

                        $modelUserAkses = new UserAkses();
                        $modelUserAkses->user_level_id = $model->id;
                        $modelUserAkses->user_app_module_id = $value['appModuleId'];
                        $modelUserAkses->unique_id = $model->id . '-' . $value['appModuleId'];
                        $modelUserAkses->is_active = !empty($value['action']);

                        if (!($flag = $modelUserAkses->save())) {

                            break;
                        }
                    }
                }

                if ($flag) {

                    \Yii::$app->session->setFlash('status', 'success');
                    \Yii::$app->session->setFlash('message1', \Yii::t('app', 'Create Data Is Success'));
                    \Yii::$app->session->setFlash('message2', \Yii::t('app', 'Create data process is success. Data has been saved'));

                    $transaction->commit();

                    $render = 'view';
                } else {

                    $model->setIsNewRecord(true);

                    \Yii::$app->session->setFlash('status', 'danger');
                    \Yii::$app->session->setFlash('message1', \Yii::t('app', 'Create Data Is Fail'));
                    \Yii::$app->session->setFlash('message2', \Yii::t('app', 'Create data process is fail. Data fail to save'));

                    $transaction->rollBack();
                }
            }
        }

        $modelUserAppModule = UserAppModule::find()
            ->joinWith([
                'userAkses' => function ($query) use ($model) {

                    $query->onCondition(['user_akses.user_level_id' => $model->id]);
                },
            ])->asArray()->all();

        $dataUserAppModule = [];

        foreach ($modelUserAppModule as $value) {

            $dataUserAppModule[$value['sub_program']][$value['nama_module']][] = $value;
        }

        $dataAppAkses = [
            \Yii::$app->params['appName']['frontend'] => 'Frontend',
            \Yii::$app->params['appName']['backoffice'] => 'Backoffice',
            \Yii::$app->params['appName']['user-app'] => 'User App',
            \Yii::$app->params['appName']['driver-app'] => 'Driver App'
        ];

        return $this->render($render, [
            'model' => $model,
            'modelUserAppModule' => $dataUserAppModule,
            'dataAppAkses' => $dataAppAkses
        ]);
    }

    /**
     * Updates an existing UserLevel model.
     * If update is successful, the browser will be redirected to the 'update' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id, $save = null)
    {
        $model = $this->findModel($id);

        if ($model->load(($post = \Yii::$app->request->post()))) {

            if (empty($save)) {

                \Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            } else {

                $transaction = \Yii::$app->db->beginTransaction();
                $flag = false;

                if (($flag = $model->save())) {

                    foreach ($post['roles'] as $value) {

                        $isExist = false;

                        foreach ($model->userAkses as $dataUserAkses) {

                            if (($isExist = ($dataUserAkses->unique_id == $model->id . '-' . $value['appModuleId']))) {

                                $modelUserAkses = $dataUserAkses;
                                break;
                            }
                        }

                        if (!$isExist) {

                            $modelUserAkses = new UserAkses();
                            $modelUserAkses->user_level_id = $model->id;
                            $modelUserAkses->user_app_module_id = $value['appModuleId'];
                            $modelUserAkses->unique_id = $model->id . '-' . $value['appModuleId'];
                        }

                        $modelUserAkses->is_active = !empty($value['action']);

                        if (!($flag = $modelUserAkses->save())) {

                            break;
                        } else {

                            $modelUserAksesAppModule = UserAksesAppModule::find()
                                ->andWhere(['user_app_module_id' => $modelUserAkses->user_app_module_id])
                                ->andWhere(['not', ['used_by_user_role' => null]])
                                ->all();

                            foreach ($modelUserAksesAppModule as $dataUserAksesAppModule) {

                                foreach ($dataUserAksesAppModule->used_by_user_role as $dataUserRole) {

                                    $userLevelId = substr($dataUserRole, 33);

                                    if ($modelUserAkses->is_active) {

                                        if ($userLevelId == $id) {

                                            $dataUserAksesAppModule->is_active = true;
                                            break;
                                        }
                                    } else {

                                        if ($userLevelId !== $id && count($dataUserAksesAppModule->used_by_user_role) > 1) {

                                            $userAkses = UserAkses::find()
                                                ->andWhere(['unique_id' => $userLevelId . '-' . $modelUserAkses->user_app_module_id])
                                                ->asArray()->one();

                                            $dataUserAksesAppModule->is_active = $userAkses['is_active'];

                                            if ($userAkses['is_active']) {

                                                break;
                                            }
                                        } elseif ($userLevelId == $id) {

                                            $dataUserAksesAppModule->is_active = false;
                                        }
                                    }
                                }

                                if (!($flag = $dataUserAksesAppModule->save())) {

                                    break;
                                }
                            }
                        }
                    }
                }

                if ($flag) {

                    foreach ($model->app_akses['app_name'] as $i => $existAppName) {

                        $isExist = false;

                        foreach ($post['UserLevel']['app_akses']['app_name'] as $appName) {

                            if ($existAppName == $appName) {

                                $isExist = true;
                                break;
                            }
                        }

                        if (!$isExist) {

                            $jsonAppName = $model->app_akses['app_name'];

                            unset($jsonAppName[$i]);

                            $model->app_akses['app_name'] = $jsonAppName;

                            if (!($flag = $model->save())) {

                                break;
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

        $modelUserAppModule = UserAppModule::find()
            ->joinWith([
                'userAkses' => function ($query) use ($id) {

                    $query->onCondition(['user_akses.user_level_id' => $id]);
                },
            ])->asArray()->all();

        $dataUserAppModule = [];

        foreach ($modelUserAppModule as $value) {

            $dataUserAppModule[$value['sub_program']][$value['nama_module']][] = $value;
        }

        $dataAppAkses = [
            \Yii::$app->params['appName']['frontend'] => 'Frontend',
            \Yii::$app->params['appName']['backoffice'] => 'Backoffice',
            \Yii::$app->params['appName']['user-app'] => 'User App',
            \Yii::$app->params['appName']['driver-app'] => 'Driver App'
        ];

        return $this->render('update', [
            'model' => $model,
            'modelUserAppModule' => $dataUserAppModule,
            'dataAppAkses' => $dataAppAkses
        ]);
    }

    /**
     * Deletes an existing UserLevel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $transaction = \Yii::$app->db->beginTransaction();

        $flag = UserAkses::deleteAll(['user_level_id' => $id]);

        if ($flag && ($model = $this->findModel($id)) !== false) {

            $flag = false;
            $error = '';

            try {

                $flag = $model->delete();
            } catch (yii\db\Exception $exc) {

                $error = \Yii::$app->params['errMysql'][$exc->errorInfo[1]];
            }
        } else {

            $flag = true;
        }

        if ($flag) {

            \Yii::$app->session->setFlash('status', 'success');
            \Yii::$app->session->setFlash('message1', \Yii::t('app', 'Delete Is Success'));
            \Yii::$app->session->setFlash('message2', \Yii::t('app', 'Delete process is success. Data has been deleted'));

            $transaction->commit();
        } else {

            \Yii::$app->session->setFlash('status', 'danger');
            \Yii::$app->session->setFlash('message1', \Yii::t('app', 'Delete Is Fail'));
            \Yii::$app->session->setFlash('message2', \Yii::t('app', 'Delete process is fail. Data fail to delete' . $error));

            $transaction->rollBack();
        }

        $return = [];

        $return['url'] = \Yii::$app->urlManager->createUrl([$this->module->id . '/user-level/index']);

        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $return;
    }

    /**
     * Finds the UserLevel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return UserLevel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UserLevel::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
