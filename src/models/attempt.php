<?php
/**
 * @file: models/attempt.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Models;

class Attempt
{
    /**
     * Instance Of Database Module
     *
     * @var \Modules\Database
     */
    private $database;

    /**
     * Instance Of User Config
     *
     * @var \Config\User
     */
    private $config;

    /**
     * Function __construct() : Construct Attempt Model
     *
     * @param \Modules\Database $database
     */
    public function __construct(\Modules\Database $database)
    {
        $this->database = $database;
        $this->config   = \Services\Config::get('user');
    }

    /**
     * Function add() : Add's new attempt into attempts table
     * using now() date + attack migration time
     * returns insertId or false
     *
     * @param string $ip
     * @param string $expireDate
     */
    public function add(string $ip, string $expireDate = '@auto')
    {
        if ('@auto' == $expireDate) {
            $expireDate = $this->config->attack_mitigation_time;
        }

        $expireDate = date("Y-m-d H:i:s", strtotime($expireDate));

        $result = $this->database->queryAwait("INSERT INTO attempts (ip, expiredate) VALUES ('{$ip}', '{$expireDate}')");

        if (isset($result->insertId)) {
            return $result->insertId;
        }

        return false;
    }

    /**
     * Function getBlockStatus() : Gets block status by ip address
     * translate attempts count into block status
     *
     * @param  string  $ip
     * @param  integer &$refAttempts
     *
     * @return string (allow|verify|block)
     */
    public function getBlockStatus(string $ip, &$refAttempts = 0): string
    {
        $refAttempts = 0;

        $result = $this->database->queryAwait("SELECT COUNT(*) FROM attempts WHERE expiredate > now() AND ip = '{$ip}'");

        if (!isset($result->resultRows[0])) {
            return 'error';
        }

        $result = $result->resultRows[0];

        if (isset($result['COUNT(*)'])) {
            $refAttempts = $result['COUNT(*)'];
        }

        // attempts before verify
        if ($refAttempts < $this->config->attempts_before_verify) {
            return 'allow';
        }

        // attempts before ban
        if ($refAttempts < intval($this->config->attempts_before_ban)) {
            return 'verify';
        }

        // ban
        return 'block';
    }

    /**
     * Function deleteAllAttempts() : Delete all attempts by ip address
     *
     * @param string $ip
     *
     * @return \React\Promise\Promise
     */
    public function deleteAllAttempts(string $ip)
    {
        return $this->database->query("DELETE FROM attempts WHERE ip = '{$ip}'");
    }
} // EOF models/attempt.php
