<?php

// From api.php I receive: $service, (string)$params 
// $api->error = 0;Initialitated in api.php
// GET , PUT, UPDATE, DELETE, COPY...

switch ($api->method) {
    case 'GET':
        list($template,$lang) = explode('/',$params);
        
        // Paths to search for the template
        $value['api_paths'][] = '/ADNBP/templates/CloudFrameWork';
        if(is_dir($this->_webapp."/templates/CloudFrameWork")) 
            $value['api_paths'][] = '/{webapp}/templates/CloudFrameWork';
        if(strlen($this->getConf("ApiTemplatesPath")))
		   $value['api_paths'][] = $this->getConf("ApiTemplatesPath");
        
        // List the templates in those paths
        if(!strlen($template)) {
            $ret=array();

            // FrameWork's templates
            $files = scandir($this->_rootpath."/ADNBP/templates/CloudFrameWork");
            foreach ($files as $key => $content) if(strpos($content,'.htm')) $ret[] = str_replace( '.htm' ,  '', $content);

            // webapp templates
            if(is_dir($this->_webapp."/templates/CloudFrameWork")) {
                $files = scandir($this->_webapp."/templates/CloudFrameWork");
                foreach ($files as $key => $content) if(strpos($content,'.htm')) $ret[] = str_replace( '.htm' ,  '', $content);
            }
            
            // externalPath templates
			if(strlen($this->getConf("ApiTemplatesPath"))) {
				if(preg_match("/^http/", $this->getConf("ApiTemplatesPath"))){
				    $value['warning'] = "We can not get list of templates from a remote URL: ".$this->getConf("ApiTemplatesPath");
				} elseif(!is_dir($this->getConf("ApiTemplatesPath"))) {
					$api->error=503;
					$api->errorMsg = "Error. The following path does not exist: ".$this->getConf("ApiTemplatesPath");
					$ret[] = $this->getConf("ApiTemplatesPath").' doesn\'t exist';
	            } else {
	            	$files = scandir($this->getConf("ApiTemplatesPath"));
	                foreach ($files as $key => $content) if(strpos($content,'.htm')) $ret[] = str_replace( '.htm' ,  '', $content);
	            }
			} 
            
            $value['templates'] = $ret;
            
        } else if(strpos($template, '..')) {
            $returnMethod = 'HTML';
            $api->error=403;
            $value = '{templateRoute} doesn\'t allow ".." in the route. Important security issued have been reported.';
        } else {
                $returnMethod = 'HTML';
                $found =false;
                if(strpos($template,'.') === false) $template.='.htm';
				
				// Allow include the templates from GoogleCloudStoreBucket
				if(strlen($this->getConf("ApiTemplatesPath"))) {
					if(preg_match("/^http/", $this->getConf("ApiTemplatesPath"))){
						$value = file_get_contents ($this->getConf("ApiTemplatesPath")."/".$template );
						if($value!==false) $found = true;
					} elseif(is_file($this->getConf("ApiTemplatesPath")."/".$template)) {
                       $value = file_get_contents ($this->getConf("ApiTemplatesPath")."/".$template );
                       $found = true;
					} else $found = false;
				} 
				
                // If not lets try from webapp or Cloud FramWork
                if(!$found)
				if(is_file($this->_webapp."/templates/CloudFrameWork/".$template)) {
                   $value = file_get_contents ( $this->_webapp."/templates/CloudFrameWork/".$template );
                   $found = true;
                } elseif(is_file($this->_rootpath."/ADNBP/templates/CloudFrameWork/".$template)) {
                   $value = file_get_contents ( $this->_rootpath."/ADNBP/templates/CloudFrameWork/".$template );
                   $found = true;
                }
                
                // If not Found error
                if(!$found) {
                    $api->error = 404;
                    $api->errorMsg ="<html><body>template not found</body></html>"; 
                    
                // Else apply subsititutions
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
        $api->error=405;
        break;
} 

// Compatibility until migration
$error = $api->error;
$errorMsg = $api->errorMsg;