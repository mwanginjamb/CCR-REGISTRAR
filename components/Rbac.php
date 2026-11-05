<?php

namespace app\components;

use Yii;

/**
 * Role and permission names used by the application code.
 *
 * Roles (each inherits the one below):
 *   admin      — manageSettings, manageRbac
 *   qa         — updatePatient, deletePatient (soft delete), deletePatientDetails, reviewPatient
 *   registrar  — viewPatient, createPatient, createPatientDetails, updatePatientDetails
 *
 * "Patient details" are the child records edited with a patient: tumour,
 * treatments, sources and follow-up.
 *
 * The items here are seeded by migration m261105_*_seed_rbac_roles_and_permissions
 * and are protected from rename/delete in the RBAC admin screens, because code
 * refers to them by name.
 */
final class Rbac
{
    public const ROLE_REGISTRAR = 'registrar';
    public const ROLE_QA = 'qa';
    public const ROLE_ADMIN = 'admin';

    /** Given to every user once their account is verified */
    public const DEFAULT_ROLE = self::ROLE_REGISTRAR;

    public const VIEW_PATIENT = 'viewPatient';
    public const CREATE_PATIENT = 'createPatient';
    public const UPDATE_PATIENT = 'updatePatient';
    public const DELETE_PATIENT = 'deletePatient';
    public const CREATE_PATIENT_DETAILS = 'createPatientDetails';
    public const UPDATE_PATIENT_DETAILS = 'updatePatientDetails';
    public const DELETE_PATIENT_DETAILS = 'deletePatientDetails';
    public const REVIEW_PATIENT = 'reviewPatient';
    public const MANAGE_SETTINGS = 'manageSettings';
    public const MANAGE_RBAC = 'manageRbac';

    /**
     * Roles and permissions the code depends on. They cannot be renamed or
     * deleted from the RBAC screens; their permissions can still be changed.
     */
    public static function coreItems(): array
    {
        return [
            self::ROLE_REGISTRAR, self::ROLE_QA, self::ROLE_ADMIN,
            self::VIEW_PATIENT, self::CREATE_PATIENT, self::UPDATE_PATIENT, self::DELETE_PATIENT,
            self::CREATE_PATIENT_DETAILS, self::UPDATE_PATIENT_DETAILS, self::DELETE_PATIENT_DETAILS,
            self::REVIEW_PATIENT, self::MANAGE_SETTINGS, self::MANAGE_RBAC,
        ];
    }

    public static function isCoreItem(string $name): bool
    {
        return in_array($name, self::coreItems(), true);
    }

    /**
     * Assigns the default role to a user who has no role yet.
     * Users already escalated by an admin are left untouched.
     */
    public static function assignDefaultRole(int $userId): void
    {
        $auth = Yii::$app->authManager;

        if ($auth->getRolesByUser($userId) !== []) {
            return;
        }

        $role = $auth->getRole(self::DEFAULT_ROLE);
        if ($role !== null) {
            $auth->assign($role, $userId);
        }
    }
}
