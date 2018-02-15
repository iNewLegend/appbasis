<?php

namespace Core;

Class AppCommand
{
    private $name = 'welcome';
    private $method = 'index';
    private $params = [];

    protected $logger;


    function __construct($cmd = '')
    {
        if(! empty($cmd)) {
            $this->parse($cmd);
        }
    }

    function __toString()
    {
        return json_encode([
            $this->name,
            $this->method,
            $this->params
        ]);
    }

    /**
     * Parse command from format eg: /name/methods/params
     *
     * @param  string    $cmd
     * @return object
     */
    public function parse($cmd)
    {
        if (! empty($cmd) && is_string($cmd)) {
            # remove forward slash from the start & end
            
            $cmd = trim($cmd, '/');
            $cmd = rtrim($cmd, '/');

            # removes all illegal URL characters from a string
            $cmd = explode('/', $cmd);

            # set controller
            if (isset($cmd[0]) && ! empty($cmd[0])) {
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
            if (! empty($cmd)) {
                foreach ($cmd as $key => $param) {
                    $cmd[$key] =  filter_var($param, FILTER_SANITIZE_STRING);
                }

                $this->params = array_values($cmd);
            }
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }
}