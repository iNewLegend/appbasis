<?php
/**
 * @file    : db.php
 * @author  : Leonid Vinikov (czf.leo123@gmail.com)
 * @todo    : Make it better
 * @desc    : used to create the database
 */

if (PHP_SAPI !== 'cli' || isset($_SERVER['HTTP_USER_AGENT'])) {
    exit('Script execute only from CLI');
}

require 'init.php';

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * DBUilder class
 */
class DBUilder
{
    /**
     * Logger instance
     *
     * @var \Core\Logger
     */
    public $logger;
    
    /**
     * DBUilder constructor
     */
    function __construct()
    {
        $this->logger = new \Core\Logger(get_class($this));
        $this->logger->info("Logger initalized");
    }

    /**
     * Used to create users table
     *
     * @return void
     */
    public function users()
    {
        # Droping Schema
        Capsule::statement('SET FOREIGN_KEY_CHECKS = 0');
        Capsule::schema()->dropIfExists('users');
        Capsule::statement('SET FOREIGN_KEY_CHECKS = 1');

        $this->logger->warning('Drop if Exists `users` table');
        
        # Creating schema
        Capsule::schema()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('isactive')->default(true);
            $table->string('email');
            $table->string('password');
            $table->timestamps();
        });

        $this->logger->info('Creating `users` table');
        $this->logger->debug(json_encode(Capsule::select('DESCRIBE users')));
        $this->logger->notice('`users` table created');
    }

    /**
     * Used to create sessions table
     *
     * @return void
     */
    public function sessions()
    {
        # Droping Schema
        Capsule::schema()->dropIfExists('sessions');
        $this->logger->warning('Drop if Exists `sessions` table');

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

        $this->logger->info('Creating `sessions` table');
        $this->logger->debug(json_encode(Capsule::select('DESCRIBE sessions')));
        $this->logger->notice('`sessions` table created');
    }

    /**
     * Used to create attempts table
     *
     * @return void
     */
    public function attempts()
    {
        # Droping Schema
        Capsule::schema()->dropIfExists('attempts');
        $this->logger->warning('Drop if Exists `attempts` table');

        # Creating schema
        Capsule::schema()->create('attempts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ip', 39);
            $table->datetime('expiredate');
        });

        $this->logger->info('Creating `attempts` table');
        $this->logger->debug(json_encode(Capsule::select('DESCRIBE attempts')));
        $this->logger->notice('`attempts` table created');
    }

    /**
     * Used to create config table
     *
     * @return void
     */
    public function config()
    {
        # Droping Schema
        Capsule::schema()->dropIfExists('config');
        $this->logger->warning('Drop if Exists `config` table');

        # Creating schema
        Capsule::schema()->create('config', function (Blueprint $table) {
            $table->string('setting', 100);
            $table->string('value', 100)->nullable();
        });

        $this->logger->info('Creating `config` table');
        $this->logger->debug(json_encode(Capsule::select('DESCRIBE config')));
        $this->logger->notice('`config` table created');

        $this->logger->info('Creating `rows` table');

        # TODO : all config values should be in config.php
        
        $configs = [
            ['attack_mitigation_time' => '+30 minutes'],
            ['attempts_before_verify' => '3'],
            ['attempts_before_ban' => '5'],
            ['bcrypt_cost' => '10'],
            ['cookie_forget' => '+30 minutes'],
            ['cookie_http' => '0'],
            ['cookie_name' => 'authID'],
            ['cookie_path' => ''],
            ['cookie_remember' => '1 month'],
            ['cookie_secure' => '0'],
            ['password_min_score' => '1'],
            ['verify_email_max_length' => '100'],
            ['verify_email_min_length' => '5'],
            ['verify_password_min_length' => '6'],
            ['captcha_secret_key' => CAPTCHA_SECRET_KEY],
            ['captcha_site_key' => CAPTCHA_SITE_KEY]
        ];

        foreach ($configs as $config) {
            $model = new Models\Config();
            $model->setting = key($config);
            $model->value = $config[key($config)];
            $model->save();

            $this->logger->debug(key($config) . ' = ' . $config[key($config)]);
        }

        $this->logger->notice('`config` rows created');
    }
}

$db = new DBUilder();

try {
    $db->users();
    $db->sessions();
    $db->attempts();
    $db->config();
} catch (\Exception $e) {
    $db->logger->error($e->getMessage());
}