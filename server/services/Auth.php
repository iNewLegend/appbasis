<?php
/**
 * @file    : services/Auth.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Capsule\Manager as DB;

use Symfony\Component\HttpFoundation\Request;

use Models\Config;

class Auth
{
    /**
     * Self instance
     *
     * @var Auth
     */
    protected static $instance = null;

    /**
     * The login state of the current authorization
     *
     * @var boolean
     */
    protected $logged = false;

    /**
     * The ip of the current authorization
     *
     * @var string
     */
    protected $ip = null;

    /**
     * Hash of the current authorization
     *
     * @var string
     */
    protected $hash = '';

    /**
     * The instance of Session model
     *
     * @var \Models\Session
     */
    protected $session;

    /**
     * Initialize the Auth library
     * @param \Models\Session $session
     */
    public function __construct(\Models\Session $session)
    {   
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
            $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }

        $this->session = $session;

        $request = Request::createFromGlobals();

        $this->check($request->headers->get('hash'));
    }

    /**
     * Check auth by hash and return the authorization status
     *
     * @param boolean|string $hash
     * @return boolean
     */
    public function check($hash)
    {
        if (strlen($hash) != 40) {
            return false;
        }
                
        $session = $this->session->where('hash', $hash)->get()->first();
  
        if (! $session) {
            return false;
        }

        $session = $session->toArray();

        $expiredate = strtotime($session['expiredate']);
        $currentdate = strtotime(date("Y-m-d H:i:s"));

        if ($currentdate > $expiredate) {
            $this->session->delete($session['id']);
            return false;
        }

        if ($this->getIp() != $session['ip']) {
            return false;
        }

        if ($session['cookie_crc'] == sha1($hash . Config::get('captcha_site_key'))) {
            $this->logged = true;
            return true;
        }

        return false;
    }

    /**
     * Login a user and return the session
     *
     * @param int       $id
     * @param string    $ip
     * @param boolean   $remember
     * @return array|boolean
     */
    public function login($id, $ip, $remember)
    {
        $return = array();

        $return['hash'] = sha1(Config::get('captcha_site_key') . microtime());
        $return['cookie_crc'] = sha1($return['hash'] . Config::get('captcha_site_key'));
        $return['expire'] = date('Y-m-d H:i:s', strtotime(Config::get('session_remember')));
        $return['expiretime'] = 0;

        # delete all sessions for the ID
        $this->session->delete($id);

        if ($remember) {
            $return['expiretime'] = strtotime($return['expire']);
        }

        $session = new \Models\Session();

        $session->uid = $id;
        $session->hash = $return['hash'];
        $session->expiredate = $return['expire'];
        $session->ip = $ip;
        $session->agent = $_SERVER['HTTP_USER_AGENT'];
        $session->cookie_crc = $return['cookie_crc'];

        if (! $session->save()) {
            return false;
        }

        $return['expire'] = strtotime($return['expire']);

        return $return;
    }

    /**
     * Logout function
     *
     * @param string $hash
     * @return boolean
     */
    public function logout($hash)
    {
        $session = $this->session->where('hash', $hash)->get()->first();
    
        return ! empty($session);
    }

    /**
     * Returns login state
     *
     * @return boolean
     */
    public function isLogged()
    {
        return $this->logged;
    }

    /**
     * Returns user IP
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Returns user hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }
} // EOF Auth.php