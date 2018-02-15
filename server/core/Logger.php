<?php
/**
 * @file    : core/Logger.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Core;

class Logger extends \Monolog\Logger
{
    private $output;
    private $consoleFormatter;
    private $consoleHandler;
    private $owner;
    
    
    /**
     * Initialize the Logger
     *
     * @param string $owner
     */
    public function __construct($owner = 'global')
    {
        $this->owner = $owner;
        
        $this->output = new \Symfony\Component\Console\Output\ConsoleOutput();

        $this->consoleFormatter = new \Nack\Monolog\Formatter\Symfony2ConsoleFormatter();

        $this->consoleHandler = new \Nack\Monolog\Handler\Symfony2ConsoleHandler($this->output);
        $this->consoleHandler->setFormatter($this->consoleFormatter);

        parent::__construct($this->owner);

        $this->pushHandler($this->consoleHandler);

        $this->debug("Owner `" . $this->owner . "` log initialized");
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

            $name = $this->name;
            if(isset($debugTrace['class'])) {
                $class = $debugTrace['class'];
            } else {
                $class = $debugTrace['file'];
            }

            $func =  $debugTrace['function'];

            $out .= "\033[2m[$name][$class][$func]\033[0m: $message";
        } else {
            $out .= '[' . basename($debugTrace[1]['file']) . ']:' . $message; 
        }
        
        return parent::addRecord($level, $out, $context);
    }
} // EOF Logger.php    