<?php
/**
 * @file    : config/Main.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Config;

Use Core;

class Main extends \Core\ConfigBase
{
	public $session_remember = "+1 hour";
    public $captcha_secret_key = "6Lfx0xMTAAAAAGbOokIN1yRRA9g9gPBpDP8qkqnK";
    public $captcha_site_key   = "6Lfx0xMTAAAAAIebVmpyUHP2ynuCuPYAeYid2SKk";
}
