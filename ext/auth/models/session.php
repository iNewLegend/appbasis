<?php
/**
 * @file: ext/auth/models/session.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Models;

class Session
{
    /**
     * Instance Of Database Module
     *
     * @var \Modules\Database
     */
    private $database;

    /**
     * Function __construct() : Construct Session Model
     *
     * @param \Modules\Database $database
     */
    public function __construct(\Modules\Database $database)
    {
        $this->database = $database;
    }

    /**
     * Function add() : Add's new session
     * 
     * @param int       $uid
     * @param string    $hash
     * @param string    $expireDate
     * @param string    $ip
     * @param string    $crc
     *
     * @return bool
     */
    public function add(int $uid, string $hash, string $expireDate, string $ip, string $crc)
    {
        $result = $this->database->queryAwait("INSERT INTO sessions (uid, hash, expiredate, ip, cookie_crc) VALUES ({$uid}, '{$hash}', '{$expireDate}', '{$ip}', '{$crc}')");

        if (isset($result->insertId)) {
            return $result->insertId;
        }

        return false;
    }

    /**
     * Function getByHash() : Get session by specific hash
     * 
     * @param string $hash
     * 
     * @return mixed
     */
    public function getByHash(string $hash)
    {
        $result = $this->database->queryAwait("SELECT * FROM sessions WHERE hash = '{$hash}'");

        if (!isset($result->resultRows[0])) {
            return false;
        }

        $result = $result->resultRows[0];

        return $result;
    }

    /**
     * Function delete() : Delete session by specific id
     *
     * @param int $id
     * 
     * @return \React\Promise\Promise
     */
    public function delete(int $id)
    {
        return $this->database->query("DELETE FROM sessions WHERE id = '{$id}'");
    }

    /**
     * Function deleteByHash() : Delete session by specific hash
     *
     * @param string $hash
     * 
     * @return bool
     */
    public function deleteByHash(string $hash)
    {
        $result = $this->database->queryAwait("DELETE FROM sessions WHERE hash = '{$hash}'");

        if (isset($result->affectedRows) && $result->affectedRows) {
            return true;
        }

        return false;
    }
} // EOF ext/auth/models/session.php
