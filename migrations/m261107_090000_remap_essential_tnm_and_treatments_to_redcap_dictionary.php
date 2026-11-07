<?php

use yii\db\Migration;

/**
 * Aligns essential TNM and treatment codes with the REDCap dictionary, which the form
 * now reads their options from (see m261030_110011 for the other coded fields).
 *
 * Essential TNM: REDCap codes every answer 0 = No / 1 = Yes; the old lists used
 * 1 = absent / 2 = present. The CRF's single T answer (A (T3/T4) or L (T1/T2)) was split
 * over two columns; it now lives in localized_advanced (1 = A, 0 = L), and
 * localized_limited is dropped.
 *
 * Treatments: rows are keyed by the REDCap field of each therapy, and surgery codes
 * Unknown as 99 where the other therapies use 9.
 */
class m261107_090000_remap_essential_tnm_and_treatments_to_redcap_dictionary extends Migration
{
    /**
     * old code => new code, per table.column
     */
    private const MAPS = [
        'tumour.metastasis' => [
            1 => 0,    // M-  -> No, (M-)
            2 => 1,    // M+  -> Yes, M+ (M1)
        ],
        'tumour.regional_nodes_involvement' => [
            1 => 0,    // N-  -> No, (R-)
            2 => 1,    // N+  -> Yes, R+
        ],
    ];

    private const TREATMENT_TYPES = [
        'immunotherapy' => 'immuno',
        'radiation' => 'radiotherapy',
        'other' => 'other_therapy',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach (self::MAPS as $column => $map) {
            $this->remap($column, $map);
        }

        // Old localized_advanced (1 "T3", 2 "T4") and localized_limited (1 "T1", 2 "T2") both
        // answered the one T question. A row with both set keeps "advanced", the worse stage.
        $conflicts = (new \yii\db\Query())->from('{{%tumour}}')
            ->where(['localized_advanced' => [1, 2], 'localized_limited' => [1, 2]])
            ->count('*', $this->db);
        if ($conflicts > 0) {
            echo "    > WARNING: {$conflicts} tumour row(s) had both localized advanced and limited set; "
                . "kept A (T3/T4). Review them manually.\n";
        }

        $affected = $this->db->createCommand(<<<'SQL'
            UPDATE {{%tumour}}
            SET [[localized_advanced]] = CASE
                WHEN [[localized_advanced]] IN (1, 2) THEN 1
                WHEN [[localized_limited]] IN (1, 2) THEN 0
                ELSE NULL
            END
            WHERE [[localized_advanced]] IS NOT NULL OR [[localized_limited]] IS NOT NULL
            SQL)->execute();
        echo "    > merged localized_limited into tumour.localized_advanced: {$affected} row(s) changed\n";

        $this->dropColumn('{{%tumour}}', 'localized_limited');

        foreach (self::TREATMENT_TYPES as $old => $new) {
            $affected = $this->db->createCommand()
                ->update('{{%treatment}}', ['treatment' => $new], ['treatment' => $old])
                ->execute();
            echo "    > treatment '{$old}' -> '{$new}': {$affected} row(s)\n";
        }

        $affected = $this->db->createCommand()
            ->update('{{%treatment}}', ['treatment_status' => 99], ['treatment' => 'surgery', 'treatment_status' => 9])
            ->execute();
        echo "    > surgery Unknown 9 -> 99: {$affected} row(s)\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('{{%treatment}}', ['treatment_status' => 9], ['treatment' => 'surgery', 'treatment_status' => 99]);

        foreach (self::TREATMENT_TYPES as $old => $new) {
            $this->update('{{%treatment}}', ['treatment' => $old], ['treatment' => $new]);
        }
        // hormone_therapy had no old equivalent
        $this->update('{{%treatment}}', ['treatment' => 'other'], ['treatment' => 'hormone_therapy']);

        // The old columns split T3/T4 and T1/T2; the new code only says A or L, so restore
        // as T3 / T1 (lossy).
        $this->addColumn('{{%tumour}}', 'localized_limited', $this->integer()->after('localized_advanced'));
        // MySQL applies SET left to right, so localized_limited must read the old value first
        $this->db->createCommand(<<<'SQL'
            UPDATE {{%tumour}}
            SET [[localized_limited]] = CASE WHEN [[localized_advanced]] = 0 THEN 1 ELSE NULL END,
                [[localized_advanced]] = CASE WHEN [[localized_advanced]] = 1 THEN 1 ELSE NULL END
            WHERE [[localized_advanced]] IS NOT NULL
            SQL)->execute();

        foreach (self::MAPS as $column => $map) {
            $this->remap($column, array_flip($map));
        }
    }

    private function remap(string $column, array $map): void
    {
        [$table, $field] = explode('.', $column);

        $cases = [];
        $params = [];
        foreach ($map as $from => $to) {
            $cases[] = "WHEN :f{$from} THEN :t{$from}";
            $params[":f{$from}"] = $from;
            $params[":t{$from}"] = $to;
        }

        $sql = "UPDATE {{%{$table}}} SET [[{$field}]] = CASE [[{$field}]] "
            . implode(' ', $cases)
            . " END WHERE [[{$field}]] IN (" . implode(', ', array_map('intval', array_keys($map))) . ')';

        $affected = $this->db->createCommand($sql, $params)->execute();
        echo "    > remapped {$column}: {$affected} row(s)\n";
    }
}
