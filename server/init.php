<?php
/**
 * @file    : init.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

require __DIR__ . '/vendor/autoload.php';

require 'config.php';

$DB = new Illuminate\Database\Capsule\Manager;

$DB->addConnection([
    'driver'    => 'mysql',
    'host'      => DB_HOST,
    'username'  => DB_USERNAME,
    'password'  => DB_PASSWORD,
    'database'  => DB_NAME,
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$DB->bootEloquent();
$DB->setAsGlobal();
