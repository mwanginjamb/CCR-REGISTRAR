<?php

namespace app\controllers;

use app\components\SetupController;
use app\models\IcdoSiteRule;
use app\models\IcdoSiteRuleSearch;
use yii\db\ActiveRecord;

/**
 * IcdoSiteRuleController manages per-category site rules (laterality, TNM, grade).
 */
class IcdoSiteRuleController extends SetupController
{
    public string $modelClass = IcdoSiteRule::class;
    public string $searchModelClass = IcdoSiteRuleSearch::class;
    public string $label = 'Site Rule';

    protected function recordLabel(ActiveRecord $model): string
    {
        return (string) $model->topography_pattern;
    }
}
