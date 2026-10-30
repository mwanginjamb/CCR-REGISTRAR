<?php

use yii\db\Migration;

/**
 * Remaps stored codes from the old hardcoded dropdown lists to the codes used by
 * the REDCap dictionary, which the form now reads its options from.
 *
 * Each column is remapped with a single CASE expression so overlapping codes
 * (e.g. ethnic_group 1 -> 21 while 21 is also a valid new code) are only applied once.
 */
class m261030_110011_remap_coded_fields_to_redcap_dictionary extends Migration
{
    /**
     * old code => new code, per table.column
     */
    private const MAPS = [
        'patient.ethnic_group' => [
            1 => 21,   // Luo        -> LUO
            2 => 16,   // Kikuyu     -> KIKUYU
            3 => 14,   // Kamba      -> KAMBA
            4 => 20,   // Luhya      -> LUHYA
            5 => 13,   // Kalenjin   -> KALENJIN
            6 => 25,   // Meru       -> MERU
            7 => 32,   // Samburu    -> SAMBURU
            8 => 22,   // Maasai     -> MAASAI
            9 => 15,   // Somali     -> KENYAN SOMALI
            10 => 35,  // Taita      -> TAITA
            11 => 2,   // Bajuni     -> BAJUNI
            12 => 26,  // Giriama    -> MIJIKENDA
            13 => 30,  // Rendille   -> RENDILE
            14 => 37,  // Teso       -> TESO
            15 => 19,  // Kuria      -> KURIA
            16 => 3,   // Borana     -> BORANA
            17 => 26,  // Digo       -> MIJIKENDA
            18 => 17,  // Gusii      -> KISII
            19 => 20,  // Tachoni    -> LUHYA
            20 => 48,  // Others     -> OTHER (SPECIFY)
        ],
        'patient.religion' => [
            3 => 8,    // Other -> Other (3 is now Hindu)
        ],
        'tumour.laterality' => [
            5 => 99,   // Unknown
        ],
        'tumour.stage' => [
            1 => 0,    // In Situ
            2 => 1,    // Stage I
            3 => 2,    // Stage II
            4 => 3,    // Stage III
            5 => 4,    // Stage IV
            6 => 99,   // Unknown
        ],
        'treatment.treatment_status' => [
            3 => 9,    // Unknown
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach (self::MAPS as $column => $map) {
            $this->remap($column, $map);
        }

        // Old grade 6 "metastatic" has no dictionary equivalent (6 is now B-cell)
        $orphanGrades = (new \yii\db\Query())->from('{{%tumour}}')->where(['grade' => 6])->count('*', $this->db);
        if ($orphanGrades > 0) {
            echo "    > WARNING: {$orphanGrades} tumour row(s) have grade = 6 (old \"metastatic\"), "
                . "which now reads as B-cell. Review them manually.\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach (self::MAPS as $column => $map) {
            // Several old codes can share a new code (e.g. Giriama/Digo -> MIJIKENDA);
            // reverting picks the first, so this direction is lossy.
            $inverse = [];
            foreach ($map as $old => $new) {
                $inverse[$new] ??= $old;
            }
            $this->remap($column, $inverse);
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
