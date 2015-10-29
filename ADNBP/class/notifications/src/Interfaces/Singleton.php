<?php
namespace CloudFramework\Service\Notifications\Interfaces;

class Singleton {
    private static $instance = array();
    /**
     * @var float $loadTs
     */
    protected $loadTs = 0;
    /**
     * @var float $loadMem
     */
    protected $loadMem = 0;

    /**
     * Singleton instance generator
     * @return $this
     */
    public static function getInstance()
    {
        $ts = microtime(true);
        $class = get_called_class();
        if (!array_key_exists($class, self::$instance) || null === self::$instance[$class]) {
            self::$instance[$class] = self::instanceClass($class, $ts, func_get_args());
        }
        return self::$instance[$class];
    }

    /**
     * Instance generator alias
     * @return $this
     */
    public static function create()
    {
        return self::getInstance(func_get_args());
    }

    /**
     * Generic constructor for all Singleton classes
     * @param string $class
     * @param float $ts
     * @param array $args
     * @return $this
     */
    private static function instanceClass($class, $ts, array $args = array())
    {
        $reflectionClass = new \ReflectionClass($class);
        /** @var \CloudFramework\Service\Notifications\Interfaces\Singleton $instanceClass */
        if (null !== $reflectionClass->getConstructor() && $reflectionClass->getConstructor()->getNumberOfParameters() > 0) {
            $instanceClass = $reflectionClass->newInstanceArgs($args);
        } else {
            $instanceClass = $reflectionClass->newInstance();
        }
        self::initializeInstance($ts, $reflectionClass, $instanceClass);
        unset($reflectionClass);
        return $instanceClass;
    }

    /**
     * Initialize instance with pre-conditions and post-conditions
     * @param float $ts
     * @param \ReflectionClass $reflectionClass
     * @param \CloudFramework\Service\Notifications\Interfaces\Singleton $instanceClass
     */
    private static function initializeInstance($ts, \ReflectionClass $reflectionClass, Singleton $instanceClass)
    {
        try {
            $reflectionClass->getMethod('preInit')->invoke($instanceClass);
        } catch (\Exception $e) {
            //Do nothing
        }
        $instanceClass->init($ts);
        try {
            $reflectionClass->getMethod('postInit')->invoke($instanceClass);
        } catch (\Exception $e) {
            //Do nothing
        }
    }

    /**
     * Calculate timestamp and memory usage for loading class instance
     * @param float $ts
     * @param float $mem
     * @return $this
     */
    public function computePerformance($ts, $mem)
    {
        $this->loadMem = round((memory_get_usage() - $mem) / (1024 * 1024), 4);
        $this->loadTs = round(microtime(true) - $ts, 5);
        return $this;
    }

    /**
     * Inizialization method
     * @param int $ts
     */
    public function init($ts) {
        $this->loadTs = $ts ?: microtime(true);
    }
}