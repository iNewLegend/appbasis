<?php
/**
 * @file    : /app/core/controllers/user.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 * @todo    :
 */

namespace Controllers;

use Core;
use Library;
use Models;

class User extends Controller
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
     * Initialize the controller and prepare the dependencies
     *
     * @param Auth $auth
     * @param User $user
     */
    public function __construct(Auth $auth, User $user)
    {
        $this->auth = $auth;

        if(false == $this->auth->isLogged()) {
            exit( __CLASS__ . '  is only for authorized sessions' );
        }

        $this->user = $user;
    }

    /**
     * Test function
     *
     * @param int $id
     * @return array
     */
    public function index($id = null)
    {
        return ['test' => rand()];
    }
} // EOF user.php