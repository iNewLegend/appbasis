<?php
/**
 * @file: config/database.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Config;

class Database extends \Services\Config\Base
{
    public $name = 'appbasis';

    protected $host = 'localhost';

    protected $username = 'root';
    protected $password = '';
} // EOF config/database.php
