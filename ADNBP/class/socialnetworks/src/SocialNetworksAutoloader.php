<?php
/**
 * Cloud Service Autoloader for Social Network Service
 */

if(!class_exists("SocialNetworksAutoloader")) {
    /**
     * Class SocialNetworkAutoloader
     * @author Fran López <fl@bloombees.com>
     * @version 1.0
     */
    class SocialNetworksAutoloader {

        /**
         * Autoloader class function
         * @param $class
         * @return bool
         */
        public static function loadClass($class) {
            // it only autoload class into the Rain scope
            if (strpos($class, 'CloudFramework') !== false && strpos($class, 'Service') !== false  && strpos($class, 'SocialNetworks') !== false) {
                // Change order src
                $path = str_replace("\\", DIRECTORY_SEPARATOR, $class);
                $path = str_replace('CloudFramework' . DIRECTORY_SEPARATOR . 'Service' . DIRECTORY_SEPARATOR . 'SocialNetworks', '', $path);
                // transform the namespace in path
                // filepath
                $abs_path = __DIR__ . $path . ".php";
                // require the file
                if (file_exists($abs_path)) {
                    require_once $abs_path;
                }
            }
            return false;
        }
    }
}
spl_autoload_register(array('SocialNetworksAutoloader', 'loadClass'), true, true);
