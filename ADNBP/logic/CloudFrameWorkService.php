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
            echo(($this->version == $params)?"OK $params":"ERROR. Your version  ".htmlentities($params)." is different of current version:".$this->version);
            die();
		    break;
        default:
			// This allow to create own services in each WebServer
            if(is_file($this->_webapp."/logic/CloudFrameWorkService/".$service.".php"))
                include_once $this->_webapp."/logic/CloudFrameWorkService/".$service.".php";
            else echo "The Service <b>".htmlentities($service)."</b> is not installed";
            break;
    }

?>