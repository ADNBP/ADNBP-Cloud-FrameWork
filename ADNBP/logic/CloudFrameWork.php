<?php

$this->urlRedirect("/CloudFrameWork","/CloudFrameWork/home");

if(!strlen($this->getSessionVar("version")) || $_GET[nocache]) {
    $this->setSessionVar("version",$this->getCloudServiceResponse("checkVersion/".$this->version));
}

list($foo,$script,$service,$params) = split('/',$this->_url,4);


$memcache = new Memcache;
switch ($service) {
	case 'home':
        $pageContent = $memcache->get("CFHome");
        if(!strlen($pageContent) || $_GET[nocache]) {
           $pageContent = $this->getCloudServiceResponse("getTemplate/intro.htm");
           $memcache->set("CFHome","$pageContent");
        }
        $this->setConf("pageCode","home");
		break;
    case 'GeoLocation':
       $this->setConf("pageCode","GeoLocation");
       $pageContent = $memcache->get("CFGeoLocation");
       if(!strlen($pageContent) || $_GET[nocache]) {
           $pageContent = $this->getCloudServiceResponse("getTemplate/GeoLocation.htm");
           $memcache->set("CFGeoLocation","$pageContent");
       }
       $pageContent =  str_replace("{output}", print_r($this->getGeoPlugin(),true), $pageContent);
       $pageContent =  str_replace("{GoogleMapsAPI}", ($this->getConf("GoogleMapsAPI"))?"true":"false", $pageContent);
       if($this->getConf("GooglePublicAPICredential"))
       $pageContent =  str_replace("{GooglePublicAPICredential}", $this->getConf("GooglePublicAPICredential"), $pageContent);
	   break;
       
    case 'CloudSQL':
        $this->loadClass("db/CloudSQL");
        $db = new CloudSQL();
        $db->connect();
        
        $db->close();
        $this->setConf("pageCode","CloudSQL");
        $pageContent = $memcache->get("CFCloudSQL");
        if(!strlen($pageContent) || $_GET[nocache]) {
           $pageContent = $this->getCloudServiceResponse("getTemplate/CloudSQL.htm");
           $memcache->set("CFCloudSQL","$pageContent");
        }
        $pageContent =  str_replace("{output}", (!$db->error())?"OK":$db->getError(), $pageContent);
          break;

    case 'Email':
       $this->setConf("pageCode","Email");
       $pageContent = $memcache->get("CFEmail");
       if(!strlen($pageContent) || $_GET[nocache]) {
           $pageContent = $this->getCloudServiceResponse("getTemplate/Email.htm");
           $memcache->set("CFEmail","$pageContent");
       }
        include_once(dirname(__FILE__) ."/../class/email/logic/Email.php");
        
        $pageContent =  str_replace("{msg}", htmlentities($_msg), $pageContent);
        $pageContent =  str_replace("{From}", htmlentities($_from), $pageContent);
        $pageContent =  str_replace("{To}", htmlentities($_to), $pageContent);
        $pageContent =  str_replace("{Subject}", htmlentities($_subject), $pageContent);
        $pageContent =  str_replace("{txtMsg}", htmlentities($_txtMsg), $pageContent);
        $pageContent =  str_replace("{htmlMsg}", htmlentities($_htmlMsg), $pageContent);
        $pageContent =  str_replace("{sendGridUser}", htmlentities($_sendgridUser), $pageContent);
        $pageContent =  str_replace("{sendGridPassword}", htmlentities($_sendgridPassword), $pageContent);
        $pageContent =  str_replace("{source}", htmlentities(file_get_contents(dirname(__FILE__) ."/../class/email/logic/Email.php")),$pageContent);
       break;
       
    case 'SMS':
       $this->setConf("pageCode","SMS");
       $pageContent = $memcache->get("CFSMS");
       if(!strlen($pageContent) || $_GET[nocache]) {
           $pageContent = $this->getCloudServiceResponse("getTemplate/SMS.htm");
           $memcache->set("CFEmail","$pageContent");
       }
	   $_from = $this->getConf("twilioNumber");
        include_once(dirname(__FILE__) ."/../class/sms/logic/SMS.php");
        
        $pageContent =  str_replace("{msg}", htmlentities($_msg), $pageContent);
        $pageContent =  str_replace("{From}", htmlentities($_from), $pageContent);
        $pageContent =  str_replace("{To}", htmlentities($_to), $pageContent);
        $pageContent =  str_replace("{txtMsg}", htmlentities($_txtMsg), $pageContent);
        $pageContent =  str_replace("{source}", htmlentities(file_get_contents(dirname(__FILE__) ."/../class/sms/logic/SMS.php")),$pageContent);
       break;
	   
    case 'File':
       $this->setConf("pageCode","File");
       $pageContent = $memcache->get("CFFile");
       if(!strlen($pageContent) || $_GET[nocache]) {
           $pageContent = $this->getCloudServiceResponse("getTemplate/File.htm");
           $memcache->set("CFEmail","$pageContent");
       }
        include_once(dirname(__FILE__) ."/../class/io/logic/File.php");

        $pageContent =  str_replace("{source}", htmlentities($output),$pageContent);
       break;                 
	default:
		 $pageContent = $this->getCloudServiceResponse("getTemplate/".$service.".htm");
		break;
}



?>