<?php
$this->urlRedirect("/CloudFrameWork","/CloudFrameWork/home");

if(!strlen($this->getSessionVar("version")) || isset($_GET[nocache])) {
    $this->setSessionVar("version",$this->getCloudServiceResponse("version/".$this->version));
}

list($foo,$script,$service,$params) = split('/',$this->_url,4);


$memcache = new Memcache;

switch ($service) {
	case 'home':
        $pageContent = $memcache->get("CFHome");
        if(!strlen($pageContent) || isset($_GET[nocache])) {
           $pageContent = $this->getCloudServiceResponse("templates/intro");
           $memcache->set("CFHome","$pageContent");
        }
        $this->setConf("pageCode","home");
		break;
    case 'GeoLocation':
       $this->setConf("pageCode","GeoLocation");
       $pageContent = $memcache->get("CFGeoLocation");
       if(!strlen($pageContent) || isset($_GET[nocache])) {
           $pageContent = $this->getCloudServiceResponse("templates/GeoLocation");
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
        if(!strlen($pageContent) || isset($_GET[nocache])) {
           $pageContent = $this->getCloudServiceResponse("templates/CloudSQL");
           $memcache->set("CFCloudSQL","$pageContent");
        }
        $pageContent =  str_replace("{output}", (!$db->error())?"OK connecting to ".$db->getConf("dbServer"):$db->getError(), $pageContent);
          break;
    case 'Email':
       $this->setConf("pageCode","Email");
       $pageContent = $memcache->get("CFEmail");
       if(!strlen($pageContent) || isset($_GET[nocache])) {
           $pageContent = $this->getCloudServiceResponse("templates/Email");
           $memcache->set("CFEmail","$pageContent");
       }
        include_once($this->_rootpath."/ADNBP/class/email/logic/Email.php");
        
        $pageContent =  str_replace("{msg}", htmlentities($_msg), $pageContent);
        $pageContent =  str_replace("{From}", htmlentities($_from), $pageContent);
        $pageContent =  str_replace("{To}", htmlentities($_to), $pageContent);
        $pageContent =  str_replace("{Subject}", htmlentities($_subject), $pageContent);
        $pageContent =  str_replace("{txtMsg}", htmlentities($_txtMsg), $pageContent);
        $pageContent =  str_replace("{htmlMsg}", htmlentities($_htmlMsg), $pageContent);
        $pageContent =  str_replace("{sendGridUser}", htmlentities($_sendgridUser), $pageContent);
        $pageContent =  str_replace("{sendGridPassword}", htmlentities($_sendgridPassword), $pageContent);
        $pageContent =  str_replace("{source}", htmlentities(file_get_contents($this->_rootpath."/ADNBP/class/email/logic/Email.php")),$pageContent);
       break;
    case 'SMS':
       $this->setConf("pageCode","SMS");
       $pageContent = $memcache->get("CFSMS");
       if(!strlen($pageContent) || isset($_GET[nocache])) {
           $pageContent = $this->getCloudServiceResponse("templates/SMS");
           $memcache->set("CFSMS","$pageContent");
       }
	   $_from = $this->getConf("twilioNumber");
        include_once($this->_rootpath."/ADNBP/class/sms/logic/SMS.php");
        
        $pageContent =  str_replace("{msg}", htmlentities($_msg), $pageContent);
        $pageContent =  str_replace("{From}", htmlentities($_from), $pageContent);
        $pageContent =  str_replace("{To}", htmlentities($_to), $pageContent);
        $pageContent =  str_replace("{txtMsg}", htmlentities($_txtMsg), $pageContent);
        $pageContent =  str_replace("{source}", htmlentities(file_get_contents($this->_rootpath."/ADNBP/class/sms/logic/SMS.php")),$pageContent);
       break;
    case 'File':
       $this->setConf("pageCode","File");
       $pageContent = $memcache->get("CFFile");
       if(!strlen($pageContent) || isset($_GET[nocache])) {
           $pageContent = $this->getCloudServiceResponse("templates/File");
           $memcache->set("CFFile","$pageContent");
       }
        include_once($this->_rootpath."/ADNBP/class/io/logic/File.php");

        $pageContent =  str_replace("{source}", htmlentities($output),$pageContent);
        $pageContent =  str_replace("{msg}", htmlentities($msg),$pageContent);
       break;  
    case 'DataStore':
       $this->setConf("pageCode","DataStore");
       $pageContent = $memcache->get("CFDataStore");
       if(!strlen($pageContent) || isset($_GET[nocache])) {
           $pageContent = $this->getCloudServiceResponse("templates/DataStore");
           $memcache->set("CFDataStore","$pageContent");
       }
        include_once($this->_rootpath."/ADNBP/class/io/logic/DataStore.php");

        $pageContent =  str_replace("{source}", htmlentities($output),$pageContent);
       break;                        
    case 'Chat':
       // Read template  
             
       $this->setConf("pageCode","Chat");
	   if(isset($_GET['v2'])) 
       		$pageContent = $memcache->get("CFChatv2");
	   else
       		$pageContent = $memcache->get("CFChat");
       if(!strlen($pageContent) || isset($_GET[nocache])) {
       	   if(isset($_GET['v2'])) {
		      $pageContent = $this->getCloudServiceResponse("templates/Chatv2");
	           $memcache->set("CFChatv2","$pageContent");
		   } else {
           	  $pageContent = $this->getCloudServiceResponse("templates/Chat");
	           $memcache->set("CFChat","$pageContent");
		   }
       }
       // Analyzing Credentials
       $_publicKey = $this->getConf('GooglePublicAPICredential');
       if(!strlen($_publicKey)) $_publicKey = $_GET['publicKey'];
       $pageContent =  str_replace("{publicKey}", $_publicKey, $pageContent);
	   
       break;
    case 'Translate':
       // Read template        
       $this->setConf("pageCode","Translate");
       $pageContent = $memcache->get("CFTranslate");
       if(!strlen($pageContent) || isset($_GET[nocache])) {
           $pageContent = $this->getCloudServiceResponse("templates/Translate");
           $memcache->set("CFTranslate","$pageContent");
       }
       
       // Analyzing Credentials
       $_publicKey = $this->getConf('GooglePublicAPICredential');
       if(!strlen($_publicKey)) $_publicKey = $_GET['publicKey'];
       $pageContent =  str_replace("{publicKey}", $_publicKey, $pageContent);
             
       $_serverKey = $this->getConf('GoogleServerAPICredential'); 
       if(strlen($_serverKey))           
           $pageContent =  str_replace("{serverKey}", 'Currently configured: *****', $pageContent);
       else {
           $_serverKey = $_GET['serverKey'];
           $pageContent =  str_replace("{serverKey}", '<input type="input" name="serverKey"  placeholder="Write your Google Server Key" class="form-control" >', $pageContent);
       }       
       
       $pageContent =  str_replace("{msgSource}", htmlentities($_GET['msgSource']), $pageContent);
       $sourceLangs = "<option value='es,en' ".(($_GET['langs']=="es,en")?'selected':'').">Español a English
            				<option  value='es,ru' ".(($_GET['langs']=="es,ru")?'selected':'').">Español to Русский
            				<option  value='en,es' ".(($_GET['langs']=="en,es")?'selected':'').">English to Español            					
            				<option  value='en,ru' ".(($_GET['langs']=="en,ru")?'selected':'').">English to Русский
            				<option  value='ru,es' ".(($_GET['langs']=="ru,es")?'selected':'').">Русский to Español
            				<option  value='ru,en' ".(($_GET['langs']=="ru,en")?'selected':'').">Русский to English";
        $pageContent =  str_replace("{sourceLangs}", $sourceLangs, $pageContent);
		
		if(strlen($_GET['langs']))
			list($source,$target) = explode(',',$_GET['langs'],2);
		
		if(strlen($source) && strlen($target) && strlen($_GET['msgSource']) && strlen($_serverKey) && $source!=$target) {
			
			$data['q'] = $_GET['msgSource'];
			$data['source'] = $source;
			$data['target'] = $target;
			$data['key'] = $_serverKey;
			
			$ret = json_decode($this->getCloudServiceResponse('https://www.googleapis.com/language/translate/v2',$data,'GET'));
			$pageContent =  str_replace("{msgTranslated}", addslashes($ret->data->translations[0]->translatedText), $pageContent);
			
		}
		
            				
       break;  	default:
		 $pageContent = $this->getCloudServiceResponse("templates/".$service);
		break;
}



?>