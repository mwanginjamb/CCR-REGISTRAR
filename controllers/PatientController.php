<?php

namespace app\controllers;

use app\models\FollowUp;
use app\models\IcdoMorphology;
use app\models\IcdoSiteMorphologyRule;
use app\models\IcdoSiteRule;
use app\models\IcdoTopography;
use app\models\Patient;
use app\models\PatientSearch;
use app\models\Sources;
use app\models\Treatment;
use app\models\Tumour;
use app\services\Lookup;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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

    public function beforeAction($action)
    {
        $ExceptedActions = [
            'morphology-options',
            'laterality-options',
            'morphology-behaviour',
        ];

        if (in_array($action->id, $ExceptedActions)) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
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
                if (!is_numeric($idx))
                    continue; // safety
                if (empty($rowData['treatment']))
                    continue; // skip empty rows
                $treatment = new Treatment();
                $treatment->load($rowData, '');
                $treatmentRows[] = $treatment;
            }

            // Extract Sources data (supports multiple rows)
            $sourcesRaw = $post['Sources'] ?? [];
            $sourceRows = [];
            foreach ($sourcesRaw as $idx => $srcData) {
                if (!is_numeric($idx))
                    continue;
                if (empty($srcData['source_type']) && empty($srcData['source_no']))
                    continue; // skip empty
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

        $topographies = $this->getTopographyOptions();

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
        $topographies = $this->getTopographyOptions();

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
                if (!is_numeric($idx))
                    continue;
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
                if (!is_numeric($idx))
                    continue;
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
                $existingTreatmentIds = array_map(function ($t) {
                    return $t->id;
                }, $modelTreatments);
                $newTreatmentIds = array_filter(array_map(function ($t) {
                    return $t->id;
                }, $newTreatments));
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
                $existingSourceIds = array_map(function ($s) {
                    return $s->id;
                }, $modelSources);
                $newSourceIds = array_filter(array_map(function ($s) {
                    return $s->id;
                }, $newSources));
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
            'topographies' => $this->getTopographyOptions(),
        ]);
    }


    private function getTopographyOptions(): array
    {
        return ArrayHelper::map(
            IcdoTopography::find()
                ->select(['code', 'description'])
                ->where(['is_active' => 1])
                ->orderBy(['code' => SORT_ASC])
                ->asArray()
                ->all(),
            'code',
            static function (array $row): string {
                return $row['code'] . ' - ' . $row['description'];
            }
        );
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


    public function actionMorphologyOptions($topographyCode = null)
    {
        $topographyCode = strtoupper(
            trim((string) $topographyCode)
        );

        if ($topographyCode === '') {
            echo '<option value="">Select Primary Site First</option>';
            return;
        }

        $siteExists = IcdoTopography::find()
            ->where([
                'code' => $topographyCode,
                'is_active' => 1,
            ])
            ->exists();

        if (!$siteExists) {
            echo '<option value="">Invalid Primary Site</option>';
            return;
        }

        $exactPattern = $topographyCode;
        $categoryPattern = substr($topographyCode, 0, 3) . '.*';

        $commonCodes = IcdoSiteMorphologyRule::find()
            ->select('morphology_code')
            ->where(['rule_type' => 'common'])
            ->andWhere([
                'or',
                ['topography_pattern' => $exactPattern],
                ['topography_pattern' => $categoryPattern],
            ])
            ->column();

        $commonCodes = array_map('strval', $commonCodes);

        $commonModels = [];

        if ($commonCodes !== []) {
            $commonModels = IcdoMorphology::find()
                ->where([
                    'code' => $commonCodes,
                    'is_active' => 1,
                ])
                ->orderBy(['code' => SORT_ASC])
                ->all();
        }

        $otherQuery = IcdoMorphology::find()
            ->where(['is_active' => 1])
            ->orderBy(['code' => SORT_ASC]);

        if ($commonCodes !== []) {
            $otherQuery->andWhere([
                'not in',
                'code',
                $commonCodes,
            ]);
        }

        $otherModels = $otherQuery->all();

        echo '<option value="">Select Histology</option>';

        if ($commonModels !== []) {
            echo '<optgroup label="Common for selected site">';

            foreach ($commonModels as $morphology) {
                $this->renderMorphologyOption($morphology);
            }

            echo '</optgroup>';
        }

        echo '<optgroup label="Other morphology codes">';

        foreach ($otherModels as $morphology) {
            $this->renderMorphologyOption($morphology);
        }

        echo '</optgroup>';
    }

    private function renderMorphologyOption(
        IcdoMorphology $morphology
    ): void {
        $code = htmlspecialchars(
            (string) $morphology->code,
            ENT_QUOTES,
            'UTF-8'
        );

        $description = htmlspecialchars(
            (string) $morphology->description,
            ENT_QUOTES,
            'UTF-8'
        );

        echo "<option value=\"{$code}\">";
        echo "{$code} - {$description}";
        echo '</option>';
    }

    // Laterality options for the form

    public function actionLateralityOptions($topographyCode = null)
    {
        if (empty($topographyCode)) {

            echo '<option value="">Select Primary Site First</option>';

            Yii::$app->end();
        }

        $site = IcdoTopography::findOne([
            'code' => $topographyCode
        ]);

        if (!$site) {

            echo '<option value="">Invalid Primary Site</option>';

            Yii::$app->end();
        }

        $pattern = substr($topographyCode, 0, 3) . '.*';

        $rule = IcdoSiteRule::find()
            ->where([
                'topography_pattern' => $pattern
            ])
            ->one();

        /*
         * Default to Not Applicable if no rule exists.
         */
        if (!$rule || !$rule->laterality_allowed) {

            echo '<option value="">Not Applicable</option>';

            Yii::$app->end();
        }

        $options = Lookup::fieldOptions('laterality');

        echo '<option value="">Select Laterality</option>';

        foreach ($options as $value => $label) {

            /*
             * Don't show N/A because site
             * requires laterality selection.
             */
            if (stripos($label, 'Not Applicable') !== false) {
                continue;
            }

            echo "<option value='{$value}'>{$label}</option>";
        }

        Yii::$app->end();
    }

    // Morphology Behavior lookup.

    public function actionMorphologyBehaviour($code = null)
    {
        $morphology = IcdoMorphology::findOne([
            'code' => $code
        ]);

        if (!$morphology) {

            echo '';

            Yii::$app->end();
        }

        $behaviour = Lookup::fieldOptions('behaviour');

        $value = null;

        foreach ($behaviour as $optionValue => $label) {

            if ((string) $optionValue === (string) $morphology->behaviour_code) {

                $value = $optionValue;
                break;
            }
        }

        echo $value;

        Yii::$app->end();
    }


}
