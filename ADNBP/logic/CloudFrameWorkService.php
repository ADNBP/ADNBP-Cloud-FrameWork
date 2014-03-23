<?php

// This service has to be implemented in your <document_root>/logic/CloudFrameWorkService.php

if(is_file("./logic/CloudFrameWorkService.php")) {    
    include("./logic/CloudFrameWorkService.php");  
} {
    list($foo,$script,$service,$params) = split('/',$this->_url,4);
    switch ($service) {
    	case 'getTemplate':
            $template =$params;
            if(is_file("./ADNBP/templates/CloudFrameWork/".$template)) {
               echo(file_get_contents ( "./ADNBP/templates/CloudFrameWork/".$template ));
            }
            else echo("template $template not found");     
    		break;
        case 'checkVersion':
            echo(($this->version == $params)?"OK $params":"ERROR. Your version  ".htmlentities($params)." is different to current Version:".$this->getConf("CloudFrameWorkVersion"));
            break;
    	default:
    		echo "Wrong paramaters";
    		break;
    }
}

die();

?>