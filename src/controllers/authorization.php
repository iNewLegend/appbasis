<?php
/**
 * @file: controllers/authorization.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 *
 * This controller acts like a filter\translator for Authorization Service
 */

namespace Controllers;

class Authorization
{
    /**
     * Instance of Ip Module
     * @var \Modules\Ip
     */
    private $ip;

    /**
     * Instance of Logger Module
     *
     * @var \Modules\Logger
     */
    private $logger;

    /**
     * Instance of Auth Service
     *
     * @var \Services\Auth
     */
    private $auth;

    /**
     * Function __construct() : Construct Authorization Controller
     * @param \Modules\Ip     $ip     
     * @param \Modules\Logger $logger 
     * @param \Services\Auth  $auth   
     */
    public function __construct(\Modules\Ip $ip, \Modules\Logger $logger, \Services\Auth $auth)
    {
        $this->ip     = $ip;
        $this->logger = $logger;

        $this->auth = $auth; // or \Services\Auth::get();
    }

    /**
     * Function check() : Checks by hash, if session is still active.
     *
     * @param string $hash
     * 
     * @return array
     */
    public function check(string $hash = '')
    {
        $this->logger->debug("ip: `{$this->ip}` hash: `{$hash}`");

        $return = ['code' => 'fail'];
        $error  = null;
        $result = $this->auth->check($this->ip, $hash, $error);

        switch ($result) {
            case 9: // success
                {
                    $this->logger->debug("ip: `{$this->ip}` passed check successfully");
                    $return['code'] = 'success';
                }
                break;

            case 5: // fail on block status
                {
                    $return['code']    = 'block';
                    $return['subcode'] = $error;
                }
                break;
        }

        // debug:
        $debug = "ip: `{$this->ip}` code: `{$return['code']}`";

        if (isset($return['subcode'])) {
            $debug .= " subcode: `{$return['subcode']}`";
        }

        $result = var_export($result, true);

        $debug .= " result: `{$result}`";

        // out
        $this->logger->debug($debug);

        // return
        return $return;
    }

    /**
     * Function login() : User Authentication
     *
     * @param string $email
     * @param string $password
     * @param string $captcha
     * @param string $remember
     * 
     * @return array
     */
    public function login(string $email = '', string $password = '', string $captcha = '', string $remember = '')
    {
        $this->logger->info("email: `{$email}` password: `{$password}` remember: `{$remember}`");

        $return = [];
        $error  = null;

        $result = $this->auth->login($this->ip, $email, $password, $captcha, $remember, $error);

        switch ($result) {
            case 0:$return = [
                    'code'    => 'block',
                    'subcode' => $error,
                ];
                break;

            case 1:$return = [
                    'code'    => 'mail',
                    'subcode' => $error,
                ];
                break;

            case 2:
            case 3:$return = ['code' => 'wrong'];
                break;

            case 4:$return = ['code' => 'inactive'];
                break;

            case 5:$return = ['code' => 'fail'];
                break;
        }

        // ensure we have valid hash ?
        if ($hashLen = strlen($result) === 40) {
            $return = [
                'code' => 'success',
                'hash' => $result,
            ];
        } else {
            $this->logger->critical("invalid hash length: `{$hashLen}` received from Auth service");
        }

        // debug:
        $debug = "ip: `{$this->ip}` code: `{$return['code']}`";

        if (isset($return['subcode'])) {
            $debug .= " subcode: `{$return['subcode']}`";
        }

        $result = var_export($result, true);

        $debug .= " result: `{$result}`";

        // out
        $this->logger->debug($debug);

        return $return;
    }

    /**
     * Function register() : Register new user
     *
     * @param  string $email
     * @param  string $password
     * @param  string $repassword
     * @param  string $firstName
     * @param  string $lastName
     * @param  string $captcha
     *
     * @return array
     */
    public function register(string $email = '', string $password = '', string $repassword = '', string $firstName = '', string $lastName = '', string $captcha = '')
    {
        $this->logger->info("email: `{$email}` password: `{$password}` repassword: `{$repassword}` firstName: `{$firstName}` lastName: `{$lastName}` captcha: `{$captcha}`");

        $error = null;

        $result = $this->auth->register($this->ip, $email, $password, $firstName, $lastName, $captcha, $error);

        $this->logger->debug("ip: `{$this->ip}` result: `" . var_export($result, true) . "` received from Auth service");

        switch ($result) {
            case 0:return [
                    'code'    => 'block',
                    'subcode' => $error,
                ];

            case 1:return [
                    'code'    => 'mail',
                    'subcode' => $error,
                ];

            case 2:return [
                    'code'    => 'badpass',
                    'subcode' => $error,
                ];

            case 3:return ['code' => 'emailtaken'];
            case 9:return ['code' => 'success'];
            case 5:break; // system error

            default:
                $this->logger->critical("unknown result: `{$result}` received from Auth service");
        }

        return ['code' => 'error'];
    }

    /**
     * Function logout() : Logout using hash
     *
     * @param string $hash
     * 
     * @return array
     */
    public function logout($hash = '')
    {
        $this->logger->info("hash: `{$hash}`");

        if ($this->auth->logout($this->ip, $hash)) {
            return ['code' => 'success'];
        }

        return ['code' => 'fail'];
    }
} // EOF controllers/authorization.php
