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
 * @author Fran López <fl@adnry.com>
 * @package com.adnbp.framework
 */

// IF YOU ARE GOING TO CONNECT WITH OTHER APPENGINES USE THE APPSPOT.COM

if (!defined("_ADNBP_CLASS_")) {
    define("_ADNBP_CLASS_", TRUE);
    define("ROOT_CLASS_DIRECTORY", __DIR__);

    require_once ROOT_CLASS_DIRECTORY . DIRECTORY_SEPARATOR . 'autoload.php';
    /**
     * @class ADNBP
     * Environment Vars && ClassLoader for ADNBP Framework
     *
     * @version 1.0
     * @author Hector López <hlopez@adnbp.com>
     * @author Fran López <fl@adnry.com>
     * @copyright PUBLIC
     */
    class ADNBP
    {

        var $_version = "2016_Feb_20";
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
        var $_referer = null;
        var $_log = array();
        var $_date = null;
        var $system = array();
        var $__p = null;
        var $_configPaths = array();

        /**
         * Constructor
         */
        function ADNBP($session = true, $sessionId = '', $rootpath = '')
        {

            $__p = Performance::getInstance();
            $this->__p = &$__p;

            // HTTP_REFERER
            $this->_referer = $_SERVER['HTTP_REFERER'];
            if (!strlen($this->_referer)) $this->_referer = $_SERVER['SERVER_NAME'];


            if ($session) $this->sessionStart($sessionId);

            __p('session_start. Construct Class:' . __CLASS__, __FILE__);
            // If the call is just to KeepSession
            if (strpos($_SERVER['REQUEST_URI'], '/CloudFrameWorkService/keepSession') !== false) {
                header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
                header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // past date en el pasado
                $bg = (isset($_GET['bg']) && strlen($_GET['bg'])) ? $_GET['bg'] : 'FFFFFF';
                die('<html><head><title>ADNBP Cloud FrameWork KeepSession ' . time() . '</title><meta name="robots" content="noindex"></head><body bgcolor="#' . $bg . '"></body></html>');
            }


            if (!strlen($rootpath))
                $rootpath = $_SERVER['DOCUMENT_ROOT'];
            $this->_rootpath = $rootpath;
            $this->_webapp = $rootpath . "/ADNBP/webapp";

            // Paths
            // note: in Google Apps Engine PHP doen't work $_SERVER: PATH_INFO or PHP_SELF
            if (strpos($_SERVER['REQUEST_URI'], '?') !== null)
                list($this->_url, $this->_urlParams) = explode('?', $_SERVER['REQUEST_URI'], 2);
            else
                $this->_url = $_SERVER['REQUEST_URI'];

            $this->url['https'] = $_SERVER['HTTPS'];
            $this->url['host'] = $_SERVER['HTTP_HOST'];
            $this->url['url'] = $this->_url;
            $this->url['parts'] = explode('/', substr($this->_url, 1));
            $this->url['params'] = $this->_urlParams;
            $this->url['url_full'] = $_SERVER['REQUEST_URI'];
            $this->url['host_url'] = (($_SERVER['HTTPS'] == 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
            $this->url['host_url_full'] = (($_SERVER['HTTPS'] == 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $this->url['script_name'] = $_SERVER['SCRIPT_NAME'];

            $this->_scriptPath = $_SERVER['SCRIPT_NAME'];
            $this->_ip = $_SERVER['REMOTE_ADDR'];
            $this->_userAgent = $_SERVER['HTTP_USER_AGENT'];
            $this->_urlParts = explode('/', $this->_url);


            // CONFIG VARS
            $_configs = '/ADNBP/config.php - ';
            require_once __DIR__ . '/../config.php';
            //  Use this file to assign webApp. $this->setWebApp(""); if not ADNBP/webapp will be included

            if (is_file($this->_rootpath . "/config.json")) {
                if($this->readJSONConfig($this->_rootpath . "/config.json"))

                    $_configs .= implode(' - ',$this->_configPaths).' - ';
            }

            // Deprecated
            if (is_file($this->_rootpath . "/adnbp_framework_config.php")) {
                include_once($this->_rootpath . "/adnbp_framework_config.php");
                $_configs .= '/adnbp_framework_config.php - ';
            }

            // load webapp config values or FrameWork default values
            if (is_file($this->_webapp . "/config/config.php")) {
                include_once($this->_webapp . "/config/config.php");
                $_configs .= $this->_webappURL . '/config/config.php - ';
            }

            // load bucket config values. Use this to keep safely passwords etc.. in a external bucket only accesible by admin
            // Deprecated
            if (strlen($this->getConf('ConfigPath'))) {
                include_once($this->getConf('ConfigPath') . "/config.php");
                $_configs .= $this->getConf('ConfigPath') . "/config.php - ";
            }

            // For development purpose find local_config.php. Don't forget to add **/local_config.php in .gitignore
            if ($this->is('development')) {

                if (is_file($this->_rootpath . "/local_config.json")) {
                    if($this->readJSONConfig($this->_rootpath . "/local_config.json"))
                        $_configs .= '/local_config.json - ';
                }

                if (is_file($this->_rootpath . "/local_config.php")) {
                    include_once($this->_rootpath . "/local_config.php");
                    $_configs .= '/local_config.php - ';
                }

            }
            _printe($this->_conf);
            __p('LOADED CONFIGS: ', $_configs);
            unset($_configs);

            // Check if the Auth comes from X-CloudFrameWork-AuthToken and there is a hacking.
            if (strlen($this->getHeader('X-CloudFrameWork-AuthToken'))) {
                if ($this->isAuth() && $this->getAuthUserData('token') != $this->getHeader('X-CloudFrameWork-AuthToken')) {

                    $this->sendLog('access', 'Hacking', 'X-CloudFrameWork-AuthToken', 'Ilegal token ' . $this->getHeader('X-CloudFrameWork-AuthToken')
                        , 'Error comparing with internal token: ' . $this->getAuthUserData('token') . ' for user: ' . $this->getAuthUserData('email'), $this->getConf('CloudServiceLogEmail'));

                    session_destroy();
                    $_SESSION = array();
                    session_regenerate_id();
                    $this->setAuth(false);
                    die('Trying to violate CloudFrameWork Security. The Internet authorities have been reported');
                }
            }

            // About timeZone, Date & Number format
            $this->_timeZoneSystemDefault = array(date_default_timezone_get(), date('Y-m-d h:i:s'), date("P"), time());
            date_default_timezone_set(($this->getConf('timeZone')) ? $this->getConf('timeZone') : 'Europe/Madrid');
            $this->_timeZone = array(date_default_timezone_get(), date('Y-m-d h:i:s'), date("P"), time());
            $this->_format['formatDate'] = ($this->getConf('formatDate')) ? $this->getConf('timeZone') : "Y-m-d";
            $this->_format['formatDateTime'] = ($this->getConf('formatDateTime')) ? $this->getConf('timeZone') : "Y-m-d h:i:s";
            $this->_format['formatDBDate'] = ($this->getConf('formatDBDate')) ? $this->getConf('timeZone') : "Y-m-d h:i:s";
            $this->_format['formatDBDateTime'] = ($this->getConf('formatDBDateTime')) ? $this->getConf('timeZone') : "Y-m-d h:i:s";
            $this->_format['formatDecimalPoint'] = ($this->getConf('formatDecimalPoint')) ? $this->getConf('timeZone') : ",";
            $this->_format['formatThousandSep'] = ($this->getConf('formatThousandSep')) ? $this->getConf('timeZone') : ".";

            // OTHER VARS
            // CloudService API. If not defined it will point to ADNBP external Serivice
            if (!$this->getConf("CloudServiceUrl"))
                $this->setConf("CloudServiceUrl", $this->_defaultCFURL);

            // analyze Default Lang and its configuration
            $this->_userLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

            if (strpos($this->_url, '/CloudFrameWork') !== false || strpos($this->_url, '/api') !== false)
                $this->setConf("setLanguageByPath", false);

            // LANG SUPPORT
            if (strlen($this->getConf("langDefault")))
                $this->_lang = $this->getConf("langDefault");

            if ($this->getConf("setLanguageByPath")) {
                $elems = explode("/", $this->_url);
                if (isset($elems[1]) && strlen($elems[1]) && isset($elems[2]) && strlen($elems[2]))
                    $this->_lang = $elems[1];
            } else {
                if (strlen($_GET['lang']))
                    $this->setSessionVar('adnbplang', $_GET['lang']);
                if (strlen($this->getSessionVar('adnbplang')))
                    $this->_lang = $this->getSessionVar('adnbplang');
            }

            // Control langsSupported
            if (strlen($this->getConf("langsSupported")) && strpos($this->getConf("langsSupported"), $this->_lang) === false) {
                $this->_lang = (strlen($this->getConf("langDefault"))) ? $this->getConf("langDefault") : 'en';

                // Rewrite session with the right lang
                if (!$this->getConf("setLanguageByPath") && strlen($_GET['lang']))
                    $this->setSessionVar('adnbplang', $this->_lang);
            }

            // Set to lang conf var the current lang
            $this->setConf("lang", $this->_lang);

            // Active cache.
            if ($this->getConf('activeCache') || $this->getConf('CacheDics')) $this->initCache();

        }

        function sessionStart($sessionId = '')
        {

            // Init session (generally by cookies)
            if (!strlen($this->getHeader('X-CloudFrameWork-AuthToken'))) {
                if (strlen($sessionId))
                    session_id($sessionId);
                session_start();

                // Init session by X-CloudFrameWork-AuthToken
            } else {
                $securityFailed = false;
                // Checking security based in fingerprint. X-CloudFrameWork-AuthToken
                if (strlen($this->getHeader('X-CloudFrameWork-AuthToken'))) {
                    list($sessionId, $hash) = explode("_", $this->getHeader('X-CloudFrameWork-AuthToken'), 2);
                    $fp = (object)$this->getRequestFingerPrint();

                    // Security Attack from other computer
                    if ($fp->hash != $hash) {
                        $sessionId = '';
                        $securityFailed = true;
                    }
                }
                if (strlen($sessionId))
                    session_id($sessionId);
                session_start();

                if ($securityFailed) {
                    session_destroy();
                    $_SESSION = array();
                    session_regenerate_id();
                }
            }
        }


        function processConfigData($data) {

            // Tags convertion
            $convertTags = function ($data) {
                $_array = is_array($data);

                // Convert into string if we received an array
                if($_array) $data = json_encode($data);

                // Tags Conversions
                $data = str_replace('{rootPath}', $this->_rootpath, $data);
                $data = str_replace('{appPath}', $this->_webapp, $data);
                while(strpos($data,'{confVar:')!==false) {
                    list($foo,$var) = explode("{confVar:",$data,2);
                    list($var,$foo) = explode("}",$var,2);
                    $data = str_replace('{confVar:'.$var.'}',$this->getConf(trim($var)),$data);
                }

                // Convert into array if we received an array
                if($_array) $data = json_decode($data,true);
                return $data;

            };
            // going through $data
            foreach ($data as $cond => $vars) {
                if ($cond == '--') continue; // comment
                list($tagcode,$tagvalue) = explode(":",$cond,2);
                $include = false;

                // Substitute tags for strings
                $vars = $convertTags($vars);

                switch(trim(strtolower($tagcode))) {
                    case "include":
                        // Recursive Call
                        $this->readJSONConfig($vars);
                        break;
                    case "webapp":
                        $this->setWebApp($vars);
                        break;
                    case "true":
                        $include = true;
                        break;
                    case "development":
                        $include = $this->is("development");
                        break;
                    case "production":
                        $include = $this->is("production");
                        break;
                    case "indomain":
                    case "domain":
                        $domains = explode(",",$tagvalue);
                        foreach ($domains as $ind=>$inddomain ) {
                            if(trim(strtolower($tagcode))=="domain") {
                                if (strtolower($_SERVER['HTTP_HOST']) ==  strtolower(trim($inddomain)))
                                    $include = true;
                            } else {
                                if (stripos($_SERVER['HTTP_HOST'], trim($inddomain)) !== false)
                                    $include = true;
                            }
                        }

                    break;
                    case "false":
                        break;
                    default:
                        $this->setError('unknown tag:' .$tagcode);
                        break;
                }
                // Include config vars.
                if($include) {
                    if(is_array($vars)) {
                        foreach ($vars as $key => $value) {
                            if ($key == '--') continue; // comment
                            // Recursive call to analyze subelements
                            if (strpos($key, ':')) $this->processConfigData([$key => $value]);
                            else {
                                // Assign conf var values converting {} tags
                                $this->setConf($key, $convertTags($value));
                            }
                        }
                    }
                }
            }
        }
        /**
         * @param $pathfile
         * @return bool
         */
        function readJSONConfig($pathfile) {
            // Avoid recursive load JSON files
            if(isset($this->_configPaths[$pathfile])) {
                $this->setError("Recursive config file: ".$pathfile);
                return false;
            }
            $this->_configPaths[$pathfile] = $pathfile; // Control wich config paths are beeing loaded.
            try {
                $data = json_decode(@file_get_contents($pathfile),true);
                if(!is_array($data)) {
                    if(json_last_error())
                        $this->addError("Wrong format of json: ".$pathfile);
                    else
                        $this->addError(error_get_last());
                    return false;
                } else {
                    $this->processConfigData($data);
                    return true;
                }
            } catch(Exception $e) {
                $this->addError(error_get_last());
                $this->addError($e->getMessage());
                return false;
            }
        }
        /**
         * Run method
         */
        function run()
        {
            __p('run. ', '', 'note');

            $this->_basename = basename($this->_url);
            $scriptname = basename($this->_scriptPath);
            // Find out the template based in the URL
            //if URL has CloudFrameWork* & /api has an special treatment
            if (strpos($this->_url, '/CloudFrameWork') !== false || strpos($this->_url, '/api') === 0) {

                $this->setConf("setLanguageByPath", false);
                list($foo, $this->_basename, $foo) = explode('/', $this->_url, 3);
                $this->_basename .= ".php";
                // add .php extension to the basename in order to find logic and templates.

                if (strpos($this->_url, '/api/') === 0 && $this->_url != '/api/') {
                    $this->setConf("notemplate", true);
                } else {
                    $this->activeAuth();
                    $this->setConf("top", (strlen($this->getConf("portalHTMLTop"))) ? $this->getConf("portalHTMLTop") : "CloudFrameWorkTop.php");
                    $this->setConf("bottom", (strlen($this->getConf("portalHTMLBottom"))) ? $this->getConf("portalHTMLBottom") : "CloudFrameWorkBottom.php");
                    if (is_file($this->_rootpath . "/ADNBP/templates/" . $this->_basename)) {
                        $this->setConf("template", $this->_basename);
                    }

                }

            } // else if getConf("notemplate") is not defined
            else if (!$this->getConf("notemplate")) {
                __p('Menu files. ', '', 'note');
                if (is_file($this->_webapp . "/config/menu.php"))
                    include($this->_webapp . "/config/menu.php");
                __p('Menu files. ', $this->_webapp . "/config/menu.php", 'endnote');

                // looking for a match
                for ($i = 0, $_found = false, $tr = count($this->_menu); $i < $tr && !$_found; $i++) {
                    // Support for /{lang}/perm-link path
                    if (strpos($this->_menu[$i]['path'], "{lang}"))
                        $this->_menu[$i]['path'] = str_replace("{lang}", $this->_lang, $this->_menu[$i]['path']);

                    if (strpos($this->_menu[$i]['path'], "{*}")) {
                        $this->_menu[$i]['path'] = str_replace("{*}", '', $this->_menu[$i]['path']);
                        if (strpos($this->_url, $this->_menu[$i]['path']) === 0)
                            $_found = true;

                    } else if ($this->_menu[$i]['path'] == $this->_url || (!empty($this->_menu[$i][$this->getConf("lang") . "_path"]) && $this->_menu[$i][$this->getConf("lang") . "_path"] == $this->_url))
                        $_found = true;

                    // I have found an entry menu.
                    if ($_found)
                        foreach ($this->_menu[$i] as $key => $value) {
                            // Tag {buquet:} substitution
                            if (stripos($value, '{bucket:') !== false) {
                                $value = preg_replace('/{bucket:(.*)}/', $this->getConf('BucketPrefix') . '$1', $value);  // Bucket tag subsitution
                            }
                            $this->setConf($key, $value);
                        }

                    if (!$this->getConf("notemplate") && !strlen($this->getConf("top"))) {
                        $this->setConf("top", (strlen($this->getConf("portalHTMLTop"))) ? $this->getConf("portalHTMLTop") : "CloudFrameWorkTop.php");
                        $this->setConf("bottom", (strlen($this->getConf("portalHTMLBottom"))) ? $this->getConf("portalHTMLBottom") : "CloudFrameWorkBottom.php");
                    }
                }

                // If not found in the menu and it doens't have a local template desactive topbottom
                if (!$_found && is_file($this->_webapp . "/templates/" . $this->_basename . ".php")) {
                    $this->_basename .= ".php";
                }

            }


            // if it is a permilink
            if ($scriptname == "adnbppl.php" && !$_found) {
                if (!strlen($this->getConf("template")) && !$this->getConf("notemplate")) {

                    if (is_file($this->_webapp . "/templates/" . $this->_basename) || is_file($this->_rootpath . "/ADNBP/templates/" . $this->_basename))
                        $this->setConf("template", $this->_basename);
                    elseif (is_file($this->_webapp . "/logic/" . $this->_basename) || is_file($this->_rootpath . "/ADNBP/logic/" . $this->_basename))
                        $this->setConf("template", "CloudFrameWorkBasic.php");
                    elseif (is_file($this->_webapp . "/templates/404.php") && strpos($this->_url, '/CloudFrameWork') === false)
                        $this->setConf("template", "404.php");
                    else
                        $this->setConf("template", "CloudFrameWork404.php");
                }
            }


            // Create the object to control Auth
            __p('checkAuth', '', 'note');
            $this->checkAuth();
            __p('checkAuth', '', 'endnote');

            //_printe($_found,$this->getConf('requireAuth'),$this->_url);
            // if requiredAuth and not Auth... Redirects
            if ($this->getConf('requireAuth') && !$this->isAuth()) {
                $this->setSessionVar('requireAuthURL', $_SERVER['REQUEST_URI']);
                if (strlen($this->getConf('noAuthRedirectURL'))) {
                    $this->urlRedirect($this->getConf('noAuthRedirectURL'));
                } else die('Only auth users has access to this page.');
            }

            // Load Logic
            $_file = false;
            if (!strlen($this->getConf("logic"))) {
                if (is_file($this->_webapp . "/logic/" . $this->_basename)) {
                    $_file = ($this->_webapp . "/logic/" . $this->_basename);
                } elseif (is_file($this->_rootpath . "/ADNBP/logic/" . $this->_basename)) {
                    $_file = ($this->_rootpath . "/ADNBP/logic/" . $this->_basename);
                }

            } else {
                if (strpos($this->getConf("logic"), 'gs://') === 0 || strpos($this->getConf("logic"), '/') === 0) $_file = $this->getConf("logic");
                elseif (is_file($this->_webapp . "/logic/" . $this->getConf("logic"))) {
                    $_file = ($this->_webapp . "/logic/" . $this->getConf("logic"));
                } else {
                    $output = "No logic Found";
                }
            }
            if ($_file) {
                __p('Including logic file: ', $_file, 'note');
                include($_file);
                __p('Including logic file: ', '', 'endnote');
            }
            // Load top
            $_file = false;
            if (!$this->getConf("notopbottom") && !$this->getConf("notemplate") && !isset($_GET['__notop'])) {
                if (!strlen($this->getConf("top"))) {
                    if (is_file($this->_webapp . "/templates/top.php")) {
                        $_file = ($this->_webapp . "/templates/top.php");
                    } elseif (is_file("./ADNBP/templates/top.php"))
                        $_file = ("./ADNBP/templates/top.php");
                } else {
                    if (strpos($this->getConf("top"), 'gs://') === 0 || strpos($this->getConf("top"), '/') === 0) $_file = $this->getConf("top");
                    if (is_file($this->_webapp . "/templates/" . $this->getConf("top"))) {
                        $_file = ($this->_webapp . "/templates/" . $this->getConf("top"));
                    } else if (is_file($this->_rootpath . "/ADNBP/templates/" . $this->getConf("top"))) {
                        $_file = ($this->_rootpath . "/ADNBP/templates/" . $this->getConf("top"));
                    } else
                        echo "No top file found: " . $this->getConf("top");

                }
            }
            if ($_file) {
                __p('Including top html: ', $_file, 'note');
                include($_file);
                __p('Including top html: ', '', 'endnote');
            }

            // Load template
            $_file = false;
            if (!$this->getConf("notemplate") && !isset($_GET['__notemplate'])) {
                // Content of template is stored in a var 'templateVarContent'
                if (strlen($this->getConf("templateVarContent"))) {
                    $var = $this->getConf("templateVarContent");
                    // exist a var with the content of the template
                    echo $$var;

                    // Content of template is stored in a file
                } else {

                    if (!$this->getConf("template")) {
                        if (is_file("./templates/" . $this->_basename)) {
                            $_file = ("./templates/" . $this->_basename);
                        } elseif (is_file($this->_rootpath . "/ADNBP/templates/" . $this->_basename)) {
                            $_file = ($this->_rootpath . "/ADNBP/templates/" . $this->_basename);
                        } elseif ($this->getConf("logic") == "nologic") {

                        }
                    } else {
                        if (strpos($this->getConf("template"), 'gs://') === 0 || strpos($this->getConf("template"), '/') === 0) $_file = $this->getConf("template");
                        elseif (is_file($this->_webapp . "/templates/" . $this->getConf("template"))) {
                            $_file = ($this->_webapp . "/templates/" . $this->getConf("template"));
                        } elseif (is_file($this->_rootpath . "/ADNBP/templates/" . $this->getConf("template"))) {
                            $_file = ($this->_rootpath . "/ADNBP/templates/" . $this->getConf("template"));
                        } else
                            echo "No template found: " . $this->getConf("template");
                    }
                }
            }
            if ($_file) {
                __p('Including main html: ', $_file, 'note');
                include($_file);
                __p('Including main html: ', '', 'endnote');
            }

            // Load Bottom
            $_file = false;
            if (!$this->getConf("notopbottom") && !$this->getConf("notemplate") && !isset($_GET['__nobottom'])) {
                if (!strlen($this->getConf("bottom"))) {
                    if (is_file($this->_webapp . "/templates/bottom.php"))
                        $_file = ($this->_webapp . "/templates/bottom.php");
                    elseif (is_file($this->_rootpath . "/ADNBP/templates/bottom.php"))
                        $_file = ($this->_rootpath . "/ADNBP/templates/bottom.php");
                } else {
                    if (strpos($this->getConf("bottom"), 'gs://') === 0 || strpos($this->getConf("bottom"), '/') === 0) $_file = $this->getConf("bottom");
                    if (is_file($this->_webapp . "/templates/" . $this->getConf("bottom")))
                        $_file = ($this->_webapp . "/templates/" . $this->getConf("bottom"));
                    elseif (is_file($this->_rootpath . "/ADNBP/templates/" . $this->getConf("bottom")))
                        $_file = ($this->_rootpath . "/ADNBP/templates/" . $this->getConf("bottom"));
                    else
                        echo "No bottom file found: " . $this->getConf("bottom");
                }
            }
            if ($_file) {
                __p('Including bottom html: ', $_file, 'note');
                include($_file);
                __p('Including bottom html: ', '', 'endnote');
            }
            __p('End Run ' . __CLASS__ . '-' . __FUNCTION__);

            // Output performance of the system
            if (isset($_GET['__sp'])) {
                __sp();
            }

        }

        function version()
        {
            return ($this->_version);
        }

        function getRootPath()
        {
            return ($this->_rootpath);
        }

        function getWebAppPath()
        {
            return ($this->_webapp);
        }

        function getWebAppURL()
        {
            return ($this->_webappURL);
        }

        function setWebApp($dir)
        {
            if (!is_dir($this->_rootpath . $dir))
                die($dir . " doesn't exist. The path has to begin with /");
            else {
                $this->_webapp = $this->_rootpath . $dir;
                $this->_webappURL = $dir;
            }
        }

        function getGeoPlugin($ip = 'REMOTE')
        {
            $_ip = $ip;
            if (!strlen($_ip) || $_ip == 'REMOTE') $_ip = $this->_ip;
            if ($_ip == '::1' || $_ip == '127.0.0.1') $_ip = '';
            if (strlen($_ip)) $_ip = 'ip=' . $_ip;
            // Patch to avoid
            __p('Calling getGeoPlugin(' . $ip . ')', 'http://www.geoplugin.net/php.gp?' . $_ip, 'time');
            return (unserialize($this->getCloudServiceResponseCache('http://www.geoplugin.net/php.gp?' . $_ip)));
        }

        function getGeoCityIP($ip = 'REMOTE')
        {
            $_ip = $ip;
            if (!strlen($_ip) || $_ip == 'REMOTE') $_ip = $this->_ip;
            if ($_ip == '::1' || $_ip == '127.0.0.1') $_ip = '';
            if (strlen($_ip)) $_ip = 'ip=' . $_ip;
            __p('Calling getGeoCityIP(' . $ip . ')', 'https://1-dot-adnbp-first-web-site.appspot.com/api/cf_geocityip?' . $_ip, 'time');

            $ret = json_decode($this->getCloudServiceResponseCache('https://1-dot-adnbp-first-web-site.appspot.com/api/cf_geocityip?' . $_ip), true);
            // Patch to avoid
            return ($ret['data']);
        }

        function readGeoData($ip = '', $reload = false)
        {
            if (!strlen($ip)) $ip = $this->_ip;
            if (!strlen($ip)) $ip = 'REMOTE';

            if ($this->_geoData === null || !is_array($this->_geoData[$ip] || !isset($this->_geoData['reloaded'][$ip]) || !$reload))
                $this->_geoData[$ip] = $this->getSessionVar('geoCityIPCF_' . $ip);

            if (($reload || $this->_geoData === null || !is_array($this->_geoData[$ip])) || !count($this->_geoData[$ip])) {
                $this->_geoData[$ip] = array();
                $data['source_ip'] = $ip;
                $data = array_merge($data, $this->getGeoCityIP($ip));
                $this->_geoData[$ip] = $data;
                __p('receiving getGeoIP(' . $ip . ')', '', 'time');
                $this->setSessionVar('geoCityIPCF_' . $ip, $this->_geoData[$ip]);

                //avoid to call service twice in the same script
                $this->_geoData['reloaded'][$ip] = true;
            }
        }

        function getGeoData($var = '', $ip = '')
        {
            if (!strlen($ip)) $ip = $this->_ip;
            if (!strlen($ip)) $ip = 'REMOTE';

            if ($this->_geoData === null || !is_array($this->_geoData[$ip]) || isset($_GET['reload'])) {
                $this->readGeoData($ip, isset($_GET['reload']));
            }
            if (is_array($this->_geoData[$ip])) {
                if (!strlen($var)) return ($this->_geoData[$ip]);
                elseif (!empty($this->_geoData[$ip][$var])) {
                    return ($this->_geoData[$ip][$var]);
                } else {
                    return ("Key not found. Use for $ip: " . implode(array_keys($this->_geoData[$ip])));
                }
            } else {
                return ('Error reading GeoData');
            }
        }

        function setGeoData($var, $value, $ip = '')
        {
            if (!strlen($ip)) $ip = $this->_ip;
            if (!strlen($ip)) $ip = 'REMOTE';

            $this->_geoData[$ip][$var] = $value;
            $this->setSessionVar('geoCityIPCF_' . $ip, $this->_geoData[$ip]);
        }

        /**
         * Class Loader
         */
        function loadClass($class)
        {
            if (is_file(dirname(__FILE__) . "/" . $class . ".php"))
                include_once(dirname(__FILE__) . "/" . $class . ".php");
            elseif (is_file($this->_webapp . "/class/" . $class . ".php"))
                include_once($this->_webapp . "/class/" . $class . ".php");
            else
                die("$class not found");
        }

        function getCloudServiceURL($add = '')
        {
            // analyze Default Country
            if (!$this->getConf("CloudServiceUrl"))
                $this->setConf("CloudServiceUrl", $this->_defaultCFURL);

            if (strpos($this->getConf("CloudServiceUrl"), "http") === false)
                $_url = "http://" . $_SERVER['HTTP_HOST'] . $this->getConf("CloudServiceUrl");
            else
                $_url = $this->getConf("CloudServiceUrl");

            if (strlen($add))
                $add = '/' . $add;
            return ($_url . $add);
        }

        /**
         * Call External Cloud Service Caching the result
         */
        function getCloudServiceResponseCache($rute, $data = null, $verb = 'GET', $extraheaders = null, $raw = false)
        {
            $_qHash = hash('md5', $rute . json_encode($data) . $verb);
            $ret = $this->getCache($_qHash);
            if (isset($_GET['reload']) || isset($_REQUEST['CF_cleanCache']) || $ret === false || $ret === null) {
                $ret = $this->getCloudServiceResponse($rute, $data, $verb, $extraheaders, $raw);
                $this->setCache($_qHash, $ret);
            }
            return ($ret);
        }

        function hash($value)
        {
            return (hash('md5', $value));
        }


        /**
         * Call External Cloud Service
         */
        function getCloudServiceResponse($rute, $data = null, $verb = 'GET', $extraheaders = null, $raw = false)
        {
            // Creating the final URL.
            if (strpos($rute, 'http') !== false) $_url = $rute;
            else  $_url = $this->getCloudServiceURL($rute);

            __p('getCloudServiceResponse: ', "$_url " . (($data === null) ? '{no params}' : '{with params}'), 'note');

            $options = $this->system['stream_context_default']; // Take a look in ADNBP/config.php

            // Automatic send header for X-CLOUDFRAMEWORK-SECURITY if it is defined in config
            if (strlen($this->getConf("CloudServiceId")) && strlen($this->getConf("CloudServiceSecret")))
                $options['http']['header'] .= 'X-CLOUDFRAMEWORK-SECURITY: ' . $this->generateCloudFrameWorkSecurityString($this->getConf("CloudServiceId"), microtime(true), $this->getConf("CloudServiceSecret")) . "\r\n";

            // Extra Headers
            if ($extraheaders !== null && is_array($extraheaders)) {
                foreach ($extraheaders as $key => $value) {
                    $options['http']['header'] .= $key . ': ' . $value . "\r\n";
                }
            }

            // Method
            $options['http']['method'] = $verb;

            // Content-type
            if ($verb != 'GET')
                if (stripos($options['http']['header'], 'Content-type') === false) {
                    if ($raw) {
                        $options['http']['header'] .= 'Content-type: application/json' . "\r\n";
                    } else {
                        $options['http']['header'] .= 'Content-type: application/x-www-form-urlencoded' . "\r\n";
                    }
                }


            // Build contents received in $data as an array
            if (is_array($data))
                if ($verb == 'GET') {
                    if (is_array($data)) {
                        if (strpos($_url, '?') === false) $_url .= '?';
                        else $_url .= '&';
                        foreach ($data as $key => $value) $_url .= $key . '=' . rawurlencode($value) . '&';
                    }
                } else {
                    if ($raw) {
                        if (stripos($options['http']['header'], 'application/json') !== false)
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
                if ($ret === false) $this->addError(error_get_last());
            } catch (Exception $e) {
                $this->addError(error_get_last());
                $this->addError($e->getMessage());
            }


            __p('getCloudServiceResponse: ', '', 'endnote');
            return ($ret);
        }

        function checkBasicAuth($user, $passw)
        {
            include_once(dirname(__FILE__) . '/ADNBP/checkBasicAuth.php');
            return $res;
        }

        function checkAPIAuth(&$msg)
        {
            include_once(dirname(__FILE__) . '/ADNBP/checkAPIAuth.php');
            if (strlen($msgerror)) {
                $msg .= $msgerror;
                return (false);
            } else
                return (true);
        }

        function getAPIMethod()
        {
            return ((strlen($_SERVER['REQUEST_METHOD'])) ? $_SERVER['REQUEST_METHOD'] : 'GET');
        }

        function checkAPIMethod($methods)
        {
            return (strpos(strtoupper($methods), $this->getAPIMethod()) !== false);
        }

        function getAPIRawData()
        {
            return (file_get_contents("php://input"));
        }

        function getAPIPutData()
        {
            parse_str(file_get_contents("php://input"), $ret);
            return ($ret);
        }

        function getDataFromAPI($rute, $data = null, $verb = 'GET', $format = 'json', $headers = null)
        {
            include_once(dirname(__FILE__) . '/ADNBP/getDataFromAPI.php');
            return $res;
        }

        function getRequestFingerPrint($extra = '')
        {
            $ret['ip'] = $this->_ip = $_SERVER['REMOTE_ADDR'];
            $ret['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            $ret['http_referer'] = $this->_referer;
            $ret['host'] = $_SERVER['HTTP_HOST'];
            $ret['software'] = $_SERVER['SERVER_SOFTWARE'];
            if ($extra == 'geodata') {
                $ret['geoData'] = $this->getGeoData();
                unset($ret['geoData']['source_ip']);
                unset($ret['geoData']['credit']);
            }
            $ret['hash'] = sha1(implode(",", $ret));
            $ret['time'] = date('Ymdhis');
            $ret['script'] = $this->_url;
            return ($ret);
        }

        function getInputHeader($str)
        {
            $str = strtoupper($str);
            $str = str_replace('-', '_', $str);
            return ((isset($_SERVER['HTTP_' . $str])) ? $_SERVER['HTTP_' . $str] : '');
        }

        /**
         * Auth functions
         */

        // To active Auth you have to call activeAuth([{namespace}])
        function activeAuth($namespace = '')
        {
            if (!strlen($namespace)) $namespace = 'CloudUser';
            $this->setConf("activeAuth", $namespace);
            if (isset($_GET['logout'])) $this->setAuth(false);
        }

        function is($key, $params = '')
        {
            $ret = false;
            switch (strtolower($key)) {
                case 'development':
                    return (stripos($_SERVER['SERVER_SOFTWARE'], 'Development') !== false);
                    break;
                case 'production':
                    return (stripos($_SERVER['SERVER_SOFTWARE'], 'Development') === false);
                    break;
                case 'auth':
                    return ($this->isAuth());
                    break;
                case 'dirReadble':
                    if (strlen($params)) return (is_dir($params));
                    break;
                case 'dirRewritable':
                    if (strlen($params)) try {
                        if (@mkdir($params . '/__tmp__')) {
                            rmdir($params . '/__tmp__');
                            return (true);
                        }
                    } catch (Exception $e) {
                    }
                    break;
                default:
                    break;
            }
            return $ret;
        }

        function isAuth($namespace = '')
        {
            if (!strlen($namespace)) $namespace = $this->getConf("activeAuth");
            if (!strlen($namespace)) return false;

            if ($this->_isAuth === false && strlen($this->getConf("activeAuth"))) {
                $this->_isAuth = $this->getSessionVar("CloudAuth");
                if (!isset($this->_isAuth[$namespace])) return false;
            }
            return ($this->_isAuth[$namespace]['auth'] === true);
        }


        // To set true or false
        function setAuth($bool, $namespace = '')
        {
            if (!strlen($namespace)) $namespace = $this->getConf("activeAuth");
            if (!strlen($namespace)) return false;

            if ($bool === false) {
                if (isset($this->_isAuth[$namespace]['data']))
                    unset($this->_isAuth[$namespace]['data']);
                $this->_isAuth[$namespace]['auth'] = false;
                $this->deleteSessionVar('UserRoles');
            } else {
                $this->_isAuth[$namespace]['auth'] = true;
            }
            $this->setSessionVar("CloudAuth", $this->_isAuth);
            return true;

        }

        // Check checkCloudFrameWorkSecurity
        function checkCloudFrameWorkSecurity($maxSeconds = 0, $id = '', $secret = '')
        {
            if (!strlen($this->getHeader('X-CLOUDFRAMEWORK-SECURITY')))
                $this->addLog('X-CLOUDFRAMEWORK-SECURITY missing.');
            else {
                list($_id, $_zone, $_time, $_token) = explode('__', $this->getHeader('X-CLOUDFRAMEWORK-SECURITY'), 4);
                if (!strlen($_id)
                    || !strlen($_zone)
                    || !strlen($_time)
                    || !strlen($_token)
                ) {
                    $this->addLog('_wrong format in X-CLOUDFRAMEWORK-SECURITY.');
                } else {
                    $date = new DateTime(null, new DateTimeZone($_zone));
                    $secs = microtime(true) + $date->getOffset() - $_time;

                    if (!strlen($secret)) {
                        $secArr = $this->getConf('CLOUDFRAMEWORK-ID-' . $_id);
                        if (isset($secArr['secret'])) $secret = $secArr['secret'];
                    }

                    if (!strlen($secret)) {
                        $this->addLog('conf-var CLOUDFRAMEWORK-ID-' . $_id . ' missing or it is not a righ CLOUDFRAMEWORK array.');
                    } elseif (!strlen($_time) || !strlen($_token)) {
                        $this->addLog('wrong X-CLOUDFRAMEWORK-SECURITY format.');
                        // We allow an error of 2 min
                    } elseif (false && $secs < -120) {
                        $this->addLog('Bad microtime format. Negative value got: ' . $secs . '. Check the clock of the client side.');
                    } elseif (strlen($id) && $id != $_id) {
                        $this->addLog($_id . ' ID is not allowed');
                    } elseif ($this->getHeader('X-CLOUDFRAMEWORK-SECURITY') != $this->generateCloudFrameWorkSecurityString($_id, $_time, $secret)) {
                        $this->addLog('X-CLOUDFRAMEWORK-SECURITY does not match.');
                    } elseif ($maxSeconds > 0 && $maxSeconds <= $secs) {
                        $this->addLog('Security String has reached maxtime: ' . $maxSeconds . ' seconds');
                    } else {
                        $secArr['SECURITY-ID'] = $_id;
                        $secArr['SECURITY-EXPIRATION'] = ($maxSeconds) ? $maxSeconds - $secs : $maxSeconds;
                        return ($secArr);
                    }
                }
            }
            return false;
        }

        // time, has to to be microtime().
        function generateCloudFrameWorkSecurityString($id, $time = '', $secret = '')
        {
            $ret = null;
            if (!strlen($secret)) {
                $secArr = $this->getConf('CLOUDFRAMEWORK-ID-' . $id);
                if (isset($secArr['secret'])) $secret = $secArr['secret'];
            }
            if (!strlen($secret)) {
                $this->addLog('conf-var CLOUDFRAMEWORK-ID-' . $id . ' missing.');
            } else {
                if (!strlen($time)) $time = microtime(true);
                $date = new DateTime(null, new DateTimeZone('UTC'));
                $time += $date->getOffset();
                $ret = $id . '__UTC__' . $time;
                $ret .= '__' . hash_hmac('sha1', $ret, $secret);
            }
            return $ret;
        }


        // To use Auth tokens
        function authToken($command, $data = array())
        {
            // $command can be: check, generate
            if (!strlen($this->getConf("activeAuth"))) {
                $this->addLog('$this->activeAuth([{namespace]}) missing;');
                return false;
            }
            return include(__DIR__ . '/ADNBP/authToken.php');
        }

        function setAuthUserData($keys, $value='', $namespace = '')
        {
            if (!strlen($namespace))
                $namespace = $this->getConf("activeAuth");
            if (!strlen($namespace))
                return false;

            if (is_array($keys)) {
                foreach ($keys as $key=>$value)
                    $this->_isAuth[$namespace]['data'][$key] = $value;
            } else {
                $this->_isAuth[$namespace]['data'][$keys] = $value;
            }
            $this->setAuth(true, $namespace);
        }

        function getAuthUserData($key = '', $namespace = '')
        {
            if (!strlen($namespace))
                $namespace = $this->getConf("activeAuth");
            if (!strlen($namespace))
                return false;

            if (strlen($key))
                return ($this->_isAuth[$namespace]['data'][$key]);
            else
                return ($this->_isAuth[$namespace]['data']);
        }

        function getAuthUserNameSpace($namespace = '')
        {
            if (!strlen($namespace))
                $namespace = $this->getConf("activeAuth");
            if (!strlen($namespace))
                return false;

            return ($this->_isAuth[$namespace]['data']);
        }

        function setConf($var, $val)
        {
            $this->_conf[$var] = $val;
        }

        function getConf($var = '')
        {
            if (strlen($var))
                return (((isset($this->_conf[$var])) ? $this->_conf[$var] : null));
            else
                return ($this->_conf);
        }

        function pushMenu($var)
        {
            $this->_menu[] = $var;
        }

        function setSessionVar($var, $value)
        {
            $_SESSION['adnbpSessionVar_' . $var] = $value;
        }

        function deleteSessionVar($var)
        {
            unset($_SESSION['adnbpSessionVar_' . $var]);
        }

        function getSessionVar($var)
        {
            return ((isset($_SESSION['adnbpSessionVar_' . $var])) ? $_SESSION['adnbpSessionVar_' . $var] : null);
        }


        function getURLBasename()
        {
            return (basename($this->_url));
        }

        function getURL()
        {
            return ($this->_url);
        }


        // Dictionaries in method 2
        function sett($data, $dic, $key = '', $convertHtml = false)
        {
            if (!strlen($key)) {
                $this->_dicKeys['__internal__'][$dic] = $data;
            } else {
                if (!strlen($lang)) $lang = $this->_lang;
                $this->_dicKeys[$dic]->$key = ($convertHtml) ? htmlentities($data) : $data;
                $this->dics[$dic] = true;
            }
        }

        function ist($dic, $key = '', $data)
        {
            if (!strlen($key)) return (isset($this->_dicKeys['__internal__'][$dic]));
            else return (isset($this->_dicKeys[$dic]->$key));
        }

        function t($dic, $key = '', $raw = false, $lang = '')
        {

            // Internal contents
            if (!strlen($key)) return ((isset($this->_dicKeys['__internal__'][$dic])) ? $this->_dicKeys['__internal__'][$dic] : '{' . $dic . '}');


            // Lang to read
            if (!strlen($lang)) $lang = $this->_lang;

            // Load dictionary repository
            if (!isset($this->dics[$dic])) {
                if (!isset($this->_dicKeys[$dic])) {
                    $_read = false;
                    // Cache dics is activated
                    if ($this->getConf('CacheDics') && !isset($_GET['reloadCache']) && !isset($_GET['reloadDictionaries']))
                        $this->_dicKeys[$dic] = $this->getCache('Dic_' . $dic . '_' . $lang);
                    // If info does not come from Cache
                    if (!isset($this->_dicKeys[$dic]) || !is_object($this->_dicKeys[$dic])) {
                        $this->_dicKeys[$dic] = $this->readDictionaryKeys($dic, $lang);
                        $_read = (is_object($this->_dicKeys[$dic])) ? true : false;
                    }
                    // Save in Cache if it is neccesary
                    if ($this->getConf('CacheDics') && $_read)
                        $this->setCache('Dic_' . $dic . '_' . $lang, $this->_dicKeys[$dic]);
                }
                $this->dics[$dic] = true;
            }
            $ret = isset($this->_dicKeys[$dic]->$key) ? $this->_dicKeys[$dic]->$key : $dic . '-' . $key;
            return (($raw) ? $ret : str_replace("\n", "<br />", htmlentities($ret, ENT_COMPAT | ENT_HTML401, $this->_charset)));
        }

        function t1line($dic, $key, $raw = false, $lang = '')
        {
            return (preg_replace('/(\n|\r)/', ' ', $this->t($dic, $key, $raw, $lang)));
        }

        function readDictionaryKeys($cat, $lang = '')
        {
            __p('readDictionaryKeys : ', '', 'note');
            // Lang to read
            if ($lang == '') $lang = $this->_lang;

            // Where the filename is: Security control because we write local files
            $filename = '/' . preg_replace('/[^a-zA-Z0-9_-]+/', '', $lang) . '_' . preg_replace('/[^a-zA-Z0-9_-]+/', '', $cat) . '.json';
            if (strlen($this->getConf("LocalizePath"))) $filename = $this->getConf("LocalizePath") . $filename;
            else  $filename = $this->webapp . '/localize' . $filename;

            // Return json file.
            $ret = '{}';
            if (!isset($_GET['reloadDictionaries']) || !$this->getConf('CloudServiceDictionary') || !$this->getConf('CloudServiceKey')) {
                try {
                    $ret = @file_get_contents($filename);
                    if ($ret !== false) {
                        __p('readDictionaryKeys : ', $filename, 'endnote');
                        return (json_decode($ret));
                    } else {
                        $this->addLog('Error reading ' . $filename . ': ' . error_get_last());
                    }
                } catch (Exception $e) {
                    $this->addLog('Error reading ' . $filename . ': ' . $e->getMessage() . ' ' . error_get_last());
                }
            }

            // Return file generating it from a service.
            if ($this->getConf('CloudServiceDictionary') && $this->getConf('CloudServiceKey')) {
                $ret = json_decode($this->getCloudServiceResponse('dictionary/cat/' . rawurlencode($cat) . "/$lang", array('API_KEY' => $this->getConf('CloudServiceKey'))));
                if (!empty($ret) && $ret->success) {
                    foreach ($ret->data as $key => $value) {
                        $dic[$value->key] = $value->$lang;
                    }
                    $ret = json_encode($dic);
                    unset($dic);
                    try {
                        $res = @file_put_contents($filename, $ret);
                    } catch (Exception $e) {
                        $this->addError($e->getMessage());
                    }

                    if ($res === false) {
                        $this->addError(error_get_last());
                        $filename = '';
                    }
                } else {
                    $ret = '{}';
                    $this->addError('readDictionaryKeys cat=' . $cat . ' error=' . json_encode($ret));
                }
            }
            __p('readDictionaryKeys : ', $filename, 'endnote');
            return (json_decode($ret));
        }

        /*
         * DEPRECATED Functions to show contents
         */
        function setPageContent($key, $content)
        {
            $this->_pageContent[$key] = $content;
        }

        function addPageContent($key, $content)
        {
            $this->_pageContent[$key] .= $content;
        }

        function getPageContent($key)
        {
            return (htmlentities($this->_pageContent[$key], ENT_SUBSTITUTE));
        }

        function getRawPageContent($key)
        {
            return ($this->_pageContent[$key]);
        }


        function checkAuth()
        {

            $_ret = $this->isAuth();
            if (strlen($this->getConf("activeAuth"))) {
                if (is_file($this->_webapp . "/logic/CloudFrameWorkAuth.php"))
                    include($this->_webapp . "/logic/CloudFrameWorkAuth.php");
                else {
                    include($this->_rootpath . "/ADNBP/logic/CloudFrameWorkAuth.php");
                }
            }
        }


        /**
         * Redirect to other URL
         */
        function urlRedirect($url, $dest = '')
        {
            if (!strlen($dest)) {
                if ($url != $this->_url) {
                    Header("Location: $url");
                    exit;
                }
            } else if ($url == $this->_url && $url != $dest) {
                if (strlen($this->_urlParams)) {
                    if (strpos($dest, '?') === false)
                        $dest .= "?" . $this->_urlParams;
                    else
                        $dest .= "&" . $this->_urlParams;
                }
                Header("Location: $dest");
                exit;
            }

        }

        /**
         * Password checking
         */
        // Crypting strong code
        function crypt($input, $rounds = 7)
        {
            $salt = "";
            $salt_chars = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
            for ($i = 0; $i < 22; $i++) {
                $salt .= $salt_chars[array_rand($salt_chars)];
            }
            return crypt($input, sprintf('$2a$%02d$', $rounds) . $salt);
        }

        // Compare Password
        function checkPassword($passw, $compare)
        {
            return (crypt($passw, $compare) == $compare);
        }

        /*
         * String replace KeyCodes
         */
        function strCFReplace($str)
        {
            $str = str_replace('CURRENT_DATE', date('Y-m-d'), $str);
            $str = str_replace('{DirectoryOrganization_Id}', $this->getAuthUserData("currentOrganizationId"), $str);
            $str = str_replace('{OrganizationsInGroupId}', (strlen($this->getAuthUserData("currentOrganizationsInGroupId"))) ? $this->getAuthUserData("currentOrganizationsInGroupId") : $this->getAuthUserData("currentOrganizationId"), $str);
            $str = str_replace('{organizations_scope}', (strlen($this->getAuthUserData("organizations_scope"))) ? $this->getAuthUserData("organizations_scope") : $this->getAuthUserData("currentOrganizationId"), $str);

            // Replaces getting info from  getAuthUserData
            if (strpos($str, '{AuthUserData.') !== false) {
                $parts = explode('{AuthUserData.', $str);
                unset($parts[0]);

                foreach ($parts as $key => $tag) {
                    $tag = preg_replace('/}.*/', '', $tag);
                    $subparts = explode('.', $tag);

                    $value = $this->getAuthUserData($subparts[0]);
                    unset($subparts[0]);
                    foreach ($subparts as $key2 => $value2) {
                        if (is_array($value)) $value = $value[$value2];
                        elseif (is_object($value)) $value = $value->{$value2};
                        else {
                            $value = null;
                            break;
                        }
                    }
                    $str = str_replace('{AuthUserData.' . $tag . '}', $value, $str);
                }
            }
            return ($str);
        }

        function getSubstitutionsTags($str)
        {
            $ret = null;
            if (strlen(trim($str))) {
                $_expr = "((?!}}).)*";
                preg_match_all('/{{(' . $_expr . ')}}/s', $str, $ret);
            }
            return $ret;
        }

        /*
         * String with {{lang:...}} or {{dic:Cat,code}} will be translated
         */
        function applyTranslations($str, $lang)
        {
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
                        $str = str_replace($matchs[0][$i], $this->t($cat_id, $key_id, false, $lang), $str);
                    }
            return ($str);
        }


        /*
         * String with {{var}} to find substitutions with $_REQUEST['var']
         */
        function applyVarsSubsitutions($str, $data = null)
        {
            if (!strlen(trim($str))) return ($str);
            if ($data === null) $data = &$_REQUEST;
            $matchs = $this->getSubstitutionsTags($str);
            if (is_array($matchs[0]))
                for ($i = 0, $tr = count($matchs[0]); $i < $tr; $i++) {

                    // if not there is Variables
                    if (strpos($matchs[1][$i], 'if:') === 0) {
                        list($foo, $var) = explode('if:', $matchs[1][$i], 2);
                        $var = trim($var);
                        $_condition = false;  // if false delete text until next {{endif:}}

                        // There is a condition
                        if (strpos($var, '=') !== false) {
                            // Eval if exist the var.
                            // TODO: support conditional values
                        } else {
                            if ($var[0] == '!') {
                                $var = str_replace('!', '', $var);
                                $_condition = !strlen($data[$var]) || $data[$var] == '0';
                            } else $_condition = strlen($data[$var]) && $data[$var] != '0';
                        }

                        // Only parse if the var exist
                        if (isset($data[$var])) {
                            // Delete only tags
                            if ($_condition) {
                                $str = preg_replace('/' . $matchs[0][$i] . '/', '', $str, 1);
                                $str = preg_replace('/{{endif:}}/', '', $str, 1);
                                //delete {{if: .. {{endif:}}}}
                            } else {
                                // How many nested 'if's are there?
                                for ($nested = 1, $closes = 1, $j = $i + 1; $j < $tr && $closes > 0; $j++)
                                    if (strpos($matchs[1][$j], 'if:') === 0) {
                                        $nested++;
                                        $closes++;
                                    } elseif (strpos($matchs[1][$j], 'endif:') === 0) {
                                        $closes--;
                                    }

                                $pattern = '/' . $matchs[0][$i];
                                if ($nested < 1) $nested = 1;
                                for ($j = 0; $j < $nested; $j++) $pattern .= '(.*?){{endif:}}';
                                $pattern .= '/s';
                                $str = preg_replace($pattern, '', $str, 1, $count);
                            }
                        }

                        // _printe($var,$var[0],$_condition);
                        // simple substitution
                    } else if (isset($data[$matchs[1][$i]]))
                        $str = str_replace($matchs[0][$i], $data[$matchs[1][$i]], $str);
                }

            return ($str);
        }

        /*
         * The function getAllHeaders doesnt exist
         * Then use the following function to check a header
         */
        function getHeader($str)
        {
            $str = strtoupper($str);
            $str = str_replace('-', '_', $str);
            return ((isset($_SERVER['HTTP_' . $str])) ? $_SERVER['HTTP_' . $str] : '');
        }

        function getHeaders()
        {
            $ret = array();
            foreach ($_SERVER as $key => $value) if (strpos($key, 'HTTP_') === 0) {
                $ret[str_replace('HTTP_', '', $key)] = $value;
            }
            return ($ret);
        }

        /*
         *  Valida a field with different Types
         */
        function validateField($field, $type)
        {
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
         * Memory Cache
         */

        function initCache($str = '', $type = 'memory', $path = '')
        {
            $this->_cache['type'] = $type;
            switch ($this->_cache['type']) {
                case 'memory':
                    if (!is_object($this->_cache['object'])) {
                        $this->loadClass('cache/MemoryCache');
                        $this->_cache['object'] = new MemoryCache($str);
                    }
                    break;
            }
        }


        function setCache($str, $data)
        {
            if ($this->_cache === null)
                return (null);

            switch ($this->_cache['type']) {
                case 'memory':
                    $this->_cache['object']->set($str, $data);
                    break;
            }

        }

        function getCache($str)
        {
            if ($this->_cache === null) return (null);

            switch ($this->_cache['type']) {
                case 'memory':
                    return ($this->_cache['object']->get($str));
                    break;
            }
        }

        function deleteCache($str)
        {
            if ($this->_cache === null) return (null);
            switch ($this->_cache['type']) {
                case 'memory':
                    return ($this->_cache['object']->delete($str));
                    break;
            }
        }

        function getCacheTime($str)
        {
            if ($this->_cache === null) return (null);

            switch ($this->_cache['type']) {
                case 'memory':
                    return ($this->_cache['object']->getTime($str));
                    break;
            }
        }


        /*
         * Manage User Roles
         */

        function setRole($rolId, $rolName = '', $org = '')
        {
            if (!strlen($org)) $org = $this->getAuthUserData("currentOrganizationId");
            if (!strlen($rolName)) $rolName = $rolId;

            $_userRoles = $this->getSessionVar("UserRoles");
            if (empty($_userRoles))
                $_userRoles = array();

            $_userRoles[$org]['byId'][$rolId] = $rolName;
            $_userRoles[$org]['byName'][$rolName] = $rolId;
            $this->setSessionVar("UserRoles", $_userRoles);
        }

        function hasRoleId($rolId, $org = '')
        {
            if (!strlen($org)) $org = $this->getAuthUserData("currentOrganizationId");
            $_userRoles = $this->getSessionVar("UserRoles");
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

        function hasRoleName($roleName, $org = '')
        {
            if (!strlen($org))
                $org = $this->getAuthUserData("currentOrganizationId");
            $_userRoles = $this->getSessionVar("UserRoles");
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

        function resetRoles()
        {
            $this->setSessionVar("UserRoles", array());
        }

        function getRoles()
        {
            return ($this->getSessionVar("UserRoles"));
        }

        function numberFormat($n, $decs = 0)
        {
            return (number_format($n, $decs, $this->_format['decimalPoint'], $this->_format['thousandSep']));
        }

        function _checkDetectMobile()
        {
            if (!is_object($this->_mobileDetect)) {

                $this->loadClass("mobile/MobileDetect");
                $this->_mobileDetect = new MobileDetect();
            }
        }

        function isMobile()
        {
            if (!is_object($this->_mobileDetect)) $this->_checkDetectMobile();
            return ($this->_mobileDetect->isMobile());
        }

        function isTablet()
        {
            if (!is_object($this->_mobileDetect)) $this->_checkDetectMobile();
            return ($this->_mobileDetect->isTablet());

        }

        function isDetect($key)
        {
            if (!is_object($this->_mobileDetect)) $this->_checkDetectMobile();
            return ($this->_mobileDetect->{'is' . $key}());
        }

        /* ERROR & LOG FUNCTIONS */

        function setError($errorMsg)
        {
            $this->errorMsg = array();
            $this->addError($errorMsg);
        }

        function addError($errorMsg)
        {
            $this->error = true;
            $this->errorMsg[] = $errorMsg;
        }

        function addLog($msg)
        {
            $this->_log[] = $msg;
        }

        function getLog()
        {
            return $this->_log;
        }

        function sendLog($type, $cat, $subcat, $title, $text = '', $email = '', $app = '', $interactive = false)
        {
            if (!$this->getConf('CloudServiceLog') && !$this->getConf('LogPath')) return false;
            if (!strlen($app)) $app = $this->url['host'];
            $app = str_replace(' ', '_', $app);
            $params['id'] = $this->getConf('CloudServiceId');
            $params['cat'] = $cat;
            $params['subcat'] = $subcat;
            $params['title'] = $title;
            if (!is_string($text)) $text = json_encode($text);
            $params['text'] = $text . ((strlen($text)) ? "\n\n" : '');
            if ($this->error) $params['text'] .= "Errors: " . json_encode($this->errorMsg) . "\n\n";
            if (count($this->_log)) $params['text'] .= "Errors: " . json_encode($this->errorMsg);
            $params['ip'] = $this->_ip;
            $params['fingerprint'] = json_encode($this->getRequestFingerPrint());

            // Tell the service to send email of the report.
            if (strlen($email) && $this->validateField($email, 'email'))
                $params['email'] = $email;
            //_printe($app,$text,$params);
            if ($this->getConf('CloudServiceLog')) {
                if ($interactive) $params['interactive'] = 1;
                $ret = json_decode($this->getCloudServiceResponse('queue/log/' . urlencode($app) . '/' . urlencode($type), $params, 'POST'));
                if (!$ret->success) $this->addError($ret);
                return ($ret);
            } else {
                return ('Sending to LogPath not yet implemented');
            }
        }

        /**
         * Checks if current path url exists in Cloud Framework menu
         * @return bool
         */
        public function checkRouteExists()
        {
            $exists = false;
            if(count($this->_menu)) {
                $actualPath = $this->_url;
                foreach($this->_menu as $menu) {
                    if ($actualPath == $menu['path']) {
                        $exists = true;
                        break;
                    }
                }
            }
            return $exists;
        }

        /**
         * Do an internal redirect to other path
         * @param string $newPath
         */
        public function internalRedirect($newPath)
        {
            $this->_url = $newPath;
            $this->run();
            exit;
        }

    }

}