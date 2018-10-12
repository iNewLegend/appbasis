<?php
/**
 * @file: controllers/user.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Controllers;

class User
{
    /**
     * Instance of User Model
     *
     * @var \Models\User
     */
    private $user;

    /**
     * Session array that comes from guard.
     *
     * @var array
     */
    private $session;

    /**
     * Function __construct() : Construct User Controller
     *
     * @param \Modules\Logger $logger
     * @param \Guards\UserGuard $guard
     */
    public function __construct(\Modules\Logger $logger, \Guards\UserGuard $guard)
    {
        $this->user = new \Models\User(\Services\Database\Pool::get());
        $this->session = $guard->getSession();
    }

    /**
     * Function index() : Return user info, basic one.
     *
     * @return void
     */
    public function index()
    {
        return $this->user->getForEndpoint($this->session['uid']);
    }
} // EOF controllers/user.php
