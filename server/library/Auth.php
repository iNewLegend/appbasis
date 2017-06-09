<?php
/**
 * @file    : /app/library/Auth.php
 * @author  : czf.leo123@gmail.com
 * @todo    : move from cookies to sessions
 * @desc    : do the actual login
*/
namespace Library;

class Auth
{
    /**
     * Save self $instance for static use
     *
     * @var Auth
     */
    protected static $instance = null;

    /**
    * The login state of the current authorization
    *
    * @var bool
    */
    protected $logged = false;

    /**
    * The ip of the current authorization
    *
    * @var string
    */
    protected $ip = null;

    /**
    * Hash of the current authorization
    *
    * @var string
    */
    protected $hash = '';

    /**
    * the block status of the current authorization
    *
    * @var boolean
    */
    protected $blockStatus = false;

    /**
    * The instance of Attempt model
    *
    * @var \Models\Attempt
    */
    protected $attempt;

    /**
    * The instance of Session model
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
    protected $logger;
    /**
    * Initialize the Auth library
    *
    */
    public function __construct(
        \Models\Attempt $attempt,
        \Models\Session $session,
        \Models\Config $config,
        \Core\Logger $logger
    ) {
    
        $this->attempt  = $attempt;
        $this->session  = $session;
        $this->config   = $config;
        $this->logger   = $logger;

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
            $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }

        $this->blockStatus = $this->attempt->getBlockStatus($this->ip);

        self::$instance = $this;
    }

    /**
    * Check auth by hash and return the authorization status
    *
    * @param  string    $hash
    * @return boolean
    */
    public function check($hash = false)
    {
        if (false === $hash) {
            $hash = $_COOKIE[$this->config->cookie_name];
        }
        
        if (strlen($hash) == 40) {
            if ($this->session->check($hash, $this->ip)) {
                $this->logged = true;
                $this->hash = $hash;
            }
        }

        return $this->logged;
    }
    /**
     * Login a user and return the session
     *
     * @param int       $id
     * @param string    $ip
     * @param boolean   $remember
     * @return array|boolean
     */
    public function login($id, $ip, $remember)
    {
        $session = $this->session->add($id, $remember, $ip);
        
        if (! $session) {
            return false;
        }

        setcookie($this->config->cookie_name,
            $session['hash'],
            $session['expire'],
            $this->config->cookie_path,
            $this->config->cookie_domain,
            $this->config->cookie_secure,
            $this->config->cookie_http);

        return $session;
    }

    /**
    * Logout a user
    *
    * @param    $hash   string
    * @return   boolean
    */
    public function logout($hash)
    {
        if (false === $hash) {
            $hash = $_COOKIE[$this->config->cookie_name];
        }

        if ($ret = $this->session->deleteByHash($hash)) {
            return setcookie($this->config->cookie_name,
                "",
                time() - 3600,
                $this->config->cookie_pat,
                $this->config->cookie_domain,
                $this->config->cookie_secure,
                $this->config->cookie_http);
        }
        
        return false;
    }

    /**
    * Verifies a google captcha code
    *
    * @param  string    $captcha
    * @return boolean
    */
    public function checkCaptcha($captcha)
    {
        $secret = $this->config->get('captcha_secret_key');

        try {
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = [
                'secret'   => $secret,
                'response' => $captcha,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ];

            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data)
                ]
            ];

            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);

            return json_decode($result)->success;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
    * validate email
    *
    * @param  string    $email
    * @return object
    */
    public function validateEmail($email)
    {
        $return = new \stdClass();
        $return->error = true;

        $emailLength = strlen($email);

        if ($emailLength < intval($this->config->get('verify_email_min_length'))) {
            $return->message = 'the email is too short';
            return $return;
        }

        if ($emailLength > intval($this->config->get('verify_email_max_length'))) {
            $return->message = 'the email is too long';
            return $return;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $return->message = 'not valid email';
            return $return;
        }

        $return->error = false;

        return $return;
    }

    /**
    * validate password
    *
    * @param  string    $password
    * @return object
    */
    public function validatePassword($password)
    {
        $return = new \stdClass();
        $return->error = true;

        if (strlen($password) < intval($this->config->get('verify_password_min_length'))) {
            $return->message = 'the password is too short';
            return $return;
        }

        $zxcvbn = new \ZxcvbnPhp\Zxcvbn();
        $passwordScore = $zxcvbn->passwordStrength('-' . $password)['score'];

        if ($passwordScore < intval($this->config->password_min_score)) {
            $return->message = 'The password is too weak';
            return $return;
        }

        $return->error = false;

        return $return;
    }

    /**
    * validate if user is logged in
    *
    * @return boolean
    */
    public function isLogged()
    {
        if (isset($this->logged)) {
            return $this->logged;
        }

        return self::$instance->logged;
    }

    /**
    * get user ip
    *
    * @return string
    */
    public function getIp()
    {
        if (isset($this->ip)) {
            return $this->ip;
        }

        return self::$instance->ip;
    }

    /**
    * get user hash
    *
    * @return string
    */
    public function getHash()
    {
        if (isset($this->hash)) {
            return $this->hash;
        }

        return self::$instance->hash;
    }

    /**
    * get user block status
    *
    * @return string
    */
    public function getBlockStatus()
    {
        if (isset($this->blockStatus)) {
            return $this->blockStatus;
        }

        return self::$instance->blockStatus;
    }
} // EOF Auth.php