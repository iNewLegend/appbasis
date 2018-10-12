<?php
/**
 * @file: config/user.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Config;

class User extends \Services\Config\Base
{
	// #todo": move to auth.php
    public $attack_mitigation_time = "+30 minutes";

    // #todo": move to auth.php
    public $attempts_before_verify = "4";
    public $attempts_before_ban    = "9";

    // #todo": move  change varibale names remove `verify_`
    public $verify_email_max_length    = "100";
    public $verify_email_min_length    = "5";
    public $verify_password_min_length = "6";

    public $password_min_score = "1";
} // EOF config/user.php
