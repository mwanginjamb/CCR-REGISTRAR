<?php

use yii\db\Migration;

/**
 * Seeds {{%source_reference}} with the hospital list from the REDCap dictionary
 * (fields source_1_hosp / source_2_hosp, identical lists).
 *
 * The list is copied here rather than read from the imported dictionary so a fresh
 * database is seeded before any dictionary import. REDCap groups every entry under
 * "hospital", including labs, the hospice and the civil registrar; recategorise
 * entries (source_type 2 Lab, 3 Hospice) in the setup table as needed.
 */
class m261030_121447_seed_source_reference_hospitals extends Migration
{
    private const SOURCE_TYPE_HOSPITAL = 1;

    /**
     * [REDCap value, source no, name]
     */
    private const HOSPITALS = [
        ['1', '00', 'CIVIL REGISTRAR OF DEATH'],
        ['2', '01', 'KENYA MEDICAL RESEARCH INSTITUTE'],
        ['3', '02', 'KENYATTA NATIONAL HOSPITAL'],
        ['4', '03', 'THE NAIROBI HOSPITAL'],
        ['5', '04', 'MATER HOSPITAL'],
        ['6', '05', 'THE AGA KHAN HOSPITAL'],
        ['7', '06', 'M.P. SHAH HOSPITAL'],
        ['8', '07', 'MASABA HOSPITAL'],
        ['9', '08', 'AVENUE HOSPITAL'],
        ['10', '09', 'METROPOLITAN HOSPITAL'],
        ['11', '10', 'NAIROBI WEST HOSPITAL'],
        ['12', '11', 'NAIROBI HOSPICE'],
        ['13', '12', 'GERTRUDES GARDEN HOS'],
        ['14', '13', 'ZAMBEZI HOSPITAL'],
        ['15', '14', 'SPINAL INJURY HOSPITAL'],
        ['16', '15', 'ARMED FORCES MEMORIAL'],
        ['17', '16', 'MBAGATHI DISTRICT HOSPITAL'],
        ['18', '17', 'THIKA DISTRICT HOSPITAL'],
        ['19', '18', 'NYERI PROVINCIAL GENERAL HOSPITAL'],
        ['20', '19', 'FAMILY PLANNING CLINIC'],
        ['21', '20', 'EQUATOR NURSING HOME'],
        ['22', '21', 'SOUTH B. NURSING HOM'],
        ['23', '22', 'NAIROBI WOMENS HOSPITAL'],
        ['24', '23', 'MEDICAL IMAGING & T.C'],
        ['25', '24', 'MERU TEACHING AND REFERRAL'],
        ['26', '25', 'GURU NANAK HOSPIAL'],
        ['27', '26', 'GENESIS NURSING & MA'],
        ['28', '27', 'PRIME CARE HOSPITAL'],
        ['29', '28', 'ST. MARY\'S MISSION HOSPITAL'],
        ['30', '29', 'PCEA KIKUYU HOSPITAL'],
        ['31', '30', 'JAMAA HOSPITAL'],
        ['32', '31', 'UON - DENTAL SCHOOL'],
        ['33', '32', 'PRIME CARE HOSPITAL'],
        ['34', '33', 'KAREN HOSPITAL'],
        ['35', '34', 'LASER LABORATORY'],
        ['36', '35', 'STERLING LABORATORY'],
        ['37', '36', 'EASTLEIGH MEDICAL LABORATORY'],
        ['38', '37', 'RADIENT HEALTH NURSING HOME'],
        ['39', '38', 'MADINA NURSING HOME'],
        ['40', '39', 'KENYA DENTAL LABORATORY'],
        ['41', '40', 'MEDIPATH LABORATORIE'],
        ['42', '41', 'CLINICAL LABORATORY'],
        ['43', '42', 'NAIROBI CLINICAL LAB'],
        ['44', '43', 'NAIROBI DIAGNOSTIC L'],
        ['45', '44', 'HURLINGHAM ONCOLOGY'],
        ['46', '45', 'NAIROBI PATHOLOGY SE'],
        ['47', '46', 'SEARCH LABORATORIES'],
        ['48', '47', 'CITY NURSING HOME'],
        ['49', '48', 'HURUMA NURSING HOME'],
        ['50', '49', 'INDER NURSING HOME'],
        ['51', '50', 'MEDICAL DIAGNOSTIC L'],
        ['52', '51', 'ACACIA LABORATORY'],
        ['53', '52', 'COPTIC HOSPITAL'],
        ['54', '53', 'MATERNICARE HOSPITAL'],
        ['55', '54', 'NGONG-HILLS NURSING'],
        ['56', '55', 'NAZARETH HOSPITAL'],
        ['57', '56', 'MOI TEACHING & REFFERAL HOSPITAL'],
        ['58', '57', 'NEW NYANZA PROVINCIAL HOSPITAL'],
        ['59', '58', 'MARIAKANI COTTAGE HOSPITAL'],
        ['60', '59', 'CANCER CARE KENYA'],
        ['61', '60', 'DIAGNOTSIC AND MEDICAL LEGAL'],
        ['62', '61', 'IMMUNO-MOLLECULAR DIAGNOSTIC LAB'],
        ['63', '62', 'KNH/UON LABORATORIES'],
        ['64', '63', 'NAIROBI HOSPITAL LAB'],
        ['65', '64', 'MATER PATHOLOGY LAB'],
        ['66', '65', 'AGA KHAN LABORATORY'],
        ['67', '66', 'MP SHAH LABORATORY'],
        ['68', '67', 'KENYATTA UNIVERSITY TEACHING AND REFFERAL HOSPITAL'],
        ['69', '68', 'AVENUE LABORATORY'],
        ['70', '69', 'DR.MUNGANIA'],
        ['71', '70', 'GERMAN MEDICAL CENTER'],
        ['72', '71', 'HERCULES HEALTHCARE'],
        ['73', '72', 'PRIVATE LABS'],
        ['74', '73', 'TENWEK HOSPITAL'],
        ['75', '74', 'TENWEK HOSPITAL LAB'],
        ['76', '75', 'NAKURU PROVINCIAL GENERAL HOSPITAL'],
        ['77', '76', 'JANICE CHOLETRON MEDICAL & CANCER HOSPITAL'],
        ['78', '77', 'MEDANTA AFRICARE'],
        ['79', '78', 'JERAMOGI OGINGA ODINGA TEACHING AND REFERRAL HOSPITAL'],
        ['80', '79', 'CONSOLATE HOSPITAL KYENI'],
        ['81', '80', 'MATIBABU CLINIC LAB'],
        ['82', '81', 'NAIROBI SOUTH HOSPITAL'],
        ['83', '82', 'STRATUS MEDICAL IMAGING'],
        ['84', '83', 'MEDITEST DIAGNOSTIC SERVICES'],
        ['85', '84', 'MEDIHEAL HOSPITAL'],
        ['86', '85', 'SOUTHLANDS MEDICAL LAB'],
        ['87', '86', 'HISTOCYTE PATHOLOGY'],
        ['88', '87', 'NAIROBI RADIOTHERAPY CANCER CENTRE'],
        ['89', '88', 'MERIDIAN EQUATOR HOSPITAL'],
        ['90', '89', 'DR.RASIK KANTARIA JARALAM MEDICAL SERVICES'],
        ['91', '90', 'MEDITEST DIAGNOSTIC SERVICES LAB'],
        ['92', '91', 'PATHCARE LABORATORY'],
        ['93', '92', 'PHARMART HEALTH CARE'],
        ['94', '93', 'DR AK GACHII'],
        ['95', '94', 'LIFEPATH DIAGNOSTIC EA.LTD'],
        ['96', '95', 'AIC KIJABE'],
        ['97', '96', 'LANCET LABORATORY KENYA'],
        ['98', '97', 'PRIVATE DOCTOR'],
        ['99', '98', 'TEXAS CANCER CARE'],
        ['100', '99', 'UNKNOWN'],
        ['101', '100', 'OTHERS (SPECIFY)'],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $now = time();
        $rows = [];
        foreach (self::HOSPITALS as [$redcapValue, $sourceNo, $name]) {
            $rows[] = [self::SOURCE_TYPE_HOSPITAL, $sourceNo, $name, $redcapValue, 1, $now, $now];
        }

        $this->batchInsert(
            '{{%source_reference}}',
            ['source_type', 'source_no', 'name', 'redcap_value', 'is_active', 'created_at', 'updated_at'],
            $rows
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%source_reference}}', [
            'source_type' => self::SOURCE_TYPE_HOSPITAL,
            'source_no' => array_column(self::HOSPITALS, 1),
        ]);
    }
}
