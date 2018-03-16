<?php
/**
 * @file    : controllers/user.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Controllers;

use Guards;
use Models;
use Services;

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
     * @param Guards\UserGuard $userGuard
     * @param Models\User $user
     * @param Services\Auth $auth
     */
    public function __construct(Guards\UserGuard $userGuard, Models\User $user, Services\Auth $auth)
    {
        $this->user = $user;
        $this->auth = $auth;
    }

    /**
     * Default method of the controller
     *
     * @param mixed uid
     * 
     * @return void
     */
    public function index($uid = null)
    {
        if (!$uid) {
            $uid = $this->auth->getUid();
        }

        $user = $this->user->where("id", $uid)->get()->first();

        return $user;
    }
} // EOF user.php
