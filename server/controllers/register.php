<?php
/**
* file 		: /app/core/controllers/register.php
* author 	: czf.leo123@gmail.com
* todo		:
*/

namespace Controllers;

use Symfony\Component\HttpFoundation\Request;

class Register extends \Controller
{
    /**
     * The instance of auth library
     *
     * @var \Library\Auth
     */
    protected $auth;

    /**
     * The instance of user model
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
     * The instance of Config model
     *
     * @var \Models\Config
     */
    protected $config;

    /**
     * Create models that will be used later
     */
    public function __construct()
    {
        $this->auth = $this->Library('Auth');

        $this->user = $this->Model("User");
        $this->attempt = $this->Model('Attempt');
        $this->config = $this->Model('Config');
    }

    /**
     * Default method of the controller
     *
     * @return void
     */
    public function index()
    {
    }

    /**
     * Register a new user
     *
     * @return string|array
     */
    public function register()
    {
        $request = Request::createFromGlobals();

        if(! $request->isXmlHttpRequest()) {
            exit(__FUNCTION__ . '() method is only available for ajax requests');
        }
        $request->request->replace(json_decode($request->getContent(), true));

        $ip = $this->auth->getIp();

        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $captcha = $request->request->get('captcha');


        $block_status = $this->attempt->getBlockStatus($ip);

        if($block_status == 'block') {
            return "Your ip have been blocked for a while";
        }

        $validEmail = $this->auth->validateEmail($email);

        if($validEmail->error) {
            $this->attempt->add($ip);
            return $validEmail->message;
        }

        if($this->user->isEmailTaken($email)) {
            $this->attempt->add($ip);
            return 'The email is already taken';
        }

        $validPassword = $this->auth->validatePassword($password);

        if($validPassword->error) {
            return $validPassword->message;
        }

        if(! $this->auth->checkCaptcha($captcha)) {
            return ['code' => 'verify'];
        }

        $user = new \Models\User;

        $user->email = $email;
        $user->password = password_hash($password, PASSWORD_BCRYPT, ['cost' => $this->config->get('bcrypt_cost')]);
        $user->isactive = true;

        if($user->save()) {
            return ['code' => 'success'];
        }

        return "system error";
    }
} // EOF register.php