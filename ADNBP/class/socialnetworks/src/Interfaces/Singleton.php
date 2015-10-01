<?php
namespace CloudFramework\Service\SocialNetworks\Interfaces;

class Singleton {
    /**
     * @var array Singleton cached reference to singleton instance
     */
    protected static $instance = array();

    /**
     * gets the instance via lazy initialization (created on first usage)
     *
     * @return $this
     */
    public static function getInstance()
    {
        $class = get_called_class();
        if (!array_key_exists($class, self::$instance) || !self::$instance[$class] instanceof $class) {
            self::$instance[$class] = new $class(func_get_args());
        }
        return self::$instance[$class];
    }

    /**
     * getInstance alias
     * @return mixed
     */
    public static function create() {
        return self::getInstance();
    }
}