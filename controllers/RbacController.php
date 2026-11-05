<?php

namespace app\controllers;

use app\components\Rbac;
use app\models\PermissionForm;
use app\models\RoleForm;
use app\models\User;
use app\models\UserRoleForm;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\rbac\Item;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * RBAC administration: roles, permissions, user role assignment and system users.
 *
 * Built-in roles and permissions (Rbac::coreItems()) can have their permissions
 * edited but cannot be renamed or deleted, because code checks them by name.
 */
class RbacController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [Rbac::MANAGE_RBAC], // admin role
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete-role' => ['POST'],
                    'delete-permission' => ['POST'],
                    'toggle-user-status' => ['POST'],
                ],
            ],
        ];
    }

    /* ─── Roles ──────────────────────────────────────────────────────────── */

    public function actionIndex()
    {
        $auth = Yii::$app->authManager;

        $rows = [];
        foreach ($auth->getRoles() as $name => $role) {
            $children = $auth->getChildren($name);
            $rows[] = [
                'name' => $name,
                'description' => $role->description,
                'permissions' => array_keys(array_filter($children, fn($c) => $c->type === Item::TYPE_PERMISSION)),
                'inherits' => array_keys(array_filter($children, fn($c) => $c->type === Item::TYPE_ROLE)),
                'users' => count($auth->getUserIdsByRole($name)),
                'core' => Rbac::isCoreItem($name),
            ];
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $rows,
            'key' => 'name',
            'pagination' => ['pageSize' => 10],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreateRole()
    {
        $model = new RoleForm(['scenario' => 'create']);
        $auth = Yii::$app->authManager;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $role = $auth->createRole($model->role_name);
                $role->description = $model->description;
                $auth->add($role);
                $this->setRoleChildren($role, $model);

                $transaction->commit();
                Yii::$app->session->setFlash('success', "Role \"{$role->name}\" created.");
                return $this->redirect(['index']);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error creating role: ' . $e->getMessage());
            }
        }

        return $this->render('create-role', [
            'model' => $model,
            'permissions' => $auth->getPermissions(),
            'roles' => $auth->getRoles(),
        ]);
    }

    public function actionUpdateRole($name)
    {
        $auth = Yii::$app->authManager;
        $role = $this->findRole($name);

        $children = $auth->getChildren($name);
        $model = new RoleForm(['scenario' => 'update']);
        $model->role_name = $role->name;
        $model->originalName = $role->name;
        $model->description = $role->description;
        // Direct children only: inherited permissions stay with the inherited role
        $model->permissions = array_keys(array_filter($children, fn($c) => $c->type === Item::TYPE_PERMISSION));
        $model->childRoles = array_keys(array_filter($children, fn($c) => $c->type === Item::TYPE_ROLE));

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->role_name !== $model->originalName) {
                    $role = $this->renameItem($role, $model->role_name);
                }

                $role->description = $model->description;
                $auth->update($role->name, $role);

                // Replace children with the submitted permissions and inherited roles
                $auth->removeChildren($role);
                $this->setRoleChildren($role, $model);

                $transaction->commit();
                Yii::$app->session->setFlash('success', "Role \"{$role->name}\" updated.");
                return $this->redirect(['index']);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error updating role: ' . $e->getMessage());
            }
        }

        return $this->render('update-role', [
            'model' => $model,
            'permissions' => $auth->getPermissions(),
            'roles' => $auth->getRoles(),
        ]);
    }

    public function actionDeleteRole($name)
    {
        $auth = Yii::$app->authManager;
        $role = $this->findRole($name);

        if (Rbac::isCoreItem($name)) {
            Yii::$app->session->setFlash('error', "\"{$name}\" is a built-in role and cannot be deleted.");
            return $this->redirect(['index']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $auth->remove($role); // also removes its assignments and child/parent links
            $transaction->commit();
            Yii::$app->session->setFlash('success', "Role \"{$name}\" deleted, with its user assignments.");
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', "Error deleting role \"{$name}\": " . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /* ─── User role assignment ───────────────────────────────────────────── */

    /**
     * List users with their roles
     * @return string
     */
    public function actionUserRoles()
    {
        $auth = Yii::$app->authManager;

        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
            'pagination' => ['pageSize' => 20],
            'sort' => ['defaultOrder' => ['username' => SORT_ASC]],
        ]);

        return $this->render('assignment/user-roles', [
            'dataProvider' => $dataProvider,
            'rolesByUser' => fn(User $user) => array_keys($auth->getRolesByUser($user->id)),
        ]);
    }

    /**
     * Update user roles
     * @param mixed $userId
     * @return string|\yii\web\Response
     */
    public function actionAssignRole($userId)
    {
        $auth = Yii::$app->authManager;
        $user = $this->findUser($userId);

        $model = new UserRoleForm();
        $model->userId = $user->id;
        $model->roles = array_keys($auth->getRolesByUser($user->id));

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $auth->revokeAll($user->id);
                foreach ((array) $model->roles as $roleName) {
                    $auth->assign($auth->getRole($roleName), $user->id);
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', "Roles updated for {$user->username}.");
                return $this->redirect(['user-roles']);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error updating roles: ' . $e->getMessage());
            }
        }

        return $this->render('assignment/assign-role', [
            'model' => $model,
            'user' => $user,
            'allRoles' => $auth->getRoles(),
        ]);
    }

    /* ─── System users ───────────────────────────────────────────────────── */

    public function actionUsers()
    {
        $auth = Yii::$app->authManager;

        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
            'pagination' => ['pageSize' => 20],
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        return $this->render('users', [
            'dataProvider' => $dataProvider,
            'rolesByUser' => fn(User $user) => array_keys($auth->getRolesByUser($user->id)),
        ]);
    }

    /**
     * Activates or deactivates a user account. Inactive users cannot log in.
     */
    public function actionToggleUserStatus($id)
    {
        $user = $this->findUser($id);

        if ((int) $user->id === (int) Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', 'You cannot deactivate your own account.');
            return $this->redirect(['users']);
        }

        $activate = (int) $user->status !== User::STATUS_ACTIVE;
        $user->status = $activate ? User::STATUS_ACTIVE : User::STATUS_INACTIVE;
        $user->save(false, ['status', 'updated_at']);

        // A newly activated account gets the default role if it has none
        if ($activate) {
            Rbac::assignDefaultRole($user->id);
        }

        Yii::$app->session->setFlash('success', "{$user->username} " . ($activate ? 'activated.' : 'deactivated.'));

        return $this->redirect(['users']);
    }

    /* ─── Permissions ────────────────────────────────────────────────────── */

    public function actionPermissions()
    {
        $auth = Yii::$app->authManager;
        $permissions = $auth->getPermissions();

        $models = [];
        foreach ($permissions as $name => $permission) {
            $row = new \stdClass();
            $row->name = $permission->name;
            $row->description = $permission->description ?: '-';
            $row->parents = implode(', ', array_keys($this->getPermissionParents($name))) ?: '-';
            $row->children = implode(', ', array_keys($auth->getChildren($name))) ?: '-';
            $row->roles = implode(', ', array_keys(array_filter(
                $auth->getRoles(),
                fn($role) => $auth->hasChild($role, $permission)
            ))) ?: '-';
            $row->core = Rbac::isCoreItem($name);
            $models[] = $row;
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $models,
            'key' => 'name',
            'pagination' => ['pageSize' => 10],
        ]);

        return $this->render('permissions/index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreatePermission()
    {
        $model = new PermissionForm(['scenario' => 'create']);
        $auth = Yii::$app->authManager;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $permission = $auth->createPermission($model->name);
                $permission->description = $model->description;
                $auth->add($permission);

                if ($model->parent) {
                    $auth->addChild($auth->getPermission($model->parent), $permission);
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', "Permission \"{$permission->name}\" created.");
                return $this->redirect(['permissions']);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error: ' . $e->getMessage());
            }
        }

        return $this->render('permissions/create', [
            'model' => $model,
            'existingPermissions' => $auth->getPermissions(),
        ]);
    }

    public function actionUpdatePermission($name)
    {
        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission($name);

        if ($permission === null) {
            Yii::$app->session->setFlash('error', "Permission \"{$name}\" not found.");
            return $this->redirect(['permissions']);
        }

        $model = new PermissionForm(['scenario' => 'update']);
        $model->name = $permission->name;
        $model->originalName = $permission->name;
        $model->description = $permission->description;
        $model->parent = array_key_first($this->getPermissionParents($name));

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->name !== $model->originalName) {
                    $permission = $this->renameItem($permission, $model->name);
                }

                $permission->description = $model->description;
                $auth->update($permission->name, $permission);

                // Replace the parent permission (roles that grant it are unchanged)
                foreach ($this->getPermissionParents($permission->name) as $parent) {
                    $auth->removeChild($parent, $permission);
                }
                if ($model->parent) {
                    $auth->addChild($auth->getPermission($model->parent), $permission);
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', "Permission \"{$permission->name}\" updated.");
                return $this->redirect(['permissions']);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error: ' . $e->getMessage());
            }
        }

        return $this->render('permissions/update', [
            'model' => $model,
            'existingPermissions' => $auth->getPermissions(),
        ]);
    }

    public function actionDeletePermission($name)
    {
        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission($name);

        if ($permission === null) {
            Yii::$app->session->setFlash('error', 'Permission not found.');
        } elseif (Rbac::isCoreItem($name)) {
            Yii::$app->session->setFlash('error', "\"{$name}\" is used by the application and cannot be deleted.");
        } else {
            $auth->remove($permission);
            Yii::$app->session->setFlash('success', "Permission \"{$name}\" deleted.");
        }

        return $this->redirect(['permissions']);
    }

    /**
     * Sets which permissions a permission includes (permission groups).
     */
    public function actionManageHierarchy($name)
    {
        $auth = Yii::$app->authManager;
        $item = $auth->getPermission($name);

        if ($item === null) {
            throw new NotFoundHttpException("Permission \"{$name}\" not found.");
        }

        $request = Yii::$app->request;

        if ($request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $auth->removeChildren($item);
                foreach ((array) $request->post('children', []) as $childName) {
                    $child = $auth->getPermission($childName);
                    if ($child !== null && $child->name !== $item->name && $auth->canAddChild($item, $child)) {
                        $auth->addChild($item, $child);
                    }
                }
                $transaction->commit();
                Yii::$app->session->setFlash('success', "Hierarchy for \"{$name}\" updated.");
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error: ' . $e->getMessage());
            }

            return $this->redirect(['permissions']);
        }

        return $this->render('manage-hierarchy', [
            'item' => $item,
            'allItems' => array_filter($auth->getPermissions(), fn($p) => $p->name !== $name),
            'currentChildren' => $auth->getChildren($name),
        ]);
    }

    /* ─── Helpers ────────────────────────────────────────────────────────── */

    /**
     * Direct parent permissions of a permission.
     * @return \yii\rbac\Permission[] name => permission
     */
    private function getPermissionParents(string $childName): array
    {
        $auth = Yii::$app->authManager;
        $child = $auth->getPermission($childName);

        return array_filter(
            $auth->getPermissions(),
            fn($permission) => $child !== null && $auth->hasChild($permission, $child)
        );
    }

    private function setRoleChildren(Item $role, RoleForm $model): void
    {
        $auth = Yii::$app->authManager;

        foreach ((array) $model->childRoles as $roleName) {
            $auth->addChild($role, $auth->getRole($roleName));
        }
        foreach ((array) $model->permissions as $permName) {
            $auth->addChild($role, $auth->getPermission($permName));
        }
    }

    /**
     * Renames a role or permission by creating the new item and moving its
     * children, parents and user assignments over, then removing the old one.
     */
    private function renameItem(Item $old, string $newName): Item
    {
        $auth = Yii::$app->authManager;

        $new = $old->type === Item::TYPE_ROLE ? $auth->createRole($newName) : $auth->createPermission($newName);
        $new->description = $old->description;
        $new->ruleName = $old->ruleName;
        $auth->add($new);

        foreach ($auth->getChildren($old->name) as $child) {
            $auth->addChild($new, $child);
        }
        foreach (array_merge($auth->getRoles(), $auth->getPermissions()) as $parent) {
            if ($parent->name !== $old->name && $auth->hasChild($parent, $old)) {
                $auth->addChild($parent, $new);
            }
        }
        if ($old->type === Item::TYPE_ROLE) {
            foreach ($auth->getUserIdsByRole($old->name) as $userId) {
                $auth->assign($new, $userId);
            }
        }

        $auth->remove($old);

        return $new;
    }

    private function findRole($name): Item
    {
        $role = Yii::$app->authManager->getRole($name);
        if ($role === null) {
            throw new NotFoundHttpException("Role \"{$name}\" not found.");
        }
        return $role;
    }

    private function findUser($id): User
    {
        $user = User::findOne(['id' => $id]);
        if ($user === null) {
            throw new NotFoundHttpException('User not found.');
        }
        return $user;
    }
}
