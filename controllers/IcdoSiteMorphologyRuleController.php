<?php

namespace app\controllers;

use app\components\SetupController;
use app\models\IcdoSiteMorphologyRule;
use app\models\IcdoSiteMorphologyRuleSearch;
use yii\db\ActiveRecord;

/**
 * IcdoSiteMorphologyRuleController manages site–morphology rules
 * (which histologies are common or allowed for a topography).
 */
class IcdoSiteMorphologyRuleController extends SetupController
{
    public string $modelClass = IcdoSiteMorphologyRule::class;
    public string $searchModelClass = IcdoSiteMorphologyRuleSearch::class;
    public string $label = 'Site–Morphology Rule';

    protected function recordLabel(ActiveRecord $model): string
    {
        return "{$model->topography_pattern} / {$model->morphology_code} ({$model->rule_type})";
    }
}
