<?php
/**
 * @file    : models/Attempt.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Models;

use Core;
use \Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    private $config;

    /**
     * Indicates if the model should be timestamped
     *
     * @return bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @return array
     */
    protected $fillable = ['ip', 'expiredate'];

    /**
     * Initialize model
     *
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);

        $this->config = Core\Config::get("User");
    }

    /**
     * Adds an attempt to database
     *
     * @param string $ip
     * @return boolean
     */
    public function add($ip)
    {
        $attempt = new Attempt();

        $attempt->ip = $ip;

        $attempt->expiredate = date("Y-m-d H:i:s", strtotime($this->config->attack_mitigation_time));

        return $attempt->save();
    }

    /**
     * Get block status and delete old attempts if they expires
     *
     * @param string $ip
     * @return boolean
     */
    public function getBlockStatus($ip)
    {
        $this->deleteExpiredAttempts($ip);

        $attempts = $this->where('ip', '=', $ip);
        $attempts = ($attempts ? $attempts->count() : 0);

        # attempts before verify
        if ($attempts < $this->config->attempts_before_verify) {
            return 'allow';
        }

        # attempts before ban
        if ($attempts < intval($this->config->attempts_before_ban)) {
            return 'verify';
        }

        # ban
        return 'block';
    }

    /**
     * Delete expired attempts
     *
     * @param string $ip
     * @return boolean
     */
    public function deleteExpiredAttempts($ip)
    {
        $attempts = $this->where('ip', '=', $ip);

        $attempts->get();

        if (empty($attempts)) {
            return false;
        }

        $deleteIds = [];

        foreach ($attempts as $attempt) {
            $attempt = $attempt->toArray();

            $expiredate  = strtotime($attempt['expiredate']);
            $currentdate = strtotime(date('Y-m-d H:i:s'));

            if ($currentdate > $expiredate) {
                $deleteIds[] = $attempt['id'];
            }
        }

        return $this->whereIn('id', $deleteIds)->delete();
    }

    /**
     * Delete all attempts
     *
     * @param string $ip
     * @return boolean
     */
    public function deleteAllAttempts($ip)
    {
        $attempts = $this->where('ip', '=', $ip);

        return $attempts->delete();
    }
} // EOF Attempt.php
