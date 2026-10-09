<?php

namespace app\commands;

use app\models\IcdoTopography;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class LateralityController extends Controller
{
    /**
     * Seeds category-level ICD-O site rules.
     *
     * Usage:
     *   php yii laterality/seed-site-rules
     *   php yii laterality/seed-site-rules --dry-run=1
     *   php yii laterality/seed-site-rules --replace=1
     */
    public bool $dryRun = false;
    public bool $replace = false;

    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), [
            'dryRun',
            'replace',
        ]);
    }

    public function optionAliases(): array
    {
        return array_merge(parent::optionAliases(), [
            'd' => 'dryRun',
            'r' => 'replace',
        ]);
    }

    public function actionSeedSiteRules(): int
    {
        /*
         * Category-level laterality rules.
         *
         * Keys are ICD-O topography categories without the subsite digit.
         * Values:
         *   required = a laterality value must be captured
         *   allowed  = laterality is clinically applicable
         *
         * This is intentionally conservative. Categories whose applicability
         * varies by subsite should be handled later with exact-code overrides.
         */
        $lateralityRules = [
            'C07' => ['required' => true, 'allowed' => true],  // Parotid gland
            'C08' => ['required' => true, 'allowed' => true],  // Other major salivary glands
            'C09' => ['required' => true, 'allowed' => true],  // Tonsil
            'C30' => ['required' => true, 'allowed' => true],  // Nasal cavity and middle ear
            'C31' => ['required' => true, 'allowed' => true],  // Accessory sinuses
            'C34' => ['required' => true, 'allowed' => true],  // Bronchus and lung
            'C38' => ['required' => true, 'allowed' => true],  // Selected thoracic sites
            'C40' => ['required' => true, 'allowed' => true],  // Bones of limbs
            'C41' => ['required' => true, 'allowed' => true],  // Selected bone sites
            'C44' => ['required' => true, 'allowed' => true],  // Skin
            'C47' => ['required' => true, 'allowed' => true],  // Peripheral nerves
            'C49' => ['required' => true, 'allowed' => true],  // Connective/soft tissue
            'C50' => ['required' => true, 'allowed' => true],  // Breast
            'C56' => ['required' => true, 'allowed' => true],  // Ovary
            'C57' => ['required' => true, 'allowed' => true],  // Other female genital organs
            'C62' => ['required' => true, 'allowed' => true],  // Testis
            'C63' => ['required' => true, 'allowed' => true],  // Other male genital organs
            'C64' => ['required' => true, 'allowed' => true],  // Kidney
            'C65' => ['required' => true, 'allowed' => true],  // Renal pelvis
            'C66' => ['required' => true, 'allowed' => true],  // Ureter
            'C69' => ['required' => true, 'allowed' => true],  // Eye
            'C74' => ['required' => true, 'allowed' => true],  // Adrenal gland
        ];

        /*
         * Read the categories that are actually present in the imported
         * ICD-O topography table.
         */
        $codes = IcdoTopography::find()
            ->select('code')
            ->where(['is_active' => 1])
            ->orderBy(['code' => SORT_ASC])
            ->column();

        if (empty($codes)) {
            $this->stderr(
                "No active ICD-O topography records were found.\n",
                Console::FG_RED
            );

            return ExitCode::DATAERR;
        }

        $availableCategories = [];

        foreach ($codes as $code) {
            $code = strtoupper(trim((string) $code));

            if (!preg_match('/^C\d{2}(?:\.\d)?$/', $code)) {
                $this->stderr(
                    "Skipping invalid topography code: {$code}\n",
                    Console::FG_YELLOW
                );
                continue;
            }

            $availableCategories[substr($code, 0, 3)] = true;
        }

        if (empty($availableCategories)) {
            $this->stderr(
                "No valid ICD-O topography categories were found.\n",
                Console::FG_RED
            );

            return ExitCode::DATAERR;
        }

        $rows = [];
        $timestamp = time();

        foreach (array_keys($availableCategories) as $category) {
            $laterality = $lateralityRules[$category] ?? [
                'required' => false,
                'allowed' => false,
            ];

            $rows[] = [
                'topography_pattern' => $category . '.*',
                'laterality_required' => (int) $laterality['required'],
                'laterality_allowed' => (int) $laterality['allowed'],

                /*
                 * Leave these unknown until authoritative staging and
                 * grading rules are imported.
                 */
                'tnm_allowed' => null,
                'grade_allowed' => null,

                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'created_by' => null,
                'updated_by' => null,
            ];
        }

        usort(
            $rows,
            static fn(array $a, array $b): int =>
                strcmp(
                    $a['topography_pattern'],
                    $b['topography_pattern']
                )
        );

        $pairedCount = count(
            array_filter(
                $rows,
                static fn(array $row): bool =>
                    (bool) $row['laterality_allowed']
            )
        );

        $this->stdout(
            sprintf(
                "Prepared %d rules: %d laterality-enabled and %d not applicable.\n",
                count($rows),
                $pairedCount,
                count($rows) - $pairedCount
            ),
            Console::FG_CYAN
        );

        if ($this->dryRun) {
            $this->stdout(
                "Dry-run mode enabled. No database changes were made.\n",
                Console::FG_YELLOW
            );

            foreach ($rows as $row) {
                $status = $row['laterality_allowed']
                    ? 'laterality required'
                    : 'laterality not applicable';

                $this->stdout(
                    sprintf(
                        "%-6s %s\n",
                        $row['topography_pattern'],
                        $status
                    )
                );
            }

            return ExitCode::OK;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if ($this->replace) {
                Yii::$app->db
                    ->createCommand()
                    ->delete('{{%icdo_site_rule}}')
                    ->execute();

                $this->stdout(
                    "Existing site rules removed.\n",
                    Console::FG_YELLOW
                );
            }

            $inserted = 0;
            $updated = 0;

            foreach ($rows as $row) {
                $exists = Yii::$app->db
                    ->createCommand(
                        'SELECT 1
                         FROM {{%icdo_site_rule}}
                         WHERE topography_pattern = :pattern'
                    )
                    ->bindValue(
                        ':pattern',
                        $row['topography_pattern']
                    )
                    ->queryScalar();

                Yii::$app->db->createCommand()->upsert(
                    '{{%icdo_site_rule}}',
                    $row,
                    [
                        'laterality_required' =>
                            $row['laterality_required'],
                        'laterality_allowed' =>
                            $row['laterality_allowed'],

                        /*
                         * Do not overwrite manually curated TNM or grade
                         * values during subsequent runs.
                         */
                        'updated_at' => $timestamp,
                        'updated_by' => null,
                    ]
                )->execute();

                if ($exists) {
                    $updated++;
                } else {
                    $inserted++;
                }
            }

            $transaction->commit();

            $this->stdout(
                sprintf(
                    "Site-rule seed completed: %d inserted, %d updated, %d total.\n",
                    $inserted,
                    $updated,
                    count($rows)
                ),
                Console::FG_GREEN
            );

            return ExitCode::OK;
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            $this->stderr(
                "Site-rule seed failed: {$exception->getMessage()}\n",
                Console::FG_RED
            );

            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
}