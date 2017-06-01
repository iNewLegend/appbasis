<?php
/**
 * file 		: /app/core/controllers/auth.php
 * author 	: czf.leo123@gmail.com
 * todo		:
 */

namespace Controllers;

class User extends \Controller
{
    /**
     * The instance of user model
     *
     * @var \Models\User
     */
    protected $user;

    /**
     * The instance of auth library
     *
     * @var \Models\Session
     */
    protected $auth;

    /**
     * Create models that will be used later
     */
    public function __construct()
    {
        $this->auth = $this->Library('auth');

        if(false == $this->auth->isLogged()) {
            exit( __CLASS__ . '  is only for authorized sessions' );
        }

        $this->user = $this->Model('user');
    }

    /**
     * Return the user info
     *
     * @param $id int
     * @return array
     */
    public function index($id = null)
    {
        return ['test' => rand()];
    }
}