<?php
/**
* file      : /app/core/controllers/register.php
* author    : czf.leo123@gmail.com
* todo      :
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
     * The instance of logger
     *
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * Create models that will be used later
     */
    public function __construct(\Core\Logger $logger,
        \Models\User $user,
        \Models\Attempt $attempt,
        \Models\Config $config,
        \Library\Auth $auth)
    {
        $this->logger = $logger;

        $this->user = $user;
        $this->attempt = $attempt;
        $this->config = $config;

        $this->auth = $auth;
    }

    /**
     * Register a new user
     *
     * @return string|array
     */
    public function register()
    {
        $request = Request::createFromGlobals();

        $request->request->replace(json_decode($request->getContent(), true));

        $ip = $this->auth->getIp();

        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $captcha = $request->request->get('captcha');

        $this->logger->debug("email: `$email`, password: `$password`");

        $block_status = $this->attempt->getBlockStatus($ip);

        if ($block_status == 'block') {
            return "Your ip have been blocked for a while";
        }

        $validEmail = $this->auth->validateEmail($email);

        if ($validEmail->error) {
            $this->attempt->add($ip);
            return $validEmail->message;
        }

        if ($this->user->isEmailTaken($email)) {
            $this->attempt->add($ip);
            return 'The email is already taken';
        }

        $validPassword = $this->auth->validatePassword($password);

        if ($validPassword->error) {
            return $validPassword->message;
        }

        if (! $this->auth->checkCaptcha($captcha)) {
            return ['code' => 'verify'];
        }

        $user = new \Models\User;

        $user->email = $email;
        $user->password = password_hash($password, PASSWORD_BCRYPT, ['cost' => $this->config->get('bcrypt_cost')]);
        $user->isactive = true;

        if ($user->save()) {
            return ['code' => 'success'];
        }

        return "system error";
    }
} // EOF register.php
