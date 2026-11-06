<?php

namespace app\services;

use app\models\PatientReview;
use app\models\User;
use Yii;

/**
 * Emails the registrar who created a patient (patient.created_by) when QA
 * flags the case for review.
 *
 * Sender: the message is sent through the app's SMTP account (params
 * senderEmail) — SMTP servers reject mail "from" addresses they do not own —
 * with the QA user's name in From and their email as Reply-To, so replies go
 * straight to the reviewing QA user.
 *
 * Never throws: the outcome is stored on the review (notified_email /
 * notified_at or notify_error) and returned for the flash message.
 */
class ReviewNotifier
{
    /**
     * @return string|null null when the email was sent, otherwise why it was not
     */
    public function flagged(PatientReview $review, User $qaUser): ?string
    {
        $patient = $review->patient;
        $registrar = $patient->created_by ? User::findOne($patient->created_by) : null;

        if ($registrar === null) {
            return $this->fail($review, 'the patient has no recorded creator');
        }
        if (empty($registrar->email)) {
            return $this->fail($review, "user {$registrar->username} has no email address");
        }

        $senderEmail = Yii::$app->params['senderEmail'] ?? null;
        if (empty($senderEmail)) {
            return $this->fail($review, 'no sender email is configured (params senderEmail / SMTP_USERNAME)');
        }

        try {
            $message = Yii::$app->mailer
                ->compose(
                    ['html' => 'qaReviewFlagged-html', 'text' => 'qaReviewFlagged-text'],
                    ['review' => $review, 'patient' => $patient, 'registrar' => $registrar, 'qaUser' => $qaUser]
                )
                ->setFrom([$senderEmail => "{$qaUser->username} (" . Yii::$app->name . ' QA)'])
                ->setTo([$registrar->email => $registrar->username])
                ->setSubject(sprintf('QA review: case PT-%05d needs correction', $patient->id));

            if (!empty($qaUser->email)) {
                $message->setReplyTo([$qaUser->email => $qaUser->username]);
            }

            if (!$message->send()) {
                return $this->fail($review, 'the mail server did not accept the message');
            }
        } catch (\Throwable $e) {
            Yii::error('QA review email failed: ' . $e->getMessage(), __METHOD__);
            return $this->fail($review, $e->getMessage());
        }

        $review->updateAttributes([
            'notified_email' => $registrar->email,
            'notified_at' => time(),
            'notify_error' => null,
        ]);

        return null;
    }

    private function fail(PatientReview $review, string $reason): string
    {
        $review->updateAttributes(['notify_error' => $reason, 'notified_at' => null]);
        return $reason;
    }
}
