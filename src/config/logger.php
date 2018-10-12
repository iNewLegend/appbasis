<?php
/**
 * @file: config/logger.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Config;

class Logger extends \Services\Config\Base
{
    public $core_container  = true;
    public $core_guard      = true;
    public $core_controller = true;
    public $core_object     = true;
    public $core_handler    = true;

    public $module_loader   = true;
    public $module_database = true;

    public static $service_config = true;

} // EOF config/logger.php
