<?php
/**
 * @file    : controllers/user.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Controllers;

use Core;
use Services;
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
     * The instance of Auth service
     *
     * @var \Services\Auth
     */
    protected $auth;

    /**
     * Initialize the controller and prepare the dependencies
     *
     * @param Auth $auth
     * @param User $user
     */
    public function __construct(Services\Auth $auth ,Models\User $user)
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