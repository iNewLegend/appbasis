<?php
/**
 * @file    : guards/User.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */
namespace Guards;

class UserGuard
{
    /**
     * Initialize UserGuard
     *
     * @param \Services\Auth $auth
     */
    function __construct(\Services\Auth $auth)
    {
        if(false == $auth->isLogged()) {
            throw new \Exception( get_class() . ': restricted.');
        }
    }
} // EOF User.php