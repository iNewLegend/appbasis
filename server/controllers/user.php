<?php
/**
 * @file    : controllers/user.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 * @todo    : i don't like it that we need load $auth in constructor,
 * in future it will not be used in all functions, and i need found a solution for this.
 */

namespace Controllers;

use Core;
use Services;
use Models;
use Guards;

class User 
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
     * @var Services\Auth
     */
    protected $auth;

    /**
     * Initialize the controller and prepare the dependencies
     *
     * @param Models\User $user
     */
    public function __construct(Guards\UserGuard $userGuard, Models\User $user, Services\Auth $auth)
    {       
        $this->user = $user;
        $this->auth = $auth;
    }

    /**
     * Test function
     * $id
     * @return array
     */
    public function index($uid = null)
    {
        if(! $uid) {
            $uid = $this->auth->getUid();
        }

        $user = $this->user->where("id", $uid)->get()->first();

        return $user;
    }
} // EOF user.php