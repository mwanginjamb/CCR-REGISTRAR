<?php

use yii\helpers\Html;

/**
 * QA flagged a case: sent to the registrar who created it.
 * Identifies the case by ID only; patient details stay behind the login.
 *
 * @var yii\web\View $this
 * @var app\models\PatientReview $review
 * @var app\models\Patient $patient
 * @var app\models\User $registrar
 * @var app\models\User $qaUser
 */

$caseId = sprintf('PT-%05d', $patient->id);
$link = Yii::$app->urlManager->createAbsoluteUrl(['patient/view', 'id' => $patient->id]);
?>
<div style="font-family: Arial, Helvetica, sans-serif; color: #191c1e; font-size: 14px; line-height: 1.5; max-width: 560px;">
    <p>Hello <?= Html::encode($registrar->username) ?>,</p>

    <p>
        <strong><?= Html::encode($qaUser->username) ?></strong> (QA) reviewed case
        <strong><?= Html::encode($caseId) ?></strong>, which you registered, and flagged it for correction.
    </p>

    <table cellpadding="0" cellspacing="0" style="width: 100%; border-left: 4px solid #ba1a1a; background: #ffdad6; margin: 16px 0;">
        <tr>
            <td style="padding: 12px 16px;">
                <p style="margin: 0 0 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #93000a;">
                    <?= Html::encode($review->getReasonLabel()) ?>
                </p>
                <p style="margin: 0; white-space: pre-line;"><?= Html::encode($review->note) ?></p>
            </td>
        </tr>
    </table>

    <p>Please correct the record, then use <strong>Resubmit to QA</strong> on the case page:</p>

    <p>
        <a href="<?= Html::encode($link) ?>"
           style="display: inline-block; padding: 10px 20px; background: #001a48; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold;">
            Open case <?= Html::encode($caseId) ?>
        </a>
    </p>

    <p style="color: #444651; font-size: 12px;">
        Reply to this email to reach <?= Html::encode($qaUser->username) ?> directly.
        You need to be logged in to <?= Html::encode(Yii::$app->name) ?> to open the case.
    </p>
</div>
