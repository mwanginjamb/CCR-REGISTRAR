<?php
namespace app\controllers;

use Yii;
use yii\rest\Controller;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\Response;
use app\models\Patient;
use app\models\Tumour;
use app\models\Treatment;
use app\models\Sources;
use app\models\FollowUp;

class PatientApiController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => ['application/json' => Response::FORMAT_JSON],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['create' => ['POST']],
            ],
        ]);
    }

    public function actionCreate(): array
    {
        $body = Yii::$app->request->bodyParams;

        $db = Yii::$app->db;
        $tx = $db->beginTransaction();           // all-or-nothing across models

        try {
            // ── 1. Patient ────────────────────────────────────────────────────
            $patient = new Patient();
            $patient->load($body['Patient'] ?? [], '');

            if (!empty($body['_geo'])) {
                $patient->geo_lat = $body['_geo']['lat'] ?? null;
                $patient->geo_lng = $body['_geo']['lng'] ?? null;
                $patient->geo_accuracy = $body['_geo']['accuracy'] ?? null;
                $patient->geo_captured = $body['_geo']['captured_at'] ?? null;
                if ($patient->geo_captured) {
                    $patient->geo_captured = str_replace(['T', 'Z'], [' ', ''], $patient->geo_captured);
                }
            }

            if (!$patient->save()) {
                $tx->rollBack();
                Yii::$app->response->statusCode = 422;
                return ['model' => 'Patient', 'errors' => $patient->errors];
            }

            // ── 2. Tumour ─────────────────────────────────────────────────────
            $tumour = new Tumour();
            $tumour->patient_id = $patient->id;
            $tumour->load($body['Tumour'] ?? [], '');

            if (!$tumour->save()) {
                $tx->rollBack();
                Yii::$app->response->statusCode = 422;
                return ['model' => 'Tumour', 'errors' => $tumour->errors];
            }

            // ── 3. Treatments (array of rows) ─────────────────────────────────
            $treatmentRows = $body['Treatment'] ?? [];

            // Separate concurrent_illness (scalar) from treatment rows (array)
            // concurrent_illness is a field on Treatment model but not a row itself
            $concurrentIllness = null;
            if (isset($treatmentRows['concurrent_illness'])) {
                $concurrentIllness = $treatmentRows['concurrent_illness'];
                unset($treatmentRows['concurrent_illness']);
            }

            foreach ($treatmentRows as $rowData) {
                if (empty($rowData['treatment_type']))
                    continue; // skip blank rows

                $treatment = new Treatment();
                $treatment->patient_id = $patient->id;
                $treatment->concurrent_illness = $concurrentIllness;
                $treatment->load($rowData, '');

                if (!$treatment->save()) {
                    $tx->rollBack();
                    Yii::$app->response->statusCode = 422;
                    return ['model' => 'Treatment', 'errors' => $treatment->errors];
                }

                // Only persist concurrent_illness on the first treatment row
                $concurrentIllness = null;
            }

            // Edge case: concurrent_illness filled but zero treatment rows added
            if ($concurrentIllness !== null) {
                $treatment = new Treatment();
                $treatment->patient_id = $patient->id;
                $treatment->concurrent_illness = $concurrentIllness;

                if (!$treatment->save()) {
                    $tx->rollBack();
                    Yii::$app->response->statusCode = 422;
                    return ['model' => 'Treatment', 'errors' => $treatment->errors];
                }
            }

            // ── 4. Sources ────────────────────────────────────────────────────
            $sources = new Sources();
            $sources->patient_id = $patient->id;
            $sources->load($body['Sources'] ?? [], '');

            if (!$sources->save()) {
                $tx->rollBack();
                Yii::$app->response->statusCode = 422;
                return ['model' => 'Sources', 'errors' => $sources->errors];
            }

            // ── 5. Follow-up ──────────────────────────────────────────────────
            $followUp = new FollowUp();
            $followUp->patient_id = $patient->id;
            $followUp->load($body['FollowUp'] ?? [], '');

            if (!$followUp->save()) {
                $tx->rollBack();
                Yii::$app->response->statusCode = 422;
                return ['model' => 'FollowUp', 'errors' => $followUp->errors];
            }

            $tx->commit();

            return [
                'id' => $patient->id,
                'tumour_id' => $tumour->id,
                'status' => 'synced',
                'geo' => [
                    'lat' => $patient->geo_lat,
                    'lng' => $patient->geo_lng,
                ],
            ];

        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::$app->response->statusCode = 500;
            return ['error' => $e->getMessage()];
        }
    }
}