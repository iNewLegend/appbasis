<?php
/**
* file 		: /app/core/controllers/auth.php
* author 	: czf.leo123@gmail.com
* todo		:
*/

namespace Controllers;

use Symfony\Component\HttpFoundation\Request;

class Authorization extends \Core\Controller
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

     * The instance of logger
     *
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * Initialize the controller and prepare the dependencies
     */
    public function __construct(\Core\Logger $logger,
        \Models\User $user,
        \Models\Attempt $attempt,
        \Models\Session $session,
        \Models\Config $config,
        \Library\Auth $auth)
    {
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
    */
    public function check($hash = '')
    {
        $return = ['code' => 'fail'];

        if(strlen($hash) == 40) {
            if($this->auth->check($hash)) {
                $return['code'] = 'success';
            }
        }

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
        $request->request->replace(json_decode($request->getContent(), true));

        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $remember = $request->request->get('remember', 0);
        $captcha = $request->request->get('captcha');

        $this->logger->debug("email: `$email`, password: `$password`, remember: `$remember`");

        $ip = $this->auth->getIp();
        $block_status = $this->attempt->getBlockStatus($ip);

        if($block_status == 'verify') {
            if(! $this->auth->checkCaptcha($captcha)) {
                return ['code' => 'verify'];
            }
        } else if($block_status == 'block') {
            return "your ip address has been blocked";
        }

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

        $session = $this->auth->login($id, $ip, $remember);

        if(! $session) {
            return ['code' => 'fail',
                'error' => 'system error'];
        }

        # delete all attempts after successfully login
        $this->attempt->deleteAttempts($ip, true);

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
        if(strlen($hash) == 40) {
            if($this->auth->logout($hash)) {
                return ['code' => 'success'];
            }
        }
        return ['code' => 'fail'];
    }
} // EOF authorization.php
