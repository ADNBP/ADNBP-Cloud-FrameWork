<?php

// This service has to be implemented in your <document_root>/logic/CloudFrameWorkService.php
    list($foo,$script,$service,$params) = split('/',$this->_url,4);
    
    switch ($service) {
        case 'getTemplate':
            $template =$params;
            if(is_file($this->_rootpath."/ADNBP/templates/CloudFrameWork/".$template)) {
               echo(file_get_contents ( $this->_rootpath."/ADNBP/templates/CloudFrameWork/".$template ));
            } else if(is_file($this->_webapp."/templates/CloudFrameWork/".$template)) {
               echo(file_get_contents ( $this->_webapp."/templates/CloudFrameWork/".$template ));
            } else echo("template $template not found");   
			die();  
            break;
        case 'checkVersion':
            if(!strlen($params)) echo "Your current version is: ".$this->version;
            else echo(($this->version == $params)?"OK $params":"Warning. Your version  ".htmlentities($params)." is different of current version:".$this->version);
            die();
            break;
        case 'getMyIP':
            echo "Your IP is: ".$_SERVER[REMOTE_ADDR];
            die();
		    break;
        case 'fetchURL':
            if(strpos($_GET[url], 'http') !== false) {
                echo @file_get_contents($_GET[url]);
            } else {
                echo "You have to provide an URL";
            }

            die();
            break;
        default:
			// This allow to create own services in each WebServer
            if(is_file($this->_webapp."/logic/CloudFrameWorkService/".$service.".php"))
                include_once $this->_webapp."/logic/CloudFrameWorkService/".$service.".php";
            else { echo "You have select at least one valid service. Example: <a href=/CloudFrameWorkService/checkVersion>/CloudFrameWorkService/checkVersion</a>";
                if(strlen($service)) echo '<li><b>'.htmlentities($service)."</b> is not installed";
            }
            break;
    }

?>