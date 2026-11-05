<?php

namespace app\controllers;

use app\components\SetupController;
use app\models\SourceReference;
use app\models\SourceReferenceSearch;
use yii\db\ActiveRecord;

/**
 * SourceReferenceController manages the source setup list (hospitals, labs,
 * hospices) used for "Source No" suggestions on the patient form.
 *
 * Entries are deactivated rather than deleted: patient source rows store the
 * source number, so deleting an entry would lose its name on existing records.
 */
class SourceReferenceController extends SetupController
{
    public string $modelClass = SourceReference::class;
    public string $searchModelClass = SourceReferenceSearch::class;
    public string $label = 'Source';

    protected function recordLabel(ActiveRecord $model): string
    {
        return "{$model->source_no} - {$model->name}";
    }
}
