<?php
/**
 * @file    : init.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 * @todo    :
 */
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

require 'config.php';
require 'core/Controller.php';
require 'core/Logger.php';
require 'core/Core.php';

$capsule = new Capsule();

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => DB_HOST,
    'username' => DB_USERNAME,
    'password' => DB_PASSWORD,
    'database' => DB_NAME,
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => ''
]);

$capsule->bootEloquent();
$capsule->setAsGlobal();

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");