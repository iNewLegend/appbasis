<?php
/**
 * @file    : models/Attempt.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Models;

use \Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
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
     * Adds an attempt to database
     *
     * @return boolean
     */
    public function add($ip)
    {
        $attempt = new Attempt();

        $attempt->ip = $ip;
        $attempt->expiredate = date("Y-m-d H:i:s", strtotime(Config::get('attack_mitigation_time')));

        return $attempt->save();
    }

    /**
     * Get block status and delete old attempts if they expires
     *
     * @return string
     */
    public function getBlockStatus($ip)
    {
        Attempt::deleteExpiredAttempts($ip);

        $attempts = Attempt::where('ip', '=', $ip);
        $attempts = ($attempts ? $attempts->count() : 0);

        # attempts before verify
        if ($attempts < Config::get('attempts_before_verify')) {
            return 'allow';
        }

        # attempts before ban
        if ($attempts < intval(Config::get('attempts_before_ban'))) {
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
        $attempts = Attempt::where('ip', '=', $ip);

        $attempts->get();

        if (empty($attempts)) {
            return false;
        }

        $deleteIds = [];

        foreach ($attempts as $attempt) {
            $attempt = $attempt->toArray();

            $expiredate = strtotime($attempt['expiredate']);
            $currentdate = strtotime(date('Y-m-d H:i:s'));

            if ($currentdate > $expiredate) {
                $deleteIds[] = $attempt['id'];
            }
        }

        return Attempt::whereIn('id', $deleteIds)->delete();
    }

    /**
     * Delete all attempts
     *
     * @param string $ip
     * @return boolean
     */
    public function deleteAllAttempts($ip)
    {
        $attempts = Attempt::where('ip', '=', $ip);
        
        return $attempts->delete();
    }
} // EOF Attempt.php