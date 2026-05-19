<?php

namespace app\controllers;

use app\models\Patient;
use app\models\PatientSearch;
use app\models\Tumour;
use app\models\Treatment;
use app\models\FollowUp;
use app\models\Sources;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use Yii;

/**
 * PatientController implements the CRUD actions for Patient model.
 */
class PatientController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Patient models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PatientSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Patient model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
 * Creates a new Patient and its related models (Tumour, Treatments, Sources, FollowUp).
 * If creation is successful, the browser will be redirected to the 'view' page.
 * @return string|\yii\web\Response
 */
public function actionCreate()
{
    $this->layout = 'questionnaire';

    

    $model = new Patient();
    $modelTumour = new Tumour();
    $modelTumour->full_tnm = 0;  // default checkbox value
    $modelFollowUp = new FollowUp();

    // Will be populated from POST data on validation error
    $modelTreatments = [];
    $modelSources = [];

    if ($this->request->isPost && !$this->request->isAjax) {
        $post = $this->request->post();

        // Load Patient
        $model->load($post);

        // Load Tumour
        $modelTumour->load($post);

        // Load FollowUp
        $modelFollowUp->load($post);

        // Extract Treatments data
        $treatmentRaw = $post['Treatment'] ?? [];
        $concurrentIllness = null;
        if (isset($treatmentRaw['concurrent_illness'])) {
            $concurrentIllness = $treatmentRaw['concurrent_illness'];
            unset($treatmentRaw['concurrent_illness']);
        }

        // Build array of Treatment models from numeric indices
        $treatmentRows = [];
        foreach ($treatmentRaw as $idx => $rowData) {
            if (!is_numeric($idx)) continue; // safety
            if (empty($rowData['treatment'])) continue; // skip empty rows
            $treatment = new Treatment();
            $treatment->load($rowData, '');
            $treatmentRows[] = $treatment;
        }

        // Extract Sources data (supports multiple rows)
        $sourcesRaw = $post['Sources'] ?? [];
        $sourceRows = [];
        foreach ($sourcesRaw as $idx => $srcData) {
            if (!is_numeric($idx)) continue;
            if (empty($srcData['source_type']) && empty($srcData['source_no'])) continue; // skip empty
            $source = new Sources();
            $source->load($srcData, '');
            $sourceRows[] = $source;
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            // 1. Save Patient (provides id for foreign keys)
            if (!$model->save()) {
                 throw new \Exception('Patient errors: ' . json_encode($model->getErrors()));
            }
            // 2. Save Tumour
            $modelTumour->patient_id = $model->id;
            if (!$modelTumour->save()) {
                throw new \Exception('Tumour errors: ' . json_encode($modelTumour->getErrors()));
            }

            // 3. Save Treatments (multiple rows)
            $firstTreatment = true;
            foreach ($treatmentRows as $treatment) {
                $treatment->patient_id = $model->id;
                if ($firstTreatment && $concurrentIllness !== null) {
                    $treatment->concurrent_illness = $concurrentIllness;
                    $firstTreatment = false;
                }
                if (!$treatment->save()) {
                    throw new \Exception('Treatment errors: ' . json_encode($treatment->getErrors()));
                }
            }
            // Edge case: concurrent_illness provided but no treatment rows
            if ($firstTreatment && $concurrentIllness !== null) {
                $treatment = new Treatment();
                $treatment->patient_id = $model->id;
                $treatment->concurrent_illness = $concurrentIllness;
                if (!$treatment->save()) {
                    throw new \Exception('Treatment errors: ' . json_encode($treatment->getErrors()) . ' (concurrent illness only)');
                }
            }

            // 4. Save Sources (multiple rows)
            foreach ($sourceRows as $source) {
                $source->patient_id = $model->id;
                if (!$source->save()) {
                    throw new \Exception('Sources errors: ' . json_encode($source->getErrors()));
                }
            }

            // 5. Save FollowUp
            $modelFollowUp->patient_id = $model->id;
            if (!$modelFollowUp->save()) {
                throw new \Exception('FollowUp errors: ' . json_encode($modelFollowUp->getErrors()));
            }

            $transaction->commit();
            return $this->redirect(['view', 'id' => $model->id]);

        } catch (\Exception $e) {
            $transaction->rollBack();

            // Collect all validation errors from each model and attach them to the Patient model
            // so they appear in the standard error summary.
            $errorModels = [
                'Patient' => $model,
                'Tumour' => $modelTumour,
                'FollowUp' => $modelFollowUp,
            ];
            foreach ($treatmentRows as $i => $t) {
                $errorModels["Treatment #$i"] = $t;
            }
            foreach ($sourceRows as $i => $s) {
                $errorModels["Source #$i"] = $s;
            }

            foreach ($errorModels as $name => $obj) {
                foreach ($obj->getErrors() as $attr => $errors) {
                    foreach ($errors as $err) {
                        $model->addError('_form', "[$name] $attr: $err");
                    }
                }
            }

            // Repopulate the treatment and source models for the view (so submitted values persist)
            $modelTreatments = $treatmentRows;
            $modelSources = $sourceRows;

            // If no treatment rows were created, provide at least one empty treatment model
            // to match the view's initial expectation (prevents JavaScript errors).
            if (empty($modelTreatments)) {
                $modelTreatments = [new Treatment()];
            }
            // If no source rows, provide at least one empty source model
            if (empty($modelSources)) {
                $modelSources = [new Sources()];
            }
        }
    }

    // Render the form (either initial load or after validation errors)
    return $this->render('create', [
        'model' => $model,
        'modelTumour' => $modelTumour,
        'modelTreatments' => $modelTreatments,
        'modelSources' => $modelSources,
        'modelFollowUp' => $modelFollowUp,
    ]);
}

/**
 * Updates an existing Patient and its related models (Tumour, Treatments, Sources, FollowUp).
 * If update is successful, the browser will be redirected to the 'view' page.
 * @param int $id Patient ID
 * @return string|\yii\web\Response
 * @throws NotFoundHttpException if the patient cannot be found
 */
public function actionUpdate($id)
{
    $this->layout = 'questionnaire';

    $model = $this->findModel($id);

    // Load related models that have exactly one record per patient
    $modelTumour = Tumour::findOne(['patient_id' => $id]) ?: new Tumour();
    $modelTumour->full_tnm = $modelTumour->full_tnm ?? 0;

    $modelFollowUp = FollowUp::findOne(['patient_id' => $id]) ?: new FollowUp();

    // Load multiple related models
    $modelTreatments = Treatment::find()->where(['patient_id' => $id])->all();
    if (empty($modelTreatments)) {
        $modelTreatments = [new Treatment()];
    }

    $modelSources = Sources::find()->where(['patient_id' => $id])->all();
    if (empty($modelSources)) {
        $modelSources = [new Sources()];
    }

    if ($this->request->isPost && !$this->request->isAjax) {
        $post = $this->request->post();

        // Load Patient, Tumour, FollowUp
        $model->load($post);
        $modelTumour->load($post);
        $modelFollowUp->load($post);

        // Extract Treatments data (same structure as create)
        $treatmentRaw = $post['Treatment'] ?? [];
        $concurrentIllness = null;
        if (isset($treatmentRaw['concurrent_illness'])) {
            $concurrentIllness = $treatmentRaw['concurrent_illness'];
            unset($treatmentRaw['concurrent_illness']);
        }

        // Build an array of treatment models from POST
        $newTreatments = [];
        foreach ($treatmentRaw as $idx => $rowData) {
            if (!is_numeric($idx)) continue;
            // Skip completely empty rows (no treatment and no status/date)
            if (empty($rowData['treatment']) && empty($rowData['treatment_status']) && empty($rowData['treatment_date'])) {
                continue;
            }
            // Determine if this is an existing record (has id) or new
            $treatmentId = $rowData['id'] ?? null;
            if ($treatmentId && ($existing = Treatment::findOne(['id' => $treatmentId, 'patient_id' => $id]))) {
                $treatment = $existing;
            } else {
                $treatment = new Treatment();
            }
            $treatment->load($rowData, '');
            $newTreatments[] = $treatment;
        }

        // Extract Sources data
        $sourcesRaw = $post['Sources'] ?? [];
        $newSources = [];
        foreach ($sourcesRaw as $idx => $srcData) {
            if (!is_numeric($idx)) continue;
            if (empty($srcData['source_type']) && empty($srcData['source_no']) && empty($srcData['source_date'])) {
                continue;
            }
            $sourceId = $srcData['id'] ?? null;
            if ($sourceId && ($existing = Sources::findOne(['id' => $sourceId, 'patient_id' => $id]))) {
                $source = $existing;
            } else {
                $source = new Sources();
            }
            $source->load($srcData, '');
            $newSources[] = $source;
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            // 1. Update Patient
            if (!$model->save()) {
                throw new \Exception('Patient validation failed');
            }

            // 2. Update Tumour
            $modelTumour->patient_id = $model->id;
            if (!$modelTumour->save()) {
                throw new \Exception('Tumour validation failed');
            }

            // 3. Synchronize Treatments
            // Mark for deletion: existing records not present in $newTreatments
            $existingTreatmentIds = array_map(function($t) { return $t->id; }, $modelTreatments);
            $newTreatmentIds = array_filter(array_map(function($t) { return $t->id; }, $newTreatments));
            $toDelete = array_diff($existingTreatmentIds, $newTreatmentIds);
            if (!empty($toDelete)) {
                Treatment::deleteAll(['id' => $toDelete]);
            }

            $firstSaved = false;
            foreach ($newTreatments as $treatment) {
                $treatment->patient_id = $model->id;
                if (!$firstSaved && $concurrentIllness !== null) {
                    $treatment->concurrent_illness = $concurrentIllness;
                    $firstSaved = true;
                } else {
                    // Clear concurrent_illness for subsequent treatments (it belongs only to the first)
                    $treatment->concurrent_illness = null;
                }
                if (!$treatment->save()) {
                    throw new \Exception('Treatment validation failed');
                }
            }
            // If concurrent_illness exists but no treatments were saved, create a dummy treatment row
            if (!$firstSaved && $concurrentIllness !== null) {
                $dummy = new Treatment();
                $dummy->patient_id = $model->id;
                $dummy->concurrent_illness = $concurrentIllness;
                if (!$dummy->save()) {
                    throw new \Exception('Treatment (concurrent illness only) failed');
                }
            }

            // 4. Synchronize Sources
            $existingSourceIds = array_map(function($s) { return $s->id; }, $modelSources);
            $newSourceIds = array_filter(array_map(function($s) { return $s->id; }, $newSources));
            $toDeleteSources = array_diff($existingSourceIds, $newSourceIds);
            if (!empty($toDeleteSources)) {
                Sources::deleteAll(['id' => $toDeleteSources]);
            }

            foreach ($newSources as $source) {
                $source->patient_id = $model->id;
                if (!$source->save()) {
                    throw new \Exception('Sources validation failed');
                }
            }

            // 5. Update FollowUp
            $modelFollowUp->patient_id = $model->id;
            if (!$modelFollowUp->save()) {
                throw new \Exception('FollowUp validation failed');
            }

            $transaction->commit();
            return $this->redirect(['view', 'id' => $model->id]);

        } catch (\Exception $e) {
            $transaction->rollBack();

            // Collect all errors and attach to Patient model for error summary
            $errorModels = [
                'Patient' => $model,
                'Tumour' => $modelTumour,
                'FollowUp' => $modelFollowUp,
            ];
            foreach ($newTreatments as $i => $t) {
                $errorModels["Treatment #$i"] = $t;
            }
            foreach ($newSources as $i => $s) {
                $errorModels["Source #$i"] = $s;
            }

            foreach ($errorModels as $name => $obj) {
                foreach ($obj->getErrors() as $attr => $errors) {
                    foreach ($errors as $err) {
                        $model->addError('_form', "[$name] $attr: $err");
                    }
                }
            }

            // Reassign the models to the view with submitted data (preserve input)
            $modelTreatments = $newTreatments;
            $modelSources = $newSources;

            if (empty($modelTreatments)) {
                $modelTreatments = [new Treatment()];
            }
            if (empty($modelSources)) {
                $modelSources = [new Sources()];
            }
        }
    }

    return $this->render('update', [
        'model' => $model,
        'modelTumour' => $modelTumour,
        'modelTreatments' => $modelTreatments,
        'modelSources' => $modelSources,
        'modelFollowUp' => $modelFollowUp,
    ]);
}
    /**
     * Deletes an existing Patient model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Patient model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Patient the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Patient::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
