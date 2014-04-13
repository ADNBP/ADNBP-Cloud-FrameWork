<?php  
    ########################################################### 
    # Madrid  nov de 2013
    # ADNBP Business & IT Perfomrnance S.L.
    # http://www.adnbp.com (info@adnbp.coom)
    # Last update: nov 2012
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

function _print() {
	$args = func_get_args();
	for ($i=0,$tr=count($args); $i < $tr; $i++) {
		if($args[$i] == "exit") exit;
		else if(is_array($args[$i])) echo "<pre>".print_r($args[$i],true)."</pre>"; 
		else echo "<li>".$args[$i];
		
	}
}

if (!defined ("_ADNBP_CLASS_") ) {
    define ("_ADNBP_CLASS_", TRUE);
    
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
        var $_lang = "es";
        var $_parseDic = "";  // String to parse a dictionary
        var $_dic = array();
        var $_charset = "UTF-8";
        var $_url = ''; 
        var $_urlParams = ''; 
        var $_scriptPath = ''; 
        var $_ip = '';
        var $_country = null;
        var $_userAgent = '';
        var $_userLanguages = array();
        var $_basename = '';
        var $_isAuth = false;
        var $version = "1.1.5";
        var $_defaultCFURL="http://cloud.adnbp.com/CloudFrameWorkService";
        /**
        * Constructor
        */
        function ADNBP ($session=true,$sessionId='') {
            if($session) {
                if(strlen($sessionId))
                    session_id($sessionId);
                session_start();
            }

            // Paths
            // note: in Google Apps Engine PHP doen't work $_SERVER: PATH_INFO or PHP_SELF
            list($this->_url,$this->_urlParams) = explode('?',$_SERVER['REQUEST_URI'],2);
            $this->_scriptPath = $_SERVER['SCRIPT_NAME'];
            $this->_ip = $_SERVER['REMOTE_ADDR'];
            $this->_userAgent = $_SERVER['HTTP_USER_AGENT'];

            // CONFIG BASIC
            $this->setConf("CloudFrameWorkVersion",$this->_version);
            
            if(!is_file("./config/config.php")) {
                $this->setConf("CloudServiceUrl","/CloudFrameWorkService");
                $this->pushMenu(array("level"=>0,"path"=>"/","en"=>"Welcome","template"=>"CloudFrameWorkIntro.php","notopbottom"=>1));
                $this->setConf("GooglePublicAPICredential","AIzaSyARDfk6bgUxrCZbg2n68--f0LL6k8b_mjg"); 
                $this->setConf("GoogleMapsAPI",true);                 
            } else {
                include_once("./config/config.php");
                if (!$this->getConf("CloudServiceUrl")) $this->setConf("CloudServiceUrl",$this->_defaultCFURL);
            }            
            
            // analyze Default Lang
            $this->_userLanguages = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
            
            if($this->getConf("setLanguageByPath")) {
                $elems = explode("/",$this->_url);
                if(strlen($elems[1]) && strlen($elems[2])) $this->_lang = $elems[1];
            } elseif( strlen($_GET['adnbplang'])) $this->_lang = $_GET['adnbplang'];
            $this->setConf("lang",$this->_lang);
            
        }
        function version() {return($this->version);}

        function readGeoPlugin() {
            // analyze Default Country
            $this->_country = unserialize (file_get_contents('http://www.geoplugin.net/php.gp?ip='.$this->_ip));
        }
        
        function getGeoPlugin() {
            if(!is_array($this->_country)) $this->readGeoPlugin();
            return($this->_country);
        }
        
        
        /**
        * Class Loader
        */
        function loadClass ($class) {
            if(is_file(dirname(__FILE__) ."/".$class.".php"))
                include_once(dirname(__FILE__) ."/".$class.".php");
            else die("$class not found");
        }
        
        
        /**
        * Call External Cloud Service
        */
        function getCloudServiceResponse($rute) {
            // analyze Default Country
            if (!$this->getConf("CloudServiceUrl")) $this->setConf("CloudServiceUrl",$this->_defaultCFURL);
           
            if(strpos($this->getConf("CloudServiceUrl"), "http") === false) 
               $_url = "http://".$_SERVER[HTTP_HOST].$this->getConf("CloudServiceUrl")."/".$rute;
            else 
                $_url = $this->getConf("CloudServiceUrl")."/".$rute;
            return(file_get_contents($_url));
        }
                /**
        * Var confs
        */
        function setAuth($bool,$namespace='CloudUser') {
            
             if(!strlen($namespace)) $namespace = $this->getConf("requireAuth");
             if(!strlen($namespace)) return false;

             $this->_isAuth[$namespace][auth] = ($bool === true); 
             if(!$this->_isAuth[$namespace][auth] ) unset ($this->_isAuth[$namespace][data]);
             $this->setSessionVar("CloudAuth",$this->_isAuth);
        }
        
        // About User Auth information
        function requireAuth($namespace='CloudUser') {
            $this->setConf("requireAuth",$namespace);
        }
        
        function isAuth($namespace='') {
             if(!strlen($namespace)) $namespace = $this->getConf("requireAuth");
             if(!strlen($namespace)) return false;
             
             if($_GET[logout]) {
                $this->setAuth(false,$namespace);
                Header("Location: $this->_url");
                exit;
             }
             
             if($this->_isAuth === false && strlen($this->getConf("requireAuth") ) ) {
                 $this->_isAuth = $this->getSessionVar("CloudAuth");
             }
             return($this->_isAuth[$namespace][auth] === true);
        }

        function setAuthUserData($key,$value,$namespace='') {
             if(!strlen($namespace)) $namespace = $this->getConf("requireAuth");
             if(!strlen($namespace)) return false;

            $this->_isAuth[$namespace][data][$key] = $value;
            $this->setAuth(true,$namespace);
        }
        function getAuthUserData($key,$namespace='') {
             if(!strlen($namespace)) $namespace = $this->getConf("requireAuth");
             if(!strlen($namespace)) return false;

             return($this->_isAuth[$namespace][data][$key]);
        }
        
        
        function setConf ($var,$val) {$this->_conf[$var] = $val;}        
        function getConf ($var) { return((isset($this->_conf[$var]))?$this->_conf[$var]:false);} 
        function pushMenu ($var) { $this->_menu[] = $var;}    
        function setSessionVar ($var,$value) { $_SESSION['adnbpSessionVar_'.$var] = $value;}      
        function getSessionVar ($var) { return($_SESSION['adnbpSessionVar_'.$var]); }
        function getGeoPluginInfo($var) {return((isset($_country['geoplugin_'.$var]))?$_country['geoplugin_'.$var]:false);}
        function getURLBasename () {return(basename($this->_url));}        


        /**
        *  Parse string to dictionary
        */
        function _parseDic() {
            $_ok=true;
            list($foo,$this->_parseDic) = explode("ADNBP_DIC_FILE",$this->_parseDic,2);
            list($foo,$this->_parseDic) = explode("adnbp_dic_languages=",$this->_parseDic,2);
            list($langs,$this->_parseDic) = explode("adnbp_dic_var=",$this->_parseDic,2);
            if(strlen($langs)) $lang = explode(",",$this->_parseDic,2);
            for($i=0,$tr=count($translates);$i<$tr;$i++);
            
            do {
                list($content,$this->_parseDic) = explode("adnbp_dic_var=",$this->_parseDic,2);
                $translates = explode("<=>",$content); 
                $var = $translates[0];
                if(strlen($var)) for($i=1,$tr=count($translates);$i<$tr;$i++) {
                      list($lang,$translate) = explode(",",$translates[$i],2);
                      $this->setDicContent($var,trim($translate),$lang);
                }
                // if(!strlen($this->getDicContent($var))) $this->setDicContent($var,$translate);  // put a default value for current lang
                
            } while(strlen($this->_parseDic));
        }
        
        function setDicContent($key,$content,$lang="") {
            if(!strlen($lang)) $lang = $this->_lang;
            $this->_dic[$key][$lang] = $content;
        }
        function getDicContent($key,$lang="") {
            if(!strlen($lang)) $lang = $this->_lang; 
            return((strlen($this->_dic[$key][$lang]))?$this->_dic[$key][$lang]:$key);
        }
        function getDicContentInHTML($key,$lang="") {
            if(!strlen($lang)) $lang = $this->_lang; 
            return((strlen($this->_dic[$key][$lang]))?str_replace("\n","<br />",htmlentities($this->_dic[$key][$lang],ENT_COMPAT | ENT_HTML401,$this->_charset)):htmlentities($key));
        }
        /**
        * Run method
        */
        function run () {
            
			
            $this->_basename = basename($this->_url);
            $scriptname = basename($this->_scriptPath);
			
			if(strpos($this->_url, '/rest/') !== false) {
				
				
				
            // if previously there is not 'notemplate'=true a read the menu by default.
			} elseif(strpos($this->_url, '/CloudFrameWork') !== false) {
                
                list($foo,$this->_basename,$foo) = explode('/',$this->_url,3);
                $this->_basename.=".php"; // add .php extension to the basename in order to find logic and templates.

                if(strpos($this->_url, '/CloudFrameWorkService') === false) {
                    
                    $this->requireAuth();                                    
                    $this->setConf("top","CloudFrameWorkTop.php");
                    $this->setConf("bottom","CloudFrameWorkBottom.php");
                    
                    if(is_file("./ADNBP/templates/".$this->_basename)) {
                        $this->setConf("template",$this->_basename);
                    }
                    
                } else {
                    $this->setConf("notemplate",true);
                }
                
            } else if(!$this->getConf("notemplate")) { 
                 if(is_file("./config/menu.php")) 
                     include("./config/menu.php");
                 
                 // looking for a match
                 for($i=0,$_found=false,$tr=count($this->_menu);$i<$tr && !$_found;$i++) {
                     // Support for /{lang}/perm-link path
                     if(strpos($this->_menu[$i][path],"{lang}"))
                         $this->_menu[$i][path] = str_replace("{lang}",$this->_lang,$this->_menu[$i][path]);
                     
					 if(strpos($this->_menu[$i][path],"{*}")) {
					 	 $this->_menu[$i][path] = str_replace("{*}",'',$this->_menu[$i][path]);
					 	 if(strpos($this->_url, $this->_menu[$i][path]) === 0)  $_found = true;

					 } else if($this->_menu[$i][path] ==  $this->_url || $this->_menu[$i][$this->getConf("lang")."_path"] == $this->_url) 
	                     $_found = true;
					 
					 if($_found) foreach ($this->_menu[$i] as $key => $value) {
                         $this->setConf($key,$value);
                     }
                 }
                 
                // If not found in the menu and it doens't have a local template desactive topbottom
                
                if(!$_found && is_file("./templates/".$this->_basename.".php")) {
                    $this->_basename.=".php";
                }   
                              
            }

            
            // if it is a permilink
            if($scriptname=="adnbppl.php" ){
                 if(!strlen($this->getConf("template")) && !$this->getConf("notemplate")) {
                 	if(is_file("./templates/".$this->_basename) || is_file("./ADNBP/templates/".$this->_basename))
					   $this->setConf("template",$this->_basename);
					elseif(is_file("./logic/".$this->_basename) || is_file("./ADNBP/logic/".$this->_basename))
                        $this->setConf("template","CloudFrameWorkBasic.php");
                    elseif(is_file("./templates/404.php") && strpos($this->_url, '/CloudFrameWork') === false)
                        $this->setConf("template","404.php");
                    else
                        $this->setConf("template","CloudFrameWork404.php");
                 }
            }
            
           if(strlen($this->getConf("dictionary")))
           if(is_file("./localize/".$this->getConf("dictionary").".txt")) {
               $this->_parseDic = file_get_contents("./localize/".$this->getConf("dictionary").".txt");
               $this->_parseDic();
               // die(print_r($this->_dic));
           }
  
           $this->checkAuth();
                if(!$this->getConf("logic")) {
                    if(is_file("./logic/".$this->_basename)) 
                         include("./logic/".$this->_basename);
                    elseif(is_file("./ADNBP/logic/".$this->_basename)) 
                         include("./ADNBP/logic/".$this->_basename);
    
                } else {
                    if(is_file("./logic/".$this->getConf("logic")))
                        include("./logic/".$this->getConf("logic"));
                    else {
                        $output = "No logic Found";
                    }
                }    
            
					
            
            if(!$this->getConf("notopbottom") && !$this->getConf("notemplate")) {
              if(!strlen($this->getConf("top"))) {
                    if(is_file("./templates/top.php"))
                        include("./templates/top.php");
                    elseif(is_file("./ADNBP/templates/top.php"))
                        include("./ADNBP/templates/top.php");
                } else {
                    if(is_file("./templates/".$this->getConf("top")))
                        include("./templates/".$this->getConf("top"));
					else if(is_file("./ADNBP/templates/".$this->getConf("top")))
                        include("./ADNBP/templates/".$this->getConf("top"));
                    else echo "No top file found t.";
                    
                }
            }
            
            if(!$this->getConf("notemplate")) {
                if(!$this->getConf("template")) {
                    if(is_file("./templates/".$this->_basename))
                        include("./templates/".$this->_basename);
                    elseif(is_file("./ADNBP/templates/".$this->_basename))
                        include("./ADNBP/templates/".$this->_basename);
                    elseif ($this->getConf("logic")=="nologic") {
                        
                    }
                } else {
                    if(is_file("./templates/".$this->getConf("template")))
                        include("./templates/".$this->getConf("template"));
                    elseif(is_file("./ADNBP/templates/".$this->getConf("template")))
                        include("./ADNBP/templates/".$this->getConf("template"));
                    else echo "No template found.";
                }
            } 
                        
            if(!$this->getConf("notopbottom") && !$this->getConf("notemplate")) {
                if(!strlen($this->getConf("bottom"))) {
                    if(is_file("./templates/bottom.php"))
                        include("./templates/bottom.php");
                    elseif(is_file("./ADNBP/templates/bottom.php"))
                        include("./ADNBP/templates/bottom.php");
                } else {
                    if(is_file("./templates/".$this->getConf("bottom")))
                        include("./templates/".$this->getConf("bottom"));
                    elseif(is_file("./ADNBP/templates/".$this->getConf("bottom")))
                        include("./ADNBP/templates/".$this->getConf("bottom"));
                    else echo "No bottom file found.";
                    
                }
            }
        }         
        
        function checkAuth() {
            $_ret = $this->isAuth();
            if(strlen($this->getConf("requireAuth")))  {
                  if(is_file(dirname(__FILE__) ."/../logic/CloudFrameWorkAuth.php"))
                     include(dirname(__FILE__) ."/../logic/CloudFrameWorkAuth.php");                  
                  else
                     die($this->getConf(" CloudFrameWorkAuth NOT FOUND"));
            }
            
        }
        /**
        * Class Loader
        */
        function urlRedirect ($url,$dest) {
            if($url == $this->_url && $url != $dest) {
                if(strlen($this->_urlParams)) $dest .= "?".$this->_urlParams;
                Header("Location: $dest");
                exit;
            }

        }
    }
}