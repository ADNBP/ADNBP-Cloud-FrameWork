<?php
###########################################################
# Madrid  nov de 2012
# ADNBP Business & IT Perfomrnance S.L.
# http://www.adnbp.com (info@adnbp.coom)
# Last update: feb 2015
# Project ADNBP Framework
#
#####
# Equipo de trabajo:
#   Héctor López
###########################################################

/**
 * Environment Vars && ClassLoader for ADNBP Framework
 * @version 1.0
 * @author Hector López <hlopez@adnbp.com>
 * @package com.adnbp.framework
 */

function _print() { __print(func_get_args());
}

function _printe() { __print(array_merge(func_get_args(), array('exit')));
}

function __print($args) {
	echo "<pre>";
	for ($i = 0, $tr = count($args); $i < $tr; $i++) {
		if ($args[$i] === "exit")
			exit ;
		echo "\n<li>[$i]: ";
		if (is_array($args[$i]))
			echo print_r($args[$i], true);
		else if (is_object($args[$i]))
			echo var_dump($args[$i]);
		else if (is_bool($args[$i]))
			echo($args[$i]) ? 'true' : 'false';
		else if (is_null($args[$i]))
			echo 'NULL';
		else
			echo $args[$i];
	}
	echo "</pre>";
}

if (!defined("_ADNBP_CLASS_")) {
	define("_ADNBP_CLASS_", TRUE);

	/**
	 * @class ADNBP
	 * Environment Vars && ClassLoader for ADNBP Framework
	 *
	 * @version 1.0
	 * @author Hector López <hlopez@adnbp.com>
	 * @copyright PUBLIC
	 */

	class ADNBP {

		var $_conf = array();
		var $_menu = array();
		var $_sessionVarsFromGet = array();
		var $_lang = "en";
		var $_langsSupported = array("en" => "true");
		var $_parseDic = "";
		// String to parse a dictionary
		var $_dic = array();
		var $_dics = array();
		var $_translations = array();
		var $_pageContent = array();
		var $_charset = "UTF-8";
		var $_url = '';
		var $_urlParams = '';
		var $_urlParts = array();
		var $_scriptPath = '';
		var $_ip = '';
		var $_geoData = null;
		var $_userAgent = '';
		var $_userLanguages = array();
		var $_basename = '';
		var $_isAuth = false;
		var $_version = "2015Jan.09";
		var $_defaultCFURL = "https://cloud.adnbp.com/api";
		var $_webapp = '';
		var $_webappURL = '';
		var $_rootpath = '';
		var $_timeZone = 'Europe/Madrid';
		var $error = false;
		var $errorMsg = '';
		var $_timePerformance = array();
		var $_cache = null;
		var $_format = array();
		var $_mobileDetect = null;
		var $__performance = null;

		/**
		 * Constructor
		 */
		function ADNBP($session = true, $sessionId = '', $rootpath = '') {
			global $__performance;
			$this->__performance = &$__performance;
			__addPerformance('LOAD CLASS & INIT OBJECT $this '.__CLASS__.'-'.__FUNCTION__,__FILE__);

			if ($session) {
				if (strlen($sessionId))
					session_id($sessionId);
				session_start();

			}
			__addPerformance('session_start: ','','memory');
			// If the call is just to KeepSession
			if (strpos($this -> _url, '/CloudFrameWorkService/keepSession') !== false) {
				header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
				header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // past date en el pasado
				$bg = (isset($_GET['bg']) && strlen($_GET['bg'])) ? $_GET['bg'] : 'FFFFFF';
				die('<html><head><title>ADNBP Cloud FrameWork KeepSession ' . time() . '</title><meta name="robots" content="noindex"></head><body bgcolor="#' . $bg . '"></body></html>');
			}

			// Temporary bug workaround
			// https://code.google.com/p/googleappengine/issues/detail?id=11695#c6
			apc_delete('_ah_app_identity_:https://www.googleapis.com/auth/devstorage.read_only');
			apc_delete('_ah_app_identity_:https://www.googleapis.com/auth/devstorage.read_write');
			 
			// About timeZone
			/*
			 if(!strlen(date_default_timezone_get()))
			 date_default_timezone_set($this->_timeZone);
			 else $this->_timeZone = date_default_timezone_get();
			 */
			date_default_timezone_set($this -> _timeZone);
			$this -> _format['decimalPoint'] = ",";
			$this -> _format['thousandSep'] = ".";

			// $this->_webapp = dirname(dirname(__FILE__))."/webapp";
			// $this->_rootpath = dirname(dirname(dirname(__FILE__)));
			if (!strlen($rootpath))
				$rootpath = $_SERVER['DOCUMENT_ROOT'];
			$this -> _rootpath = $rootpath;
			$this -> _webapp = $rootpath . "/webapp";

			// Paths
			// note: in Google Apps Engine PHP doen't work $_SERVER: PATH_INFO or PHP_SELF
			if (strpos($_SERVER['REQUEST_URI'], '?') !== null)
				list($this -> _url, $this -> _urlParams) = explode('?', $_SERVER['REQUEST_URI'], 2);
			else
				$this -> _url = $_SERVER['REQUEST_URI'];

			$this -> _scriptPath = $_SERVER['SCRIPT_NAME'];
			$this -> _ip = $_SERVER['REMOTE_ADDR'];
			$this -> _userAgent = $_SERVER['HTTP_USER_AGENT'];
			$this -> _urlParts = explode('/',$this-> _url);



			$_configs ='';
			//  Use this file to assign webApp. $this->setWebApp(""); if not ADNBP/webapp will be included
			if (is_file($this -> _rootpath . "/adnbp_framework_config.php")) {
				include_once ($this -> _rootpath . "/adnbp_framework_config.php");
				$_configs.='/adnbp_framework_config.php - ';
			}
			// CONFIG VARS
			// load FrameWork default values
			include_once ($this -> getRootPath() . "/ADNBP/config/config.php");
			$_configs.='/ADNBP/config/config.php - ';
			// load Defaults Values

			// load webapp config values
			if (is_file($this -> _webapp . "/config/config.php")) {
				include_once ($this -> _webapp . "/config/config.php");
				$_configs.=$this -> _webappURL.'/config/config.php - ';
				
			}
			// load bucket config values. Use this to keep safely passwords etc.. in a external bucket only accesible by admin
			if (strlen($this -> getConf('ConfigPath')) && is_file($this -> getConf('ConfigPath') . "/config.php")) {
				include_once ($this -> getConf('ConfigPath') . "/config.php");
				$_configs.=$this -> getConf('ConfigPath') . "/config.php";
			}
			// For development purpose find local_config.php. Don't forget to add **/local_config.php in .gitignore
			if ($_SERVER['SERVER_NAME'] == 'localhost') {
				if (is_file($this -> _rootpath . "/local_config.php")) {
					include_once ($this -> _rootpath . "/local_config.php");
					$_configs.='/local_config.php - ';
				}
				if (is_file($this -> _webapp . "/local_config.php")) {
					include_once ($this -> _webapp . "/local_config.php");
					$_configs.=$this -> _webappURL.'/local_config.php - ';
				}
			}

			__addPerformance('load configs: ', $_configs);unset($_configs);

			// OTHER VARS
			// CloudService API. If not defined it will point to ADNBP external Serivice
			if (!$this -> getConf("CloudServiceUrl"))
				$this -> setConf("CloudServiceUrl", $this -> _defaultCFURL);

			// analyze Default Lang and its configuration
			$this -> _userLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

			if (strpos($this -> _url, '/CloudFrameWork') !== false || strpos($this -> _url, '/api') !== false)
				$this -> setConf("setLanguageByPath", false);

			// LANG SUPPORT
			if (strlen($this -> getConf("langDefault")))
				$this -> _lang = $this -> getConf("langDefault");

			if ($this -> getConf("setLanguageByPath")) {
				$elems = explode("/", $this -> _url);
				if (isset($elems[1]) && strlen($elems[1]) && isset($elems[2]) && strlen($elems[2]))
					$this -> _lang = $elems[1];
			} else {
				if (strlen($_GET['lang']))
					$this -> setSessionVar('adnbplang', $_GET['lang']);
				if (strlen($this -> getSessionVar('adnbplang')))
					$this -> _lang = $this -> getSessionVar('adnbplang');
			}

			// Control langsSupported
			if (strlen($this -> getConf("langsSupported")) && strpos($this -> getConf("langsSupported"), $this -> _lang) === false) {
				$this -> _lang = (strlen($this -> getConf("langDefault"))) ? $this -> getConf("langDefault") : 'en';

				// Rewrite session with the right lang
				if (!$this -> getConf("setLanguageByPath") && strlen($_GET['lang']))
					$this -> setSessionVar('adnbplang', $this -> _lang);
			}

			// Set to lang conf var the current lang
			$this -> setConf("lang", $this -> _lang);
			
			__addPerformance('END '.__CLASS__.'-'.__FUNCTION__);			

		}

		function version() {
			return ($this -> _version);
		}

		function getRootPath() {
			return ($this -> _rootpath);
		}

		function getWebAppPath() {
			return ($this -> _webapp);
		}

		function getWebAppURL() {
			return ($this -> _webappURL);
		}

		function setWebApp($dir) {
			if (!is_dir($this -> _rootpath . $dir))
				die($dir . " doesn't exist. The path has to begin with /");
			else {
				$this -> _webapp = $this -> _rootpath . $dir;
				$this -> _webappURL = $dir;
			}
		}

		function _checkParameter(&$_data, $var, $saveInSession = false, $resetIfEmpty = false, $method = 'request') {

			if (strlen($this -> getConf($var)))
				$_data[$var] = $this -> getConf($var);
			else {
				if ($method == 'request')
					$val = $_REQUEST[$var];
				else if ($method == 'get')
					$val = $_GET[$var];
				else if ($method == 'post')
					$val = $_POST[$var];

				if ($resetIfEmpty || strlen($val))
					$_data[$var] = $val;
				//Force to get empty values

				if ($saveInSession)
					if ($resetIfEmpty) {
						$this -> setSessionVar($var, $_data[$var]);
					} else {
						if (!strlen($_data[$var]))
							$_data[$var] = $this -> getSessionVar($var);
						else {
							$this -> setSessionVar($var, $_data[$var]);
						}
					}
			}
		}

		function checkGetParameter(&$_data, $var, $saveInSession = false, $resetIfEmpty = false) {$this -> _checkParameter($_data, $var, $saveInSession, $resetIfEmpty, 'get');
		}

		function checkPostParameter(&$_data, $var, $saveInSession = false, $resetIfEmpty = false) {$this -> _checkParameter($_data, $var, $saveInSession, $resetIfEmpty, 'post');
		}

		function checkRequestParameter(&$_data, $var, $saveInSession = false, $resetIfEmpty = false) {$this -> _checkParameter($_data, $var, $saveInSession, $resetIfEmpty, 'request');
		}

		function getGeoPlugin($ip = 'REMOTE') {
			if (!strlen($ip))
				$ip = 'REMOTE';
			if ($ip == 'REMOTE')
				$ip = $this -> _ip;
			if ($ip == '::1' || $ip = '127.0.0.1')
				$ip = '';
			return (unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $ip)));
		}

		function readGeoData($ip = 'REMOTE', $reload = false) {
			if (!strlen($ip))
				$ip = 'REMOTE';

			if (isset($this -> _geoData['reloaded'][$ip]) || !$reload || $this -> _geoData === null || !is_array($this -> _geoData[$ip]))
				$this -> _geoData[$ip] = $this -> getSessionVar('geoPluggin_' . $ip);
			

			if (!isset($this -> _geoData['reloaded'][$ip]) &&
			    ($reload || $this -> _geoData === null || !is_array($this -> _geoData[$ip]))) {
				__addPerformance('Calling geoData service '.__FUNCTION__,__FILE__,'time');
				$this -> _geoData[$ip] = array();
				$data = $this -> getGeoPlugin($ip);
				__addPerformance('receiving geoData  ','http://www.geoplugin.net/php.gp?ip=' . $ip,'time');
				

				foreach ($data as $key => $value) {
					$key = str_replace('geoplugin_', '', $key);
					$this -> _geoData[$ip][$key] = $value;
				}
				$this -> setSessionVar('geoPluggin_' . $ip, $this -> _geoData[$ip]);
				
				//avoid to call service twice in the same script
				$this -> _geoData['reloaded'][$ip] = true;
			}
		}

		function getGeoData($var, $ip = 'REMOTE') {

			if ($this -> _geoData === null || !is_array($this -> _geoData[$ip]) || isset($_GET['reload'])) {
				$this -> readGeoData($ip, isset($_GET['reload']));
			}

			if (is_array($this -> _geoData[$ip])) {
				if (!empty($this -> _geoData[$ip][$var])) {
					return ($this -> _geoData[$ip][$var]);
				} else {
					return ('Key not found. Use: ' . implode(array_keys($this -> _geoData[$ip])));
				}
			} else {
				return ('Error reading GeoData');
			}
		}
		function setGeoData($var, $value,$ip = 'REMOTE') {
			$this -> _geoData[$ip][$var] = $value;
			$this -> setSessionVar('geoPluggin_' . $ip, $this -> _geoData[$ip]);
		}
		/**
		 * Class Loader
		 */
		function loadClass($class) {
			if (is_file(dirname(__FILE__) . "/" . $class . ".php"))
				include_once (dirname(__FILE__) . "/" . $class . ".php");
			else
				die("$class not found");
		}

		function init(&$obj, $type) {
			switch ($type) {
				case 'db' :
					$this -> loadClass("db/CloudSQL");
					$db = new CloudSQL();
					$db -> connect();
					break;
				default :
					break;
			}
		}

		function getCloudServiceURL($add = '') {
			// analyze Default Country
			if (!$this -> getConf("CloudServiceUrl"))
				$this -> setConf("CloudServiceUrl", $this -> _defaultCFURL);

			if (strpos($this -> getConf("CloudServiceUrl"), "http") === false)
				$_url = "http://" . $_SERVER['HTTP_HOST'] . $this -> getConf("CloudServiceUrl");
			else
				$_url = $this -> getConf("CloudServiceUrl");

			if (strlen($add))
				$add = '/' . $add;
			return ($_url . $add);
		}

		/**
		 * Call External Cloud Service
		 */
		function getCloudServiceResponseCache($rute, $data = null, $verb = null, $extraheaders = null, $raw = false) {
		    $_qHash = hash('md5',$rute.json_encode($data).$verb);	
			$ret = $this->getCache($_qHash);
			if(isset($_GET['reload']) || $ret===false || $ret === null) {
				$ret = $this->getCloudServiceResponse($rute, $data , $verb , $extraheaders , $raw );
				$this->setCache($_qHash,$ret);
			}	
			return($ret);
		}
		function getCloudServiceResponse($rute, $data = null, $verb = null, $extraheaders = null, $raw = false) {
			__addPerformance('Start getCloudServiceResponse: ',"$rute " . json_encode($data),'time');
			

			if (strpos($rute, 'http') !== false)
				$_url = $rute;
			else
				$_url = $this -> getCloudServiceURL($rute);

			if ($data !== null && is_array($data) && $verb === null or $verb == 'POST') {
				$verb = 'POST';

				if (!$raw) {
					$build_data = http_build_query($data);
					$options = array('http' => array('header' => "Content-type: application/x-www-form-urlencoded\r\n", 'method' => 'POST', 'ignore_errors' => '1', 'content' => $build_data, ));
				} else {
					$build_data = json_encode($data);
					$options = array('http' => array('header' => "Content-type: application/raw\r\n", 'method' => 'POST', 'ignore_errors' => '1', 'content' => $build_data, ));
				}

				if ($extraheaders !== null && is_array($extraheaders)) {
					foreach ($extraheaders as $key => $value) {
						$options['http']['header'] .= $key . ': ' . $value . "\r\n";
					}
				}

				// You have to calculate the Content-Length to run as script
				$options['http']['header'] .= sprintf('Content-Length: %d', strlen($build_data)) . "\r\n";

				if (strlen($this -> getConf("CloudServiceId")) && strlen($this -> getConf("CloudServiceToken"))) {
					$_date = time();
					$options['http']['header'] .= 'X-Cloudservice-Date: ' . $_date . "\r\n";
					$options['http']['header'] .= 'X-Cloudservice-Id: ' . $this -> getConf("CloudServiceId") . "\r\n";
					$options['http']['header'] .= 'X-Cloudservice-Signature: ' . strtoupper(sha1($this -> getConf("CloudServiceId") . $_date . $this -> getConf("CloudServiceToken"))) . "\r\n";
				}
				$options['http']['header'] .= 'Connection: close' . "\r\n";
				$context = stream_context_create($options);
				$ret = @file_get_contents($_url, false, $context);
				if($ret===false) $this->setError(error_get_last());

			} else {
				$options = array('http' => array('method' => 'GET', 'ignore_errors' => '1', ));
				if ($extraheaders !== null && is_array($extraheaders)) {
					foreach ($extraheaders as $key => $value) {
						$options['http']['header'] .= $key . ': ' . $value . "\r\n";
					}
				}

				if ($verb === null)
					$verb = 'GET';
				$_extraGET = '?';
				if (is_array($data)) {
					$_url .= '?';
					foreach ($data as $key => $value)
						$_url .= $key . '=' . urlencode($value) . '&';
				}

				$options['http']['header'] .= 'Connection: close' . "\r\n";
				$context = stream_context_create($options);
				$ret = @file_get_contents($_url, false, $context);
				if($ret===false) $this->setError(error_get_last());

			}
			__addPerformance('Received getCloudServiceResponse: ');
			
			return ($ret);
		}

		function checkBasicAuth($user, $passw) {
			include_once (dirname(__FILE__) . '/ADNBP/checkBasicAuth.php');
			return $res;
		}

		function checkAPIAuth(&$msg) {
			include_once (dirname(__FILE__) . '/ADNBP/checkAPIAuth.php');
			if (strlen($msgerror)) { $msg .= $msgerror;
				return (false);
			} else
				return (true);
		}

		function getAPIMethod() {
			return ((strlen($_SERVER['REQUEST_METHOD'])) ? $_SERVER['REQUEST_METHOD'] : 'GET');
		}

		function checkAPIMethod($methods) {
			return (strpos(strtoupper($methods), $this -> getAPIMethod()) !== false);
		}

		function getAPIRawData() {
			return (file_get_contents("php://input"));
		}

		function getAPIPutData() {
			parse_str(file_get_contents("php://input"), $ret);
			return ($ret);
		}

		function getDataFromAPI($rute, $data = null, $verb = 'GET', $format = 'json', $headers = null) {
			include_once (dirname(__FILE__) . '/ADNBP/getDataFromAPI.php');
			return $res;
		}

		/**
		 * Var confs
		 */
		function setAuth($bool, $namespace = 'CloudUser') {

			if (!strlen($namespace))
				$namespace = $this -> getConf("requireAuth");
			if (!strlen($namespace))
				return false;

			$this -> _isAuth[$namespace]['auth'] = ($bool === true);
			if (!$this -> _isAuth[$namespace]['auth'])
				unset($this -> _isAuth[$namespace]['data']);
			$this -> setSessionVar("CloudAuth", $this -> _isAuth);
		}

		// About User Auth information
		function requireAuth($namespace = 'CloudUser') {
			$this -> setConf("requireAuth", $namespace);
		}

		function isAuth($namespace = '') {
			if (!strlen($namespace))
				$namespace = $this -> getConf("requireAuth");
			if (!strlen($namespace))
				return false;

			if (isset($_GET['logout']) && strlen($_GET['logout']) > 0) {
				$this -> setAuth(false, $namespace);
				Header("Location: $this->_url");
				exit ;
			}

			if ($this -> _isAuth === false && strlen($this -> getConf("requireAuth"))) {
				$this -> _isAuth = $this -> getSessionVar("CloudAuth");
			}
			return ($this -> _isAuth[$namespace]['auth'] === true);
		}

		function setAuthUserData($key, $value, $namespace = '') {
			if (!strlen($namespace))
				$namespace = $this -> getConf("requireAuth");
			if (!strlen($namespace))
				return false;

			$this -> _isAuth[$namespace]['data'][$key] = $value;
			$this -> setAuth(true, $namespace);
		}

		function getAuthUserData($key = '', $namespace = '') {
			if (!strlen($namespace))
				$namespace = $this -> getConf("requireAuth");
			if (!strlen($namespace))
				return false;

			if (strlen($key))
				return ($this -> _isAuth[$namespace]['data'][$key]);
			else
				return ($this -> _isAuth[$namespace]['data']);
		}

		function getAuthUserNameSpace($namespace = '') {
			if (!strlen($namespace))
				$namespace = $this -> getConf("requireAuth");
			if (!strlen($namespace))
				return false;

			return ($this -> _isAuth[$namespace]['data']);
		}

		function setConf($var, $val) {$this -> _conf[$var] = $val;
		}

		function getConf($var = '') {
			if (strlen($var))
				return (((isset($this -> _conf[$var])) ? $this -> _conf[$var] : false));
			else
				return ($this -> _conf);
		}

		function pushMenu($var) { $this -> _menu[] = $var;
		}

		function setSessionVar($var, $value) { $_SESSION['adnbpSessionVar_' . $var] = $value;
		}

		function getSessionVar($var) {
			return ((isset($_SESSION['adnbpSessionVar_' . $var])) ? $_SESSION['adnbpSessionVar_' . $var] : null);
		}

		function getGeoPluginInfo($var) {
			return ((isset($_country['geoplugin_' . $var])) ? $_country['geoplugin_' . $var] : false);
		}

		function getURLBasename() {
			return (basename($this -> _url));
		}

		function getURL() {
			return ($this -> _url);
		}

		/**
		 *  Parse string to dictionary
		 */
		function _parseDic() {
			$_ok = true;
			if (strpos($this -> _parseDic, "ADNBP_DIC_FILE") !== null)
				list($foo, $this -> _parseDic) = explode("ADNBP_DIC_FILE", $this -> _parseDic, 2);

			if (strpos($this -> _parseDic, "adnbp_dic_languages") !== null)
				list($foo, $this -> _parseDic) = explode("adnbp_dic_languages=", $this -> _parseDic, 2);

			list($langs, $this -> _parseDic) = explode("adnbp_dic_var=", $this -> _parseDic, 2);
			if (strlen($langs))
				$lang = explode(",", $this -> _parseDic, 2);

			if (strpos($this -> _parseDic, "adnbp_dic_var=") !== null)
				do {
					$content = '';
					list($content, $this -> _parseDic) = explode("adnbp_dic_var=", $this -> _parseDic, 2);
					$translates = explode("<=>", $content);
					$var = trim($translates[0]);
					if (strlen($var))
						for ($i = 1, $tr = count($translates); $i < $tr; $i++) {
							list($lang, $translate) = explode(",", $translates[$i], 2);
							$this -> setDicContent($var, trim($translate), $lang);
						}
					// if(!strlen($this->getDicContent($var))) $this->setDicContent($var,$translate);  // put a default value for current lang

				} while(strlen($this->_parseDic) && strpos($this->_parseDic, "adnbp_dic_var=")!==null);
		}

		// Dictionaries in method 1.
		function setDicContent($key, $content, $lang = "") {
			if (!strlen($lang))
				$lang = $this -> _lang;
			$this -> _dic[$key][$lang] = $content;
		}

		function getDicContent($key, $lang = "") {
			if (!strlen($lang))
				$lang = $this -> _lang;
			return ((strlen($this -> _dic[$key][$lang])) ? $this -> _dic[$key][$lang] : $key);
		}

		function getDicContentInHTML($key, $lang = "") {
			if (!strlen($lang))
				$lang = $this -> _lang;
			return ((strlen($this -> _dic[$key][$lang])) ? str_replace("\n", "<br />", htmlentities($this -> _dic[$key][$lang], ENT_COMPAT | ENT_HTML401, $this -> _charset)) : htmlentities($key));
		}

		// Dictionaries in method 2
		function t($dic, $key, $raw = false, $lang = '') {
			
			$dic = str_replace('..', '', trim($dic));
			// Security issue to avoid includes ../
			$dic = str_replace('/', '', $dic);
			// Security issue to avoid includes ../
			if (!strlen($lang))
				$lang = $this -> _lang;

			// Load dictionary repository
			if (!isset($this -> dics[$dic])) {
				$this -> _translations[$dic] = $this -> readTranslationKeys($dic, $lang);
				__addPerformance("getDicContentInHTML()->readTranslationKeys($dic)");
				$this -> dics[$dic] = true;
			}
			$ret = isset($this -> _translations[$dic] -> $key) ? $this -> _translations[$dic] -> $key : $dic . '-' . $key;
			return (($raw) ? $ret : str_replace("\n", "<br />", htmlentities($ret, ENT_COMPAT | ENT_HTML401, $this -> _charset)));
		}

		function readTranslationKeys($dic, $lang = '') {
			
			if ($lang == '')
				$lang = $this -> _lang;

			// Eval return cache to read Dics
    		$_qHash = hash('md5','readTranslationKeys->'.$dic.'_'.$lang);	
			if(!is_object($this -> _cache['object'])) $this->initCache();
			if(!isset($_GET['reloadDictionaries']) && !isset($_GET['reload']) && is_object($this -> _cache['object']) ) {
				$ret = $this->getCache($_qHash);
				if(is_object($ret)) return($ret);
			}


			// Security control because we write local files
			$patron = '/[^a-zA-Z0-9_-]+/';
			$filename = '/' . preg_replace($patron, '', $lang) . '_' . preg_replace($patron, '', $dic) . '.json';

			// If I have defined a specific Path out of the webapp I can write from external service.
			if (strlen($this -> getConf("LocalizePath")) && is_dir($this -> getConf("LocalizePath"))) {

				// Try to get the dictionary dinamically if ApiDictionaryURL is defined and to write locally
				if (strlen($this -> getConf("ApiDictionaryURL")) && (!is_file($this -> getConf("LocalizePath") . $filename) || isset($_GET['reloadDictionaries']))) {
					if (!strlen($_GET['reloadDictionaries']) || $dic == $_GET['reloadDictionaries']) {

						$content = json_decode($this -> getCloudServiceResponse($this -> getConf("ApiDictionaryURL") . '/' . rawurlencode($dic) . "/$lang"));

						if (!empty($content) && $content -> success) {
							$dic = array();
							foreach ($content->data as $key => $value) {
								$dic[$value -> key] = $value -> $lang;
							}
							file_put_contents($this -> getConf("LocalizePath") . $filename, json_encode($dic));
							__addPerformance('file_put_contents: ',$this -> getConf("LocalizePath") . $filename,'time');
							unset($dic);
						}
					}
				}

				// Read the dictionary if the file exist
				if (is_file($this -> getConf("LocalizePath") . $filename)) {
					$ret =  json_decode(file_get_contents($this -> getConf("LocalizePath") . $filename));
					if(is_object($this -> _cache['object']) ) {
						$this->setCache($_qHash,$ret);
					}
					return($ret);
									}
			} else {
				if (is_file($this -> webapp . '/localize' . $filename)) {
					$ret =  json_decode($this -> webapp . '/localize' . $filename);
					if(is_object($this -> _cache['object']) ) {
						$this->setCache($_qHash,$ret);
					}
					return($ret);
				}
			}
			return (json_decode('{}'));
		}

		function setPageContent($key, $content) { $this -> _pageContent[$key] = $content;
		}

		function addPageContent($key, $content) { $this -> _pageContent[$key] .= $content;
		}

		function getPageContent($key) {
			return (htmlentities($this -> _pageContent[$key], ENT_SUBSTITUTE));
		}

		function getRawPageContent($key) {
			return ($this -> _pageContent[$key]);
		}
		

		/**
		 * Run method
		 */
		function run() {

			__addPerformance('INIT '.__CLASS__.'-'.__FUNCTION__);
			$this -> _basename = basename($this -> _url);
			$scriptname = basename($this -> _scriptPath);

		// Find out the template based in the URL
			//if URL has CloudFrameWork* & /api has an special treatment
			if (strpos($this -> _url, '/CloudFrameWork') !== false || strpos($this -> _url, '/api') !== false) {

				$this -> setConf("setLanguageByPath", f);
				list($foo, $this -> _basename, $foo) = explode('/', $this -> _url, 3);
				$this -> _basename .= ".php";
				// add .php extension to the basename in order to find logic and templates.

				if (strpos($this -> _url, '/api/') !== false && $this -> _url != '/api/') {
					$this -> setConf("notemplate", true);
				} else {
					$this -> requireAuth();
					$this -> setConf("top", (strlen($this -> getConf("portalHTMLTop"))) ? $this -> getConf("portalHTMLTop") : "CloudFrameWorkTop.php");
					$this -> setConf("bottom", (strlen($this -> getConf("portalHTMLBottom"))) ? $this -> getConf("portalHTMLBottom") : "CloudFrameWorkBottom.php");
					if (is_file($this -> _rootpath . "/ADNBP/templates/" . $this -> _basename)) {
						$this -> setConf("template", $this -> _basename);
					}

				}

			} 
			// else if getConf("notemplate") is not defined
			else if (!$this -> getConf("notemplate")) {

				if (is_file($this -> _webapp . "/config/menu.php"))
					include ($this -> _webapp . "/config/menu.php");

				// looking for a match
				for ($i = 0, $_found = false, $tr = count($this -> _menu); $i < $tr && !$_found; $i++) {
					// Support for /{lang}/perm-link path
					if (strpos($this -> _menu[$i]['path'], "{lang}"))
						$this -> _menu[$i]['path'] = str_replace("{lang}", $this -> _lang, $this -> _menu[$i]['path']);

					if (strpos($this -> _menu[$i]['path'], "{*}")) {
						$this -> _menu[$i]['path'] = str_replace("{*}", '', $this -> _menu[$i]['path']);
						if (strpos($this -> _url, $this -> _menu[$i]['path']) === 0)
							$_found = true;

					} else if ($this -> _menu[$i]['path'] == $this -> _url || (!empty($this -> _menu[$i][$this -> getConf("lang") . "_path"]) && $this -> _menu[$i][$this -> getConf("lang") . "_path"] == $this -> _url))
						$_found = true;

					if ($_found)
						foreach ($this->_menu[$i] as $key => $value) {
							$this -> setConf($key, $value);
						}

					if (!$this -> getConf("notemplate") && !strlen($this -> getConf("top"))) {
						$this -> setConf("top", (strlen($this -> getConf("portalHTMLTop"))) ? $this -> getConf("portalHTMLTop") : "CloudFrameWorkTop.php");
						$this -> setConf("bottom", (strlen($this -> getConf("portalHTMLBottom"))) ? $this -> getConf("portalHTMLBottom") : "CloudFrameWorkBottom.php");
					}
				}

				// If not found in the menu and it doens't have a local template desactive topbottom

				if (!$_found && is_file($this -> _webapp . "/templates/" . $this -> _basename . ".php")) {
					$this -> _basename .= ".php";
				}

			}

			// if it is a permilink
			if ($scriptname == "adnbppl.php") {
				if (!strlen($this -> getConf("template")) && !$this -> getConf("notemplate")) {

					if (is_file($this -> _webapp . "/templates/" . $this -> _basename) || is_file($this -> _rootpath . "/ADNBP/templates/" . $this -> _basename))
						$this -> setConf("template", $this -> _basename);
					elseif (is_file($this -> _webapp . "/logic/" . $this -> _basename) || is_file($this -> _rootpath . "/ADNBP/logic/" . $this -> _basename))
						$this -> setConf("template", "CloudFrameWorkBasic.php");
					elseif (is_file($this -> _webapp . "/templates/404.php") && strpos($this -> _url, '/CloudFrameWork') === false)
						$this -> setConf("template", "404.php");
					else
						$this -> setConf("template", "CloudFrameWork404.php");
				}
			}

		// Dictionaries deprecated
			// Insert global dictionary - deprectated
			if (is_file($this -> _webapp . "/localize/global.txt")) {
				$this -> _parseDic = file_get_contents($this -> _webapp . "/localize/global.txt");
				$this -> _parseDic();
				__addPerformance('Load and parse dics ',$this -> _webappURL . "/localize/global.txt",'memory');
			}

			if (strlen($this -> getConf("dictionary")))
				if (is_file($this -> _webapp . "/localize/" . $this -> getConf("dictionary") . ".txt")) {
					$this -> _parseDic = file_get_contents($this -> _webapp . "/localize/" . $this -> getConf("dictionary") . ".txt");
					$this -> _parseDic();
					__addPerformance('Load and parse dics ',$this -> _webappURL . "/localize/" . $this -> getConf("dictionary") . ".txt",'memory');
				}

		// Create the object to control Auth
			$this -> checkAuth();
			__addPerformance('checkAuth');
			
		// Load Logic
			if (!strlen($this -> getConf("logic"))) {
				if (is_file($this -> _webapp . "/logic/" . $this -> _basename)) {
					include ($this -> _webapp . "/logic/" . $this -> _basename);
					__addPerformance('Logic file: ',$this -> _webappURL. "/logic/" . $this -> _basename);
				} elseif (is_file($this -> _rootpath . "/ADNBP/logic/" . $this -> _basename)) {
					include ($this -> _rootpath . "/ADNBP/logic/" . $this -> _basename);
					__addPerformance('Logic file: ',"/ADNBP/logic/" . $this -> _basename);
				}

			} else {
				if (is_file($this -> _webapp . "/logic/" . $this -> getConf("logic"))) {
					include ($this -> _webapp . "/logic/" . $this -> getConf("logic"));
					__addPerformance('Logic file: ',$this -> _webappURL. "/logic/" . $this -> getConf("logic"));
				} else {
					$output = "No logic Found";
				}
			}

		// Load top
			if (!$this -> getConf("notopbottom") && !$this -> getConf("notemplate") && !isset($_GET['__notop'])) {
				if (!strlen($this -> getConf("top"))) {
					if (is_file($this -> _webapp . "/templates/top.php")){
						include ($this -> _webapp . "/templates/top.php");
					} elseif (is_file("./ADNBP/templates/top.php"))
						include ("./ADNBP/templates/top.php");
				} else {
					if (is_file($this -> _webapp . "/templates/" . $this -> getConf("top"))) {
						include ($this -> _webapp . "/templates/" . $this -> getConf("top"));
					} else if (is_file($this -> _rootpath . "/ADNBP/templates/" . $this -> getConf("top"))) {
						include ($this -> _rootpath . "/ADNBP/templates/" . $this -> getConf("top"));
					} else
						echo "No top file found: " . $this -> getConf("top");

				}
			}
			__addPerformance('Load Top template: ');

		// Load template
			if (!$this -> getConf("notemplate") && !isset($_GET['__notemplate'])) {
				// Content of template is stored in a var 'templateVarContent'
				if (strlen($this -> getConf("templateVarContent"))) {
					$var = $this -> getConf("templateVarContent");
					// exist a var with the content of the template
					echo $$var;

					// Content of template is stored in a file defined in template
				} else {
					if (!$this -> getConf("template")) {
						if (is_file("./templates/" . $this -> _basename)){
							include ("./templates/" . $this -> _basename);
							__addPerformance('Load main template: ',"./templates/". $this -> _basename);
							
						} elseif (is_file($this -> _rootpath . "/ADNBP/templates/" . $this -> _basename)){
							include ($this -> _rootpath . "/ADNBP/templates/" . $this -> _basename);
							__addPerformance('Load main template: ',"/ADNBP/templates/". $this -> _basename);
						} elseif ($this -> getConf("logic") == "nologic") {

						}
					} else {
						if (is_file($this -> _webapp . "/templates/" . $this -> getConf("template"))){
							include ($this -> _webapp . "/templates/" . $this -> getConf("template"));
							__addPerformance('Load main template: ',$this -> _webappURL."/templates/". $this -> getConf("template"));
						} elseif (is_file($this -> _rootpath . "/ADNBP/templates/" . $this -> getConf("template"))){
							include ($this -> _rootpath . "/ADNBP/templates/" . $this -> getConf("template"));
							__addPerformance('Load main template: ',"/ADNBP/templates/". $this -> getConf("template"));
						} else
							echo "No template found: " . $this -> getConf("template");
					}
				}
			}
			
			
			if (!$this -> getConf("notopbottom") && !$this -> getConf("notemplate") && !isset($_GET['__nobottom'])) {
				if (!strlen($this -> getConf("bottom"))) {
					if (is_file($this -> _webapp . "/templates/bottom.php"))
						include ($this -> _webapp . "/templates/bottom.php");
					elseif (is_file($this -> _rootpath . "/ADNBP/templates/bottom.php"))
						include ($this -> _rootpath . "/ADNBP/templates/bottom.php");
				} else {
					if (is_file($this -> _webapp . "/templates/" . $this -> getConf("bottom")))
						include ($this -> _webapp . "/templates/" . $this -> getConf("bottom"));
					elseif (is_file($this -> _rootpath . "/ADNBP/templates/" . $this -> getConf("bottom")))
						include ($this -> _rootpath . "/ADNBP/templates/" . $this -> getConf("bottom"));
					else
						echo "No bottom file found: " . $this -> getConf("bottom");

				}
			}
			__addPerformance('Load Bottom and END '.__CLASS__.'-'.__FUNCTION__);
			
		}

		
		function checkAuth() {
			$_ret = $this -> isAuth();
			if (strlen($this -> getConf("requireAuth"))) {
				if (is_file($this -> _webapp . "/logic/CloudFrameWorkAuth.php"))
					include ($this -> _webapp . "/logic/CloudFrameWorkAuth.php");
				else {
					include ($this -> _rootpath . "/ADNBP/logic/CloudFrameWorkAuth.php");
				}
			}
		}

		/**
		 * Redirect to other URL
		 */
		function urlRedirect($url, $dest = '') {
			if (!strlen($dest)) {
				if ($url != $this -> _url) {
					Header("Location: $url");
					exit ;
				}
			} else if ($url == $this -> _url && $url != $dest) {
				if (strlen($this -> _urlParams)) {
					if (strpos($dest, '?') === false)
						$dest .= "?" . $this -> _urlParams;
					else
						$dest .= "&" . $this -> _urlParams;
				}
				Header("Location: $dest");
				exit ;
			}

		}

		/**
		 * Password checking
		 */
		function crypt($input, $rounds = 7) {
			$salt = "";
			$salt_chars = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
			for ($i = 0; $i < 22; $i++) {
				$salt .= $salt_chars[array_rand($salt_chars)];
			}
			return crypt($input, sprintf('$2a$%02d$', $rounds) . $salt);
		}

		function checkPassword($passw, $compare) {
			return (crypt($passw, $compare) == $compare);
		}

		/*
		 * String replace KeyCodes
		 */
		function strCFReplace($str) {
			$str = str_replace('CURRENT_DATE', date('Y-m-d'), $str);
			$str = str_replace('{DirectoryOrganization_Id}', $this -> getAuthUserData("currentOrganizationId"), $str);
			$str = str_replace('{OrganizationsInGroupId}', (strlen($this -> getAuthUserData("currentOrganizationsInGroupId"))) ? $this -> getAuthUserData("currentOrganizationsInGroupId") : $this -> getAuthUserData("currentOrganizationId"), $str);
			return ($str);
		}

		/*
		 * String with {{lang:...}} codes to apply in a language
		 */
		function applyTranslations($str, $lang) {
			if (!strlen($lang))
				return ($str);
			$str = trim($str);
			// erase no desired chars

			unset($matchs);
			$_expr = "((?!}}).)*";
			preg_match_all('/{{(' . $_expr . ')}}/s', $str, $matchs);
			if (is_array($matchs[0]))
				for ($i = 0, $tr = count($matchs[0]); $i < $tr; $i++)
					if (strpos($matchs[1][$i], 'lang:') !== false) {
						$_defaultIndex = 1;
						$_selectedIndex = -1;

						// Lets find the language to show
						unset($langs);
						$_expr = "((?!}}).)*";
						$langs = explode('lang:', $matchs[0][$i]);
						// preg_match_all('/lang:(.+)/', $matchs[1][$i],$langs);
						for ($j = 1, $tr2 = count($langs); $j < $tr2; $j++) {
							if (preg_match('/^(default|.*,default\[\[)/', $langs[$j]))
								$_defaultIndex = $j;
							if (preg_match('/^(' . $lang . '|.*,' . $lang . '\[\[)/', $langs[$j]))
								$_selectedIndex = $j;
						}
						if ($_selectedIndex < 0)
							$_selectedIndex = $_defaultIndex;

						// Extract the text of that language
						unset($text);
						$_expr = "((?!\]\]).)*";
						preg_match('/\[\[(' . $_expr . ')\]\]/s', $langs[$_selectedIndex], $text);
						$str = str_replace($matchs[0][$i], $text[1], $str);
					} else if (strpos($matchs[1][$i], 'dic:') !== false) {
						list($foo, $dic) = explode('dic:', $matchs[1][$i], 2);
						list($cat_id, $key_id) = explode(',', $dic, 2);
						$str = str_replace($matchs[0][$i], $this -> t($cat_id, $key_id, false, $lang), $str);
					}
			return ($str);
		}

		/*
		 * String with {{var}} to find substitutions with $_REQUEST['var']
		 */
		function applyVarsSubsitutions($str) {
			unset($matchs);
			$_expr = "((?!}}).)*";
			preg_match_all('/{{(' . $_expr . ')}}/s', $str, $matchs);
			if (is_array($matchs[0]))
				for ($i = 0, $tr = count($matchs[0]); $i < $tr; $i++) {
					// if not there is Variables
					if (isset($_REQUEST[$matchs[1][$i]]))
						$str = str_replace($matchs[0][$i], $_REQUEST[$matchs[1][$i]], $str);
				}
			return ($str);
		}

		/*
		 * The function getAllHeaders doesnt exist
		 * Then use the following function to check a header
		 */
		function getHeader($str) {
			$str = strtoupper($str);
			$str = str_replace('-', '_', $str);
			return ((isset($_SERVER['HTTP_' . $str])) ? $_SERVER['HTTP_' . $str] : '');
		}

		/*
		 *  Valida a field with different Types
		 */
		function validateField($field, $type) {
			switch ($type) {
				case 'email' :
					return (filter_var($field, FILTER_VALIDATE_EMAIL));
					break;
				case 'url' :
					return (filter_var($field, FILTER_VALIDATE_URL));
					break;
				default :
					return (false);
					break;
			}
		}

		/*
		 *  Error Handle
		 */
		function setError($errorMsg) {
			$this -> error = true;
			$this -> errorMsg[] = $errorMsg;
		}

		/*
		 * Time Performce
		 */
		function initTime() {
			$this -> _timePerformance['times'][] = array('Init Server response', $_SERVER["REQUEST_TIME_FLOAT"], 0);
			$this -> _timePerformance['times'][] = array('ADNBP Class Construction', microtime(true), microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]);
			$this -> _timePerformance['max'] = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
		}

		function setPartialTime($txt) {
			$time = microtime(true) - $this -> _timePerformance['times'][count($this -> _timePerformance['times']) - 1][1];
			$this -> _timePerformance['times'][] = array($txt, microtime(true), $time);
			$this -> _timePerformance['max'] = ($time > $this -> _timePerformance['max']) ? $time : $this -> _timePerformance['max'];
		}

		function getTotalTime($prec = 4) {
			$txt = 'Require TotalTime';
			$this -> setPartialTime($txt);
			$currentTime = microtime(true);
			return (round($currentTime - $this -> _timePerformance['times'][0][1], $prec));
		}

		function getLastPartialTime() {
			$ret = null;
			$last = 0;
			if (is_array($this -> _timePerformance['times']))
				$last = count($this -> _timePerformance['times']) - 1;
			return (($last >= 0) ? $this -> _timePerformance['times'][$last] : null);
		}

		/*
		 * Memory Cache
		 */

		function initCache($str='', $type = 'memory',$path='') {
			$this -> _cache['type'] = $type;
			switch ($this -> _cache['type']) {
				case 'memory':
					if(!is_object($this -> _cache['object'] )) {
						$this->loadClass('cache/MemoryCache');
						$this -> _cache['object'] = new MemoryCache($str);
					}
					break;
				default:
					if (!strlen(trim($str)))
						$str = 'ADNBP GLOBAL';
					$this -> _cache['object'] = new Memcache;
					$this -> _cache['str'] = $str;
					if (strlen($this -> _cache['object'] -> get($str)))
						$this -> _cache['data'] = unserialize(gzuncompress($this -> _cache['object'] -> get($str)));
					else
						$this -> _cache['data'] = array();
					break;
			}
		}

		function resetCache() {
			if ($this -> _cache === null)
				return (null);
			if($this -> _cache['type']!='memoryOld') return false;
			
			$this -> _cache['data'] = array();
			$this -> saveCache();
		}

		function setCache($str, $data) {
			if ($this -> _cache === null)
				return (null);

			switch ($this -> _cache['type']) {
				case 'memory':
					$this -> _cache['object']->set($str,$data);
					break;
				default:
					$this -> _cache['data'][$str] = gzcompress(serialize($data));
					$this -> saveCache();					
					break;
			}

		}

		function getCache($str) {
			if ($this -> _cache === null)
				return (null);

			switch ($this -> _cache['type']) {
				case 'memory':
					return($this -> _cache['object']->get($str));
					break;
				default:
					if (!isset($this -> _cache['data'][$str]))
						return (null);
					return (unserialize(gzuncompress($this -> _cache['data'][$str])));
					break;
			}
		}
		
		function getCacheTime($str) {
			if ($this -> _cache === null)
				return (null);

			switch ($this -> _cache['type']) {
				case 'memory':
					return($this -> _cache['object']->getTime($str));
					break;
			}
		}
		function saveCache() {
			if ($this -> _cache === null)
				return (null);
			if($this -> _cache['type']!='memoryOld') return false;

			$this -> _cache['data']['_microtime_'] = microtime(true);
			$this -> _cache['object'] -> set($this -> _cache['str'], gzcompress(serialize($this -> _cache['data'])));
		}

		/*
		 * Manage User Roles
		 */

		function setRole($rolId, $rolName, $org = '') {
			if (!strlen($org))
				$org = $this -> getAuthUserData("currentOrganizationId");
			$_userRoles = $this -> getSessionVar("UserRoles");
			if (empty($_userRoles))
				$_userRoles = array();

			$_userRoles[$org]['byId'][$rolId] = $rolName;
			$_userRoles[$org]['byName'][$rolName] = $rolId;
			$this -> setSessionVar("UserRoles", $_userRoles);
		}

		function hasRoleId($rolId, $org = '') {
			if (!strlen($org))
				$org = $this -> getAuthUserData("currentOrganizationId");
			$_userRoles = $this -> getSessionVar("UserRoles");
			if (empty($_userRoles))
				$_userRoles = array();

			if (!is_array($rolId))
				$rolId = array($rolId);
			$ret = false;
			foreach ($rolId as $key => $value) {
				if (strlen($value) && !empty($_userRoles[$org]['byId'][$value]) && strlen($_userRoles[$org]['byId'][$value]))
					$ret = true;
			}
			return ($ret);

		}

		function hasRoleName($roleName, $org = '') {
			if (!strlen($org))
				$org = $this -> getAuthUserData("currentOrganizationId");
			$_userRoles = $this -> getSessionVar("UserRoles");
			if (empty($_userRoles))
				$_userRoles = array();

			if (!is_array($roleName))
				$roleName = array($roleName);
			$ret = false;
			foreach ($roleName as $key => $value) {
				if (strlen($value) && !empty($_userRoles[$org]['byName'][$value]) && strlen($_userRoles[$org]['byName'][$value]))
					$ret = true;
			}
			return ($ret);
		}

		function resetRoles() {
			$this -> setSessionVar("UserRoles", array());
		}

		function getRoles() {
			return ($this -> getSessionVar("UserRoles"));
		}

		function numberFormat($n, $decs = 0) {
			return (number_format($n, $decs, $this -> _format['decimalPoint'], $this -> _format['thousandSep']));
		}

		function _checkDetectMobile() {
			if (!is_object($this -> _mobileDetect)) {

				$this -> loadClass("mobile/MobileDetect");
				$this -> _mobileDetect = new MobileDetect();
			}
		}

		function isMobile() {
			$this -> _checkDetectMobile();
			return ($this -> _mobileDetect -> isMobile());
		}

		function isTablet() {
			$this -> _checkDetectMobile();
			return ($this -> _mobileDetect -> isTablet());

		}

		function isDetect($key) {
			$this -> _checkDetectMobile();
			return ($this -> _mobileDetect -> {'is'.$key}());
		}

	}

}