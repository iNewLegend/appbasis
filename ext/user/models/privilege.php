<?php
/**
 * @file: ext/user/models/privileges.php
 * @author: Leonid vinikov <czf.leo123@gmail.com>
 */

namespace Models;

class Privilege
{
    /**
     * Instance Of Database Module
     *
     * @var \Modules\Database
     */
    private $database;

    /**
     * Function __construct() : Construct Privileges Model
     *
     * @param \Modules\Database $database
     */
    public function __construct(\Modules\Database $database)
    {
        $this->database = $database;
    }

    /**
     * Function getRoles() : Get roles
     *
     * @param string $uid
     * 
     * @return array|bool
     */
    public function getRoles(string $uid)
    {
        $result = $this->database->queryAwait("SELECT role FROM privileges WHERE uid = '{$uid}'");

        if (isset($result->resultRows[0])) {
            $return = array();

            foreach($result->resultRows as $row) {
                $return [] = $row['role'];
            }
        } else {
            $return = false;
        }

        return $return;
    }
} // EOF ext/user/models/privilege.php
