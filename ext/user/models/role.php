<?php
/**
 * @file: ext/user/models/role.php
 * @author: Leonid vinikov <czf.leo123@gmail.com>
 */

namespace Models;

class Role
{
    /**
     * Instance Of Database Module
     *
     * @var \Modules\Database
     */
    private $database;

    /**
     * Function __construct() : Construct Role Model 
     *
     * @param \Modules\Database $database
     */
    public function __construct(\Modules\Database $database)
    {
        $this->database = $database;
    }
} // EOF ext/user/models/role.php
