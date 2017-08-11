<?php
/**
 * @file    : /server/config.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'zxc51190');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'test');

# used by db.php for auto generate config table.
$config =
[
    ['captcha_secret_key' => '6Lfx0xMTAAAAAGbOokIN1yRRA9g9gPBpDP8qkqnK'],
    ['captcha_site_key' => '6Lfx0xMTAAAAAIebVmpyUHP2ynuCuPYAeYid2SKk'],
    ['attack_mitigation_time' => '+30 minutes'],
    ['attempts_before_verify' => '3'],
    ['attempts_before_ban' => '5'],
    ['session_remember' => '+1 hour'],
    ['password_min_score' => '1'],
    ['verify_email_max_length' => '100'],
    ['verify_email_min_length' => '5'],
    ['verify_password_min_length' => '6']
];