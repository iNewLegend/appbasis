<?php
/**
* file      : # db.php
* author    : czf.leo123@gmail.com
* todo      : make it better
* desc     : used to create the DB
*/

require 'init.php';

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class DBUilder
{
    public function users()
    {        
        # Droping Schema
        Capsule::statement('SET FOREIGN_KEY_CHECKS = 0');
        Capsule::schema()->dropIfExists('users');
        Capsule::statement('SET FOREIGN_KEY_CHECKS = 1');

        echo 'drop users table' . '<br />';
        # Creating schema
        Capsule::schema()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('isactive')->default(true);
            $table->string('email');
            $table->string('password');
            $table->timestamps();
        });
        echo 'create users table' . '<br />';
    }

    public function sessions()
    {
        # Droping Schema
        Capsule::schema()->dropIfExists('sessions');
        echo 'drop sessions table' . '<br />';

        # Creating schema
        Capsule::schema()->create('sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('uid')->unsigned();
            $table->foreign('uid')->references('id')->on('users');
            $table->string('hash', 40);
            $table->dateTime('expiredate');
            $table->string('ip', 39);
            $table->string('agent', 200);
            $table->string('cookie_crc', 40);
        });

        echo 'create sessions table' . '<br />';
    }

    public function attempts()
    {
        # Droping Schema
        Capsule::schema()->dropIfExists('attempts');
        echo 'drop attempts table' . '<br />';

        # Creating schema
        Capsule::schema()->create('attempts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ip', 39);
            $table->datetime('expiredate');
        });
        echo 'create attempts table' . '<br />';
    }

    public function config()
    {
        # Droping Schema
        Capsule::schema()->dropIfExists('config');
        echo 'drop cofnig table' . '<br />';

        # Creating schema
       Capsule::schema()->create('config', function (Blueprint $table) {
            $table->string('setting', 100);
            $table->string('value', 100)->nullable();
       });

        echo 'create cofnig table' . '<br />';

        $config = Controller::Model('Config');

        $config = new Models\Config();
        $config->setting    = 'attack_mitigation_time';
        $config->value      = '+30 minutes';
        $config->save();

        $config = new Models\Config();
        $config->setting    = 'attempts_before_verify';
        $config->value      = '3';
        $config->save();

        $config = new Models\Config();
        $config->setting    = 'attempts_before_ban';
        $config->value      = '5';
        $config->save();

        $config = new Models\Config();
        $config->setting    = 'bcrypt_cost';
        $config->value      = '10';
        $config->save();

        $config = new Models\Config();
        $config->setting    = 'cookie_forget';
        $config->value      = '+30 minutes';
        $config->save();

        $config = new Models\Config();
        $config->setting    = 'cookie_http';
        $config->value      = '0';
        $config->save();

        $config = new Models\Config();
        $config->setting    = 'cookie_name';
        $config->value      = 'authID';
        $config->save();

        $config = new Models\Config();
        $config->setting    = 'cookie_path';
        $config->value      = '';
        $config->save();

        $config = new Models\Config();
        $config->setting    = 'cookie_remember';
        $config->value      = '+1 month';
        $config->save();

        $config = new Models\Config();
        $config->setting    = 'cookie_secure';
        $config->value      = '0';
        $config->save();

        $config = new Models\Config();
        $config->setting    = 'password_min_score';
        $config->value      = '1';
        $config->save();

        $config = new Models\Config();
        $config->setting    = 'verify_email_max_length';
        $config->value      = '100';
        $config->save();

        $config = new Models\Config();
        $config->setting    = 'verify_email_min_length';
        $config->value      = '5';
        $config->save();

        $config = new Models\Config();
        $config->setting    = 'verify_password_min_length';
        $config->value      = '6';
        $config->save();

        $config = new Models\Config();
        $config->setting    = 'captcha_secret_key';
        $config->value      = CAPTCHA_SECRET_KEY;
        $config->save();

        $config = new Models\Config();
        $config->setting    = 'captcha_site_key';
        $config->value      = CAPTCHA_SITE_KEY;
        $config->save();

        echo 'create cofnig table values' . '<br />';
    }
}

$db = new DBUilder();

try {
    $db->users();
    $db->sessions();
    $db->attempts();
    $db->config();
} catch(Exception $e) {
    echo $e->getMessage();
}


