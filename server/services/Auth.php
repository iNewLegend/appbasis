<?php
/**
 * @file    : services/Auth.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Services;

use Symfony\Component\HttpFoundation\Request;

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
     * The block status of the current authorization
     *
     * @var boolean
     */
    protected $blockStatus = false;

    /**
     * The instance of Attempt model
     *
     * @var \Models\Attempt
     */
    protected $attempt;

    /**
     * The instance of Session model
     *
     * @var \Models\Session
     */
    protected $session;

    /**
     * Initialize the Auth library
     *
     * @param \Models\Attempt $attempt
     * @param \Models\Session $session
     */
    
    public function __construct(\Models\Attempt $attempt, \Models\Session $session)
    {
        $this->attempt  = $attempt;
        $this->session  = $session;
        
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
            $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }

        $this->blockStatus = $this->attempt->getBlockStatus($this->ip);

        self::$instance = $this;

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
        if (strlen($hash) == 40) {
            if ($this->session->check($hash, $this->ip)) {
                $this->hash = $hash;
                $this->logged = true;

                return true;
            }
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
        $return = $this->session->add($id, $remember, $ip);

        if(! $return) {
            $this->logged = true;
            $this->hash = $return['hash'];
        }

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
     * Returns login state
     *
     * @return boolean
     */
    public function isLogged()
    {
        if (isset($this->logged)) {
            return $this->logged;
        }

        return self::$instance->logged;
    }

    /**
     * Returns user IP
     *
     * @return string
     */
    public function getIp()
    {
        if (isset($this->ip)) {
            return $this->ip;
        }

        return self::$instance->ip;
    }

    /**
     * Returns user hash
     *
     * @return string
     */
    public function getHash()
    {
        if (isset($this->hash)) {
            return $this->hash;
        }

        return self::$instance->hash;
    }

    /**
     * Returns user block status
     *
     * @return string
     */
    public function getBlockStatus()
    {
        if (isset($this->blockStatus)) {
            return $this->blockStatus;
        }

        return self::$instance->blockStatus;
    }
} // EOF Auth.php