<?php
/**
 * @file: ext/auth/controllers/authorization.php
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
     * 
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
        $result = $this->auth->check((string)$this->ip, $hash, $error);

        switch ($result) {
            case \Services\Auth\Model\EnumCheckReturn::SUCCESS: {
                    $this->logger->debug("ip: `{$this->ip}` passed check successfully");
                    $return['code'] = 'success';
                }
                break;

            case \Services\Auth\Model\EnumCheckReturn::FAIL_ON_BLOCK_CHECK: {
                    $return['code']    = 'block';
                    $return['subcode'] = $error;
                }
                break;
        }

        // #DEBUG :
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
     * @param string    $email
     * @param string    $password
     * @param string    $captcha
     * @param bool      $remember
     * 
     * @return array
     */
    public function login(string $email = '', string $password = '', string $captcha = '', bool $remember = false)
    {
        $this->logger->info("email: `{$email}` password: `{$password}` remember: `{$remember}`");

        $return = [];
        $error  = null;

        $result = $this->auth->login((string)$this->ip, $email, $password, $captcha, $remember, $error);

        switch ($result) {
            case \Services\Auth\Model\EnumLoginReturn::FAIL_ON_BLOCK_CHECK: {
                    $return = [
                        'code'    => 'block',
                        'subcode' => $error,
                    ];
                }
                break;

            case \Services\Auth\Model\EnumLoginReturn::INVALID_EMAIL: {
                    $return = [
                        'code'    => 'mail',
                        'subcode' => $error,
                    ];
                }
                break;

            case \Services\Auth\Model\EnumLoginReturn::WRONG_EMAIL:
            case \Services\Auth\Model\EnumLoginReturn::WRONG_PASSWORD: {
                    $return = ['code' => 'wrong'];
                }
                break;

            case \Services\Auth\Model\EnumLoginReturn::USER_INACTIVE: {
                    $return = ['code' => 'inactive'];
                }
                break;

            case \Services\Auth\Model\EnumLoginReturn::SYSTEM_ERROR: {
                    $return = ['code' => 'fail'];
                }
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

        // # DEBUG:
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
     * @param string $email
     * @param string $password
     * @param string $repassword
     * @param string $firstName
     * @param string $lastName
     * @param string $captcha
     *
     * @return array
     */
    public function register(string $email = '', string $password = '', string $repassword = '', string $firstName = '', string $lastName = '', string $captcha = '')
    {
        $this->logger->info("email: `{$email}` password: `{$password}` repassword: `{$repassword}` firstName: `{$firstName}` lastName: `{$lastName}` captcha: `{$captcha}`");

        $error = null;

        $result = $this->auth->register((string)$this->ip, $email, $password, $firstName, $lastName, $captcha, $error);

        $this->logger->debug("ip: `{$this->ip}` result: `" . var_export($result, true) . "` received from Auth service");

        switch ($result) {
            case \Services\Auth\Model\EnumRegisterReturn::FAIL_ON_BLOCK_CHECK:
                return [
                    'code'    => 'block',
                    'subcode' => $error,
                ];

            case \Services\Auth\Model\EnumRegisterReturn::INVALID_EMAIL:
                return [
                    'code'    => 'mail',
                    'subcode' => $error,
                ];

            case \Services\Auth\Model\EnumRegisterReturn::BAD_PASSWORD:
                return [
                    'code'    => 'badpass',
                    'subcode' => $error,
                ];

            case \Services\Auth\Model\EnumRegisterReturn::EMAIL_TAKEN:
                return ['code' => 'emailtaken'];

            case \Services\Auth\Model\EnumRegisterReturn::SUCCESS:
                return ['code' => 'success'];

            case \Services\Auth\Model\EnumRegisterReturn::SYSTEM_ERROR:
                break; // system error

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

        if ($this->auth->logout((string)$this->ip, $hash)) {
            return ['code' => 'success'];
        }

        return ['code' => 'fail'];
    }
} // EOF ext/auth/controllers/authorization.php
