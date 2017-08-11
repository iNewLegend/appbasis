<?php
/**
 * @file    : core/Logger.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Core;

class Logger extends \Monolog\Logger
{
    protected $output;
    protected $consoleFormatter;
    protected $consoleHandler;
    
    /**
     * Initialize the Logger
     *
     * @param string $name
     */
    public function __construct($name = 'global')
    {
        $this->output = new \Symfony\Component\Console\Output\ConsoleOutput();

        $this->consoleFormatter = new \Nack\Monolog\Formatter\Symfony2ConsoleFormatter();

        $this->consoleHandler = new \Nack\Monolog\Handler\Symfony2ConsoleHandler($this->output);
        $this->consoleHandler->setFormatter($this->consoleFormatter);

        parent::__construct($name);

        $this->pushHandler($this->consoleHandler);
    }

    /**
     * Add record to output
     *
     * @param int $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function addRecord($level, $message, array $context = [])
    {
        $levelName = static::getLevelName($level);
        $debugTrace = debug_backtrace();
        $date = date("d-m-y H:m:s");

        $out = "[$date][$levelName]";

        if(isset($debugTrace[2])) {
            $debugTrace = $debugTrace[2];

            $class = $debugTrace['class'];
            $func =  $debugTrace['function'];

            $out .= "[$class][$func]: $message";
        } else {
            $out .= '[' . basename($debugTrace[1]['file']) . ']:' . $message; 
        }
        
        return parent::addRecord($level, $out, $context);
    }
} // EOF Logger.php    