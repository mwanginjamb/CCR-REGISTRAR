<?php

namespace app\controllers;

use app\models\SourceReference;
use app\models\SourceReferenceSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * SourceReferenceController manages the source setup list (hospitals, labs,
 * hospices) used for "Source No" suggestions on the patient form.
 *
 * Entries are deactivated rather than deleted: patient source rows store the
 * source number, so deleting an entry would lose its name on existing records.
 */
class SourceReferenceController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['@'],
                        ],
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'toggle-active' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all SourceReference models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new SourceReferenceSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new SourceReference model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new SourceReference();
        $model->loadDefaultValues();

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Source {$model->source_no} - {$model->name} added.");
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing SourceReference model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Source {$model->source_no} - {$model->name} updated.");
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Activates or deactivates an entry. Inactive entries are no longer suggested
     * on the patient form but still name existing patient source rows.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionToggleActive($id)
    {
        $model = $this->findModel($id);
        $model->is_active = $model->is_active ? 0 : 1;
        $model->save(false, ['is_active', 'updated_at', 'updated_by']);

        Yii::$app->session->setFlash(
            'success',
            "Source {$model->source_no} - {$model->name} " . ($model->is_active ? 'activated.' : 'deactivated.')
        );

        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    /**
     * Finds the SourceReference model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return SourceReference the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SourceReference::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
