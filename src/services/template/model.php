<?php
/**
 * @file: models/_NAME.php
 * @author: Name <email@email.com>
 */

namespace Models;

class __NAME
{
    /**
     * Instance Of Database Module
     *
     * @var \Modules\Database
     */
    private $database;

    /**
     * Function __construct() : Construct _NAME 
     *
     * @param \Modules\Database $database
     */
    public function __construct(\Modules\Database $database)
    {
        $this->database = $database;
    }
} // EOF models/_NAME.php
