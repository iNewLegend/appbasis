<?php
/**
 * @file    : server/controllers/register.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Controllers;

use Core;
use Models;
use Library\Helper;
use Library\Validator;

class Register
{
    /**
     * The instance of User model
     *
     * @var \Models\User
     */
    protected $user;

    /**
     * The instance of Attempt model
     *
     * @var \Models\Attempt
     */
    protected $attempt;

    /**
     * The instance of Logger
     *
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * Initialize the controller and prepare the dependencies
     *
     * @param Core\Logger $logger
     * @param Models\User $user
     * @param Models\Attempt $attempt
     */
    public function __construct(Core\Logger $logger, Models\User $user, Models\Attempt $attempt)
    {
        $this->logger = $logger;

        $this->user = $user;
        $this->attempt = $attempt;
    }

    /**
     * Attempts to register
     *
     * @return string|array
     */
    public function register()
    {
        $request = Helper::getRequest();
        
        $email = $request->get('email');
        $password = $request->get('password');
        $captcha = $request->get('captcha');

        $this->logger->debug("email: `$email`, password: `$password`");

        $ip = Helper::getIp();
        $block_status = $this->attempt->getBlockStatus($ip);

        $this->logger->debug("ip: `$ip`, block_status: `$block_status`");

        if ($block_status == 'block') {
            return "Your ip have been blocked for a while";
        }

        $validEmail = Validator::validateEmail($email);

        if ($validEmail->error) {
            $this->attempt->add($ip);
            return $validEmail->message;
        }

        if ($this->user->isEmailTaken($email)) {
            $this->attempt->add($ip);
            return 'The email is already taken';
        }

        $validPassword = Validator::validatePassword($password);

        if ($validPassword->error) {
            return $validPassword->message;
        }

        if (! Validator::checkCaptcha($captcha)) {
            return ['code' => 'verify'];
        }
        
        if ($this->user->add($email, password_hash($password, PASSWORD_BCRYPT), true)) {
            return ['code' => 'success'];
        }

        return "system error";
    }
} // EOF register.php