<?php

// From api.php I receive: $service, (string)$params 
// $error = 0;Initialitated in api.php
// GET , PUT, UPDATE, DELETE, COPY...

switch ($this->getAPIMethod()) {
    case 'GET':
        list($template,$lang) = explode('/',$params);
        if(!strlen($template)) {
            $ret=array();
            if(is_dir($this->_webapp."/templates/CloudFrameWork")) {
                $files = scandir($this->_webapp."/templates/CloudFrameWork");
                foreach ($files as $key => $content) if(strpos($content,'.htm')) $ret[] = str_replace( '.htm' ,  '', $content);
            }
            $files = scandir($this->_rootpath."/ADNBP/templates/CloudFrameWork");
            foreach ($files as $key => $content) if(strpos($content,'.htm')) $ret[] = str_replace( '.htm' ,  '', $content);
            $value['templates'] = $ret;
            
        } else if(strpos($template, '..')) {
            $returnMethod = 'HTML';
            $error=403;
            $value = '{templateRoute} doesn\'t allow ".." in the route. Important security issued have been reported.';
        } else {
                $returnMethod = 'HTML';
                $found =true;
                if(strpos($template,'.') === false) $template.='.htm';
				
				// Allow include the templates from GoogleCloudStoreBucket
				if(strlen($this->getConf("GoogleCloudStoreBucket"))) {
					if(is_file($this->getConf("GoogleCloudStoreBucket")."/templates/CloudFrameWork/".$template)) {
                       $value = file_get_contents ($this->getConf("GoogleCloudStoreBucket")."/templates/CloudFrameWork/".$template );
					} else $found = false;
				} elseif(is_file($this->_webapp."/templates/CloudFrameWork/".$template)) {
                   $value = file_get_contents ( $this->_webapp."/templates/CloudFrameWork/".$template );
                } elseif(is_file($this->_rootpath."/ADNBP/templates/CloudFrameWork/".$template)) {
                   $value = file_get_contents ( $this->_rootpath."/ADNBP/templates/CloudFrameWork/".$template );
                } else $found = false;
                
                if(!$found) {
                    $error = 404;
                    $errorMsg ="<html><body>template not found</body></html>"; 
                } else {
                    // Do substitutions
                	if(strlen($lang))
						$value = $this->applyTranslations($value,$lang); // substitute {{lang:xxxx }}
					$value = $this->applyVarsSubsitutions($value);
                    die($value);
                }        
        }
        break;
    default:
        $error=405;
        break;
} 