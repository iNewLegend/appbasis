<?php
/**
 * @file    : server/controllers/user.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 * @todo    :
 */

namespace Controllers;

use Core;
use Library;
use Models;

class User extends Core\Controller 
{
    /**
     * The instance of User model
     *
     * @var \Models\User
     */
    protected $user;

    /**
     * The instance of Auth library
     *
     * @var \Library\Auth
     */
    protected $auth;

    /**
     * The instance of Guard library
     *
     * @var \Library\Guard
     */
    protected $guard;

    /**
     * Initialize the controller and prepare the dependencies
     *
     * @param Auth $auth
     * @param User $user
     * @param Guard $guard
     */
    public function __construct(Library\Auth $auth ,Models\User $user)
    {
        if(false == $auth->isLogged()) {
            exit(__CLASS__ . ' restricted! only for authorized users.');
        }

        $this->auth = $auth;
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