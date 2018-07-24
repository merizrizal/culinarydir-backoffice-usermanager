<?php

namespace backend\controllers;

use Yii;
use backend\models\UserLevel;
use backend\models\search\UserLevelSearch;
use backend\models\UserAppModule;
use backend\models\UserAkses;
use sybase\SybaseController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * UserLevelController implements the CRUD actions for UserLevel model.
 */
class UserLevelController extends SybaseController
{
    private $params = [];

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
        if (Yii::$app->request->isAjax) {
            $this->layout = 'ajax';
        }

        $searchModel = new UserLevelSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

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
        if (Yii::$app->request->isAjax) {
            $this->layout = 'ajax';
        }

        $this->params['id'] = $id;

        $modelUserAppModule = UserAppModule::find()
                ->joinWith([
                    'userAkses' => function($query) {
                        $query->onCondition('user_akses.user_level_id = ' . $this->params['id']);
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
        if (Yii::$app->request->isAjax) {
            $this->layout = 'ajax';
        }

        $render = 'create';

        $model = new UserLevel();

        if ($model->load(Yii::$app->request->post()) && (($post = Yii::$app->request->post()))) {

            if (empty($save)) {

                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            } else {

                $transaction = Yii::$app->db->beginTransaction();
                $flag = false;

                if (($flag = $model->save())) {

                    foreach ($post['roles'] as $value) {

                        $modelUserAkses = new UserAkses();
                        $modelUserAkses->user_level_id = $model->id;
                        $modelUserAkses->user_app_module_id = $value['appModuleId'];
                        $modelUserAkses->is_active = !empty($value['action']) ? 1 : 0;

                        if (!($flag = $modelUserAkses->save())) {
                            break;
                        }
                    }
                }

                if ($flag) {

                    Yii::$app->session->setFlash('status', 'success');
                    Yii::$app->session->setFlash('message1', Yii::t('app', 'Create Data Is Success'));
                    Yii::$app->session->setFlash('message2', Yii::t('app', 'Create data process is success. Data has been saved'));

                    $transaction->commit();

                    $render = 'view';
                } else {

                    $model->setIsNewRecord(true);

                    Yii::$app->session->setFlash('status', 'danger');
                    Yii::$app->session->setFlash('message1', Yii::t('app', 'Create Data Is Fail'));
                    Yii::$app->session->setFlash('message2', Yii::t('app', 'Create data process is fail. Data fail to save'));

                    $transaction->rollBack();
                }
            }
        }

        if (empty($model->id)) {
            $this->params['id'] = -90909;
        } else {
            $this->params['id'] = $model->id;
        }

        $modelUserAppModule = UserAppModule::find()
                ->joinWith([
                    'userAkses' => function($query) {
                        $query->onCondition('user_akses.user_level_id = ' . $this->params['id']);
                    },
                ])->asArray()->all();

        $dataUserAppModule = [];
        foreach ($modelUserAppModule as $value) {
            $dataUserAppModule[$value['sub_program']][$value['nama_module']][] = $value;
        }

        return $this->render($render, [
            'model' => $model,
            'modelUserAppModule' => $dataUserAppModule,
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
        if (Yii::$app->request->isAjax) {
            $this->layout = 'ajax';
        }

        $model = $this->findModel($id);
        $this->params['id'] = $id;

        if ($model->load(($post = Yii::$app->request->post()))) {

            if (empty($save)) {

                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            } else {

                $transaction = Yii::$app->db->beginTransaction();
                $flag = false;

                if (($flag = $model->save())) {

                    foreach ($post['roles'] as $value) {

                        if ($value['userAksesId'] > 0) {
                            $modelUserAkses = UserAkses::findOne($value['userAksesId']);
                        } else {

                            $modelUserAkses = new UserAkses();
                            $modelUserAkses->user_level_id = $model->id;
                            $modelUserAkses->user_app_module_id = $value['appModuleId'];
                        }

                        $modelUserAkses->is_active = !empty($value['action']) ? 1 : 0;

                        if (!($flag = $modelUserAkses->save())) {
                            break;
                        }
                    }
                }

                if ($flag) {

                    Yii::$app->session->setFlash('status', 'success');
                    Yii::$app->session->setFlash('message1', Yii::t('app', 'Update Data Is Success'));
                    Yii::$app->session->setFlash('message2', Yii::t('app', 'Update data process is success. Data has been saved'));

                    $transaction->commit();
                } else {

                    Yii::$app->session->setFlash('status', 'danger');
                    Yii::$app->session->setFlash('message1', Yii::t('app', 'Update Data Is Fail'));
                    Yii::$app->session->setFlash('message2', Yii::t('app', 'Update data process is fail. Data fail to save'));

                    $transaction->rollBack();
                }
            }
        }

        $modelUserAppModule = UserAppModule::find()
                ->joinWith([
                    'userAkses' => function($query) {
                        $query->onCondition('user_akses.user_level_id = ' . $this->params['id']);
                    },
                ])->asArray()->all();

        $dataUserAppModule = [];
        foreach ($modelUserAppModule as $value) {
            $dataUserAppModule[$value['sub_program']][$value['nama_module']][] = $value;
        }

        return $this->render('update', [
            'model' => $model,
            'modelUserAppModule' => $dataUserAppModule,
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
        $transaction = Yii::$app->db->beginTransaction();

        $flag = UserAkses::deleteAll(['user_level_id' => $id]);

        if ($flag && ($model = $this->findModel($id)) !== false) {

            $flag = false;
            $error = '';

            try {
                $flag = $model->delete();
            } catch (yii\db\Exception $exc) {
                $error = Yii::$app->params['errMysql'][$exc->errorInfo[1]];
            }
        } else {
            $flag = true;
        }

        if ($flag) {

            Yii::$app->session->setFlash('status', 'success');
            Yii::$app->session->setFlash('message1', Yii::t('app', 'Delete Is Success'));
            Yii::$app->session->setFlash('message2', Yii::t('app', 'Delete process is success. Data has been deleted'));

            $transaction->commit();
        } else {

            Yii::$app->session->setFlash('status', 'danger');
            Yii::$app->session->setFlash('message1', Yii::t('app', 'Delete Is Fail'));
            Yii::$app->session->setFlash('message2', Yii::t('app', 'Delete process is fail. Data fail to delete' . $error));

            $transaction->rollBack();
        }

        $return = [];

        $return['url'] = Yii::$app->urlManager->createUrl(['user-level/index']);

        Yii::$app->response->format = Response::FORMAT_JSON;
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
