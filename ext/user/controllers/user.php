<?php
/**
 * @file: ext/user/controllers/user.php
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
     * Instance of Privilege Model
     *
     * @var \Models\Privilege
     */
    private $privilege;

    /**
     * Session array that comes from guard.
     *
     * @var array
     */
    private $session;

    /**
     * Function __construct() : Construct User Controller
     *
     * @param \Modules\Logger   $logger
     * @param \Guards\User      $guard
     */
    public function __construct(\Modules\Logger $logger, \Guards\User $guard)
    {
        $this->user = new \Models\User(\Services\Database\Pool::get());
        $this->privilege = new \Models\Privilege(\Services\Database\Pool::get());

        $this->session = $guard->getSession();
    }

    /**
     * Function index() : Return user info, basic one.
     *
     * @return array|bool
     */
    public function index()
    {
        if ($user = $this->user->getForEndpoint($this->session['uid'])) {
            // get all role(s) privileges for the user
            $user['roles'] = $this->privilege->getRoles($user['id']);   
        }

        return $user;
    }
} // EOF ext/user/controllers/user.php
