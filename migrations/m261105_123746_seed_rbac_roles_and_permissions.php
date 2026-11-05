<?php

use yii\db\Migration;

/**
 * Seeds the RBAC roles and permissions (see app\components\Rbac).
 *
 *   admin      → qa        + manageSettings, manageRbac
 *   qa         → registrar + updatePatient, deletePatient, deletePatientDetails, reviewPatient
 *   registrar  →             viewPatient, createPatient, createPatientDetails, updatePatientDetails
 *
 * Existing active users without a role get "registrar". No one is made admin
 * here; grant it with: php yii user/assign-role <username> admin
 *
 * Requires the RBAC tables: php yii migrate --migrationPath=@yii/rbac/migrations
 */
class m261105_123746_seed_rbac_roles_and_permissions extends Migration
{
    private const PERMISSIONS = [
        'viewPatient' => 'View patients and their details',
        'createPatient' => 'Register new patients',
        'updatePatient' => 'Edit patient demographics',
        'deletePatient' => 'Delete patients (soft delete)',
        'createPatientDetails' => 'Add tumour, treatment, source and follow-up records',
        'updatePatientDetails' => 'Edit tumour, treatment, source and follow-up records',
        'deletePatientDetails' => 'Remove treatment and source rows',
        'reviewPatient' => 'Mark flagged records as reviewed',
        'manageSettings' => 'Manage ICD-O codes, rules and sources',
        'manageRbac' => 'Manage roles, permissions and user assignments',
    ];

    /** role => [description, inherited role, own permissions] */
    private const ROLES = [
        'registrar' => ['Registrar: views and records cases', null, [
            'viewPatient', 'createPatient', 'createPatientDetails', 'updatePatientDetails',
        ]],
        'qa' => ['Quality assurance: full rights on patient records', 'registrar', [
            'updatePatient', 'deletePatient', 'deletePatientDetails', 'reviewPatient',
        ]],
        'admin' => ['Administrator: settings and access control', 'qa', [
            'manageSettings', 'manageRbac',
        ]],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        foreach (self::PERMISSIONS as $name => $description) {
            $permission = $auth->createPermission($name);
            $permission->description = $description;
            $auth->add($permission);
        }

        foreach (self::ROLES as $name => [$description, $parentOf, $permissions]) {
            $role = $auth->createRole($name);
            $role->description = $description;
            $auth->add($role);

            if ($parentOf !== null) {
                $auth->addChild($role, $auth->getRole($parentOf));
            }
            foreach ($permissions as $permission) {
                $auth->addChild($role, $auth->getPermission($permission));
            }
        }

        // Existing active users get the default role
        $registrar = $auth->getRole('registrar');
        $userIds = (new \yii\db\Query())->select('id')->from('{{%user}}')->where(['status' => 10])->column($this->db);
        foreach ($userIds as $userId) {
            if ($auth->getRolesByUser($userId) === []) {
                $auth->assign($registrar, $userId);
                echo "    > assigned registrar to user #{$userId}\n";
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        foreach (array_keys(self::ROLES) as $name) {
            if ($role = $auth->getRole($name)) {
                $auth->remove($role); // also removes its assignments and child links
            }
        }
        foreach (array_keys(self::PERMISSIONS) as $name) {
            if ($permission = $auth->getPermission($name)) {
                $auth->remove($permission);
            }
        }
    }
}
