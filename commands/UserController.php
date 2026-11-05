<?php

namespace app\commands;

use app\components\Rbac;
use app\models\User;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class UserController extends Controller
{
    /**
     * Creates an active user with the default role, skipping email verification.
     *
     * Usage:
     *   php yii user/create <username> <email>
     */
    public function actionCreate(string $username, string $email): int
    {
        $password = $this->prompt('Password:', [
            'required' => true,
            'validator' => static function ($input, &$error) {
                $min = Yii::$app->params['user.passwordMinLength'];
                if (mb_strlen($input) < $min) {
                    $error = "Password must be at least {$min} characters.";
                    return false;
                }
                return true;
            },
        ]);

        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->status = User::STATUS_ACTIVE;
        $user->setPassword($password);
        $user->generateAuthKey();

        if (!$user->save()) {
            $this->stderr(json_encode($user->getErrors()) . PHP_EOL, Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        Rbac::assignDefaultRole($user->id);

        $this->stdout("User #{$user->id} ({$user->username}) created with role " . Rbac::DEFAULT_ROLE . '.' . PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }

    /**
     * Replaces a user's roles with the given role. Use it to create the first admin.
     *
     * Usage:
     *   php yii user/assign-role <username> <role>
     *   php yii user/assign-role francis admin
     */
    public function actionAssignRole(string $username, string $roleName): int
    {
        $auth = Yii::$app->authManager;

        $user = User::findOne(['username' => $username]);
        if ($user === null) {
            $this->stderr("User '{$username}' not found." . PHP_EOL, Console::FG_RED);
            return ExitCode::DATAERR;
        }

        $role = $auth->getRole($roleName);
        if ($role === null) {
            $this->stderr("Role '{$roleName}' not found. Roles: " . implode(', ', array_keys($auth->getRoles())) . PHP_EOL, Console::FG_RED);
            return ExitCode::DATAERR;
        }

        $auth->revokeAll($user->id);
        $auth->assign($role, $user->id);

        $this->stdout("User #{$user->id} ({$user->username}) now has role {$role->name}." . PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }
}
