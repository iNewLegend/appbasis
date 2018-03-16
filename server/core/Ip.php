<?php
/**
 * @file    : core/Ip.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Core;

class Ip
{
    /**
     * IP Address
     *
     * @var string
     */
    private $ip = '';

    /**
     * Initialize IP, and validate
     *
     * @param string $ip
     * @throws Exception
     */
    public function __construct($ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \Exception("$ip is not valid ip address", 1);
        }

        $this->ip = $ip;
    }

    /**
     * Get IP Address
     *
     * @return void
     */
    public function get()
    {
        return $this->$ip;
    }
    
    /**
     * Return IP Address
     *
     * @return void
     */
    public function __toString()
    {
        return $this->ip;
    }
} // EOF Ip.php
