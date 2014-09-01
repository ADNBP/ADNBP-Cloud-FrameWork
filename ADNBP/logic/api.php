<?php

    // Error code
    $error = 0;  // it support: 200,201,204,400 etc..
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
                include_once $this->_rootpath."/ADNBP/logic/api/apiDoc.php";
                if(is_file($this->_webapp."/logic/api/api/apiDoc.php"))  include_once $this->_webapp."/logic/api/apiDoc.php";
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
            // This allow to create own services in each WebServer
            if(is_file($this->_webapp."/logic/api/".$service.".php"))
                include_once $this->_webapp."/logic/api/".$service.".php";
            elseif(is_file($this->_rootpath."/ADNBP/logic/api/".$service.".php"))
                include_once $this->_rootpath."/ADNBP/logic/api/".$service.".php";
           else {
                 $error = 404;
                 $errorMsg= 'Unknow file '.$service.' in '.$this->_url;
            }
            break;
        }

        // Output header
        switch ($error) {
            case 405:
                header("HTTP/1.0 405 Method Not Allowed");
                $errorMsg= 'Method '.$this->getAPIMethod().' is not supported';
                
                break;
            case 400:
                header("HTTP/1.0 400 Bad Request");
                break;  

            case 401:
                header("HTTP/1.0 401 Unauthorized");
                break;  
            case 404:
                header("HTTP/1.0 404 Not Found");
                if(!strlen($errorMsg))
                    $errorMsg= 'Unknow '.$service.' in '.$this->_url;
                break;
            case 503:
                header("HTTP/1.0 504 Service Unavailable");
                break;
            default:
                break;
        }
        
        // Output Value
        switch ($returnMethod) {
            case 'JSON':
                header("Content-type: application/json");
                if(!$error) $value['success'] = true;
                else {
                    $value['success'] = false;
                    $value['error']=array('message'=>$errorMsg);
                }
                die(json_encode($value));                   
                break;
            
            default:
                header("Content-type: text/html");
                if($error) $value = $errorMsg;
                die($value);
                break;
        }
    }

?>