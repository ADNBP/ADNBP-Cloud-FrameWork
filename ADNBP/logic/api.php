<?php

    $this->loadClass("api/RESTful");
    $api = new RESTful();
	
	
	// Rules for Cross-Domain AJAX
	// https://www.getpostman.com/collections/f6c73fa68b03add49d09
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: POST,GET,PUT");
	header("Access-Control-Allow-Headers: Content-Type");
	header('Access-Control-Max-Age: 1000');
		

    // Error code && Ok Code
    $error = 0;  // it support: 400 etc..
    $okRet = 200;  // it support: 200,201,204,400 etc..
    $errorMsg = '';
    $returnMethod = 'JSON';  // support: JSON, HTML
        
        
    //$headers = apache_request_headers(); // Store all headers
    $isSuperAdmin = (strlen($this->getHeader('X-Adnbp-Superuser')))?$this->checkPassword($this->getHeader('X-Adnbp-Superuser'),$this->getConf("adminPassword")):false;
    $apiMethod = $this->getAPIMethod(); // GET , PUT, UPDATE, DELETE, COPY...
    
// This service has to be implemented in your <document_root>/logic/CloudFrameWorkService.php
    list($foo,$script,$service,$params) = explode('/',$this->_url,4);
    $service = strtolower($service);
    
    if(!strlen($service) && !strlen($params)) {
                 $this->setConf("notemplate",false);
                include_once $this->_rootpath."/ADNBP/logic/apiDoc.php";
                if(is_file($this->_webapp."/logic/api/apiDoc.php"))  include_once $this->_webapp."/logic/api/apiDoc.php";
    } else {
        switch ($service) {
        
        case 'auth':
                if(strlen($params)) {
                echo '<h1>Server Side</h1>';
                echo '<li>conf-var: CloudServiceToken-'.$params.': '.(strlen($this->getConf("CloudServiceToken-".$params))?'exist. OK':'missing. ERROR').'</li>';
                echo '<h1>Client Side</h1>';
                echo '<li>header: X-Cloudservice-Id '.(strlen($this->getHeader('X-Cloudservice-Id'))?'exist. OK':'missing. ERROR').'</li>';
                echo '<li>header: X-Cloudservice-Date '.(strlen($this->getHeader('X-Cloudservice-Date'))?'exist. OK':'missing. ERROR').'</li>';
                echo '<li>header: X-Cloudservice-Signature '.(strlen($this->getHeader('X-Cloudservice-Signature'))?'exist. OK':'missing. ERROR').'</li>';
                $msg = '';
                if(strlen($this->getConf("CloudServiceToken-".$params)))
                if($this->checkAPIAuth($msg)){
                    echo "<li>API Auth OK";
                } else echo "<li>API Auth Error: ".$msg;
                
            } else {
                echo "A Id param is required: ../checkAPIAuth/{Id}";
            }
            die();
            break;
        case 'version':
            if(!strlen($params)) echo "Your current version is: ".$this->version();
            else echo(($this->version() == $params)?"OK $params":"Warning. Your version  ".htmlentities($params)." is different of current version:".$this->version);
            die();
            break;
      
        case 'fetchURL':
            if(strpos($_GET[url], 'http') !== false) {
                echo @file_get_contents($_GET[url]);
            } else {
                echo "You have to provide 'url' GET variable: .../fetchURL?url={encodedURL} ";
            }

            die();
            break;
        default:
			// This allows to create your own services in each WebServer
            $__includePath ='';
            
            // If ApiPath is defined, normally pointing into a bucket..
            if(strlen($this->getConf("ApiPath"))) 
                if(is_file($this->getConf("ApiPath").'/'.$service.".php"))
                    $__includePath = $this->getConf("ApiPath").'/'.$service.".php";
                elseif(is_file($this->_rootpath."/ADNBP/logic/api/".$service.".php"))
                    $__includePath = $this->_rootpath."/ADNBP/logic/api/".$service.".php";
            
            //  If there is no path found lets try under logic/api
            if($__includePath=='')
               if(is_file($this->_webapp."/logic/api/".$service.".php"))
                    $__includePath = $this->_webapp."/logic/api/".$service.".php";
               elseif(is_file($this->_rootpath."/ADNBP/logic/api/".$service.".php"))
                    $__includePath =  $this->_rootpath."/ADNBP/logic/api/".$service.".php";
            
            //Now include the file or show the error
            if(strlen($__includePath)) {
                include_once $__includePath;
            } else {
                $error = 404;
                if(strlen($this->getConf("ApiPath")))
                     $errorMsg= 'Unknow file '.$service.' in bucket '.$this->getConf("ApiPath");
                else
                     $errorMsg= 'Unknow file '.$service.' in '.$this->_url;
            }
 
            break;
        }

        // Compatibility until migration to $api
        $api->error = $error;
		$api->errorMsg = $errorMsg;
		$api->contentTypeReturn = $returnMethod;

        $ret = array();
		$ret['header'] = $api->getHeader();
        $ret['status'] = $api->getReturnCode();
        $ret['method'] = $api->method;
        $ret['success'] = ($api->error)?false:true;
		$ret['ip']=$this->_ip;
        $ret['url']=(($_SERVER['HTTPS']=='on')?'https://':'http://').$_SERVER['HTTP_HOST'].'/'.$_SERVER['REQUEST_URI'];
        $ret['user_agent']=$this->userAgent;
		$ret['params']=json_encode($api->formParams);
        if($api->error) {
                $ret['error']['message']=$api->errorMsg;
        }
		
		// Send Logs APILog
		if($api->service != 'logs' && strlen($this->getConf("ApiLogsURL"))) {
			
			if(isset($_REQUEST['addLog']) && !isset($_REQUEST['test'])) {
				// $logParams['test_mode'] = 'on';
				$logParams['title'] = 'API '.$this->_url;
				$logParams['text'] = json_encode($ret);
				$urlLog = $this->getConf("ApiLogsURL").'/Logs/';
				$urlLog .= ($api->error)?'Error':'Sucess';
				
				$retLog = json_decode($this->getCloudServiceResponse($urlLog,$logParams));
				
				if(is_object($retLog) && isset($retLog->success) && $retLog->success) $ret['log_saved'] = true;
				else {
					$ret['log_saved'] = false;
					$ret['log_message'] = json_encode($retLog);
				}
			} else {
				if(isset($_REQUEST['addLog'])) {
					$ret['log_ignored'] = true;
				    $ret['log_message'] = 'test form-var has been passed';
				}
			}
		}

        if(is_array($value)) $ret = array_merge($ret,$value);
		$api->sendHeaders();
		// Output Value
        switch ($api->contentTypeReturn) {
            case 'JSON':
                die(json_encode($ret));                   
                break;
            
            default:
                if($api->error) $value = $api->errorMsg;
                die($value);
                break;
        }
    }

?>