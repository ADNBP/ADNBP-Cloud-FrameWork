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
 * Core module
 */
if (!defined("_ADNBP_CORE_CLASSES_"))
{
    define("_ADNBP_CORE_CLASSES_", TRUE);

    // Global functions
    function __print($args)
    {
        echo "<pre>";
        for ($i = 0, $tr = count($args); $i < $tr; $i++) {
            if ($args[$i] === "exit")
                exit;
            echo "\n<li>[$i]: ";
            if (is_array($args[$i]))
                echo print_r($args[$i], TRUE);
            else if (is_object($args[$i]))
                echo var_dump($args[$i]);
            else if (is_bool($args[$i]))
                echo ($args[$i]) ? 'true' : 'false';
            else if (is_null($args[$i]))
                echo 'NULL';
            else
                echo $args[$i];
            echo "</li>";
        }
        echo "</pre>";
    }
    function _print()
    {
        __print(func_get_args());
    }
    function _printe()
    {
        __print(array_merge(func_get_args(), array('exit')));
    }

    // Independend classes
    Class Performance
    {
        var $data = [];
        function __construct()
        {
            // Performance Vars
            $this->data['initMicrotime'] = microtime(true);
            $this->data['lastMicrotime'] = $this->data['initMicrotime'];
            $this->data['initMemory'] = memory_get_usage() / (1024 * 1024);
            $this->data['lastMemory'] = $this->data['initMemory'];
            $this->data['lastIndex'] = 1;
            $this->data['info'][] = 'File: ' . str_replace($_SERVER['DOCUMENT_ROOT'], '', __FILE__);
            $this->data['info'][] = 'Init Memory Usage: ' . number_format(round($this->data['initMemory'], 4), 4) . 'Mb';

        }
        function add($title, $file = '', $type = 'all')
        {
            // Hidding full path (security)
            $file = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);


            if ($type == 'note') $line = "[$type";
            else $line = $this->data['lastIndex'] . ' [';

            if (strlen($file)) $file = " ($file)";

            $_mem = memory_get_usage() / (1024 * 1024) - $this->data['lastMemory'];
            if ($type == 'all' || $type == 'endnote' || $type == 'memory' || $_GET['data'] == $this->data['lastIndex']) {
                $line .= number_format(round($_mem, 3), 3) . ' Mb';
                $this->data['lastMemory'] = memory_get_usage() / (1024 * 1024);
            }

            $_time = microtime(TRUE) - $this->data['lastMicrotime'];
            if ($type == 'all' || $type == 'endnote' || $type == 'time' || $_GET['data'] == $this->data['lastIndex']) {
                $line .= (($line == '[') ? '' : ', ') . (round($_time, 3)) . ' secs';
                $this->data['lastMicrotime'] = microtime(TRUE);
            }
            $line .= '] ' . $title;
            $line = (($type != 'note') ? '[' . number_format(round(memory_get_usage() / (1024 * 1024), 3), 3) . ' Mb, '
                    . (round(microtime(TRUE) - $this->data['initMicrotime'], 3))
                    . ' secs] / ' : '') . $line . $file;
            if ($type == 'endnote') $line = "[$type] " . $line;
            $this->data['info'][] = $line;

            if ($title) {
                if(!isset($this->data['titles'][$title])) $this->data['titles'][$title] = ['mem'=>'','time'=>0,'lastIndex'=>''];
                $this->data['titles'][$title]['mem'] = $_mem;
                $this->data['titles'][$title]['time'] += $_time;
                $this->data['titles'][$title]['lastIndex'] = $this->data['lastIndex'];

            }

            if (isset($_GET['__p']) && $_GET['__p'] == $this->data['lastIndex']) {
                __sp();
                exit;
            }

            $this->data['lastIndex']++;

        }
        function getTotalTime($prec=3) {
            return (round(microtime(TRUE) - $this->data['initMicrotime'], $prec));
        }
        function getTotalMemory($prec=3) {
            return number_format(round(memory_get_usage() / (1024 * 1024), $prec), $prec);
        }
    }
    Class Session
    {
        var $start = false;
        var $id = '';

        function __construct($id='')
        {
            $this->id = $id;
        }

        function init() {
            // I will only start session if someone call me..
            if (strlen($this->id))
                session_id($this->id);
            session_start();
            $this->start = true;
        }

        function get($var) {
            if(!$this->start) $this->init();
            if(array_key_exists('CloudSessionVar_' . $var, $_SESSION)) {
                    try {
                        $ret = unserialize(gzuncompress($_SESSION['CloudSessionVar_' . $var]));
                    } catch (Exception $e) {
                        return null;
                    }
                    return $ret;
            }
            return null;
        }
        function set($var,$value) {
            if(!$this->start) $this->init();
            $_SESSION['CloudSessionVar_' . $var] = $this->_gzEnabled ? gzcompress(serialize($value)) : serialize($value);
        }
        function delete($var) {
            if(!$this->start) $this->init();
            unset($_SESSION['CloudSessionVar_' . $var]);
        }
    }
    Class System
    {
        var $url,$root_path,$app_path,$app_url;
        var $config=[];
        var $ip, $user_agent,$format,$time_zone;
        function __construct()
        {
            list($this->url['url'],$this->url['params']) = explode('?', $_SERVER['REQUEST_URI'], 2);
            $this->url['https'] = $_SERVER['HTTPS'];
            $this->url['host'] = $_SERVER['HTTP_HOST'];
            $this->url['parts'] = explode('/', substr($this->url['url'], 1));
            $this->url['url_full'] = $_SERVER['REQUEST_URI'];
            $this->url['host_url'] = (($_SERVER['HTTPS'] == 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
            $this->url['host_url_uri'] = (($_SERVER['HTTPS'] == 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $this->url['script_name'] = $_SERVER['SCRIPT_NAME'];

            // paths
            $this->root_path = (strlen($_SERVER['DOCUMENT_ROOT']))?$_SERVER['DOCUMENT_ROOT']: __DIR__ . '/../../../';
            $this->app_path = $this->rootPath;

            // Remote user:
            $this->ip = $_SERVER['REMOTE_ADDR'];
            $this->user_agent = $_SERVER['HTTP_USER_AGENT'];

            // About timeZone, Date & Number format
            $this->time_zone = array(date_default_timezone_get(), date('Y-m-d h:i:s'), date("P"), time());
            //date_default_timezone_set(($this->getConf('timeZone')) ? $this->getConf('timeZone') : 'Europe/Madrid');
            //$this->_timeZone = array(date_default_timezone_get(), date('Y-m-d h:i:s'), date("P"), time());
            $this->format['formatDate'] = "Y-m-d";
            $this->format['formatDateTime'] = "Y-m-d h:i:s";
            $this->format['formatDBDate'] = "Y-m-d h:i:s";
            $this->format['formatDBDateTime'] = "Y-m-d h:i:s";
            $this->format['formatDecimalPoint'] = ",";
            $this->format['formatThousandSep'] = ".";

            // General conf
            // TODO default formats, currencies, timezones, etc..
            $this->config['setLanguageByPath']= false;

        }
        function urlRedirect($url, $dest = '') {
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

        function getRequestFingerPrint($extra = '')
        {
            $ret['ip'] =  $_SERVER['REMOTE_ADDR'];
            $ret['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            $ret['http_referer'] = $_SERVER['HTTP_REFERER'];
            $ret['host'] = $_SERVER['HTTP_HOST'];
            $ret['software'] = $_SERVER['SERVER_SOFTWARE'];
            if ($extra == 'geodata') {
                $ret['geoData'] = $this->core->getGeoData();
                unset($ret['geoData']['source_ip']);
                unset($ret['geoData']['credit']);
            }
            $ret['hash'] = sha1(implode(",", $ret));
            $ret['time'] = date('Ymdhis');
            $ret['uri'] = $_SERVER['REQUEST_URI'];
            return ($ret);
        }

    }
    Class Loggin
    {
        var $lines = 0;
        var $data = [];

        function set($data)
        {
            $this->lines = 0;
            $this->data = [];
            $this->add($data);
        }
        function add($data)
        {
            $this->data[] = $data;
            $this->lines++;
        }
        function get()
        {
            return $this->data;
        }

    }
    Class Is
    {
        function development()
        {
            return (stripos($_SERVER['SERVER_SOFTWARE'], 'Development') !== false);
        }
        function production()
        {
            return (stripos($_SERVER['SERVER_SOFTWARE'], 'Development') === false);
        }
        function dirReadble($dir)
        {
            if (strlen($dir)) return (is_dir($dir));
        }
        function dirRewritable($dir)
        {
            if (strlen($params)) try {
                if (@mkdir($params . '/__tmp__')) {
                    rmdir($params . '/__tmp__');
                    return (true);
                }
            } catch (Exception $e) {
                return false;
            }
        }
    }
    // Core dependent classes
    Class Core
    {
        public $obj = [];
        public $__p,$session,$system,$logs,$errors,$is,$user,$config;
        var $_version = '20160408';
        function __construct($sessionId = '') {
            $this->__p  = new Performance();
            $this->session  = new Session($sessionId);
            $this->system  = new System();
            $this->logs  = new Loggin();
            $this->errors= new Loggin();
            $this->is = new Is();
            $this->__p->add('Construct Class with objects (__p,__session[started='.(($this->session->start)?'true':'false').'],__system,__logs,__errors):' . __CLASS__, __FILE__);
        }
        public function run()
        {
            $this->user = new User($this);
            $this->config = new Config($this, __DIR__ . '/config.json');
            $this->__p->add('Loaded user and config objects. __session[started='.(($this->session->start)?'true':'false').'],' , __METHOD__);
            $this->dispatch();

        }
        function setAppPath($dir) {
            if(is_dir($this->system->root_path.$dir)) {
                $this->system->app_path = $this->system->root_path.$dir;
                $this->system->app_url = $dir;
            } else {
                $this->errors->add($dir . " doesn't exist. The path has to begin with /");
            }
        }

        function dispatch() {
            // API end points
            if(strpos($this->system->url['url'],'/h/api')===0 ) {
                if(!strlen($this->system->url['parts'][2])) $this->errors->add('missing api end point');
                else {
                    $apifile = $this->system->url['parts'][2];

                    // path to file
                    if($apifile[0]=='_' || $apifile[0]=='queue')
                        $pathfile = __DIR__ . "/api/{$apifile}.php";
                    else
                        $pathfile = $this->system->app_path."/api/{$apifile}.php";

                    // IF NOT EXIST
                    if (!file_exists(__DIR__ . "/api/{$apifile}.php")) {
                        $this->errors->add("api $apifile does not exist");

                    } elseif(!$this->errors->lines){
                        try {
                            include_once __DIR__ . '/class/RESTful.php';
                            include_once __DIR__ . "/api/{$apifile}.php";
                            if (class_exists('API')) {
                                $api = new API($this);
                                $api->main();
                                $this->__p->add("Executed RESTfull->main()", "/api/{$apifile}.php");
                                $api->send();

                            } else {
                                $this->errors->add("api $apifile does not include a API class extended from RESTFul with method ->main()");
                            }
                        } catch (Exception $e) {
                            $this->errors->add(error_get_last());
                            $this->errors->add($e->getMessage());
                        }
                        $this->__p->add("API including RESTfull.php and {$apifile}.php: ",'There are ERRORS');
                    }
                    return false;
                }
            }
        }
    }
    Class User
    {
        private $core;
        private $isAuth = null;
        private $namespace;
        private $data = [];

        function __construct(Core &$core,$namespace='Default')
        {
            $this->core = $core;
            $this->namespace = (is_string($namespace) && strlen($namespace))?$namespace:'Default';
        }

        function init($namespace='') {
            if(strlen($namespace)) $this->namespace = $namespace;

            $this->data[$this->namespace]= $this->core->session->get("_User_".$this->namespace);
            if(null === $this->data[$this->namespace]) $this->data[$this->namespace] =['__auth'=>false];
        }

        function setVar($var,$data) {
            if(null === $this->data[$this->namespace]) $this->init();
            $this->data[$this->namespace][$var] = $data;
            $this->data[$this->namespace]['__auth'] = true;
            $this->core->session->set("_User_".$this->namespace,$this->data[$this->namespace]);
        }

        function getVar($var) {
            if(null === $this->data[$this->namespace]) $this->init();
            return (array_key_exists($var,$this->data[$this->namespace]))?$this->data[$this->namespace][$var]:null;
        }

        function isAuth() {
            if(null === $this->isAuth) $this->init();
            return(true === $this->data[$this->namespace]['__auth']);
        }
        function setAuth($bool) {
            if($bool) $this->setData('__auth',true);
            else {
                $this->data[$this->namespace] = ['__auth'=>false];
                $this->core->session->set("_User_".$this->namespace,$this->data[$this->namespace]);
            }
        }
    }
    Class Config
    {
        private $core;
        private $_configPaths = [];
        var $data = [];
        var $menu = [];
        function __construct(Core &$core,$path)
        {
            $this->core = $core;

            $this->readConfigJSONFile($path);
        }
        function get($var)
        {
            return (key_exists($var,$this->data))?$this->data[$var]:null;
        }
        function set($var,$data) {
            $this->data[$var] = $data;
        }
        function pushMenu($var) {
            $this->menu[] = $var;
        }

        function processConfigData($data)
        {
            // Tags convertion
            $convertTags = function ($data) {
                $_array = is_array($data);

                // Convert into string if we received an array
                if($_array) $data = json_encode($data);
                // Tags Conversions
                $data = str_replace('{{rootPath}}', $this->core->system->root_path, $data);
                $data = str_replace('{{appPath}}', $this->core->system->app_path, $data);
                while(strpos($data,'{{confVar:')!==false) {
                    list($foo,$var) = explode("{{confVar:",$data,2);
                    list($var,$foo) = explode("}}",$var,2);
                    $data = str_replace('{{confVar:'.$var.'}}',$this->get(trim($var)),$data);
                }
                // Convert into array if we received an array
                if($_array) $data = json_decode($data,true);
                return $data;

            };
            // going through $data
            foreach ($data as $cond => $vars) {
                if ($cond == '--') continue; // comment
                $tagcode = '';
                if(strpos($cond,':')!== false) {
                    list($tagcode, $tagvalue) = explode(":", $cond, 2);
                    $include = false;
                } else {
                    $include = true;
                    $vars = [$cond=>$vars];
                }

                // Substitute tags for strings
                $vars = $convertTags($vars);
                // If there is a condition tag
                if(!$include) {
                    switch (trim(strtolower($tagcode))) {

                        case "include":
                            // Recursive Call
                            $this->readConfigJSONFile($vars);
                            break;

                        case "webapp":
                            $this->core->setAppPath($vars);
                            break;

                        case "uservar":
                        case "authvar":
                            if(strpos($tagvalue,'=')!==false) {
                                list($authvar, $authvalue) = explode("=", $tagvalue);
                                if ($this->core->user->isAuth() && $this->core->user->getVar($authvar) == $authvalue)
                                    $include = true;
                            }
                            break;
                        case "confvar":
                            if(strpos($tagvalue,'=')!==false) {
                                list($confvar, $confvalue) = explode("=", $tagvalue);
                                if ($this->get($confvar) == $confvalue)
                                    $include = true;
                            }
                            break;
                        case "sessionvar":
                            if(strpos($tagvalue,'=')!==false) {
                                list($sessionvar, $sessionvalue) = explode("=", $tagvalue);
                                if ($this->core->session->get($sessionvar) == $sessionvalue)
                                    $include = true;
                            }
                            break;
                        case "servervar":
                            if(strpos($tagvalue,'=')!==false) {
                                list($servervar, $servervalue) = explode("=", $tagvalue);
                                if ($_SERVER($servervar) == $servervalue)
                                    $include = true;
                            }
                            break;
                        case "redirect":
                            // Array of redirections
                            if (is_array($vars)) {
                                foreach ($vars as $ind => $urls)
                                    if (!is_array($urls)) {
                                        $this->setError('Wrong redirect format. It has to be an array of redirect elements: [{prog:dest},{..}..]');
                                    } else {
                                        foreach ($urls as $urlOrig => $urlDest) {
                                            if ($urlOrig == '*' || !strlen($urlOrig))
                                                $this->core->system->urlRedirect($urlDest);
                                            else
                                                $this->core->system->urlRedirect($urlOrig, $urlDest);
                                        }
                                    }

                            } else {
                                $this->setError('Wrong redirect format. It has to be an array of redirect elements: [{prog:dest},{..}..]');
                            }
                            break;
                        case "true":
                            $include = true;
                            break;
                        case "auth":
                        case "noauth":
                            if (trim(strtolower($tagcode)) == 'auth')
                                $include = $this->core->user->isAuth();
                            else
                                $include = !$this->core->user->isAuth();
                            break;
                        case "development":
                            $include = $this->core->is->development();
                            break;
                        case "production":
                            $include = $this->core->is->production();
                            break;
                        case "indomain":
                        case "domain":
                            $domains = explode(",", $tagvalue);
                            foreach ($domains as $ind => $inddomain) if (strlen(trim($inddomain))) {
                                if (trim(strtolower($tagcode)) == "domain") {
                                    if (strtolower($_SERVER['HTTP_HOST']) == strtolower(trim($inddomain)))
                                        $include = true;
                                } else {
                                    if (stripos($_SERVER['HTTP_HOST'], trim($inddomain)) !== false)
                                        $include = true;
                                }
                            }
                            break;
                        case "inurl":
                        case "notinurl":
                            $urls = explode(",", $tagvalue);

                            // If notinurl the condition is upsidedown
                            if (trim(strtolower($tagcode)) == "notinurl") $include = true;
                            foreach ($urls as $ind => $inurl) if (strlen(trim($inurl))) {
                                if (trim(strtolower($tagcode)) == "inurl") {
                                    if ((strpos($this->core->system->url['url'], trim($inurl)) !== false))
                                        $include = true;
                                } else {
                                    if ((strpos($this->core->system->url['url'], trim($inurl)) !== false))
                                        $include = false;
                                }
                            }
                            break;

                        case "menu":
                            if (is_array($vars)) {
                                foreach ($vars as $key => $value) {
                                    $this->pushMenu($value);
                                }
                            } else {
                                $this->addError("menu: tag does not contain an array");
                            }
                            break;
                        case "false":
                            break;
                        default:
                            $this->setError('unknown tag: |' . $tagcode . '|');
                            break;
                    }
                }
                // Include config vars.
                if($include) {
                    if(is_array($vars)) {
                        foreach ($vars as $key => $value) {
                            if ($key == '--') continue; // comment
                            // Recursive call to analyze subelements
                            if (strpos($key, ':')) {

                                $this->processConfigData([$key => $value]);
                            }
                            else {
                                // Assign conf var values converting {} tags
                                $this->set($key, $convertTags($value));
                            }
                        }
                    }
                }
            }

        }
        function readConfigJSONFile($path) {
            // Avoid recursive load JSON files
            if(isset($this->_configPaths[$path])) {
                $this->core->errors->add("Recursive config file: ".$path);
                return false;
            }
            $this->_configPaths[$path] = $path; // Control wich config paths are beeing loaded.
            try {
                $data = json_decode(@file_get_contents($path),true);
                if(!is_array($data)) {
                    if(json_last_error())
                        $this->core->errors->add("Wrong format of json: ".$path);
                    else
                        $this->core->errors->add(error_get_last());
                    return false;
                } else {
                    $this->processConfigData($data);
                    return true;
                }
            } catch(Exception $e) {
                $this->core->errors->add(error_get_last());
                $this->core->errors->add($e->getMessage());
                return false;
            }
        }
    }

}