<?php
/**
 * @file    : controllers/authorization.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Controllers;

use Core;
use Services;
use Models;
use Library\Validator;

class Authorization extends Core\Controller
{
    /**
     * The instance of User model
     *
     * @var User
     */
    protected $user;

    /**
     * The instance of Attempt model
     *
     * @var \Models\Attempt
     */
    protected $attempt;

    /**
     * The instance of session model
     *
     * @var \Models\Session
     */
    protected $session;

    /**
     * The instance of Config model
     *
     * @var \Models\Config
     */
    protected $config;

    /**
     * The instance of Auth service
     *
     * @var \Services\Auth
     */
    protected $auth;

    /**
     * The instance of Logger
     *
     * @var \Core\Logger
     */
    protected $logger;

    /**
     * Initialize the controller and prepare the dependencies
     *
     * @param Core\Logger $logger
     * @param Models\User $user
     * @param Models\Attempt $attempt
     * @param Models\Session $session
     * @param Models\Config $config
     * @param Services\Auth $auth
     */
    public function __construct(Core\Logger $logger,
        Models\User $user,
        Models\Attempt $attempt,
        Models\Session $session,
        Models\Config $config,
        Services\Auth $auth)  {

        $this->logger = $logger;

        $this->user = $user;
        $this->attempt = $attempt;
        $this->session = $session;
        $this->config = $config;

        $this->auth = $auth;
    }

    /**
     * Function used to check authorization status
     *
     * @param string $hash
     * @return array
     */
    public function check($hash = '')
    {
        $return = ['code' => 'fail'];

        if (strlen($hash) == 40) {
            if ($this->auth->check($hash)) {
                $return['code'] = 'success';
            }
        }

        return $return;
    }

    /**
     * Attempts a user login
     *
     * @return string|array
     */
    public function login()
    {
        $request = $this->getRequest();
        
        $email = $request->get('email');
        $password = $request->get('password');
        $remember = $request->get('remember', 0);
        $captcha = $request->get('captcha');

        $this->logger->debug("email: `$email`, password: `$password`, remember: `$remember`");

        $ip = $this->auth->getIp();
        $block_status = $this->attempt->getBlockStatus($ip);

        $this->logger->debug("ip: `$ip`, block_status: `$block_status`");

        if ($block_status == 'verify') {
            if (! Validator::checkCaptcha($captcha)) {
                return ['code' => 'verify'];
            }
        } elseif ($block_status == 'block') {
            return "your ip address has been blocked";
        }

        $validEmail = Validator::validateEmail($email);

        if ($validEmail->error) {
            $this->attempt->add($ip);
            return $validEmail->message;
        }

        $id = $this->user->getId(strtolower($email));

        if (! $id) {
            $this->attempt->add($ip);
            return 'username or password incorrect';
        }

        $user = $this->user->getBase($id);

        if (! password_verify($password, $user['password'])) {
            $this->attempt->add($ip);
            return 'username or password incorrect';
        }

        if ($user['isactive'] < 1) {
            $this->attempt->add($ip);
            return 'the account is inactive';
        }

        $session = $this->auth->login($id, $ip, $remember);

        if (! $session) {
            return ['code' => 'fail',
                'error' => 'System error, Please contact the Administrator.'];
        }

        # delete all attempts after successfully login
        $this->attempt->deleteAllAttempts($ip);

        return [
            'code' => 'success',
            'hash' => $session['hash']
        ];
    }

    /**
     * Logout function
     *
     * @param string $hash
     * @return array
     */
    public function logout($hash)
    {
        if (strlen($hash) == 40) {
            if ($this->auth->logout($hash)) {
                return ['code' => 'success'];
            }
        }
        return ['code' => 'fail'];
    }
} // EOF authorization.php