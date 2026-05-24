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
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['POST'],
                ],
            ],
        ]);
    }
    
    // Parse JSON request body before action runs
    public function beforeAction($action)
    {

        if ($action->id === 'create') {
            $this->enableCsrfValidation = false;
        }
        if (parent::beforeAction($action)) {
            if (Yii::$app->request->getContentType() === 'application/json') {
                $rawBody = Yii::$app->request->getRawBody();
                $data = json_decode($rawBody, true);
                if (is_array($data)) {
                    // Set the parsed data as body parameters
                    Yii::$app->request->setBodyParams($data);
                }
            }
            return true;
        }
       return parent::beforeAction($action);
    }

    public function actionCreate(): array
    {
        // Now bodyParams will contain the JSON data
        $body = Yii::$app->request->bodyParams;
        
        // Debug logging
        Yii::info('API Payload: ' . json_encode($body), 'api-debug');
        
        if (empty($body)) {
            Yii::$app->response->statusCode = 400;
            return ['error' => 'No data received', 'raw' => Yii::$app->request->getRawBody()];
        }
        
        $db = Yii::$app->db;
        $tx = $db->beginTransaction();

        try {
            // 1. Patient
            $patient = new Patient();
            $patientData = $body['Patient'] ?? [];
            
            // Load patient data directly
            if (!empty($patientData)) {
                foreach ($patientData as $key => $value) {
                    if ($patient->hasAttribute($key)) {
                        $patient->$key = $value;
                    }
                }
            }
            
            // Handle geo data from _geo
            if (isset($body['_geo']) && is_array($body['_geo'])) {
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
                return [
                    'model' => 'Patient',
                    'errors' => $patient->getErrors(),
                    'attributes' => $patient->getAttributes()
                ];
            }

            // 2. Tumour
            $tumour = new Tumour();
            $tumour->patient_id = $patient->id;
            $tumourData = $body['Tumour'] ?? [];
            if (!empty($tumourData)) {
                foreach ($tumourData as $key => $value) {
                    if ($tumour->hasAttribute($key)) {
                        $tumour->$key = $value;
                    }
                }
            }
            
            if (!$tumour->save()) {
                $tx->rollBack();
                Yii::$app->response->statusCode = 422;
                return ['model' => 'Tumour', 'errors' => $tumour->getErrors()];
            }

            // 3. Treatments
            $treatmentRows = $body['Treatment'] ?? [];
            $concurrentIllness = $body['concurrent_illness'] ?? null;
            
            foreach ($treatmentRows as $rowData) {
                if (empty($rowData['treatment']) && empty($rowData['treatment_status'])) {
                    continue;
                }
                
                $treatment = new Treatment();
                $treatment->patient_id = $patient->id;
                $treatment->concurrent_illness = $concurrentIllness;
                foreach ($rowData as $key => $value) {
                    if ($treatment->hasAttribute($key)) {
                        $treatment->$key = $value;
                    }
                }
                
                if (!$treatment->save()) {
                    $tx->rollBack();
                    Yii::$app->response->statusCode = 422;
                    return ['model' => 'Treatment', 'errors' => $treatment->getErrors()];
                }
                
                $concurrentIllness = null;
            }

            // 4. Sources
            $sourceRows = $body['Sources'] ?? [];
            foreach ($sourceRows as $rowData) {
                if (empty($rowData['source_type']) && empty($rowData['source_no'])) {
                    continue;
                }
                
                $source = new Sources();
                $source->patient_id = $patient->id;
                foreach ($rowData as $key => $value) {
                    if ($source->hasAttribute($key)) {
                        $source->$key = $value;
                    }
                }
                
                if (!$source->save()) {
                    $tx->rollBack();
                    Yii::$app->response->statusCode = 422;
                    return ['model' => 'Sources', 'errors' => $source->getErrors()];
                }
            }

            // 5. FollowUp
            $followUp = new FollowUp();
            $followUp->patient_id = $patient->id;
            $followUpData = $body['FollowUp'] ?? [];
            if (!empty($followUpData)) {
                foreach ($followUpData as $key => $value) {
                    if ($followUp->hasAttribute($key)) {
                        $followUp->$key = $value;
                    }
                }
            }
            
            if (!$followUp->save()) {
                $tx->rollBack();
                Yii::$app->response->statusCode = 422;
                return ['model' => 'FollowUp', 'errors' => $followUp->getErrors()];
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
            Yii::error('API Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString(), 'api-error');
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Health check endpoint for connectivity testing
     */
    public function actionHealth()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        // Return minimal response for quick connectivity check
        return [
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0'
        ];
    }
}