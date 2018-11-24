<?php
/**
 * @file: config/auth.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Config;

class Auth extends \Services\Config\Base
{
	public $session_remember_default = "+6 hour";
    public $session_remember_level1  = "+12 hour";

    public $captcha_site_key   = "6Le5p2kUAAAAAHbrEDT04OhpQolfXyzAv2NtXnAz";
    public $captcha_secret_key = "6Le5p2kUAAAAADtx8xoXiWsVmX2u5ZXBj-oqzn7H";
} // EOF config/auth.php
