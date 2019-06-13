<?php

/**
 * @file: ext/auth/services/auth.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Services;

class Auth
{
    /**
     * Self instance
     *
     * @var \Services\Auth
     */
    private static $instance;

    /**
     * Instance of Logger Module
     *
     * @var \Modules\Logger
     */
    private $logger;

    /**
     * Instance of Attempt Model
     *
     * @var \Models\Session
     */
    private $session;

    /**
     * Instance of Session Model
     *
     * @var \Models\Attempt
     */
    private $attempt;

    /**
     * Instance of User Model
     *
     * @var \Models\User
     */
    private $user;

    /**
     * Config of Auth
     *
     * @var \Config\Auth
     */
    private $config;

    /**
     * Function get() : Get self service
     *
     * @return \Services\Auth
     */
    public static function get()
    {
        if (empty(self::$instance)) {
            $logger = new \Modules\Logger(self::class);

            $config = \Services\Config::get("database")->getAll('some_secret_key');

            $config = [
                'dbname' => $config['name'],
                'user'   => $config['username'],
                'passwd' => $config['password'],
                'host'   => $config['host'],
            ];

            $database = new \Modules\Database($config, null, \Core\Auxiliary::getLoop(), true);

            $token = $logger->callBackSet("AuthDB", "connect", uniqid());

            $database->connect(function ($error) use ($database, $logger, $token) {
                if ($error) {
                    $logger->critical($error);
                }

                if ($database->isConnected()) {
                    $logger->callBackFire($token, $database->getConnectionState());
                }
            });

            $logger->callBackDeclare($token);

            self::$instance = new Auth($logger, $database);
        }

        return self::$instance;
    }

    /**
     * Function __construct() : Construct Auth (Authorization) Service
     *
     * @param \Modules\Logger $logger
     * @param \Modules\Database $database
     */
    public function __construct(\Modules\Logger $logger, \Modules\Database $database)
    {
        $this->logger  = $logger;
        $this->session = new \Models\Session($database);
        $this->attempt = new \Models\Attempt($database);
        $this->user    = new \Models\User($database);

        $this->config = \Services\Config::get("Auth");
        $this->initialize();
    }

    /**
     * Function initialize() : Initialize Auth (Authorization) Service
     *
     * @return void
     */
    private function initialize()
    {
        $this->logger->debug("loaded");
    }

    /**
     * Function checkCaptcha() : Verifies a google captcha code
     *
     * @param string $ip
     * @param string $captcha
     *
     * @return bool
     */
    public function checkCaptcha(string $ip, string $captcha)
    {
        $secret = $this->config->captcha_secret_key;

        try {
            $url  = 'https://www.google.com/recaptcha/api/siteverify';
            $data = [
                'secret'   => $secret,
                'response' => $captcha,
                'remoteip' => $ip,
            ];

            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                ],
            ];

            $context = stream_context_create($options);
            $result  = file_get_contents($url, false, $context);

            return json_decode($result)->success;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Function add() : Add's user to session, return new the session or false on fail
     *
     * @param int       $id
     * @param string    $ip
     * @param bool      $remember
     *
     * @return string|bool
     */
    private function add(int $id, string $ip, bool $remember = false)
    {
        $return = array();

        $return['hash']   = sha1($this->config->captcha_site_key . microtime());
        $return['crc']    = sha1($return['hash'] . $this->config->captcha_site_key);
        $return['expire'] = $this->config->session_remember_default;

        if ($remember) {
            $return['expire'] = $this->config->session_remember_level1;
        }

        $return['expire'] = date('Y-m-d H:i:s', strtotime($return['expire']));

        // delete all sessions for the ID
        $this->session->delete($id);

        // add the session
        if (!$this->session->add($id, $return['hash'], $return['expire'], $ip, $return['crc'])) {
            return false;
        }

        return $return['hash'];
    }

    /**
     * Function checkBlockStatus() : Check block status and verify captcha when required
     *
     * @param string $blockStatus
     * @param string $ip = ''
     * @param string $captcha = ''
     *
     * @return bool
     */
    public function checkBlockStatus(string $blockStatus, string $ip = '', string $captcha = '')
    {
        $return = false;

        switch ($blockStatus) {

            case 'verify':
                {
                    if ($this->checkCaptcha($ip, $captcha)) {
                        $this->logger->debug("ip: `{$ip}` captcha verified");
                        $return = true;
                    }

                }
                break;

            case 'allow':
                {
                    $return = true;
                }
                break;

            case 'block':break;

            default:
                $this->logger->error("ip: `{$ip}` unknown block status: `{$blockStatus}`");

        }

        return $return;
    }

    /**
     * Function check() : Check auth by hash and return result as \Services\Auth\Model\EnumCheckReturn
     *
     * @param string    $ip
     * @param string    $hash
     * @param mixed     $refError - reference for error extra info.
     * @param mixed     $refSession - reference for session
     *
     * @return \Services\Auth\Model\EnumCheckReturn
     */
    public function check(string $ip, string $hash, &$refError = null, &$refSession = null)
    {
        $attempts    = 0;
        $blockStatus = $this->attempt->getBlockStatus($ip, $attempts);

        $this->logger->debug("ip: `{$ip}` hash: `{$hash}` blockStatus: `{$blockStatus}` attempt(s): `{$attempts}`");

        if (false === $this->checkBlockStatus($blockStatus, $ip)) {
            $refError = $blockStatus;
            return \Services\Auth\Model\EnumCheckReturn::FAIL_ON_BLOCK_CHECK; // fail on block status
        }

        if (empty($hash)) {
            return \Services\Auth\Model\EnumCheckReturn::EMPTY_HASH; // empty hash
        }

        if (strlen($hash) != 40) {
            return \Services\Auth\Model\EnumCheckReturn::INVALID_HASH; // invalid hash length
        }

        $refSession = $this->session->getByHash($hash);

        if ($ip != $refSession['ip']) {
            return \Services\Auth\Model\EnumCheckReturn::INVALID_IP; // invalid ip
        }

        $expireDate  = strtotime($refSession['expiredate']);
        $currentDate = time();

        if ($currentDate > $expireDate) {
            $this->session->delete($refSession['id']);
            return \Services\Auth\Model\EnumCheckReturn::SESSION_EXPIRED; // expired
        }

        if ($refSession['cookie_crc'] != sha1($hash . $this->config->captcha_site_key)) {
            return \Services\Auth\Model\EnumCheckReturn::INVALID_CRC; //invalid crc
        }

        return \Services\Auth\Model\EnumCheckReturn::SUCCESS;
    }

    /**
     * Function login() : User authentication
     *
     * @param string  $ip
     * @param string  $email
     * @param string  $password
     * @param string  $captcha
     * @param bool    $remember
     * @param mixed   &$refError - reference to error
     *
     * @return \Services\Auth\Model\EnumLoginReturn|string hash
     */
    public function login(string $ip, string $email, string $password, string $captcha, bool $remember, &$refError = 0)
    {
        // todo, when you add attempts, there should level or config for controlling it sensitive

        $attempts = 0;

        $blockStatus = $this->attempt->getBlockStatus($ip, $attempts);

        $this->logger->debug("ip: `{$ip}` email: `{$email}` password: `{$password}` remember: `{$remember}` blockStatus: `{$blockStatus}` attempt(s): `{$attempts}`");

        if (false == $this->checkBlockStatus($blockStatus, $ip, $captcha)) {
            if ($blockStatus !== 'block') {
                $this->attempt->add($ip);
            }

            $refError = $blockStatus;

            return \Services\Auth\Model\EnumLoginReturn::FAIL_ON_BLOCK_CHECK;
        }

        $invalidEmail = \Library\Auth\Validator::invalidEmail($email);
        $this->logger->debug("Validator::checkEmail(`$email`) return `" . var_export($invalidEmail, true) . "`");

        if ($invalidEmail) {
            $refError = $invalidEmail;

            $this->attempt->add($ip);
            $this->logger->info("invalid email: ip: `{$ip}` email: `{$email}` error: `{$error}`");

            return \Services\Auth\Model\EnumLoginReturn::INVALID_EMAIL;
        }

        $id = $this->user->getId(strtolower($email));

        if (!$id) {
            $this->attempt->add($ip);
            $this->logger->info("wrong email: ip: `{$ip}` email: `{$email}`");

            return \Services\Auth\Model\EnumLoginReturn::WRONG_EMAIL;
        }

        $user = $this->user->getBase($id);

        if (!password_verify($password, $user['password'])) {
            $this->attempt->add($ip);
            $this->logger->info("wrong password: ip: `{$ip}` email: `{$email}`");

            return \Services\Auth\Model\EnumLoginReturn::WRONG_PASSWORD;
        }

        if ($user['isactive'] < 1) {
            $this->attempt->add($ip);
            $this->logger->info("inactive user: ip: `{$ip}` email: `{$email}`");

            return \Services\Auth\Model\EnumLoginReturn::USER_INACTIVE;
        }

        // delete all login attempts
        $this->attempt->deleteAllAttempts($ip);

        $hash = $this->add($id, $ip, $remember);

        if (!$hash) {
            /* System error, Please contact the Administrator. */
            $this->logger->error("session creation failed: ip: `{$ip}` email: `{$email}`");

            return \Services\Auth\Model\EnumLoginReturn::SYSTEM_ERROR;
        }

        $this->logger->info("login success: ip: `{$ip}` email: `{$email}` hash: `{$hash}`");

        return $hash;
    }

    /**
     * Function register() : Register new user
     * 
     * @param string  $ip
     * @param string  $email
     * @param string  $password
     * @param string  $firstName
     * @param string  $lastName
     * @param string  $captcha
     * @param mixed   &$refError - reference to error
     * 
     * @return \Services\Auth\Model\EnumRegisterReturn
     */
    public function register(string $ip, string $email, string $password, string $firstName, string $lastName, string $captcha, &$refError = 0)
    {
        $attempts = 0;

        $blockStatus = $this->attempt->getBlockStatus($ip, $attempts);

        $this->logger->debug("ip: `{$ip}` email: `{$email}` password: `{$password}` blockStatus: `{$blockStatus}` attempt(s): `{$attempts}`");

        if (false == $this->checkBlockStatus($blockStatus, $ip, $captcha)) {
            if ($blockStatus !== 'block') {
                $this->attempt->add($ip);
            }

            $refError = $blockStatus;

            return \Services\Auth\Model\EnumRegisterReturn::FAIL_ON_BLOCK_CHECK;
        }

        $invalidEmail = \Library\Auth\Validator::invalidEmail($email);
        $this->logger->debug("Validator::invalidEmail(`$email`) return `" . var_export($invalidEmail, true) . "`");

        if ($invalidEmail) {
            $refError = $invalidEmail;

            $this->logger->info("invalid email: ip: `{$ip}` email: `{$invalidEmail}` error: `{$refError}`");

            return \Services\Auth\Model\EnumRegisterReturn::INVALID_EMAIL;
        }

        $badPassword = \Library\Auth\Validator::isBadPassword($password);
        $this->logger->debug("Validator::isBadPassword(`{$password}`) return: `" . var_export($badPassword, true) . "`");

        if ($badPassword) {
            $this->attempt->add($ip);

            $refError = $badPassword;

            $this->logger->info("bad password: ip: `{$ip}` email: `{$email}` password: `{$password}` error: `{$refError}`");

            return \Services\Auth\Model\EnumRegisterReturn::BAD_PASSWORD;
        }

        $isEmailTaken = $this->user->isEmailTaken($email);
        $this->logger->debug("Models\User::isEmailTaken(`$email`) return `" . var_export($isEmailTaken, true) . "`");

        if ($isEmailTaken) {
            $this->attempt->add($ip);
            
            $refError = $isEmailTaken;


            $this->logger->info("email taken email: ip: `{$ip}` email: `{$email}` error: `{$refError}`");

            return \Services\Auth\Model\EnumRegisterReturn::EMAIL_TAKEN;
        }

        if ($id = $this->user->add($email, password_hash($password, PASSWORD_BCRYPT), $firstName, $lastName, true)) {
            $this->logger->info("register success: ip: `{$ip}` email: `{$email}` id: `{$id}`");

            return \Services\Auth\Model\EnumRegisterReturn::SUCCESS;
        }

        return \Services\Auth\Model\EnumRegisterReturn::SYSTEM_ERROR; // system error
    }

    /**
     * Function logout() : Logout user
     * 
     * @param string $ip   
     * @param string $hash 
     * 
     * @return bool       
     */
    public function logout(string $ip, string $hash)
    {
        $this->logger->debug("ip: `{$ip} `hash: `{$hash}` strlen(hash): `" . strlen($hash) . '`');

        if (strlen($hash) == 40) {
            if ($this->session->deleteByHash($hash)) {
                $this->logger->info("logout success: ip: `{$ip}` hash: `{$hash}`");
                return true;
            }
        }

        $this->logger->info("logout fail: ip: `{$ip}` hash: `{$hash}`");

        return false;
    }

} // EOF ext/auth/services/auth.php
