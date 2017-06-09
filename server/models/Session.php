<?php
/**
* file 		: /app/models/Session.php
* author 	: czf.leo123@gmail.com
* todo		:
* desc		: used to save and mange sessions
*/

namespace Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Session extends Eloquent
{
    /**
     * Indicates if the model should be timestamped
     *
     * @return bool
     */
    public $timestamps = false;

    /**
     * Adds an attempt to database
     *
     * @param int $id
     * @param boolean $remember
     * @param string $ip
     * @return array|boolean
     */
    public function add($id, $remember, $ip)
    {
        $return = array();

        $return['hash'] = sha1(Config::get('captcha_site_key') . microtime());
        $return['cookie_crc'] = sha1($return['hash'] . Config::get('captcha_site_key'));

        $return['expire'] = date('Y-m-d H:i:s', strtotime(Config::get('cookie_remember')));

        # delete all sessions for the ID
        $this->delete($id);

        if($remember) {
            $return['expiretime'] = strtotime($return['expire']);
        } else {
            $return['expiretime'] = 0;
        }

        $session = new Session();

        $session->uid = $id;
        $session->hash = $return['hash'];
        $session->expiredate = $return['expire'];
        $session->ip = $ip;
        $session->agent = $_SERVER['HTTP_USER_AGENT'];
        $session->cookie_crc = $return['cookie_crc'];

        if(! $session->save()) {
            return false;
        }

        $return['expire'] = strtotime($return['expire']);

        return $return;
    }

    /**
     * check if a session is valid
     *
     * @param string $hash
     * @param string $ip
     * @return boolean
     */
    public function check($hash, $ip)
    {
        $session = $this->where('hash', $hash)->get()->first();

        if(! $session) {
            return false;
        }

        $session = $session->toArray();

        $expiredate = strtotime($session['expiredate']);
        $currentdate = strtotime(date("Y-m-d H:i:s"));

        if($currentdate > $expiredate) {
            $this->delete($session['id']);
            return false;
        }

        if($ip != $session['ip']) {
            return false;
        }

        if($session['cookie_crc'] == sha1($hash . Config::get('captcha_site_key'))) {
            return true;
        }

        return false;
    }

    /**
     * Delete a session
     *
     * @param string $hash
     * @return boolean
     */
    public function deleteByHash($hash)
    {
        $session = Session::where('hash', $hash)->get()->first();
    
        return ! empty($session);
    }
} // EOF Session.php