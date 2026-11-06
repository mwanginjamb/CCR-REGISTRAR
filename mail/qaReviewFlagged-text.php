<?php

/**
 * QA flagged a case: plain-text version.
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
Hello <?= $registrar->username ?>,

<?= $qaUser->username ?> (QA) reviewed case <?= $caseId ?>, which you registered, and flagged it for correction.

Reason: <?= $review->getReasonLabel() ?>

<?= $review->note ?>


Please correct the record, then use "Resubmit to QA" on the case page:
<?= $link ?>


Reply to this email to reach <?= $qaUser->username ?> directly.
You need to be logged in to <?= Yii::$app->name ?> to open the case.
