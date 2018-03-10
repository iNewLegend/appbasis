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
    private static $instance = null;

    /**
     * The login state of the current authorization
     *
     * @var boolean
     */
    private $state = false;

    /**
     * Hash of the current authorization
     *
     * @var string
     */
    private $hash = '';

    /**
     * The instance of Session model
     *
     * @var \Models\Session
     */
    private $session;

    /**
     * Uniqiue id of the currect authorization
     *
     * @var int
     */
    private $uid;

    /**
     * ip of the currect client
     *
     * @var string
     */
    private $ip;

    private $config;

    /**
     * Initialize the Auth library
     * @param \Models\Session $session
     */
    public function __construct(\Models\Session $session, $ip)
    {
        $this->session = $session;

        $request = Request::createFromGlobals();
        
        $this->hash = $request->headers->get('hash');
        $this->check($this->hash);

        $this->ip = $ip;

        $this->config = \Core\Config::get("Main");
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
                
        $session = $this->session->getByHash($hash);

        $expiredate = strtotime($session['expiredate']);
        $currentdate = strtotime(date("Y-m-d H:i:s"));

        if ($currentdate > $expiredate) {
            $this->session->delete($session['id']);
            return false;
        }

        if ($this->ip != $session['ip']) {
            return false;
        }

        // ## Check it
        if ($session['cookie_crc'] == sha1($hash . $this->config->captcha_site_key)) {
            $this->state = true;
            $this->uid = $session['uid'];

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

        $return['hash'] = sha1($this->config->captcha_site_key . microtime());
        $return['cookie_crc'] = sha1($return['hash'] . $this->config->captcha_site_key);
        $return['expire'] = date('Y-m-d H:i:s', strtotime($this->config->session_remember));
        $return['expiretime'] = 0;

        # delete all sessions for the ID
        $this->session->delete($id);

        # store the expression date
        if ($remember) {
            $return['expiretime'] = strtotime($return['expire']);
        }

        # add the session
        if (! $this->session->add($id, $return['hash'], $return['expire'], $ip, $return['cookie_crc'])) {
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
        return $this->session->deleteByHash($hash);
    }

    /**
     * Function returns login state
     *
     * @return boolean
     */
    public function isLogged()
    {
        return $this->state;
    }

    /**
     * Functions returns uid
     *
     * @return void
     */
    public function getUid()
    {
        return $this->uid;
    }

    public function getIp() 
    {
        return $this->ip;
    }
} // EOF Auth.php