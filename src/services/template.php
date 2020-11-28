<?php
/**
 * @file: services/template.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Services;

class Template
{
    /**
     * Instance of Logger Module
     *
     * @var \Modules\Logger
     */
    private $logger;

    /**
     * Lock
     *
     * @var bool
     */
    private $lock = false;

    /**
     * Function get() : Get Self Service
     *
     * @return \Services\Template
     */
    public static function get($params)
    {
        $logger  = null;
        $command = null;

        foreach ($params as &$param) {
            if ($param instanceof \Modules\Logger) {
                $logger = $param;
                continue;
            }

            if ($param instanceof \Modules\Command) {
                $command = $param;
            }
        }

        return new Template($logger, $command);
    }

    /**
     * Function __construct() : Construct Template Service
     *
     * @param \Modules\Logger   $logger
     * @param \Modules\Command  $command
     */
    public function __construct(\Modules\Logger $logger, \Modules\Command $command = null)
    {
        $this->logger = $logger;

        $this->initialize($command);
    }

    /**
     * Function initialize() : Initialize Template Service
     *
     * @param \Modules\Command $cmd
     * 
     * @return void
     */
    private function initialize(\Modules\Command $cmd = null)
    {
        $this->logger->debug("loaded");

        if ($cmd && count( $cmd->getArguments() ) <= 1 ) {
            // disable another functions
            $this->lock = true;
        }
    }

    /**
     * Function copy() : copy template to new type.
     *
     * @param string $type
     * @param string $name
     * 
     * @return bool
     */
    private function copy($type, $name)
    {
        $this->logger->notice("attempting to copy type: `{$type}` name: {$name}");

        $src = __DIR__ . '/template/' . $type . '.php';

        if (!file_exists($src)) {
            $this->logger->critical("type: `{$type}` does not exist, src: `{$src}`");
            return false;
        }

        $dst = \Library\Helper::basePath();

        switch ($type): case 'module':
                $dst .= '/modules/';
                break;

            case 'model':
                $dst .= '/models/';
                break;

            case 'config':
                $dst .= '/config/';
                break;

            case 'controller':
                $dst .= '/controllers/';
                break;

            case 'service':
                $dst .= '/services/';
                break;

        endswitch;

        $dst .= $name . '.php';

        $this->logger->notice("destination: `{$dst}`");

        if (file_exists($dst)) {
            $this->logger->critical("destination: `{$dst}` is already exist");
            return false;
        }

        $this->logger->notice("attempting to load source: '{$src}'");

        if (!$content = file_get_contents($src)) {
            $this->logger->critical("failed to read source: `{$src}`");
            return false;
        }

        $content = str_replace('__NAME', ucfirst($name), $content);
        $content = str_replace('_NAME', $name, $content);

        if (!file_put_contents($dst, $content)) {
            $this->logger->critical("failed to write destination: `{$dst}`");
            unlink($dst);
            return false;
        }

        return true;
    }

    /**
     * function template() : Create new Template
     *
     * @param string $type
     * @param string $name
     * 
     * @return bool
     */
    private function template(string $type, string $name)
    {
        $return = false;

        if ($this->lock) {
            $this->index($type);
            return false;
        }

        if (empty($name)) {
            $this->logger->critical("`{$name}` is empty");
        }

        $this->logger->notice("attempting create new `{$type}` template: `{$name}`");

        if ($this->copy($type, $name)) {
            $this->logger->info("{$type}: `{$name}` was successfuly created");

            $return = true;
        } else {
            $this->logger->warning("create new {$type}: `{$name}` failure");
        }

        return $return;
    }


    /**
     * Function index() : Show help info
     * 
     * @param string $switch
     * 
     * @return void
     */
    public function index(string $switch = '')
    {
        static $commands = [
            'module'     => ['create new module' => ['syntax' => 'template module <name>']],
            'model'      => ['create new model' => ['syntax' => 'template model <name>']],
            'config'     => ['create new config' => ['syntax' => 'template config <name>']],
            'controller' => ['create new controller' => ['syntax' => 'template controller <name>']],
            'service'    => ['create new service' => ['syntax' => 'template service <name>']],
        ];

        if (!empty($switch)) {
            foreach ($commands as $key => &$cmd) {
                if ($key == $switch) {
                    $this->logger->debugJson($cmd, 'syntax');
                    return;
                }
            }
        }

        $this->logger->debugJson($commands, 'template commands');
    }

    /**
     * Function module() : Create new Module
     *     *
     * @param string $name
     * 
     * @return bool
     */
    public function module(string $name = '')
    {
        return $this->template('module', $name);
    }

    /**
     * Function model() : Create new Model
     *     *
     * @param string $name
     * 
     * @return bool
     */
    public function model(string $name = '')
    {
        return $this->template('model', $name);
    }

    /**
     * Function config() : Create new Config
     *     *
     * @param string $name
     * 
     * @return bool
     */
    public function config(string $name = '')
    {
        return $this->template('config', $name);
    }

    /**
     * Function service() : Create new Service
     *     *
     * @param string $name
     * 
     * @return bool
     */
    public function service(string $name = '')
    {
        return $this->template('service', $name);
    }

    /**
     * Function controller() : Create new Controller
     *     *
     * @param string $name
     * 
     * @return bool
     */
    public function controller(string $name = '')
    {
        return $this->template('controller', $name);
    }
} // EOF services/template.php
