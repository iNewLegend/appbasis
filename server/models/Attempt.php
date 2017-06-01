<?php
/**
* file 		: /app/models/Attempt.php
* author 	: czf.leo123@gmail.com
* todo		:
* desc		: used to access attempts table and control it
*/

namespace Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Attempt extends Eloquent
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
     * get block status and delete old attempts if they expires
     *
     * @return string
     */
    public function getBlockStatus($ip)
    {
        Attempt::deleteAttempts($ip);

        $attempts = Attempt::where('ip', '=', $ip);
        $attempts = ($attempts ? $attempts->count() : 0);

        # attempts before verify
        if($attempts < Config::get('attempts_before_verify')) {
            return 'allow';
        }

         # attempts before ban
        if($attempts < intval(Config::get('attempts_before_ban'))) {
            return 'verify';
        }

        # ban
        return 'block';
    }

    /**
     * delete attempts old attempts
     *
     * @param  string    $ip
     * @param  bool      $all = false
     * @return boolean
     */
    public function deleteAttempts($ip, $all = false)
    {
        $attempts = Attempt::where('ip', '=', $ip);

        if($all) {
            return $attempts->delete();
        }

        $attempts->get();

        if(empty($attempts)) return false;

        $deleteIds = [];

        foreach($attempts as $attempt) {
            $attempt = $attempt->toArray();

            $expiredate = strtotime($attempt['expiredate']);
            $currentdate = strtotime(date('Y-m-d H:i:s'));

            if($currentdate > $expiredate) {
                $deleteIds[] = $attempt['id'];
            }
        }

        return Attempt::whereIn('id', $deleteIds)->delete();
    }
} // EOF Attempt.php