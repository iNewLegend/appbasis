<?php
/**
 * @file    : core/Logger.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Core;

class Logger extends \Monolog\Logger
{
    private static $initOnce = false;
    private $output;
    private $consoleFormatter;
    private $consoleHandler;
    private $owner;
    private $unique;
    
    
    /**
     * Initialize the Logger
     *
     * @param string $owner
     */
    public function __construct($owner = 'global')
    {
        $this->unique = uniqid();
        $this->owner = $owner;
        
        $this->output = new \Symfony\Component\Console\Output\ConsoleOutput();

        $this->consoleFormatter = new \Nack\Monolog\Formatter\Symfony2ConsoleFormatter();

        $this->consoleHandler = new \Nack\Monolog\Handler\Symfony2ConsoleHandler($this->output);
        $this->consoleHandler->setFormatter($this->consoleFormatter);

        parent::__construct($this->owner);

        $this->pushHandler($this->consoleHandler);

        if($owner == "global" && self::$initOnce == false) {
            echo "[ DATE ]    [ LEVEL ]    [ Uniuqe ]       [ Logger Owner ]    [ Running  Class ]    [ Running Function ]:    [ Log ] \n\n"; 
        }


        if(self::$initOnce === false) {
            self::$initOnce = true;
        }


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
        $date = date("d-m-y H:m:s");
        $levelName = static::getLevelName($level);
        $unique = $this->unique;

        $out = "[$date][$levelName][$unique]";

        $debugTrace = debug_backtrace();

        if(isset($debugTrace[2])) {
            $debugTrace = $debugTrace[2];

            $name = $this->name;
            if(isset($debugTrace['class'])) {
                $class = $debugTrace['class'];
            } else {
                $class = $debugTrace['file'];
            }

            $func =  $debugTrace['function'];

            
            # this is not good at night vison
            //$out .= "\033[2m[$name][$class][$func]\033[0m: $message";
            # this better
            $out .= "\033[1m[$name][$class][$func]\033[0m: $message";
            


        } else {
            $out .= '[' . basename($debugTrace[1]['file']) . ']:' . $message; 
        }
        
        return parent::addRecord($level, $out, $context);
    }
} // EOF Logger.php    