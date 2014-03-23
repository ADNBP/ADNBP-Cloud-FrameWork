<?php
/**
 * Opauth example
 * 
 * This is an example on how to instantiate Opauth
 * For this example, Opauth config is loaded from a separate file: opauth.conf.php
 * 
 */

/**
 * Define paths
 */
 $this->setConf("pageCode","oauth");
 
define('CONF_FILE', dirname(__FILE__).'/../class/auth/opauth/'.'opauth.conf.php');
define('OPAUTH_LIB_DIR', dirname(__FILE__).'/../class/auth/opauth/lib/Opauth/');
 

/**
* Load config
*/
if (!file_exists(CONF_FILE)){
    trigger_error('Config file missing at '.CONF_FILE, E_USER_ERROR);
    exit();
}
require CONF_FILE;

/**
 * Instantiate Opauth with the loaded config
 */
 
    list($foo,$this->_basename,$param) = explode('/',$this->_url,3);
    if(strlen($param)) {
        include OPAUTH_LIB_DIR.'Opauth.php';
        $Opauth = new Opauth( $config );
        exit;
    } 
 
?>