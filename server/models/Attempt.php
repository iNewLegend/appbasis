<?php
/**
 * @file    : models/Attempt.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 * @todo    : models should never access another model in the same way i do , this is bad practice
 *          : and should be handled !
 */

namespace Models;

//https://stackoverflow.com/questions/26244817/trouble-with-multiple-model-observers-in-laravel
//https://www.abeautifulsite.net/a-better-way-to-write-config-files-in-php

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
        $attempt->expiredate = date("Y-m-d H:i:s", strtotime(Config::get('attack_mitigation_time')));// conifg file

        return $attempt->save();
    }

    /**
     * Get block status and delete old attempts if they expires
     *
     * @return string
     */
    public function getBlockStatus($ip)
    {
        $this->deleteExpiredAttempts($ip);

        $attempts = $this->where('ip', '=', $ip);
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
        $attempts = $this->where('ip', '=', $ip);

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
