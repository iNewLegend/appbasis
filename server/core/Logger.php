<?php 

namespace Core;

/**
 * Logger class 
 * @todo add log to files.
 */
class Logger extends \Monolog\Logger
{
    protected $output;
    protected $consoleFormatter;
    protected $consoleHandler;
    
    public function __construct($name = 'channel')
    {
        $this->output = new \Symfony\Component\Console\Output\ConsoleOutput();

        $this->consoleFormatter = new \Nack\Monolog\Formatter\Symfony2ConsoleFormatter();

        $this->consoleHandler = new \Nack\Monolog\Handler\Symfony2ConsoleHandler($this->output);
        $this->consoleHandler->setFormatter($this->consoleFormatter);

        parent::__construct($name);

        $this->pushHandler($this->consoleHandler);
    }


    public function addRecord($level, $message, array $context = []): bool
    {
        //$file = debug_backtrace()[2]['file'];
        $class = debug_backtrace()[2]['class'];
        $func = debug_backtrace()[2]['function'];
        $levelName = static::getLevelName($level);

        $date = date("d-m-y H:m:s");

        $out = "[$date][$levelName][$class][$func]: $message";

        return parent::addRecord($level, $out, $context);
    }
}