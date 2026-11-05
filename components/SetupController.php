<?php

namespace app\components;

use Yii;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Base controller for the Settings section (reference data and rules).
 *
 * Provides index / create / update plus either:
 *   - toggle-active, for tables with an is_active column. Patient records store
 *     these codes, so entries are deactivated rather than deleted.
 *   - delete, for tables without is_active (the rule tables).
 *
 * Each subclass sets $modelClass, $searchModelClass and $label, implements
 * recordLabel(), and provides views/<controller-id>/index.php and _form.php.
 * Create and update share views/setup/form.php.
 */
abstract class SetupController extends Controller
{
    /**
     * Who may manage settings. Switch to ['admin'] once RBAC roles exist;
     * the Settings menu (views/layouts/_settingsNav.php) checks the same.
     */
    public const ACCESS_ROLES = ['@'];

    /** @var string ActiveRecord class managed by this controller */
    public string $modelClass;

    /** @var string Search model class providing search($params) */
    public string $searchModelClass;

    /** @var string Singular name shown in titles and messages, e.g. "Topography Code" */
    public string $label;

    /**
     * Short human-readable identifier for flash messages, e.g. "C50.9 - Breast, NOS".
     */
    abstract protected function recordLabel(ActiveRecord $model): string;

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
                            'roles' => static::ACCESS_ROLES,
                        ],
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                        'toggle-active' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all records, with filters.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new $this->searchModelClass();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a record. On success, redirects to the index page.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        /** @var ActiveRecord $model */
        $model = new $this->modelClass();
        $model->loadDefaultValues();

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "{$this->label} {$this->recordLabel($model)} added.");
            return $this->redirect(['index']);
        }

        return $this->render('//setup/form', [
            'model' => $model,
            'title' => "New {$this->label}",
        ]);
    }

    /**
     * Updates a record. On success, redirects to the index page.
     *
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "{$this->label} {$this->recordLabel($model)} updated.");
            return $this->redirect(['index']);
        }

        return $this->render('//setup/form', [
            'model' => $model,
            'title' => "Edit {$this->label}: {$this->recordLabel($model)}",
        ]);
    }

    /**
     * Activates or deactivates a record (tables with is_active only).
     *
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException|BadRequestHttpException
     */
    public function actionToggleActive($id)
    {
        $model = $this->findModel($id);

        if (!$model->hasAttribute('is_active')) {
            throw new BadRequestHttpException("{$this->label} entries cannot be deactivated.");
        }

        $model->is_active = $model->is_active ? 0 : 1;
        $model->save(false, array_filter(
            ['is_active', 'updated_at', 'updated_by'],
            [$model, 'hasAttribute']
        ));

        Yii::$app->session->setFlash(
            'success',
            "{$this->label} {$this->recordLabel($model)} " . ($model->is_active ? 'activated.' : 'deactivated.')
        );

        return $this->redirect($this->request->referrer ?: ['index']);
    }

    /**
     * Deletes a record (tables without is_active only).
     *
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException|BadRequestHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->hasAttribute('is_active')) {
            throw new BadRequestHttpException("{$this->label} entries are deactivated, not deleted.");
        }

        $label = $this->recordLabel($model);
        $model->delete();
        Yii::$app->session->setFlash('success', "{$this->label} {$label} deleted.");

        return $this->redirect($this->request->referrer ?: ['index']);
    }

    /**
     * Finds the model based on its primary key value.
     *
     * @param int $id ID
     * @return ActiveRecord the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = $this->modelClass::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
