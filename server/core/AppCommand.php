<?php
/**
 * @file    : core/AppCommand.php
 * @author  : Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Core;

class AppCommand
{
    /**
     * Default command name (controller)
     *
     * @var string
     */
    private $name = 'welcome';

    /**
     * Default command method (function)
     *
     * @var string
     */
    private $method = 'index';

    /**
     * Command parameters
     *
     * @var string
     */
    private $params = [];

    /**
     * The Logger instance
     *
     * @var \Core\Logger
     */
    protected $logger;

    /**
     * Initialize AppCommand and parse $cmd
     *
     * @param string $cmd
     */
    public function __construct($cmd = '')
    {
        if (!empty($cmd)) {
            $this->parse($cmd);
        }
    }

    /**
     * Parse command from format eg: /name/methods/params
     *
     * @param  string    $cmd
     * @return object
     */
    public function parse($cmd)
    {
        if (!empty($cmd) && is_string($cmd)) {
            # remove forward slash from the start & end

            $cmd = trim($cmd, '/');
            $cmd = rtrim($cmd, '/');

            # removes all illegal URL characters from a string
            $cmd = explode('/', $cmd);

            # set controller
            if (isset($cmd[0]) && !empty($cmd[0])) {
                # only abc for controller name
                $cmd[0] = preg_replace("/[^a-zA-Z]+/", "", $cmd[0]);

                $this->name = $cmd[0];
                unset($cmd[0]);
            }

            # set method
            if (isset($cmd[1])) {
                # only abc and digits for method name
                $cmd[1] = preg_replace("/[^a-zA-Z0-9]+/", "", $cmd[1]);

                $this->method = $cmd[1];
                unset($cmd[1]);
            }

            # set params
            if (!empty($cmd)) {
                foreach ($cmd as $key => $param) {
                    $cmd[$key] = filter_var($param, FILTER_SANITIZE_STRING);
                }

                $this->params = array_values($cmd);
            }
        }
    }

    /**
     * Get command name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set command name
     *
     * @param  string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get command method name
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set method name
     *
     * @param  string $method
     * @return void
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Get command parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set command parameters
     *
     * @param array $params
     * @return void
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Return's command in JSON format
     *
     * @return array
     */
    public function __toString()
    {
        return json_encode([
            $this->name,
            $this->method,
            $this->params,
        ]);
    }
} // EOF AppCommand.php
