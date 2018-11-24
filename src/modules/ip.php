<?php
/**
 * @file: modules/ip.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Modules;

class Ip
{
    /**
     * @var string
     */
    public $address = '';

    /**
     * @var string
     */
    public $id = '';

    /**
     * @var string
     */
    private $ip;

    /**
     * Function __construct() : Construct Ip Module and validate
     *
     * @param string $ip
     *
     * @throws Exception
     */
    public function __construct($remoteAddr)
    {
        $this->id = time() . '.' . uniqid();

        $this->address = $remoteAddr;

        if (strstr($this->address, "//")) {
            $tmp = substr($this->address, strpos($this->address, "//") + 2);

        } else {
            $tmp = $this->address;
        }

        if (strstr($tmp, ':')) {
            $tmp = substr($tmp, 0, strpos($tmp, ':'));
        }

        $this->ip = $tmp;
    }

    /**
     * Function get() : Get IP Address
     *
     * @return void
     */
    public function get()
    {
        return $this->address;
    }

    /**
     * Function get() : Get IP Address
     *
     * @return void
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Function get() : Get IP Address
     *
     * @return void
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Function __toString() : Return IP Address
     *
     * @return void
     */
    public function __toString()
    {
        return $this->ip;
    }
} // EOF Ip.php
