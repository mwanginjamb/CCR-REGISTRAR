<?php

namespace app\models;

use app\components\Rbac;
use app\models\User;
use yii\base\InvalidArgumentException;
use yii\base\Model;

class VerifyEmailForm extends Model
{
    /**
     * @var string
     */
    public $token;

    /**
     * @var User
     */
    private $_user;


    /**
     * Creates a form model with given token.
     *
     * The token is matched on unverified AND already-verified accounts: mail
     * link scanners, a double click or an admin activation can verify the
     * account before the user clicks, and that should not look like an error
     * (see isAlreadyVerified()). Deleted accounts never match.
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws InvalidArgumentException if token is empty or not valid
     */
    public function __construct($token, array $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Verify email token cannot be blank.');
        }
        $this->token = $token;
        $this->_user = User::findOne([
            'verification_token' => $token,
            'status' => [User::STATUS_INACTIVE, User::STATUS_ACTIVE],
        ]);
        if (!$this->_user) {
            throw new InvalidArgumentException('Wrong verify email token.');
        }
        parent::__construct($config);
    }

    /**
     * Whether the account behind the token is already active, so there is
     * nothing left to verify.
     */
    public function isAlreadyVerified(): bool
    {
        return (int) $this->_user->status === User::STATUS_ACTIVE;
    }

    /**
     * Verify email
     *
     * @return User|null the saved model or null if saving fails
     */
    public function verifyEmail()
    {
        $user = $this->_user;
        if ($this->isAlreadyVerified()) {
            return $user;
        }
        $user->status = User::STATUS_ACTIVE;

        if (!$user->save(false)) {
            return null;
        }

        // Verified accounts start as registrar; an admin escalates them as needed
        Rbac::assignDefaultRole($user->id);

        return $user;
    }
}
