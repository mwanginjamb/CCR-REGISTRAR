<?php

namespace app\controllers;

use app\components\Rbac;
use app\models\Patient;
use app\models\PatientReview;
use app\services\ReviewNotifier;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * QA review workflow for a patient case:
 *   flag      — QA opens a review with a reason and note; the registrar who
 *               created the case is emailed (see ReviewNotifier)
 *   resubmit  — the registrar sends the corrected case back to QA
 * QA closes reviews with patient/mark-reviewed.
 */
class ReviewController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['actions' => ['flag'], 'allow' => true, 'roles' => [Rbac::REVIEW_PATIENT]],
                    ['actions' => ['resubmit'], 'allow' => true, 'roles' => [Rbac::RESUBMIT_PATIENT_REVIEW]],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'resubmit' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * QA flags a case for correction and notifies its registrar.
     */
    public function actionFlag($patientId)
    {
        $patient = $this->findPatient($patientId);

        if ($patient->hasActiveReview()) {
            Yii::$app->session->setFlash('info', 'This case already has an open QA review. Resolve it before flagging again.');
            return $this->redirect(['patient/view', 'id' => $patient->id]);
        }

        $review = new PatientReview(['scenario' => PatientReview::SCENARIO_FLAG]);

        if ($review->load(Yii::$app->request->post()) && $review->validate()) {
            /** @var \app\models\User $qaUser */
            $qaUser = Yii::$app->user->identity;

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $review->flag($patient, (int) $qaUser->id);
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }

            // After commit: a mail failure must not undo the flag
            $problem = (new ReviewNotifier())->flagged($review, $qaUser);
            $caseId = sprintf('PT-%05d', $patient->id);

            if ($problem === null) {
                Yii::$app->session->setFlash('success', "Case {$caseId} flagged for review. The registrar ({$review->notified_email}) has been emailed.");
            } else {
                Yii::$app->session->setFlash('success', "Case {$caseId} flagged for review.");
                Yii::$app->session->addFlash('warning', "The registrar was not emailed: {$problem}.");
            }

            return $this->redirect(['patient/view', 'id' => $patient->id]);
        }

        return $this->render('flag', [
            'patient' => $patient,
            'review' => $review,
        ]);
    }

    /**
     * The registrar sends the corrected case back to QA.
     * Registrars may only resubmit cases they created.
     */
    public function actionResubmit($id)
    {
        $review = PatientReview::findOne(['id' => $id]);
        if ($review === null || $review->patient === null) {
            throw new NotFoundHttpException('Review not found.');
        }

        $user = Yii::$app->user;
        $isCreator = (int) $review->patient->created_by === (int) $user->id;
        if (!$isCreator && !$user->can(Rbac::REVIEW_PATIENT)) {
            throw new ForbiddenHttpException('Only the registrar who created this case can resubmit it.');
        }

        if ($review->status !== PatientReview::STATUS_OPEN) {
            Yii::$app->session->setFlash('info', 'This review is not waiting for a correction.');
            return $this->redirect(['patient/view', 'id' => $review->patient_id]);
        }

        $review->scenario = PatientReview::SCENARIO_RESUBMIT;
        $review->load(Yii::$app->request->post());
        if (!$review->validate()) {
            Yii::$app->session->setFlash('error', implode(' ', $review->getErrorSummary(true)));
            return $this->redirect(['patient/view', 'id' => $review->patient_id]);
        }

        $review->resubmit((int) $user->id, $review->resubmit_note);
        Yii::$app->session->setFlash('success', 'Case resubmitted to QA.');

        return $this->redirect(['patient/view', 'id' => $review->patient_id]);
    }

    private function findPatient($id): Patient
    {
        $patient = Patient::findOne(['id' => $id]);
        if ($patient === null) {
            throw new NotFoundHttpException('Patient not found.');
        }
        return $patient;
    }
}
