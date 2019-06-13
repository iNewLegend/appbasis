<?php
/**
 * @file: ext/auth/config/auth.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Config;

class Auth extends \Services\Config\Base
{
    public $attack_mitigation_time = "+30 minutes";

    public $attempts_before_verify = "4";
    public $attempts_before_ban    = "9";

	public $session_remember_default = "+6 hour";
    public $session_remember_level1  = "+12 hour";

    public $captcha_site_key   = "6Le5p2kUAAAAAHbrEDT04OhpQolfXyzAv2NtXnAz";
    public $captcha_secret_key = "6Le5p2kUAAAAADtx8xoXiWsVmX2u5ZXBj-oqzn7H";

    public $email_max_length    = "100";
    public $email_min_length    = "5";
    public $password_min_length = "6";

    public $password_min_score = "1";
} // EOF ext/auth/config/auth.php
