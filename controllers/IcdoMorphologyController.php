<?php

namespace app\controllers;

use app\components\SetupController;
use app\models\IcdoMorphology;
use app\models\IcdoMorphologySearch;
use yii\db\ActiveRecord;

/**
 * IcdoMorphologyController manages ICD-O morphology (histology) codes.
 */
class IcdoMorphologyController extends SetupController
{
    public string $modelClass = IcdoMorphology::class;
    public string $searchModelClass = IcdoMorphologySearch::class;
    public string $label = 'Morphology Code';

    protected function recordLabel(ActiveRecord $model): string
    {
        return "{$model->code} - {$model->description}";
    }
}
