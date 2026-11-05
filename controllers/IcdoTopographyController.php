<?php

namespace app\controllers;

use app\components\SetupController;
use app\models\IcdoTopography;
use app\models\IcdoTopographySearch;
use yii\db\ActiveRecord;

/**
 * IcdoTopographyController manages ICD-O topography (primary site) codes.
 */
class IcdoTopographyController extends SetupController
{
    public string $modelClass = IcdoTopography::class;
    public string $searchModelClass = IcdoTopographySearch::class;
    public string $label = 'Topography Code';

    protected function recordLabel(ActiveRecord $model): string
    {
        return "{$model->code} - {$model->description}";
    }
}
