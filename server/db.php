<?php
/**
 * @file    : db.php
 * @author  : Leonid Vinikov (czf.leo123@gmail.com)
 * @todo    : its ugly
 * 
 * Database table(s) maker.
 */

if (PHP_SAPI !== 'cli') {
    exit('Script execute only from CLI');
}

require 'init.php';

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;

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
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::schema()->dropIfExists('users');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $this->logger->warning('Drop if Exists `users` table');
        
        # Creating schema
        DB::schema()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('isactive')->default(true);
            $table->string('email');
            $table->string('password');
            $table->timestamps();
        });

        $this->logger->info('Creating `users` table');
        $this->logger->debug(json_encode(DB::select('DESCRIBE users')));
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
        DB::schema()->dropIfExists('sessions');
        $this->logger->warning('Drop if Exists `sessions` table');

        # Creating schema
        DB::schema()->create('sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('uid')->unsigned();
            $table->foreign('uid')->references('id')->on('users');
            $table->string('hash', 40);
            $table->dateTime('expiredate');
            $table->string('ip', 39);
            $table->string('cookie_crc', 40);
        });

        $this->logger->info('Creating `sessions` table');
        $this->logger->debug(json_encode(DB::select('DESCRIBE sessions')));
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
        DB::schema()->dropIfExists('attempts');
        $this->logger->warning('Drop if Exists `attempts` table');

        # Creating schema
        DB::schema()->create('attempts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ip', 39);
            $table->datetime('expiredate');
        });

        $this->logger->info('Creating `attempts` table');
        $this->logger->debug(json_encode(DB::select('DESCRIBE attempts')));
        $this->logger->notice('`attempts` table created');
    }
}

$db = new DBUilder();

try {
    $db->users();
    $db->sessions();
    $db->attempts();
} catch (\Exception $e) {
    $db->logger->error($e->getMessage());
}