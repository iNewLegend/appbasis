<?php

# can be used as iptables

namespace Core;

class Ip
{
    protected $ip = null;
    
    function __construct($ip) 
    {
        if(! filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \Exception("$ip is not valid ip address", 1);
        }

        $this->ip = $ip;
    }

    function __toString()
    {
        return $this->ip;
    }

    public function get()
    {
        return $this->$ip;
    }
}