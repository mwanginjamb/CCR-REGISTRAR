<?php

namespace app\commands;

use app\models\User;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class UserController extends Controller
{
    /**
     * Creates an active user, skipping email verification.
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

        $this->stdout("User #{$user->id} ({$user->username}) created." . PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }
}
