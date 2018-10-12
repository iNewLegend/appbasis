<?php
/**
@file:services/config/base.php
 * @author: Leonid Vinikov <czf.leo123@gmail.com>
 */

namespace Services\Config;

abstract class Base implements BaseInterface
{
    /**
     * The Logger instance
     *
     * @var \Modules\Logger
     */
    protected $_logger;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    protected $_owner = null;

    /**
     * Undocumented variable
     *
     * @var ReflectionClass
     */
    private $_self;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private $_keys = [];

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private $_accessToken;

    /**
     * Undocumented function
     */
    public function __construct()
    {
        $this->_logger = new \Modules\Logger(self::class, \Config\Logger::$service_config);
        $this->_self = new \ReflectionClass(get_class($this));

        $this->initialize();
    }
    
    /**
     * Undocumented function
     *
     * @return void
     */
    protected function loadKeys()
    {
        try {
            $publicKeys = $this->_self->getProperties(\ReflectionProperty::IS_PUBLIC);
            $protectedKeys = $this->_self->getProperties(\ReflectionProperty::IS_PROTECTED);
        } catch(\Exception $e) {
            $this->logger->error($e);
        }

        $this->_keys = [];

        foreach($publicKeys as $key) {
            $this->_keys[\ReflectionProperty::IS_PUBLIC][$key->name] = $key->name;
        }

        foreach($protectedKeys as $key) {
            $this->_keys[\ReflectionProperty::IS_PROTECTED][$key->name] = $key->name;
        }
    }

    /**
     * Config Initialize
     *
     * @return void
     */
    public function initialize()
    {
        $this->_logger->debug("config loaded `{$this->_self->name}`");
    }

    /**
     * Undocumented function
     *
     * @param [type] $key
     * @return void
     */
    public function protect($key)
    {
        $this->_accessToken = $key;
        $this->_logger->info("set access key: `{$this->_accessToken}`");
    }

    /**
     * Undocumented function
     *
     * @param [type] $accessToken
     * @return void
     */
    public function getAll($accessToken = null)
    {
        $return = [];

        $this->loadKeys();

        $keysVisibility = [\ReflectionProperty::IS_PUBLIC, \ReflectionProperty::IS_PROTECTED];

        foreach($keysVisibility as $visibility) {
            if(! isset($this->_keys[$visibility])) {
                continue;
            }

            if($visibility == \ReflectionProperty::IS_PROTECTED) {
                if(empty($this->_accessToken)) {
                    $this->_logger->warn("`{get_called_class()}` trying access protected data but access token is empty");
                    continue;
                } elseif($accessToken !== $this->_accessToken) {  
                    continue;
                } 
            }
            
            foreach($this->_keys[$visibility] as $key) {
                // skip self props
                if($key[0] == '_') continue;

                // critical
                $return[$key] = $this->$key;
            }
        }

        return $return;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function __toString()
    {
        $arr = [];

        foreach($this->selfProps as $prop) {
            $arr[$prop->name] = $this->{$prop->name};
        }
        
        return \json_encode($arr, JSON_PRETTY_PRINT);
    }

    /**
     * Called when requesting non exist member
     *
     * @param string $param
     * @return void
     */
    public function __get($param)
    {
        $this->_logger->error("param: `$param` not found");

        return null;
    }
} // EOF base.php
