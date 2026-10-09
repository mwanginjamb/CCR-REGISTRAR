<?php

namespace app\commands;

use app\models\IcdoMorphology;
use app\models\IcdoTopography;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

use Yii;

class MorphologyController extends Controller
{
    /**
     * Seeds common site-morphology combinations for testing and
     * assisted data entry.
     *
     * These rules are suggestions, not a complete clinical allow-list.
     *
     * Usage:
     *   ./yii morphology/seed-site-morphology-rules --dry-run=1
     *   ./yii morphology/seed-site-morphology-rules
     *   ./yii morphology/seed-site-morphology-rules --replace=1
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



    public function actionSeedSiteMorphologyRules(): int
    {
        $sourceVersion = 'starter-common-v1';

        /*
         * Each morphology is linked only when both the topography category
         * and morphology code exist in the imported ICD-O reference data.
         *
         * rule_type = common means:
         *   - show near the top of dependent dropdowns
         *   - use for suggestions
         *   - do not reject unlisted combinations
         */
        $starterRules = [
            // Oral cavity and related sites
            'C00.*' => ['8070', '8071', '8072', '8140'],
            'C01.*' => ['8070', '8071', '8072'],
            'C02.*' => ['8070', '8071', '8072'],
            'C03.*' => ['8070', '8071', '8072'],
            'C04.*' => ['8070', '8071', '8072'],
            'C05.*' => ['8070', '8071', '8072'],
            'C06.*' => ['8070', '8071', '8072'],

            // Major salivary glands
            'C07.*' => ['8140', '8200', '8430', '8550', '8562'],
            'C08.*' => ['8140', '8200', '8430', '8550', '8562'],

            // Pharynx and larynx
            'C09.*' => ['8070', '8071', '8072'],
            'C10.*' => ['8070', '8071', '8072'],
            'C11.*' => ['8070', '8071', '8072', '8082'],
            'C12.*' => ['8070', '8071', '8072'],
            'C13.*' => ['8070', '8071', '8072'],
            'C14.*' => ['8070', '8071', '8072'],
            'C32.*' => ['8070', '8071', '8072'],

            // Oesophagus
            'C15.*' => ['8070', '8071', '8072', '8140'],

            // Stomach
            'C16.*' => ['8140', '8144', '8480', '8490'],

            // Small intestine
            'C17.*' => ['8140', '8240', '8246', '8480'],

            // Colon and rectum
            'C18.*' => ['8140', '8210', '8261', '8263', '8480', '8481', '8490'],
            'C19.*' => ['8140', '8210', '8261', '8263', '8480', '8481'],
            'C20.*' => ['8140', '8210', '8261', '8263', '8480', '8070'],
            'C21.*' => ['8070', '8071', '8140'],

            // Liver and biliary tract
            'C22.*' => ['8170', '8160', '8140'],
            'C23.*' => ['8140', '8160'],
            'C24.*' => ['8140', '8160'],

            // Pancreas
            'C25.*' => ['8140', '8500', '8453'],

            // Nasal cavity, sinuses and middle ear
            'C30.*' => ['8070', '8140', '8200'],
            'C31.*' => ['8070', '8140', '8200'],

            // Lung and bronchus
            'C34.*' => [
                '8041',
                '8042',
                '8045',
                '8070',
                '8071',
                '8140',
                '8250',
                '8255',
                '8260',
                '8480',
            ],

            // Bone
            'C40.*' => ['9180', '9181', '9192', '9220', '9260'],
            'C41.*' => ['9180', '9181', '9192', '9220', '9260'],

            // Skin melanoma
            'C44.*' => ['8720', '8721', '8730', '8742', '8743'],

            // Soft tissue
            'C47.*' => ['9540', '9560', '9571'],
            'C49.*' => ['8800', '8810', '8850', '8890', '9120', '9540'],

            // Breast
            'C50.*' => [
                '8140',
                '8201',
                '8211',
                '8401',
                '8480',
                '8500',
                '8501',
                '8503',
                '8510',
                '8520',
                '8521',
                '8522',
                '8523',
                '8530',
                '8540',
                '8541',
                '8543',
            ],

            // Vulva and vagina
            'C51.*' => ['8070', '8071', '8072', '8140', '8720'],
            'C52.*' => ['8070', '8071', '8072', '8140'],

            // Cervix
            'C53.*' => [
                '8070',
                '8071',
                '8072',
                '8077',
                '8083',
                '8140',
                '8260',
                '8380',
                '8480',
            ],

            // Corpus uterus
            'C54.*' => ['8140', '8260', '8380', '8382', '8480', '8890'],
            'C55.*' => ['8140', '8380', '8890'],

            // Ovary
            'C56.*' => [
                '8140',
                '8260',
                '8380',
                '8441',
                '8460',
                '8461',
                '8470',
                '8480',
                '8490',
                '8590',
                '8620',
                '9060',
            ],

            // Prostate
            'C61.*' => ['8140', '8141', '8255'],

            // Testis
            'C62.*' => [
                '9061',
                '9064',
                '9070',
                '9071',
                '9080',
                '9081',
                '9100',
            ],

            // Kidney and urinary tract
            'C64.*' => ['8260', '8310', '8312', '8317', '8318'],
            'C65.*' => ['8120', '8130'],
            'C66.*' => ['8120', '8130'],
            'C67.*' => ['8120', '8122', '8130', '8140'],

            // Eye
            'C69.*' => ['8720', '8770', '9510', '9511', '9512', '9513'],

            // Brain and central nervous system
            'C70.*' => ['9530', '9531', '9539'],
            'C71.*' => [
                '9380',
                '9381',
                '9382',
                '9400',
                '9401',
                '9440',
                '9441',
                '9450',
                '9451',
                '9470',
                '9471',
                '9473',
            ],
            'C72.*' => ['9380', '9500', '9560'],

            // Thyroid
            'C73.*' => ['8020', '8140', '8290', '8330', '8335', '8340', '8341'],

            // Adrenal gland
            'C74.*' => ['8370', '8700', '9490', '9500'],

            // Other endocrine glands
            'C75.*' => ['8140', '8270', '8272', '9360'],

            // Unknown primary
            'C80.*' => ['8000', '8010', '8020', '8070', '8140'],
        ];

        /*
         * Read valid imported references.
         */
        $validTopographyCategories = [];

        foreach (
            IcdoTopography::find()
                ->select('code')
                ->where(['is_active' => 1])
                ->column() as $code
        ) {
            $code = strtoupper(trim((string) $code));

            if (preg_match('/^C\d{2}(?:\.\d)?$/', $code)) {
                $validTopographyCategories[substr($code, 0, 3)] = true;
            }
        }

        $validMorphologies = array_fill_keys(
            array_map(
                'strval',
                IcdoMorphology::find()
                    ->select('code')
                    ->where(['is_active' => 1])
                    ->column()
            ),
            true
        );

        if ($validTopographyCategories === []) {
            $this->stderr(
                "No valid active topography codes were found.\n",
                Console::FG_RED
            );

            return ExitCode::DATAERR;
        }

        if ($validMorphologies === []) {
            $this->stderr(
                "No active morphology codes were found.\n",
                Console::FG_RED
            );

            return ExitCode::DATAERR;
        }

        $rows = [];
        $skippedPatterns = [];
        $skippedMorphologies = [];

        foreach ($starterRules as $pattern => $morphologyCodes) {
            $category = substr($pattern, 0, 3);

            if (!isset($validTopographyCategories[$category])) {
                $skippedPatterns[] = $pattern;
                continue;
            }

            foreach (array_unique($morphologyCodes) as $morphologyCode) {
                $morphologyCode = (string) $morphologyCode;

                if (!isset($validMorphologies[$morphologyCode])) {
                    $skippedMorphologies[] = [
                        'pattern' => $pattern,
                        'code' => $morphologyCode,
                    ];
                    continue;
                }

                $rows[] = [
                    'topography_pattern' => $pattern,
                    'morphology_code' => $morphologyCode,
                    'rule_type' => 'common',
                    'source_version' => $sourceVersion,
                    'note' => 'Starter suggestion for testing and assisted entry; not an exhaustive validity rule.',
                ];
            }
        }

        usort(
            $rows,
            static function (array $left, array $right): int {
                return [
                    $left['topography_pattern'],
                    $left['morphology_code'],
                ] <=> [
                    $right['topography_pattern'],
                    $right['morphology_code'],
                ];
            }
        );

        $this->stdout(
            sprintf(
                "Prepared %d common site-morphology rules.\n",
                count($rows)
            ),
            Console::FG_CYAN
        );

        if ($skippedPatterns !== []) {
            $this->stdout(
                sprintf(
                    "Skipped %d patterns absent from imported topography.\n",
                    count(array_unique($skippedPatterns))
                ),
                Console::FG_YELLOW
            );
        }

        if ($skippedMorphologies !== []) {
            $this->stdout(
                sprintf(
                    "Skipped %d combinations whose morphology codes " .
                    "are absent from the imported dictionary:\n",
                    count($skippedMorphologies)
                ),
                Console::FG_YELLOW
            );

            foreach ($skippedMorphologies as $item) {
                $this->stdout(
                    sprintf(
                        "  %s -> %s\n",
                        $item['pattern'],
                        $item['code']
                    ),
                    Console::FG_YELLOW
                );
            }
        }

        if ($this->dryRun) {
            foreach ($rows as $row) {
                $this->stdout(
                    sprintf(
                        "%-6s -> %-5s [%s]\n",
                        $row['topography_pattern'],
                        $row['morphology_code'],
                        $row['rule_type']
                    )
                );
            }

            $this->stdout(
                "Dry-run completed. No database changes were made.\n",
                Console::FG_YELLOW
            );

            return ExitCode::OK;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if ($this->replace) {
                /*
                 * Delete only records owned by this seed source.
                 * Do not delete authoritative or manually curated rules.
                 */
                $deleted = Yii::$app->db
                    ->createCommand()
                    ->delete('{{%icdo_site_morphology_rule}}', [
                        'source_version' => $sourceVersion,
                    ])
                    ->execute();

                $this->stdout(
                    "Removed {$deleted} existing starter rules.\n",
                    Console::FG_YELLOW
                );
            }

            $inserted = 0;
            $updated = 0;

            foreach ($rows as $row) {
                $exists = Yii::$app->db
                    ->createCommand(
                        'SELECT 1
                     FROM {{%icdo_site_morphology_rule}}
                     WHERE topography_pattern = :pattern
                       AND morphology_code = :morphology
                       AND rule_type = :ruleType
                       AND source_version = :sourceVersion'
                    )
                    ->bindValues([
                        ':pattern' => $row['topography_pattern'],
                        ':morphology' => $row['morphology_code'],
                        ':ruleType' => $row['rule_type'],
                        ':sourceVersion' => $row['source_version'],
                    ])
                    ->queryScalar();

                Yii::$app->db
                    ->createCommand()
                    ->upsert(
                        '{{%icdo_site_morphology_rule}}',
                        $row,
                        [
                            'note' => $row['note'],
                        ]
                    )
                    ->execute();

                $exists ? $updated++ : $inserted++;
            }

            $transaction->commit();

            $this->stdout(
                sprintf(
                    "Seed completed: %d inserted, %d updated, %d total.\n",
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
                "Seed failed: {$exception->getMessage()}\n",
                Console::FG_RED
            );

            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
}