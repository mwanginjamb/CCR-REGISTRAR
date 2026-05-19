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



// create method
public function actionCreate(): array
{
    $body = Yii::$app->request->bodyParams;
    
    // Debug: Log what we received
    Yii::info('API Payload: ' . json_encode($body), 'api-debug');
    
    $db = Yii::$app->db;
    $tx = $db->beginTransaction();

    try {
        // 1. Patient - Use load() with empty form name
        $patient = new Patient();
        
        // Load patient data directly (no form name prefix)
        if (!empty($body['Patient'])) {
            $patient->load($body['Patient'], '');
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
        
        // Validate and save
        if (!$patient->save()) {
            $tx->rollBack();
            Yii::$app->response->statusCode = 422;
            return [
                'model' => 'Patient',
                'errors' => $patient->getErrors(),
                'attributes' => $patient->getAttributes() // Debug: see what was loaded
            ];
        }

        // 2. Tumour
        $tumour = new Tumour();
        $tumour->patient_id = $patient->id;
        if (!empty($body['Tumour'])) {
            $tumour->load($body['Tumour'], '');
        }
        
        if (!$tumour->save()) {
            $tx->rollBack();
            Yii::$app->response->statusCode = 422;
            return ['model' => 'Tumour', 'errors' => $tumour->getErrors()];
        }

        // 3. Treatments (array of rows)
        $treatmentRows = $body['Treatment'] ?? [];
        $concurrentIllness = $body['concurrent_illness'] ?? null;
        
        foreach ($treatmentRows as $rowData) {
            if (empty($rowData['treatment']) && empty($rowData['treatment_status'])) {
                continue;
            }
            
            $treatment = new Treatment();
            $treatment->patient_id = $patient->id;
            $treatment->concurrent_illness = $concurrentIllness;
            $treatment->load($rowData, '');
            
            if (!$treatment->save()) {
                $tx->rollBack();
                Yii::$app->response->statusCode = 422;
                return ['model' => 'Treatment', 'errors' => $treatment->getErrors()];
            }
            
            $concurrentIllness = null;
        }

        // 4. Sources (array of rows)
        $sourceRows = $body['Sources'] ?? [];
        foreach ($sourceRows as $rowData) {
            if (empty($rowData['source_type']) && empty($rowData['source_no'])) {
                continue;
            }
            
            $source = new Sources();
            $source->patient_id = $patient->id;
            $source->load($rowData, '');
            
            if (!$source->save()) {
                $tx->rollBack();
                Yii::$app->response->statusCode = 422;
                return ['model' => 'Sources', 'errors' => $source->getErrors()];
            }
        }

        // 5. FollowUp
        $followUp = new FollowUp();
        $followUp->patient_id = $patient->id;
        if (!empty($body['FollowUp'])) {
            $followUp->load($body['FollowUp'], '');
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
//End of create method

    
}