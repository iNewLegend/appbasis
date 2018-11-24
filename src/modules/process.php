<?php

/**
 * @file: modules/process.php
 * @author: Name <email@email.com>
 * 
 * This file use linux 'rkill' and 'at' commands.
 */

namespace Modules;

use Config\Logger;


class Process
{
    private $executeFile = '';
    private $outputFile = '';
    private $pidFile = '';
    private $commandLine;

    /**
     * @var \Modules\Logger
     */
    private $logger = null;

    /**
     * Function __construct() : Construct process 
     */
    public function __construct(string $executeFile, string $outputFile, string $pidFile, bool $appendOutput = true)
    {
        $this->executeFile = $executeFile;
        $this->outputFile = $outputFile;
        $this->pidFile = $pidFile;

        if( $appendOutput) {
            $this->commandLine = sprintf("%s >> %s 2>&1 & echo $! > %s", $this->executeFile, $this->outputFile, $this->pidFile);
        } else {
            $this->commandLine = sprintf("%s > %s 2>&1 & echo $! > %s", $this->executeFile, $this->outputFile, $this->pidFile);
        }
    
        $this->initialize();
    }

    /**
     * Function initialize() : Initialize process 
     *
     * @return void
     */
    private function initialize()
    {
        if (1 == 1) {//(\Services\Config::get('logger')->module_process) {
            $this->logger = new \Modules\Logger(self::class);
            $this->logger->debug("Module loaded");
        }

    }

    public function exec()
    {
        if ($this->isRunning()) {
            if($this->logger) $this->logger->warning("trying to run already running process: {$this}");

            return false;
        }

        \Library\Helper::exec($this->getCommandLine(), $this->logger);
        
        if ($this->isRunning()) {
            return true;
        }

        return false;
    }

    public function executeAt($at = 'now')
    {
        $startupFile = sys_get_temp_dir() . '/' . uniqid() . '.' . time() . '.at';

        if ($this->logger) {
            $this->logger->debug("attempting to create temporary startup file: `{$startupFile}`");
        }

        file_put_contents($startupFile, $this->getCommandLine());

        if (! file_exists($startupFile)) {
            if ($this->logger) {
                $this->logger->error("system was unable to create temporary file: `{$startupFile}`");
            }

            return false;
        }

        \Library\Helper::exec('chmod +x ' . $startupFile);

        $execAT = 'echo "' . $startupFile . '" | at ' . $at;
    
        \Library\Helper::exec($execAT, $this->logger);
        
        return $this->isRunning();
    }

    /**
     * Find out if the process is running
     *
     * @return boolean
     */
    public function isRunning()
    {
        if (file_exists("/proc/{$this->readPid()}")){
            return true;
        }

        return false;
    }
    
    public function kill()
    {
        file_put_contents($this->outputFile, time() . '-> kill !' . PHP_EOL, FILE_APPEND);

        \Library\Helper::exec(sprintf("rkill %d", $this->readPid()), $this->logger);

        return $this->isRunning();
    }

    public function rkill()
    {
        file_put_contents($this->outputFile, time() . '-> rkill !' . PHP_EOL, FILE_APPEND);


        $result = \Library\Helper::exec(sprintf("rkill %d", $this->readPid()), $this->logger);

        if (strstr($result, 'nonexistent')) {
            return false;
        }

        return true;
    }

    public function readPid()
    {
        $file = file($this->pidFile, FILE_SKIP_EMPTY_LINES);
        $pid = $file[count($file) - 1];

        return (int)$pid;
    }


    public function getExecuteFile()
    {
        return $this->executeFile;
    }


    public function setExecuteFile($executeFile)
    {

        $this->executeFile = $executeFile;
    }


    public function getOutputFile()
    {
        return $this->outputFile;
    }


    public function setOutputFile($outputFile)
    {
        $this->outputFile = $outputFile;
    }


    public function getPidFile()
    {
        return $this->pidFile;
    }

    public function setPidFile($pidFile)
    {
        $this->pidFile = $pidFile;
    }

    public function getCommandLine()
    {
        return $this->commandLine;
    }
    
    public function setCommandLine($commandLine)
    {
        $this->commandLine = $commandLine;
    }

    public function __toString()
    {
        return $this->getExecuteFile();   
    }
} // EOF modules/process.php