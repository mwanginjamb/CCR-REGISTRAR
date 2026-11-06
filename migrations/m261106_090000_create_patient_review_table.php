<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%patient_review}}`, and adds the
 * resubmitPatientReview permission (registrar; inherited by qa and admin).
 *
 * One row per QA review of a patient:
 *   open        — QA flagged the case; the registrar must correct it
 *   resubmitted — the registrar corrected it and sent it back to QA
 *   resolved    — QA marked the case as reviewed
 */
class m261106_090000_create_patient_review_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%patient_review}}', [
            'id' => $this->primaryKey(),
            'patient_id' => $this->integer()->notNull(),
            'reason' => $this->string(40)->notNull(),
            'note' => $this->text()->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('open'),

            'flagged_by' => $this->integer(),
            'flagged_at' => $this->integer()->notNull(),

            'resubmitted_by' => $this->integer(),
            'resubmitted_at' => $this->integer(),
            'resubmit_note' => $this->text(),

            'resolved_by' => $this->integer(),
            'resolved_at' => $this->integer(),
            'resolution_note' => $this->text(),

            // Email to the registrar (patient.created_by) when the case is flagged
            'notified_email' => $this->string(191),
            'notified_at' => $this->integer(),
            'notify_error' => $this->text(),
        ], $tableOptions);

        $this->createIndex('idx-patient_review-patient_id-status', '{{%patient_review}}', ['patient_id', 'status']);

        $this->addForeignKey(
            'fk-patient_review-patient_id',
            '{{%patient_review}}',
            'patient_id',
            '{{%patient}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Registrars resubmit corrected cases; qa/admin inherit it
        $auth = Yii::$app->authManager;
        $permission = $auth->createPermission('resubmitPatientReview');
        $permission->description = 'Send a corrected case back to QA';
        $auth->add($permission);
        $auth->addChild($auth->getRole('registrar'), $permission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        if ($permission = $auth->getPermission('resubmitPatientReview')) {
            $auth->remove($permission);
        }

        $this->dropForeignKey('fk-patient_review-patient_id', '{{%patient_review}}');
        $this->dropTable('{{%patient_review}}');
    }
}
