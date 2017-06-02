<?php
/**
* file 		: /app/core/controllers/auth.php
* author 	: czf.leo123@gmail.com
* todo		:
*/

namespace Controllers;

use Symfony\Component\HttpFoundation\Request;

class Authorization extends \Controller
{
    /**
     * The instance of user model
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
     * The instance of auth library
     *
     * @var \Library\Auth
     */
    protected $auth;

    /**
     * Create models that will be used later
     */
    public function __construct()
    {
        $this->user = $this->Model('User');
        $this->attempt = $this->Model('Attempt');
        $this->session = $this->Model('Session');
        $this->config = $this->Model('Config');

        $this->auth = $this->Library('Auth');
    }

    /**
    * TODO: Write PHPDOC
    *
    */
    public function index($hash)
    {
        $return = ['status' => 'false'];

        if(strlen($hash) == 40) {
            $this->auth->login($hash);
            $return['status'] = true;
        }

        return $return;
    }

    /**
     * Return the hash as javascript variable
     *
     * @return string
     */
    public function js()
    {
        $loginStatus = $this->auth->isLogged() ? 'true' : 'false';

        $return =
        "
            Auth =
            {
                Hash: '{$this->auth->getHash()}',
                LoginStatus: {$loginStatus}
            }
        ";

        return $return;
    }

    /**
     * Login function
     *
     * @return array|string
     */
    public function login()
    {
        $request = Request::createFromGlobals();

        if(! $request->isXmlHttpRequest()) {
            exit(__FUNCTION__ . '() method is only available for ajax requests');
        }
        $request->request->replace(json_decode($request->getContent(), true));

        $ip = $this->auth->getIp();

        $block_status = $this->attempt->getBlockStatus($ip);

        $captcha = $request->request->get('captcha');

        if($block_status == 'verify') {
            if(! $this->auth->checkCaptcha($captcha)) {
                return ['code' => 'verify'];
            }
        } else if($block_status == 'block') {
            return "your ip address has been blocked";
        }

        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $remember = $request->request->get('remember', 0);

        $validEmail = $this->auth->validateEmail($email);
        $validPassword = $this->auth->validatePassword($password);

        if($validEmail->error) {
            $this->attempt->add($ip);
            return $validEmail->message;
        }

        if($validPassword->error) {
            $this->attempt->add($ip);
            return $validPassword->message;
        }

        $id = $this->user->getId(strtolower($email));

        if(! $id) {
            $this->attempt->add($ip);
            return 'username or password incorrect';
        }

        $user = $this->user->getBase($id);

        if(! password_verify($password, $user['password'])) {
            $this->attempt->add($ip);
            return 'username or password incorrect';
        }

        if($user['isactive'] < 1) {
            $this->attempt->add($ip);
            return 'the account is inactive';
        }

        $session = $this->session->add($id, $remember, $ip);

        if(! $session) return "system error";

        setcookie($this->config->cookie_name,
            $session['hash'],
            $session['expire'],
            $this->config->cookie_path,
            $this->config->cookie_domain,
            $this->config->cookie_secure,
            $this->config->cookie_http);

        # delete all attempts for success fully login
        $this->attempt->deleteAttempts($this->auth->getIp(), true);

        return [
            'hash' => $session['hash']
        ];
    }

    /**
     * Logout function
     *
     * @param string $hash
     * @return string|array
     */
    public function logout($hash)
    {
        if(strlen($hash) != 40) {
            return 'invalid hash';
        }

        setcookie($this->config->cookie_name,
            "",
            time() - 3600,
            $this->config->cookie_pat,
            $this->config->cookie_domain,
            $this->config->cookie_secure,
            $this->config->cookie_http);

        return ['code' => 'success'];
    }
} // EOF authorization.php
