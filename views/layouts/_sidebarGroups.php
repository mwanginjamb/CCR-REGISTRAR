<?php

use app\components\Rbac;

/**
 * Expandable sidebar groups shared by every layout with a sidebar.
 *
 * Usage: <?= $this->render('_sidebarGroups', ['variant' => 'main']) ?>
 *
 * Each group is shown only when 'visible' is true; the controllers behind it
 * check the same permission.
 *
 * @var yii\web\View $this
 * @var string $variant 'main' | 'questionnaire'
 */

$user = Yii::$app->user;

$groups = [
    // ── Reference data and rules (app\components\SetupController) ──
    [
        'label' => 'Settings',
        'icon' => 'settings',
        'visible' => $user->can(Rbac::MANAGE_SETTINGS),
        'children' => [
            ['label' => 'ICD-O Topography', 'url' => ['/icdo-topography/index'], 'icon' => 'location_searching', 'active' => ['icdo-topography/*']],
            ['label' => 'ICD-O Morphology', 'url' => ['/icdo-morphology/index'], 'icon' => 'biotech', 'active' => ['icdo-morphology/*']],
            ['label' => 'Site Rules', 'url' => ['/icdo-site-rule/index'], 'icon' => 'rule', 'active' => ['icdo-site-rule/*']],
            ['label' => 'Site–Morphology Rules', 'url' => ['/icdo-site-morphology-rule/index'], 'icon' => 'join_inner', 'active' => ['icdo-site-morphology-rule/*']],
            ['label' => 'Sources', 'url' => ['/source-reference/index'], 'icon' => 'local_hospital', 'active' => ['source-reference/*']],
        ],
    ],

    // ── Access control (RbacController) ──
    [
        'label' => 'RBAC Mgt',
        'icon' => 'admin_panel_settings',
        'visible' => $user->can(Rbac::MANAGE_RBAC),
        'children' => [
            ['label' => 'User Role Assignment', 'url' => ['/rbac/user-roles'], 'icon' => 'manage_accounts', 'active' => ['rbac/user-roles', 'rbac/assign-role']],
            ['label' => 'Roles', 'url' => ['/rbac/index'], 'icon' => 'security_update_good', 'active' => ['rbac/index', 'rbac/create-role', 'rbac/update-role']],
            ['label' => 'Permissions', 'url' => ['/rbac/permissions'], 'icon' => 'security', 'active' => ['rbac/permissions', 'rbac/*-permission', 'rbac/manage-hierarchy']],
            ['label' => 'System Users', 'url' => ['/rbac/users'], 'icon' => 'group', 'active' => ['rbac/users']],
        ],
    ],
];

foreach ($groups as $group) {
    if ($group['visible']) {
        echo $this->render('_navGroup', ['group' => $group, 'variant' => $variant]);
    }
}
