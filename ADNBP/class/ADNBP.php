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
	echo "<pre>";
	for ($i=0,$tr=count($args); $i < $tr; $i++) {
		if($args[$i] == "exit") exit;
		else if(is_array($args[$i])) echo print_r($args[$i],true); 
		else if(is_bool($args[$i])) echo '<li>'.($args[$i])?'true':'false';
		else if(is_null($args[$i])) echo '<li>NULL';
		else echo "<li>".$args[$i];
	}
	echo "</pre>";
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
        var $_pageContent = array();
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
        var $_version = "2014Jun.17";
        var $_defaultCFURL="http://cloud.adnbp.com/CloudFrameWorkService";
        var $_webapp = '';
        var $_rootpath = '';
        /**
        * Constructor
        */
        function ADNBP ($session=true,$sessionId='') {
            
            if($session) {
                if(strlen($sessionId))
                    session_id($sessionId);
                session_start();
                
            }
            
            // $this->_webapp = dirname(dirname(__FILE__))."/webapp";
            // $this->_rootpath = dirname(dirname(dirname(__FILE__)));
			$this->_rootpath = $_SERVER['DOCUMENT_ROOT'];
            $this->_webapp = $_SERVER['DOCUMENT_ROOT']."/webapp";
			


            // Paths
            // note: in Google Apps Engine PHP doen't work $_SERVER: PATH_INFO or PHP_SELF
            list($this->_url,$this->_urlParams) = explode('?',$_SERVER['REQUEST_URI'],2);
            $this->_scriptPath = $_SERVER['SCRIPT_NAME'];
            $this->_ip = $_SERVER['REMOTE_ADDR'];
            $this->_userAgent = $_SERVER['HTTP_USER_AGENT'];



            // If the call is just to KeepSession
            if(strpos($this->_url, '/CloudFrameWorkService/keepSession') !== false) {
                
                header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
                header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Fecha en el pasado                
                $bg = (isset($_GET['bg']) && strlen($_GET['bg']))?$_GET['bg']:'FFFFFF';
                die('<html><head><title>ADNBP Cloud FrameWork KeepSession '.time().'</title><meta name="robots" content="noindex"></head><body bgcolor="#'.$bg.'"></body></html>');
            }

            // Change with $this->setWebApp("");
            // if(is_file(dirname(__FILE__)."/../../adnbp_framework_config.php"))
            if(is_file($this->_rootpath."/adnbp_framework_config.php"))
               include_once($this->_rootpath."/adnbp_framework_config.php");


            // CONFIG BASIC
            $this->setConf("CloudFrameWorkVersion",$this->_version);
            
            
            if(is_file($this->_webapp."/config/config.php")) {
                include_once($this->_webapp."/config/config.php");
                if (!$this->getConf("CloudServiceUrl")) $this->setConf("CloudServiceUrl",$this->_defaultCFURL);
            }            
            
            // analyze Default Lang
            $this->_userLanguages = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
            
            if($this->getConf("setLanguageByPath")) {
                $elems = explode("/",$this->_url);
                if(isset($elems[1]) && strlen($elems[1]) && isset($elems[2]) && strlen($elems[2])) $this->_lang = $elems[1];
            } elseif( strlen($_GET['adnbplang'])) $this->_lang = $_GET['adnbplang'];
            $this->setConf("lang",$this->_lang);
            
			
            
        }

        function version() {return($this->_version);}
        function getRootPath() {return($this->_rootpath);}
        function getWebAppPath() {return($this->_webapp);}
        function setWebApp($dir) {
            if(!is_dir($this->_rootpath.$dir))
               die($dir." doesn't exist. The path has to begin with /");
            else $this->_webapp=$this->_rootpath.$dir;
        }
		
	    function _checkParameter(&$_data,$var,$saveInSession=false,$resetIfEmpty=false,$method='request') {

	            if(strlen($this->getConf($var))) $_data[$var] = $this->getConf($var);
				else  {
		    	    if($method=='request') $val = $_REQUEST[$var];
					else if($method=='get') $val = $_GET[$var];
					else if($method=='post') $val = $_POST[$var];
					
					if($resetIfEmpty || strlen($val)) $_data[$var] = $val;  //Force to get empty values
					
					if($saveInSession)
					if($resetIfEmpty) {
						$this->setSessionVar($var,$_data[$var]);
					} else {
						if(!strlen($_data[$var])) $_data[$var] =  $this->getSessionVar($var);
						else {
							$this->setSessionVar($var,$_data[$var]);
						}
					}
				} 
	    }
		
		function checkGetParameter(&$_data,$var,$saveInSession=false,$resetIfEmpty=false) {$this->_checkParameter($_data,$var,$saveInSession,$resetIfEmpty,'get');}
		function checkPostParameter(&$_data,$var,$saveInSession=false,$resetIfEmpty=false) {$this->_checkParameter($_data,$var,$saveInSession,$resetIfEmpty,'post');}
		function checkRequestParameter(&$_data,$var,$saveInSession=false,$resetIfEmpty=false) {$this->_checkParameter($_data,$var,$saveInSession,$resetIfEmpty,'request');}

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
        
        function getCloudServiceURL($add=''){
            // analyze Default Country
            if (!$this->getConf("CloudServiceUrl")) $this->setConf("CloudServiceUrl",$this->_defaultCFURL);
           
            if(strpos($this->getConf("CloudServiceUrl"), "http") === false) 
               $_url = "http://".$_SERVER['HTTP_HOST'].$this->getConf("CloudServiceUrl");
            else 
                $_url = $this->getConf("CloudServiceUrl");    
            
			if(strlen($add)) $add = '/'.$add;
            return($_url.$add);        
        }
        
        /**
        * Call External Cloud Service
        */
        function getCloudServiceResponse($rute,$data=null,$verb=null) {
            
            if(strpos($rute, 'http')!==false) $_url = $rute;
            else $_url = $this->getCloudServiceURL($rute);
            
            
            if($data !== null && is_array($data) && $verb===null or $verb=='POST') {
                $options = array(
                    'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                        )
                 );
            } else {
            	if($verb===null) $verb='GET';
                $options = array(
                    'http' => array(
                    'method'  => $verb,
                        )
                 );
            }
            
            
			
			if(strlen($this->getConf("CloudServiceId")) && strlen($this->getConf("CloudServiceToken"))) {
				$_date = time();
				$options['http']['header'] .= 'X-Cloudservice-Date: '.$_date."\r\n";
				$options['http']['header'] .= 'X-Cloudservice-Id: '.$this->getConf("CloudServiceId")."\r\n";
				$options['http']['header'] .= 'X-Cloudservice-Signature: '
				                              .strtoupper(sha1($this->getConf("CloudServiceId").$_date.$this->getConf("CloudServiceToken")))."\r\n";
			}
	        $context  = stream_context_create($options);
	        return(file_get_contents($_url,false,$context));
			
        }

		function checkAPIAuth(&$msg) {
			include_once(dirname(__FILE__).'/ADNBP/checkAPIAuth.php');
			if(strlen($msgerror)) { $msg.=$msgerror;return(false); }
			else return(true);		
		}
		
		function getAPIMethod() {
		    return((strlen($_SERVER['REQUEST_METHOD']))?$_SERVER['REQUEST_METHOD']:'GET');	
		}
		function getAPIRawData() {
			return(file_get_contents("php://input"));
		}

        function getDataFromAPI($rute,$data=null,$verb='GET',$format='json',$headers=null) {
			include_once(dirname(__FILE__).'/ADNBP/getDataFromAPI.php');
		    return $res;			
        }        
        
         /**
        * Var confs
        */
        function setAuth($bool,$namespace='CloudUser') {
            
             if(!strlen($namespace)) $namespace = $this->getConf("requireAuth");
             if(!strlen($namespace)) return false;

             $this->_isAuth[$namespace]['auth'] = ($bool === true); 
             if(!$this->_isAuth[$namespace]['auth'] ) unset ($this->_isAuth[$namespace]['data']);
             $this->setSessionVar("CloudAuth",$this->_isAuth);
        }
        
        // About User Auth information
        function requireAuth($namespace='CloudUser') {
            $this->setConf("requireAuth",$namespace);
        }
        
        function isAuth($namespace='') {
             if(!strlen($namespace)) $namespace = $this->getConf("requireAuth");
             if(!strlen($namespace)) return false;
             
			 if(isset($_GET['logout']) && strlen($_GET['logout'])>0 ) {
                $this->setAuth(false,$namespace);
                Header("Location: $this->_url");
                exit;
             }
             
             if($this->_isAuth === false && strlen($this->getConf("requireAuth") ) ) {
                 $this->_isAuth = $this->getSessionVar("CloudAuth");
             }
             return($this->_isAuth[$namespace]['auth'] === true);
        }

        function setAuthUserData($key,$value,$namespace='') {
             if(!strlen($namespace)) $namespace = $this->getConf("requireAuth");
             if(!strlen($namespace)) return false;

            $this->_isAuth[$namespace]['data'][$key] = $value;
            $this->setAuth(true,$namespace);
        }
        function getAuthUserData($key='',$namespace='') {
             if(!strlen($namespace)) $namespace = $this->getConf("requireAuth");
             if(!strlen($namespace)) return false;

			 if(strlen($key))
             	return($this->_isAuth[$namespace]['data'][$key]);
			 else
			 	return($this->_isAuth[$namespace]['data']);
        }

        function getAuthUserNameSpace($namespace='') {
             if(!strlen($namespace)) $namespace = $this->getConf("requireAuth");
             if(!strlen($namespace)) return false;

             return($this->_isAuth[$namespace]['data']);
        }        
        
        function setConf ($var,$val) {$this->_conf[$var] = $val;}        
        function getConf ($var) { return( ((isset($this->_conf[$var]))?$this->_conf[$var]:false));} 
        function pushMenu ($var) { $this->_menu[] = $var;}    
        function setSessionVar ($var,$value) { $_SESSION['adnbpSessionVar_'.$var] = $value;}      
        function getSessionVar ($var) { return( (isset($_SESSION['adnbpSessionVar_'.$var]))?$_SESSION['adnbpSessionVar_'.$var]:null ); }
        function getGeoPluginInfo($var) {return((isset($_country['geoplugin_'.$var]))?$_country['geoplugin_'.$var]:false);}
        function getURLBasename () {return(basename($this->_url));}        
        function getURL () {return($this->_url);}        


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
                $var = trim($translates[0]);
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
		
		function setPageContent($key,$content) { $this->_pageContent[$key] = $content;}
		function addPageContent($key,$content) { $this->_pageContent[$key] .= $content;}
		function getPageContent($key) { return(htmlentities($this->_pageContent[$key],ENT_SUBSTITUTE));}
		function getRawPageContent($key) { return($this->_pageContent[$key]);}
		
        /**
        * Run method
        */
        function run () {
                
            $this->_basename = basename($this->_url);
            $scriptname = basename($this->_scriptPath);
			
			if(strpos($this->_url, '/CloudFrameWork') !== false  || $this->_basename == 'api' || strpos($this->_url, '/api/') !== false ) {
                
                list($foo,$this->_basename,$foo) = explode('/',$this->_url,3);
                $this->_basename.=".php"; // add .php extension to the basename in order to find logic and templates.

                if(strpos($this->_url, '/CloudFrameWorkService/') === false || strpos($this->_url, '/api/') !== false) {
                    
                    $this->requireAuth();   
                                                     
                    $this->setConf("top",(strlen($this->getConf("portalHTMLTop")))?$this->getConf("portalHTMLTop"):"CloudFrameWorkTop.php");
                    $this->setConf("bottom",(strlen($this->getConf("portalHTMLBottom")))?$this->getConf("portalHTMLBottom"):"CloudFrameWorkBottom.php");
                    

                    if(is_file($this->_rootpath."/ADNBP/templates/".$this->_basename)) {
                        $this->setConf("template",$this->_basename);
                    }
                    
                } else {
                    $this->setConf("notemplate",true);
                }
                
                
            } else if(!$this->getConf("notemplate")) {
                 
                 if(is_file($this->_webapp."/config/menu.php")) 
                     include($this->_webapp."/config/menu.php");
                 
                 // looking for a match
                 for($i=0,$_found=false,$tr=count($this->_menu);$i<$tr && !$_found;$i++) {
                     // Support for /{lang}/perm-link path
                     if(strpos($this->_menu[$i]['path'],"{lang}"))
                         $this->_menu[$i]['path'] = str_replace("{lang}",$this->_lang,$this->_menu[$i]['path']);
                     
					 if(strpos($this->_menu[$i]['path'],"{*}")) {
					 	 $this->_menu[$i]['path'] = str_replace("{*}",'',$this->_menu[$i]['path']);
					 	 if(strpos($this->_url, $this->_menu[$i]['path']) === 0)  $_found = true;

					 } else if($this->_menu[$i]['path'] ==  $this->_url || $this->_menu[$i][$this->getConf("lang")."_path"] == $this->_url) 
	                     $_found = true;
					 
					 if($_found) foreach ($this->_menu[$i] as $key => $value) {
                         $this->setConf($key,$value);
                     }
                     
                     if(!$this->getConf("notemplate") && !strlen($this->getConf("top"))) {
                        $this->setConf("top",(strlen($this->getConf("portalHTMLTop")))?$this->getConf("portalHTMLTop"):"CloudFrameWorkTop.php");
                        $this->setConf("bottom",(strlen($this->getConf("portalHTMLBottom")))?$this->getConf("portalHTMLBottom"):"CloudFrameWorkBottom.php");
                    }
                 }
                 
                // If not found in the menu and it doens't have a local template desactive topbottom
                
                if(!$_found && is_file($this->_webapp."/templates/".$this->_basename.".php")) {
                    $this->_basename.=".php";
                }   
                              
            }

            
            // if it is a permilink
            if($scriptname=="adnbppl.php" ){
                 if(!strlen($this->getConf("template")) && !$this->getConf("notemplate")) {
                     
                 	if(is_file($this->_webapp."/templates/".$this->_basename) || is_file($this->_rootpath."/ADNBP/templates/".$this->_basename))
					   $this->setConf("template",$this->_basename);
					elseif(is_file($this->_webapp."/logic/".$this->_basename) || is_file($this->_rootpath."/ADNBP/logic/".$this->_basename))
                        $this->setConf("template","CloudFrameWorkBasic.php");
                    elseif(is_file($this->_webapp."/templates/404.php") && strpos($this->_url, '/CloudFrameWork') === false)
                        $this->setConf("template","404.php");
                    else
                        $this->setConf("template","CloudFrameWork404.php");
                 }
            }
            
           
		   // Insert global dictionary 
		   if(is_file($this->_webapp."/localize/global.txt")) {
               $this->_parseDic = file_get_contents($this->_webapp."/localize/global.txt");
               $this->_parseDic();		
		   }
		   
		   if(strlen($this->getConf("dictionary")))
           if(is_file($this->_webapp."/localize/".$this->getConf("dictionary").".txt")) {
               $this->_parseDic = file_get_contents($this->_webapp."/localize/".$this->getConf("dictionary").".txt");
               $this->_parseDic();
               // die(print_r($this->_dic));
           }
           
           $this->checkAuth();
                if(!strlen($this->getConf("logic"))) {
                    if(is_file($this->_webapp."/logic/".$this->_basename)) 
                         include($this->_webapp."/logic/".$this->_basename);
                    elseif(is_file($this->_rootpath."/ADNBP/logic/".$this->_basename)) {
                         
                         include($this->_rootpath."/ADNBP/logic/".$this->_basename);
                    }
                    
    
                } else {
                    if(is_file($this->_webapp."/logic/".$this->getConf("logic")))
                        include($this->_webapp."/logic/".$this->getConf("logic"));
                    else {
                        $output = "No logic Found";
                    }
                }    
            
					
            if(!$this->getConf("notopbottom") && !$this->getConf("notemplate")) {
              if(!strlen($this->getConf("top"))) {
                    if(is_file($this->_webapp."/templates/top.php"))
                        include($this->_webapp."/templates/top.php");
                    elseif(is_file("./ADNBP/templates/top.php"))
                        include("./ADNBP/templates/top.php");
                } else {
                    if(is_file($this->_webapp."/templates/".$this->getConf("top")))
                        include($this->_webapp."/templates/".$this->getConf("top"));
					else if(is_file($this->_rootpath."/ADNBP/templates/".$this->getConf("top")))
                        include($this->_rootpath."/ADNBP/templates/".$this->getConf("top"));
                    else echo "No top file found: ".$this->getConf("top");
                    
                }
            }
            
            if(!$this->getConf("notemplate")) {
                if(!$this->getConf("template")) {
                    if(is_file("./templates/".$this->_basename))
                        include("./templates/".$this->_basename);
                    elseif(is_file($this->_rootpath."/ADNBP/templates/".$this->_basename))
                        include($this->_rootpath."/ADNBP/templates/".$this->_basename);
                    elseif ($this->getConf("logic")=="nologic") {
                        
                    }
                } else {
                    if(is_file($this->_webapp."/templates/".$this->getConf("template")))
                        include($this->_webapp."/templates/".$this->getConf("template"));
                    elseif(is_file($this->_rootpath."/ADNBP/templates/".$this->getConf("template")))
                        include($this->_rootpath."/ADNBP/templates/".$this->getConf("template"));
                    else echo "No template found: ".$this->getConf("template");
                }
            } 
            
                        
            if(!$this->getConf("notopbottom") && !$this->getConf("notemplate")) {
                if(!strlen($this->getConf("bottom"))) {
                    if(is_file($this->_webapp."/templates/bottom.php"))
                        include($this->_webapp."/templates/bottom.php");
                    elseif(is_file($this->_rootpath."/ADNBP/templates/bottom.php"))
                        include($this->_rootpath."/ADNBP/templates/bottom.php");
                } else {
                    if(is_file($this->_webapp."/templates/".$this->getConf("bottom")))
                        include($this->_webapp."/templates/".$this->getConf("bottom"));
                    elseif(is_file($this->_rootpath."/ADNBP/templates/".$this->getConf("bottom")))
                        include($this->_rootpath."/ADNBP/templates/".$this->getConf("bottom"));
                    else echo "No bottom file found: ".$this->getConf("bottom");
                    
                }
            }
        }         
        
        function checkAuth() {
            $_ret = $this->isAuth();
            if(strlen($this->getConf("requireAuth")))  {
                  if(is_file($this->_webapp."/logic/CloudFrameWorkAuth.php"))
                     include($this->_webapp."/logic/CloudFrameWorkAuth.php");                  
                  else {
                     include($this->_rootpath."/ADNBP/logic/CloudFrameWorkAuth.php");   
                  }
            }
            
        }
        /**
        * Redirect to other URL
        */
        function urlRedirect ($url,$dest='') {
            if(!strlen($dest)) {
                if($url != $this->_url) {
                    Header("Location: $url");
                    exit;
                }
            } else if($url == $this->_url && $url != $dest) {
                if(strlen($this->_urlParams)) {
                	if(strpos($dest, '?') === false)
                	   $dest .= "?".$this->_urlParams;
					else
                	   $dest .= "&".$this->_urlParams;
				}
                Header("Location: $dest");
                exit;
            }

        }
		
        /**
        * Password checking
        */
	    function crypt($input, $rounds = 7) {
		    $salt = "";
		    $salt_chars = array_merge(range('A','Z'), range('a','z'), range(0,9));
		    for($i=0; $i < 22; $i++) {
		      $salt .= $salt_chars[array_rand($salt_chars)];
		    }
		    return crypt($input, sprintf('$2a$%02d$', $rounds) . $salt);
		}    
		      
        
        function checkPassword($passw,$compare) {
   		 	return(crypt($passw,$compare) == $compare);
		}
		 		/*
		 * String replace KeyCodes
		 */
		 function strCFReplace($str) {
		 	$str = str_replace('CURRENT_DATE', date('Y-m-d'), $str);
		 	$str = str_replace('{DirectoryOrganization_Id}', $this->getAuthUserData("currentOrganizationId"), $str);
		 	$str = str_replace('{OrganizationsInGroupId}', (strlen($this->getAuthUserData("currentOrganizationsInGroupId")))?$this->getAuthUserData("currentOrganizationsInGroupId"):$this->getAuthUserData("currentOrganizationId"), $str);
		 	return($str);
		 }
		 
		 /*
		  * The function getAllHeaders doesnt exist
		  * Then use the following function to check a header
		  */
		  function getHeader($str) {
		  	$str = strtoupper($str);
			$str = str_replace('-', '_', $str);
			return((isset($_SERVER['HTTP_'.$str]))?$_SERVER['HTTP_'.$str]:'');
		  }
    }
}