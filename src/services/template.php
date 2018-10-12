<?php
/**
 * @file: services/template.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 *
 * @todo: rewrite and optimize
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
     * @var boolean
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
     * @param \Modules\Logger       $logger
     * @param \Modules\Command|null $command
     */
    public function __construct(\Modules\Logger $logger, \Modules\Command $command = null)
    {
        $this->logger = $logger;

        $this->initialize($command);
    }

    /**
     * Function initialize() :
     *
     * @param  \Modules\Command|null $cmd
     * @return [type]
     */
    private function initialize(\Modules\Command $cmd = null)
    {
        $this->logger->debug("loaded");

        if ($cmd && $cmd->noSubParameters()) {
            // disable another functions
            $this->lock = true;
        }
    }

    /**
     * Function copy() :
     *
     * @param  [type] $type
     * @param  [type] $name
     * @return [type]
     */
    private function copy($type, $name)
    {
        $this->logger->debug("attempting to copy type: `{$type}` name: {$name}");

        $src = __DIR__ . '/template/' . $type . '.php';

        if (!file_exists($src)) {
            $this->logger->critical("type: `{$type}` does not exist, src: `{$src}`");
            return false;
        }

        $dst = \Library\Helper::basePath();

        switch ($type):

        case 'module':
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

        $this->logger->debug("attempting to load source: '{$src}'");

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
     * Function index() :
     * @param  string $switch
     * @return [type]
     */
    public function index($switch = '')
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
     * Function module() :
     * @param  string $name
     * @return [type]
     */
    public function module(string $name = '')
    {
        if ($this->lock) {
            $this->index('module');
            return false;
        }

        if (empty($name)) {
            $this->logger->critical("`{$name}` is empty");
        }

        $this->logger->info("attempting create new module template: `{$name}`");

        if ($this->copy('module', $name)) {
            $this->logger->info("module: `$name` was success fully created");

            return;
        }

        $this->logger->warning("attempting to create new module: `{$name}` failure");
    }

    /**
     * Function model() :
     * @param  string $name
     * @return [type]
     */
    public function model(string $name = '')
    {
        if ($this->lock) {
            $this->index('model');
            return false;
        }

        if (empty($name)) {
            $this->logger->critical("`{$name}` is empty");
        }

        $this->logger->info("attempting create new model template: `{$name}`");

        if ($this->copy('model', $name)) {
            $this->logger->info("model: `$name` was success fully created");

            return;
        }

        $this->logger->warning("attempting to create new model: `{$name}` failure");
    }

    /**
     * Function config() :
     * @param  string $name
     * @return [type]
     */
    public function config(string $name = '')
    {
        $return = false;

        if ($this->lock) {
            $this->index('config');
            return false;
        }

        if (empty($name)) {
            $this->logger->critical("`{$name}` is empty");
        }

        $this->logger->info("attempting create new config template: `{$name}`");

        if ($this->copy('config', $name)) {
            $this->logger->info("config: `{$name}` was successfuly created");

            $return = true;
        } else {
            $this->logger->warning("attempting to create new config: `{$name}` failure");
        }

        return $return;
    }

    /**
     * Function controller() :
     * @param  string $name
     * @return [type]
     */
    public function controller(string $name = '')
    {
        $return = false;

        if ($this->lock) {
            $this->index('controller');
            return false;
        }

        if (empty($name)) {
            $this->logger->critical("`{$name}` is empty");
        }

        $this->logger->info("attempting create new controller template: `{$name}`");

        if ($this->copy('controller', $name)) {
            $this->logger->info("controllers: `{$name}` was successfuly created");

            $return = true;
        } else {
            $this->logger->warning("attempting to create new controller: `{$name}` failure");
        }

        return $return;
    }

    /**
     * Function service() :
     *
     * @todo all other function like this should be like this one.
     *
     * @param  string $name
     * @return [type]
     */
    public function service(string $name = '')
    {
        $return = false;

        if ($this->lock) {
            $this->index('service');
            return false;
        }

        if (empty($name)) {
            $this->logger->critical("`{$name}` is empty");
        }

        $this->logger->info("attempting create new service template: `{$name}`");

        if ($this->copy('service', $name)) {
            $this->logger->info("services: `{$name}` was successfuly created");

            $return = true;
        } else {
            $this->logger->warning("attempting to create new service: `{$name}` failure");
        }

        return $return;
    }
} // EOF template.php
