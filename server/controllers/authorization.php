<?php
/**
 * @file    : controllers/authorization.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Controllers;

use Core;
use Services;
use Models;
use Library\Helper;
use Library\Validator;

class Authorization
{
    /**
     * The instance of User model
     *
     * @var User
     */
    private $user;

    /**
     * The instance of Attempt model
     *
     * @var \Models\Attempt
     */
    private $attempt;

    /**
     * The instance of session model
     *
     * @var \Models\Session
     */
    private $session;
    
    /**
     * The instance of Auth service
     *
     * @var \Services\Auth
     */
    private $auth;

    /**
     * The instance of Logger
     *
     * @var \Core\Logger
     */
    private $logger;

    private $blockStatus;

    /**
     * Initialize the controller and prepare the dependencies
     *
     * @param Core\Logger $logger
     * @param Models\User $user
     * @param Models\Attempt $attempt
     * @param Models\Session $session
     * @param Services\Auth $auth
     */
    public function __construct(Core\Logger $logger, Models\User $user, Models\Attempt $attempt, Models\Session $session)
    {
        $this->logger = $logger;

        $this->user = $user;
        $this->attempt = $attempt;
        $this->session = $session;

        $this->auth = new Services\Auth($this->session, Core\App::getIp());

    }

    /**
     * Check blockStatus, return's data when sees something wrong.
     *
     * @param string $ip
     * @param string $captcha
     * @return mixed
     */
    private function checkBlockStatus($ip = '', $captcha = '')
    {        
        if (empty($ip)) {
            $ip = $this->auth->getIp();   
        }

        if (empty($blockStatus)) {
            $blockStatus = $this->attempt->getBlockStatus($ip);
        }

        $this->logger->debug("ip: `$ip`, blockStatus: `$blockStatus`, captcha: `$captcha`");;

        if($blockStatus !== 'allow') {
            $return['code'] = "block";
            
            switch($blockStatus) 
            {
                case 'verify': 
                    if(Validator::checkCaptcha($ip, $captcha)) {
                        $this->attempt->deleteAllAttempts($ip);

                        return false;
                    }
                    
                    $return['subcode'] = "verify";  
                break;
                    
                default:
                    $return['subcode'] = 'block';

            }

            $this->logger->debug("ip: `$ip`, code: `block`, subcode:, `" . $return['subcode'] . "`");
            
            return $return;
        }

        return false;
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

        $this->blockStatus = $this->attempt->getBlockStatus($this->auth->getIp());

        if($this->blockStatus !== 'allow') {
            $return['code'] = 'block';
            $return['subcode'] = $this->blockStatus;

            return $return;
        }

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
    public function login($email = '', $password = '', $captcha = '', $remember = '')
    {
        $ip = $this->auth->getIp();   
        $blockStatus = $this->attempt->getBlockStatus($ip);

        $this->logger->info("ip: `$ip`, email: `$email`, password: `$password`, remember: `$remember`, blockStatus: `$blockStatus`");

        if($return = $this->checkBlockStatus($ip, $captcha)) {
            return $return;
        }

        $checkEmail = Validator::checkEmail($email);

        if ($checkEmail) {
            $this->attempt->add($ip);
            
            $return = [
                'code' =>'mail',
                'subcode' => $checkEmail
            ];

            $this->logger->debug("ip: `$ip`, code: `".$return['code']."`, subcode:, `".$return['subcode']."`");
            return $return;
        }

        $id = $this->user->getId(strtolower($email));

        if (! $id) {
            $this->attempt->add($ip);
            $this->logger->debug("wrong email - ip: `$ip`, code: `wrong`");
            return ['code' => 'wrong'];
        }

        $user = $this->user->getBase($id);

        if (! password_verify($password, $user->password)) {
            $this->attempt->add($ip);
            $this->logger->debug("wrong password - ip: `$ip`, code: `wrong`");
            return ['code' => 'wrong'];
        }

        if ($user->isactive < 1) {
            $this->attempt->add($ip);
            $this->logger->debug("ip: `$ip`, code: `inactive`");
            return ['code' => 'inactive'];
        }

        $session = $this->auth->login($id, $ip, $remember);

        if (! $session) {
            /* System error, Please contact the Administrator. */
            return ['code' => 'fail'];
        }

        # delete all login attempts after login.
        $this->attempt->deleteAllAttempts($ip);

        $this->logger->debug("ip: `$ip`, email: `$email`, password: `$password`, remember: `$remember`");

        return [
            'code' => 'success',
            'hash' => $session['hash']
        ];
    }

    /**
     * Attempts to register
     *
     * @return string|array
     */
    public function register($email = '', $password = '', $captcha = '')
    {
        $ip = $this->auth->getIp();   
        $blockStatus = $this->attempt->getBlockStatus($ip);

        $this->logger->info("ip: `$ip`, email: `$email`, password: `$password`, remember: `$remember`, blockStatus: `$blockStatus`");
        
        if($return = $this->checkBlockStatus($ip, $captcha)) {
            return $return;
        }

        $checkEmail = Validator::checkEmail($email);
        
        if ($checkEmail) {
            $return = [
                'code' =>'mail',
                'subcode' => $checkEmail
            ];

            $this->logger->debug("ip: `$ip`, code: `".$return['code']."`, subcode:, `".$return['subcode']."`");
            return $return;
        }

        $validPassword = Validator::validatePassword($password);
        
        if ($validPassword) {
            $return = [
                'code' =>'badpass',
                'subcode' => $validPassword
            ];

            $this->logger->debug("ip: `$ip`, code: `".$return['code']."`, subcode:, `".$return['subcode']."`");
            return $return;
        }
        
        if($this->user->isEmailTaken($email)) {
            $this->attempt->add($ip);

            return ['code' => 'emailtaken'];
        }
        
        if ($this->user->add($email, password_hash($password, PASSWORD_BCRYPT), true)) {
            return ['code' => 'success'];
        }

        return ['code' => 'error'];
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