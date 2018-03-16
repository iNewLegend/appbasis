<?php
/**
 * @file    : guards/User.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Guards;

class UserGuard implements \Core\IGuard
{
    protected $auth;
    
    /**
     * Initialize UserGuard
     *
     * @param \Services\Auth $auth
     */
    function __construct(\Services\Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Executed when client request the guard. 
     * 
     * @throws Exception
     * @return void
     */
    public function run()
    {
        if(false == $this->auth->isLogged()) {
            throw new \Exception( get_class() . ': restricted.');
        }
    }
} // EOF User.php