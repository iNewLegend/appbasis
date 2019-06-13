<?php
/**
 * @file: interfaces/controller.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */


namespace Interfaces\Controller;

interface Disconnect
{
    /**
     * Function disconnect() : called on controller disconnection
     *
     * @return void
     */
    function disconnect();
} 

// EOF interfaces/controller.php
