<?php

namespace Config;

use Core;

class User extends \Core\ConfigBase
{    
    public $attack_mitigation_time = "+30 minutes";
    public $attempts_before_verify = "3"; 
    public $attempts_before_ban = "5"; 
    public $verify_email_max_length = "100";
    public $verify_email_min_length = "5";
    public $verify_password_min_length = "6";
    public $password_min_score = "1";
} 