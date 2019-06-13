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
    public $core_container  = true;
    public $core_controller = false;
    public $core_object     = false;
    public $core_handler    = false;

    public $module_loader   = true;
    public $module_database = Module_Database_Logs::ON;
    public $module_process  = true;

    public static $service_config = false;

} // EOF config/logger.php
