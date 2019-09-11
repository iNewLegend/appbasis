<?php
/**
 * @file: modules/logger.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 * @todo: rewrite & optimize
 *
 * At the end i think you need mange logs smarter..
 * currently first debug not works
 */

namespace Modules;

class Logger extends \Monolog\Logger
{
    /**
     * Instance name
     *
     * @var string
     */
    protected $name;

    /**
     * Unique serial for each logger, public to be seen in logs.
     *
     * @var string
     */
    public $unique;

    /**
     * Logger Owner name
     *
     * @var string
     */
    private $owner;

    /**
     * Does this logger initialized?
     *
     * @var bool
     */
    private $initialized = false;

    /**
     * Here we store tall the `callbacks` tokens
     *
     * @var array
     */
    private $callbacksTokens = [];

    /**
     * Count of created logger instances
     *
     * @var int
     */
    private static $loggersCount = 0;

    /**
     * Function handler() : Static function that called each time the self object constructed
     *
     * @param \Modules\Logger $logger
     * 
     * @return void
     */
    private static function handler(\Modules\Logger $logger)
    {
        static $calledOnce = null;

        // # NOTICE: next code section is uses handler for the first construction of module.
        if (empty($calledOnce)) {
            $calledOnce = true;
            \Monolog\ErrorHandler::register($logger);

            set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                switch ($errno): case E_WARNING:
                        $level = \Monolog\Logger::WARNING;
                        break;

                    case E_NOTICE:
                    case E_USER_NOTICE:
                    case E_DEPRECATED:
                        $level = \Monolog\Logger::NOTICE;
                        break;

                    case E_RECOVERABLE_ERROR:
                        $level = \Monolog\Logger::CRITICAL;
                        break;

                    default:
                        throw new \Exception("unknown errno: `{$errno}`");
                endswitch;
                // ----
                if ($gLogger = \Core\Auxiliary::getGlobalLogger()) {
                    \Core\Auxiliary::getGlobalLogger()->{\Monolog\Logger::getLevelName($level)}($errstr . ". -at line: `{$errline}`", [1]);
                } else {
                    \Core\Auxiliary::shutdown(0, func_get_args(), __CLASS__, 'function: `handler` closure: `set_error_handler`');
                }
            }, E_ALL);
        }

        // increase loggers count
        ++self::$loggersCount;
    }

    /**
     * Function getFreeIndex() : return unique 10 characters length hex serial order by increased count eg:
     * 00000000000, 00000000001
     * 0000000000A, 0000000000B
     * 0000000000F, 00000000010
     *
     * @return void
     */
    private static function getFreeIndex()
    {
        static $array = null;

        if ($array === null) {
            $array = array_fill(0, 10, 0);

            return implode('', $array);
        }

        $array = array_fill(0, 10, 0);

        for ($i = 0; $i != self::$loggersCount; ++$i) {
            $length = count($array) - 1;

            while ($length) {
                $val = $array[$length];

                if ($val == 9) {
                    $array[$length] = 'A';

                    continue;
                }
                if ($val === 'F') {
                    $array[$length] = '0';
                    --$length;
                    continue;
                }

                ++$array[$length];
                --$length;

                break;
            }
        }

        return implode('', $array);
    }

    /**
     * Function __construct() : Create Logger Module
     *
     * @param string    $name
     * @param bool      $autoInit
     */
    public function __construct(string $name = '', bool $autoInit = true)
    {
        $backTrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);

        if (!isset($backTrace[1])) {
            $backTrace[1] = [
                'file'     => $backTrace[0]['file'],
                'function' => 'entryPoint',
                'type'     => '::',
                'class'    => basename($backTrace[0]['file']),
            ];
        }

        // # BADCODE:
        $this->owner = [
            'type'     => $backTrace[1]['type'],
            'function' => $backTrace[1]['function'],
            'class'    => $backTrace[1]['class'],
        ];

        if (isset($backTrace[1]['file'])) {
            $this->owner['file'] = $backTrace[1]['file'];
        }
        // # BADCODE;

        // force use name
        if (empty($name)) {
            $this->warning("name cannot be empty, forcing to use owner class name: `{$this->getOwnerClass()}`");

            $name = $this->owner['class'];
        }

        // add static prefix
        if ($this->owner['type'] === '::') {
            $name = 's\\' . $name;
        }

        if ($autoInit) {
            $this->initialize($name);
        }
    }

    /**
     * Function __destruct() : Destruct Logger
     */
    public function __destruct()
    {
        if (!$this->initialized) {
            return;
        }

        $this->debug("destroying logger with name: `{$this->getName()}` instance: `{$this->getUnique()}`");

        --self::$loggersCount;
    }

    /**
     * Function initialize() : Initialize Logger
     * 
     * @param string $name
     * @param string $dateFormat
     * 
     * @return void
     */
    public function initialize(string $name, string $dateFormat = "Y-m-d H:i:s.u")
    {
        $this->unique = self::getFreeIndex();

        // # TODO: this is have to be configurable
        $output = "[%datetime%][%level_name%][" . $this->unique . "][%channel%]%message%\n";

        $streamHandlerStdout = new \Monolog\Handler\StreamHandler('php://stdout');

        $formatter = new \Monolog\Formatter\LineFormatter($output, $dateFormat);
        $formatter->allowInlineLineBreaks(true);

        $streamHandlerStdout->setFormatter($formatter);

        parent::__construct($name, [$streamHandlerStdout]);

        // each instance will passthru handler
        self::handler($this);

        // without that our module will not work
        $this->initialized = true;

        $this->debug("created new logger with name: `{$this->getName()}` caller method: `{$this->getOwnerMethod()}`", [0]);
    }

    /**
     * Function callBackSet() : Setting callback
     * 
     * @param mixed $object
     * @param mixed $function
     * @param string $token
     * 
     * @return string
     */
    public function callBackSet($object, $function, $token)
    {
        if (!$this->initialized) {
            return;
        }

        $this->callbacksTokens[$token] = [$object, $function, $token];

        return $token;
    }

    /**
     * Function callBackDeclare() Declare callback
     * 
     * @param string $token
     * 
     * @return void
     */
    public function callBackDeclare($token)
    {
        if (!$this->initialized) {
            return;
        }

        $callback = $this->callbacksTokens[$token];

        $object   = $callback[0];
        $function = $callback[1];

        $selfFunction = __FUNCTION__;
        $selfClass    = __CLASS__;

        parent::addRecord(\Monolog\Logger::DEBUG, "\033[1m[$selfClass][$selfFunction]: `{$object}::{$function}` ->\033[0m\e[38;5;38m\e[1m with callback `{$token}`\033[0m\033[0m", [1]);
    }

    /**
     * Function callBackFire() : Fire callback
     * 
     * @param string   $token
     * @param mixed    $msg
     * @param string   $context
     * 
     * @return void
     */
    public function callBackFire(string $token, $msg, string $context = '')
    {
        if (!$this->initialized) {
            return;
        }

        // object|array -> json
        if (is_object($msg) || is_array($msg)) {
            $msg = json_encode($msg);
        }

        if (!isset($this->callbacksTokens[$token])) {
            $this->warning("token: `{$token}` not found, msg: `{$msg}` context: `{$context}`");

            return;
        }

        $callback = $this->callbacksTokens[$token];

        // # CRITICAL
        unset($this->callbacksTokens[$token]);

        $object   = $callback[0];
        $function = $callback[1];

        $selfFunction = __FUNCTION__;
        $selfClass    = __CLASS__;


        if (empty($msg)) {
            if ($context == 'halt-on-null') {
                parent::addRecord(\Monolog\Logger::DEBUG, "\033[1m[$selfClass][$selfFunction]: `halt-on-null` ->\033[0m NULL \033[1m->\e[38;5;38m\e[1m callback `{$token}\033[0m`", [0]);
                return;
            }

            $msg = "null";
        }

        parent::addRecord(\Monolog\Logger::DEBUG, "\033[1m[$selfClass][$selfFunction]: `{$object}::{$function}` ->\033[0m {$msg} \033[1m->\e[38;5;38m\e[1m callback `{$token}\033[0m`", [0]);
    }

    /**
     * Function debugJson() : Print object in JSON Format.
     * 
     * @param mixed     $content
     * @param string    $name
     * 
     * @return void
     */
    public function debugJson($content, string $name = '')
    {
        if (!$this->initialized) {
            return;
        }

        $this->debug($name, ['json' => $content]);
    }

    /**
     * Function okFailed() : Print ok or failed log
     * 
     * @param bool      $status
     * @param string    $what
     * @param mixed     $object
     * 
     * @return void
     */
    public function okFailed(bool $status, string $what, $object = null)
    {
        if (!$this->initialized) {
            return;
        }

        $out = 'STATUS: ';

        if (is_object($object)) {
            $object = get_class($object);

            $out .= "\e[1mObject: `{$object}`\e[0m";
        }

        $out .= " => `\e[2m{$what}\e[25m\e[0m` ";

        $this->addRecord(\Monolog\Logger::DEBUG, $out, ['ok-failed' => $status]);
    }

    /**
     * Function addRecord() : Adds a log record.
     *
     * @todo rewrite this function, and optimize
     *
     * @param int	$level
     * @param string	$message
     * @param array	$context
     */
    public function addRecord(int $level, string $message, array $context = []) : Bool
    {
        if (!$this->initialized) {
            return false;
        }

        // so let use context to get depth in this way till it has to be extended
        // limit
        $debugTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
        $depth      = 2;

        if (isset($context['exception'])) {
            $level     = \Monolog\Logger::CRITICAL;
            $exception = $context['exception'];

            \Core\Auxiliary::shutdown($exception->getCode(), [
                'msg'  => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
        } elseif (isset($context[0])) {
            $depth = $context[0] + 2; // Should i?
        } else if (isset($context['json'])) {
            $json  = $context['json'];
            $depth = 3;
            unset($context['json']);
        }

        // override
        if (isset($context['depth'])) {
            $depth = $context['depth'];

            unset($context['depth']);
        }

        if (isset($context['ok-failed'])) {
            if ($context['ok-failed']) {
                $message .= "- \e[92m[  OK  ]\e[39m\t";
            } else {
                $message .= "- \e[91m[ FAILED ]\e[39m\t";
            }

            unset($context['ok-failed']);
        }

        $out = '';

        switch ($level) {
            case \Monolog\Logger::CRITICAL:
                $messageMagic = "\e[1m\e[97;41m{$message}\033[0m";
                break;

            case \Monolog\Logger::ERROR:
                $messageMagic = "\e[1m\e[38;5;1m{$message}\033[0m";
                break;

            case \Monolog\Logger::NOTICE:
                $messageMagic = "\e[38;5;208m\e[1m{$message}\033[0m";
                break;

            case \Monolog\Logger::INFO:
                $messageMagic = "\e[38;5;195m\e[1m{$message}\033[0m";
                break;

            case \Monolog\Logger::WARNING:
                $messageMagic = "\e[38;5;226m\e[1m{$message}\033[0m";
                break;

            default:
                $messageMagic = $message;
        }

        if (isset($debugTrace[$depth])) {
            if (isset($debugTrace[$depth]['class'])) {
                $class = $debugTrace[$depth]['class'];
            } else {
                $class = $debugTrace[$depth]['file'];
            }

            $func = $debugTrace[$depth]['function'];

            //$out .= "\033[1m[$name][$class][$func]\033[0m: $message";
            $out .= "\033[1m[$class][$func]\033[0m: $messageMagic";
        } elseif (isset($debugTrace[0])) {
            $out .= '[' . basename($debugTrace[0]['file']) . ']:' . $message;
        } else {
            $out .= '[' . basename($exception->getFile()) . ']:' . $message;
        }

        if (isset($json)) {
            if ($message) {
                $out .= ': >>';
            }

            $json = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $out .= PHP_EOL . $json;
        }

        return parent::addRecord($level, $out, array()); /*$context);*/
    }

    /**
     * Function getOwnerFile() : Get Owner Filename
     * 
     * @return string
     */
    public function getOwnerFile()
    {
        return isset($this->owner['file']) ? $this->owner['file'] : null;
    }

    /**
     * Function getOwnerClass() : Get Owner Class
     * 
     * @return string
     */
    public function getOwnerClass()
    {
        return isset($this->owner['class']) ? $this->owner['class'] : null;
    }

    /**
     * Function getOwnerFunction() : Get Owner Function
     * 
     * @return string
     */
    public function getOwnerFunction()
    {
        return isset($this->owner['function']) ? $this->owner['function'] : null;
    }

    /**
     * Function getOwnerType() : Get Owner Type
     * 
     * @return string
     */
    public function getOwnerType()
    {
        return isset($this->owner['type']) ? $this->owner['type'] : null;
    }

    /**
     * Function getOwnerMethod() : Get Owner Method
     * 
     * @return string
     */
    public function getOwnerMethod()
    {
        // # CRITICAL
        return $this->getOwnerClass() . $this->getOwnerType() . $this->getOwnerFunction();
    }

    /**
     * Function getUnique() : Get Unique
     * 
     * @return string
     */
    public function getUnique()
    {
        return $this->unique;
    }
}
