<?php
###########################################################
# Madrid  nov de 2012
# ADNBP Business & IT Perfomrnance S.L.
# http://www.adnbp.com (info@adnbp.coom)
# Last update: Apr 2015
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

		var $_version = "2015_Apr_11";
		var $_conf = array();
		var $_menu = array();
		var $_lang = "en";
		var $_langsSupported = array("en" => "true");
		var $_parseDic = "";
		// String to parse a dictionary
		var $_dic = array();
		var $_dics = array();
		var $_dicKeys = array();
		var $_pageContent = array();
		var $_charset = "UTF-8";
		var $url = null;
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
		var $_defaultCFURL = "/api";
		var $_webapp = '';
		var $_webappURL = '';
		var $_rootpath = '';
		var $_timeZoneSystemDefault = null;
		var $_timeZone = null;
		var $error = false;
		var $errorMsg = '';
		var $_timePerformance = array();
		var $_cache = null;
		var $_format = array();
		var $_mobileDetect = null;
		var $__performance = null;
		var $_referer=null;
		var $_log = array();
		var $_date = null;

		/**
		 * Constructor
		 */
		function ADNBP($session = true, $sessionId = '', $rootpath = '') {
			global $__performance;
			$this->__performance = &$__performance;
			if ($session) {
				if (strlen($sessionId))
					session_id($sessionId);
				session_start();
			}
			__addPerformance('session_start. Construct Class:'.__CLASS__,__FILE__);
			// If the call is just to KeepSession
			if (strpos($this -> _url, '/CloudFrameWorkService/keepSession') !== false) {
				header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
				header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // past date en el pasado
				$bg = (isset($_GET['bg']) && strlen($_GET['bg'])) ? $_GET['bg'] : 'FFFFFF';
				die('<html><head><title>ADNBP Cloud FrameWork KeepSession ' . time() . '</title><meta name="robots" content="noindex"></head><body bgcolor="#' . $bg . '"></body></html>');
			}

			// Temporary bug workaround
			// https://code.google.com/p/googleappengine/issues/detail?id=11695#c6
			if($this->is("production")) {
				 apc_delete('_ah_app_identity_:https://www.googleapis.com/auth/devstorage.read_only');
				 apc_delete('_ah_app_identity_:https://www.googleapis.com/auth/devstorage.read_write');
			}
			 

			// $this->_webapp = dirname(dirname(__FILE__))."/webapp";
			// $this->_rootpath = dirname(dirname(dirname(__FILE__)));
			if (!strlen($rootpath))
				$rootpath = $_SERVER['DOCUMENT_ROOT'];
			$this -> _rootpath = $rootpath;
			$this -> _webapp = $rootpath . "/ADNBP/webapp";

			// Paths
			// note: in Google Apps Engine PHP doen't work $_SERVER: PATH_INFO or PHP_SELF
			if (strpos($_SERVER['REQUEST_URI'], '?') !== null)
				list($this -> _url, $this -> _urlParams) = explode('?', $_SERVER['REQUEST_URI'], 2);
			else
				$this -> _url = $_SERVER['REQUEST_URI'];
			
			$this->url['https'] = $_SERVER['HTTPS'];
			$this->url['host'] = $_SERVER['HTTP_HOST'];
			$this->url['url'] = $this -> _url;
			$this->url['params'] = $this -> _urlParams;
			$this->url['url_full'] = $_SERVER['REQUEST_URI'];
			$this->url['host_url'] = (($_SERVER['HTTPS']=='on')?'https':'http').'://'.$_SERVER['HTTP_HOST'];
			$this->url['host_url_full'] = (($_SERVER['HTTPS']=='on')?'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$this->url['script_name'] = $_SERVER['SCRIPT_NAME'];

			$this -> _scriptPath = $_SERVER['SCRIPT_NAME'];
			$this -> _ip = $_SERVER['REMOTE_ADDR'];
			$this -> _userAgent = $_SERVER['HTTP_USER_AGENT'];
			$this -> _urlParts = explode('/',$this-> _url);

			// HTTP_REFERER
			$this->_referer = $_SERVER['HTTP_REFERER'];
			if(!strlen($this->_referer)) $this->_referer = $_SERVER['SERVER_NAME'];

			$_configs ='';
			//  Use this file to assign webApp. $this->setWebApp(""); if not ADNBP/webapp will be included
			if (is_file($this -> _rootpath . "/adnbp_framework_config.php")) {
				include_once ($this -> _rootpath . "/adnbp_framework_config.php");
				$_configs.='/adnbp_framework_config.php - ';
			}
			// CONFIG VARS
			// load webapp config values or FrameWork default values
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
			if ($this->is('development')) {
				if (is_file($this -> _rootpath . "/local_config.php")) {
					include_once ($this -> _rootpath . "/local_config.php");
					$_configs.='/local_config.php - ';
				}
				if (is_file($this -> _webapp . "/local_config.php")) {
					include_once ($this -> _webapp . "/local_config.php");
					$_configs.=$this -> _webappURL.'/local_config.php - ';
				}
			}
			__addPerformance('LOADED CONFIGS: ', $_configs);unset($_configs);

			// About timeZone, Date & Number format
			$this->_timeZoneSystemDefault = array(date_default_timezone_get(),date('Y-m-d h:i:s'),date("P"),time());
			date_default_timezone_set(($this ->getConf('timeZone'))?$this ->getConf('timeZone'):'Europe/Madrid');
			$this->_timeZone = array(date_default_timezone_get(),date('Y-m-d h:i:s'),date("P"),time());
			$this -> _format['formatDate'] = ($this ->getConf('formatDate'))?$this ->getConf('timeZone'):"Y-m-d";
			$this -> _format['formatDateTime'] = ($this ->getConf('formatDateTime'))?$this ->getConf('timeZone'):"Y-m-d h:i:s";
			$this -> _format['formatDBDate'] = ($this ->getConf('formatDBDate'))?$this ->getConf('timeZone'):"Y-m-d h:i:s";
			$this -> _format['formatDBDateTime'] = ($this ->getConf('formatDBDateTime'))?$this ->getConf('timeZone'):"Y-m-d h:i:s";
			$this -> _format['formatDecimalPoint'] = ($this ->getConf('formatDecimalPoint'))?$this ->getConf('timeZone'):",";
			$this -> _format['formatThousandSep'] = ($this ->getConf('formatThousandSep'))?$this ->getConf('timeZone'):".";

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

		function getGeoPlugin($ip = 'REMOTE') {
			$_ip = $ip;
			if (!strlen($_ip) || $_ip == 'REMOTE') $_ip = $this -> _ip;
			if ($_ip == '::1' || $_ip == '127.0.0.1') $_ip = '';
			if(strlen($_ip)) $_ip = 'ip='.$_ip;

			__addPerformance('Calling getGeoPlugin('.$ip.')','http://www.geoplugin.net/php.gp?' . $_ip,'time');
			return (unserialize(@file_get_contents('http://www.geoplugin.net/php.gp?' . $_ip)));
		}

		function readGeoData($ip = '', $reload = false) {
			if(!strlen($ip)) $ip=$this->_ip;
			if(!strlen($ip)) $ip='REMOTE';

			if (isset($this -> _geoData['reloaded'][$ip]) || !$reload || $this -> _geoData === null || !is_array($this -> _geoData[$ip]))
				$this -> _geoData[$ip] = $this -> getSessionVar('geoPluggin_' . $ip);
			
			
			if (!isset($this -> _geoData['reloaded'][$ip]) &&
			    ($reload || $this -> _geoData === null || !is_array($this -> _geoData[$ip])) || !count($this -> _geoData[$ip])) {
				$this -> _geoData[$ip] = array();
				$data['source_ip'] = $ip;
				$data = array_merge($data,$this -> getGeoPlugin($ip));
				__addPerformance('receiving getGeoPlugin('.$ip.')','','time');
				

				foreach ($data as $key => $value) {
					$key = str_replace('geoplugin_', '', $key);
					$this -> _geoData[$ip][$key] = $value;
				}
				$this -> setSessionVar('geoPluggin_' . $ip, $this -> _geoData[$ip]);
				
				//avoid to call service twice in the same script
				$this -> _geoData['reloaded'][$ip] = true;
			}
		}

		function getGeoData($var='', $ip = '') {
			if(!strlen($ip)) $ip=$this->_ip;
			if(!strlen($ip)) $ip='REMOTE';

			if ($this -> _geoData === null || !is_array($this -> _geoData[$ip]) || isset($_GET['reload'])) {
				$this -> readGeoData($ip, isset($_GET['reload']));
			}
			if (is_array($this -> _geoData[$ip])) {
				if(!strlen($var)) return($this -> _geoData[$ip]);
				elseif (!empty($this -> _geoData[$ip][$var])) {
					return ($this -> _geoData[$ip][$var]);
				} else {
					return ("Key not found. Use for $ip: " . implode(array_keys($this -> _geoData[$ip])));
				}
			} else {
				return ('Error reading GeoData');
			}
		}
		
		function setGeoData($var, $value,$ip = '') {
			if(!strlen($ip)) $ip=$this->_ip;
			if(!strlen($ip)) $ip='REMOTE';

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
		 * Call External Cloud Service Caching the result
		 */
		function getCloudServiceResponseCache($rute, $data = null, $verb = 'GET', $extraheaders = null, $raw = false) {
		    $_qHash = hash('md5',$rute.json_encode($data).$verb);	
			$ret = $this->getCache($_qHash);
			if(isset($_GET['reload']) || isset($_REQUEST['CF_cleanCache']) || $ret===false || $ret === null) {
				$ret = $this->getCloudServiceResponse($rute, $data , $verb , $extraheaders , $raw );
				$this->setCache($_qHash,$ret);
			}	
			return($ret);
		}
		
		
		/**
		 * Call External Cloud Service 
		 */
		function getCloudServiceResponse($rute, $data = null, $verb = 'GET', $extraheaders = null, $raw = false) {
			__addPerformance('Start getCloudServiceResponse: ',"$rute " . (($data===null)?'{no params}':'{with params}'),'note');
			
			// Creating the final URL.
			if (strpos($rute, 'http') !== false) $_url = $rute;
			else  $_url = $this -> getCloudServiceURL($rute);
			
			// Workaround to avoid EOF: https://code.google.com/p/googleappengine/issues/detail?id=11772&q=certificate%20invalid%20or%20non-existent&colspec=ID%20Type%20Component%20Status%20Stars%20Summary%20Language%20Priority%20Owner%20Log
			$options = array('ssl' => array('verify_peer' => false, 'allow_self_signed' => true));
			
			// Avoid long waits
			$options['http']['ignore_errors'] ='1';
			$options['http']['header'] = 'Connection: close' . "\r\n";

			// Automatic send header for X-CLOUDFRAMEWORK-SECURITY if it is defined in config
			if (strlen($this -> getConf("CloudServiceId")) && strlen($this -> getConf("CloudServiceSecret"))) 
				$options['http']['header'] .= 'X-CLOUDFRAMEWORK-SECURITY: ' . $this->generateCloudFrameWorkSecurityString($this -> getConf("CloudServiceId"),microtime(true),$this -> getConf("CloudServiceSecret")) . "\r\n";
			
			// Extra Headers
			if ($extraheaders !== null && is_array($extraheaders)) {
				foreach ($extraheaders as $key => $value) {
					$options['http']['header'] .= $key . ': ' . $value . "\r\n";
				}
			}	
			
			// Method
			$options['http']['method'] = $verb;
						
			// Content-type
			if($verb != 'GET')
			if(stripos($options['http']['header'], 'Content-type')===false) {
				if($raw) {
					$options['http']['header'] .= 'Content-type: application/json' . "\r\n";
				} else {
					$options['http']['header'] .= 'Content-type: application/x-www-form-urlencoded' . "\r\n";
				}
			}

			// Build contents received in $data as an array
			if(is_array($data))
				if($verb=='GET') {
					if (is_array($data)) {
							if(strpos($_url, '?')===false) $_url.='?';
							else $_url.='&';
							foreach ($data as $key => $value) $_url .= $key . '=' . urlencode($value) . '&';
					}
				} else {
					if ($raw) {
						if(stripos($options['http']['header'], 'application/json')!==false) 
							$build_data = json_encode($data);
						else
							$build_data = $data;
					} else {
						$build_data = http_build_query($data);
					}
					$options['http']['content'] = $build_data;
					
					// You have to calculate the Content-Length to run as script
					$options['http']['header'] .= sprintf('Content-Length: %d', strlen($build_data)) . "\r\n";
					
				}




			// Context creation
			$context = stream_context_create($options);
			
			try {
				$ret = @file_get_contents($_url, false, $context);
				if($ret===false) $this->addError(error_get_last());
			} catch(Exception $e) {
				$this->addError(error_get_last());
				$this->addError($e->getMessage());
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

		function getRequestFingerPrint() {
			$ret['ip'] = 	$this -> _ip = $_SERVER['REMOTE_ADDR'];
			$ret['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			$ret['http_referer'] = $this->_referer;
			$ret['script_hash'] = sha1($_SERVER['HTTP_HOST']
									.' - '.$_SERVER['SCRIPT_FILENAME']
									.' - '.$_SERVER['SERVER_SOFTWARE']);
			$ret['geoData'] = $this->getGeoData();
			unset($ret['geoData']['source_ip']);
			unset($ret['geoData']['credit']);
			return($ret);
		}

		function getInputHeader($str) {
			$str = strtoupper($str);
			$str = str_replace('-', '_', $str);
			return ((isset($_SERVER['HTTP_' . $str])) ? $_SERVER['HTTP_' . $str] : '');
		}
		
		/**
		 * Auth functions
		 */

		// To active Auth you have to call requireAuth([{namespace}]) 		
		function requireAuth($namespace = '') {
			if(!strlen($namespace)) $namespace = 'CloudUser';
			$this -> setConf("requireAuth", $namespace);
			if (isset($_GET['logout'])) $this->setAuth(false);
		}		 

		function is($key,$params='') {
			$ret = false;
			switch ($key) {
				case 'development':
					return(stripos($_SERVER['SERVER_SOFTWARE'], 'Development')!==false);
					break;
				case 'production':
					return(stripos($_SERVER['SERVER_SOFTWARE'], 'Development')===false);
					break;
				case 'auth':
					return($this->isAuth());
					break;
				case 'dirReadble':
					if(strlen($params)) return(is_dir($params));
					break;
				case 'dirRewritable':
					if(strlen($params)) try {
						if(@mkdir($params.'/__tmp__')) {
							rmdir($params.'/__tmp__');
							return(true);
						}
					} catch(Exception $e) {
					}
					break;
				default:
					break;
			}
			return $ret;
		}

		function isAuth($namespace = '') {
			if (!strlen($namespace)) $namespace = $this -> getConf("requireAuth");
			if (!strlen($namespace)) return false;

			if ($this -> _isAuth === false && strlen($this -> getConf("requireAuth"))) {
				$this -> _isAuth = $this -> getSessionVar("CloudAuth");
				if(!isset($this -> _isAuth[$namespace])) return false;
			}
			return ($this -> _isAuth[$namespace]['auth'] === true);
		}
				
		// To set true or false
		function setAuth($bool, $namespace = '') {
			if (!strlen($namespace)) $namespace = $this -> getConf("requireAuth");
			if (!strlen($namespace)) return false;
			
			if($bool===false ) {
				if(isset($this -> _isAuth[$namespace]['data']))
					unset($this -> _isAuth[$namespace]['data']);
				$this -> _isAuth[$namespace]['auth'] = false;
			} else {
				$this -> _isAuth[$namespace]['auth'] = true;
			}
			$this -> setSessionVar("CloudAuth", $this -> _isAuth);
			return true;
			
		}

		// Check checkCloudFrameWorkSecurity
		function checkCloudFrameWorkSecurity($id='',$maxSeconds=0,$secret='') {
			if(!strlen($this->getHeader('X-CLOUDFRAMEWORK-SECURITY'))) 
				$this->addLog( 'X-CLOUDFRAMEWORK-SECURITY missing.');
			else {
				list($_id,$_zone,$_time,$_token) = explode('__',$this->getHeader('X-CLOUDFRAMEWORK-SECURITY'),4);
				if(    !strlen($_id)
					|| !strlen($_zone)
					|| !strlen($_time)
					|| !strlen($_token)
				) {
					$this->addLog('_wrong format in X-CLOUDFRAMEWORK-SECURITY.');
				} else {
					$date = new DateTime(null, new DateTimeZone($_zone));
					$secs = microtime(true)+$date->getOffset()-$_time;
									
					if(!strlen($secret)) {
						$secArr = $this->getConf('CLOUDFRAMEWORK-ID-'.$_id);
						if(isset($secArr['secret'])) $secret =$secArr['secret'];
					}
					
					if(!strlen($secret)) {
						$this->addLog('conf-var CLOUDFRAMEWORK-ID-'.$_id.' missing or it is not a righ CLOUDFRAMEWORK array.');
					}elseif(!strlen($_time) || !strlen($_token)) {
						$this->addLog('wrong X-CLOUDFRAMEWORK-SECURITY format.');
					} elseif($secs <=0 ) {
						 $this->addLog('Bad microtime format. Negative value got. Check the clock of the client side.');
					} elseif(strlen($id) && $id != $_id) {
						$this->addLog($_id.' ID is not allowed');
					}  elseif($this->getHeader('X-CLOUDFRAMEWORK-SECURITY') != $this->generateCloudFrameWorkSecurityString($_id,$_time,$secret)) {
						$this->addLog('X-CLOUDFRAMEWORK-SECURITY does not match.');
					} elseif($maxSeconds >0 && $maxSeconds <= $secs) {
						$this->addLog('Security String has reached maxtime: '.$maxSeconds.' seconds');
					} else {
						return(true);
					}
				}
			}
			return false;
		}
		
		// time, has to to be microtime().
		function generateCloudFrameWorkSecurityString($_id,$_time='',$secret='') {
			
			$ret = null;
			if(!strlen($secret)) {
				$secArr = $this->getConf('CLOUDFRAMEWORK-ID-'.$_id);
				if(isset($secArr['secret'])) $secret =$secArr['secret'];
			}
			if(!strlen($secret)) {
					$this->addLog('conf-var CLOUDFRAMEWORK-SECRET-'.$_id.' missing.');
			} else {
				if(!strlen($_time)) $_time = microtime(true);
				$date = new DateTime(null, new DateTimeZone('UTC'));
				$_time += $date->getOffset();
				$ret = $_id.'__UTC__'.$_time;
				$ret .= '__'.hash_hmac('sha1',$ret,$this->getConf('CLOUDFRAMEWORK-SECRET-'.$_id));
			}
			return $ret;
		}
		
		
		// To use Auth tokens
		function authToken($command,$data=array()) {
			// $command can be: check, generate
			if(!strlen($this -> getConf("requireAuth"))) {
				$this->addLog('$this->requireAuth([{namespace]}) missing;');
				return false;
			}
			return  include(__DIR__.'/ADNBP/authToken.php');
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

		function setConf($var, $val) {$this -> _conf[$var] = $val; }
		function getConf($var = '') {
			if (strlen($var))
				return (((isset($this -> _conf[$var])) ? $this -> _conf[$var] : null));
			else
				return ($this -> _conf);
		}

		function pushMenu($var) { $this -> _menu[] = $var; }
		function setSessionVar($var, $value) { $_SESSION['adnbpSessionVar_' . $var] = $value; }
		function deleteSessionVar($var) { unset($_SESSION['adnbpSessionVar_' . $var]); }
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
			// Lang to read
			if (!strlen($lang)) $lang = $this -> _lang;

			// Load dictionary repository
			if (!isset($this -> dics[$dic])) {
				$this -> _dicKeys[$dic] = $this -> readDictionaryKeys($dic, $lang);
				$this -> dics[$dic] = true;
			}
			$ret = isset($this -> _dicKeys[$dic] -> $key) ? $this -> _dicKeys[$dic] -> $key : $dic . '-' . $key;
			return (($raw) ? $ret : str_replace("\n", "<br />", htmlentities($ret, ENT_COMPAT | ENT_HTML401, $this -> _charset)));
		}
		function t1line ($dic, $key, $raw = false, $lang = '') { return(preg_replace('/(\n|\r)/', ' ', $this->t($dic, $key, $raw, $lang ))); }

		function readDictionaryKeys($cat, $lang = '') {
			// Lang to read
			if ($lang == '') $lang = $this -> _lang;
			
			// Where the filename is: Security control because we write local files
			$patron = '/[^a-zA-Z0-9_-]+/';
			$filename = '/' . preg_replace($patron, '', $lang) . '_' . preg_replace($patron, '', $cat) . '.json';
			if (strlen($this -> getConf("LocalizePath")) && is_dir($this -> getConf("LocalizePath"))) 
				$filename = $this->getConf("LocalizePath").$filename;
			else 
				$filename = $this -> webapp . '/localize'.$filename;

			// Evaluating to write the json file form a external service.
			if(!$this->error)
			if($this->getConf('CloudServiceDictionary') && strlen($this->getConf('CloudServiceKey'))) 
				if(!is_file($filename) || isset($_GET['reloadDictionaries']))
					if (!strlen($_GET['reloadDictionaries']) || $cat == $_GET['reloadDictionaries']) {
						$content = json_decode($this -> getCloudServiceResponse('dictionary/cat/' . rawurlencode($cat) . "/$lang",array('API_KEY'=>$this->getConf('CloudServiceKey'))));	
						if (!empty($content) && $content -> success) {
							$dic = array();
							foreach ($content->data as $key => $value) {
								$dic[$value -> key] = $value -> $lang;
							}
							try {
								$ret = @file_put_contents($filename, json_encode($dic));
							}catch(Exception $e) {
								$_errMsg = $e->getMessage();
							}
							if($ret===false) {
								$this->setError(error_get_last());
								if(strlen($_errMsg)) $this->addError($_errMsg);
								__addPerformance('ERROR writing file readDictionaryKeys cat='.$cat,$filename,'time');
							} else
							unset($dic);
						} else {
							$this->setError('readDictionaryKeys cat='.$cat.' error='.json_encode($content));
							__addPerformance('ERROR CloudServiceResponse readDictionaryKeys cat='.$cat,'','time');
						}
					}
			
			// Returning file
			if(is_file($filename)) return(json_decode(file_get_contents($filename)));
			else return(json_decode('{}'));
		}

		/*
		 * DEPRECATED Functions to show contents
		 */
		function setPageContent($key, $content) { $this -> _pageContent[$key] = $content; }
		function addPageContent($key, $content) { $this -> _pageContent[$key] .= $content; }
		function getPageContent($key) { return (htmlentities($this -> _pageContent[$key], ENT_SUBSTITUTE)); }
		function getRawPageContent($key) { return ($this -> _pageContent[$key]); }
		

		/**
		 * Run method
		 */
		function run() {

			$this -> _basename = basename($this -> _url);
			$scriptname = basename($this -> _scriptPath);
		// Find out the template based in the URL
			//if URL has CloudFrameWork* & /api has an special treatment
			if (strpos($this -> _url, '/CloudFrameWork') !== false || strpos($this -> _url, '/api') === 0) {

				$this -> setConf("setLanguageByPath", f);
				list($foo, $this -> _basename, $foo) = explode('/', $this -> _url, 3);
				$this -> _basename .= ".php";
				// add .php extension to the basename in order to find logic and templates.

				if (strpos($this -> _url, '/api/') === 0 && $this -> _url != '/api/') {
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
		// Crypting strong code
		function crypt($input, $rounds = 7) {
			$salt = "";
			$salt_chars = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
			for ($i = 0; $i < 22; $i++) {
				$salt .= $salt_chars[array_rand($salt_chars)];
			}
			return crypt($input, sprintf('$2a$%02d$', $rounds) . $salt);
		}

		// Compare Password
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

		function getSubstitutionsTags($str) {
			$ret = null;
			if(strlen(trim($str))) {
				$_expr = "((?!}}).)*";
				preg_match_all('/{{(' . $_expr . ')}}/s', $str, $ret);
			}
			return $ret;
		}

		/*
		 * String with {{lang:...}} or {{dic:Cat,code}} will be translated
		 */
		function applyTranslations($str, $lang) {
			if (!strlen($lang) || !strlen(trim($str))) return ($str);
			$matchs = $this->getSubstitutionsTags($str);
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
			if (!strlen(trim($str))) return ($str);
			$matchs = $this->getSubstitutionsTags($str);
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
			$this -> errorMsg = array();
			$this->addError($errorMsg);
		}
		function addError($errorMsg) {
			$this -> error = true;
			$this -> errorMsg[] = $errorMsg;			
		}
		
		function addLog($msg) { $this -> _log[] = $msg; }
		function getLog() { return $this->_log; }
		
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
		
		/*
		 * Deprecated
		 */
		function resetCache() {
			if ($this -> _cache === null)
				return (null);
			if($this -> _cache['type']!='memoryOld') return false;
			
			$this -> _cache['data'] = array();
			$this -> saveCache();
		}
		/*
		 * Deprecated
		 */	
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