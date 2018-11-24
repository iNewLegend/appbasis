<?php
/**
 * @file: config/logger.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Config;

abstract class Module_Database_Logs
{
    const OFF = 0;
    const ON = 1;
    const DEEP = 2;
}

class Logger extends \Services\Config\Base
{
    public $core_container  = false;
    public $core_guard      = true;
    public $core_controller = true;
    public $core_object     = true;
    public $core_handler    = true;

    public $module_loader   = false;
    public $module_database = Module_Database_Logs::ON;
    public $module_process  = true;

    public static $service_config = true;

} // EOF config/logger.php
