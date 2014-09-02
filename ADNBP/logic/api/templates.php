<?php

// From api.php I receive: $service, (string)$params 
// $error = 0;Initialitated in api.php
// GET , PUT, UPDATE, DELETE, COPY...

switch ($this->getAPIMethod()) {
    case 'GET':
        if(!strlen($params)) {
            
            $ret=array();
            if(is_dir($this->_webapp."/templates/CloudFrameWork")) {
                $files = scandir($this->_webapp."/templates/CloudFrameWork");
                foreach ($files as $key => $content) if(strpos($content,'.htm')) $ret[] = str_replace( '.htm' ,  '', $content);
            }
            $files = scandir($this->_rootpath."/ADNBP/templates/CloudFrameWork");
            foreach ($files as $key => $content) if(strpos($content,'.htm')) $ret[] = str_replace( '.htm' ,  '', $content);
            $value['templates'] = $ret;
            
        } else if(strpos($params, '..')) {
            $returnMethod = 'HTML';
            $error=403;
            $value = '{templateRoute} doesn\'t allow ".." in the route. Important security issued have been reported.';
        } else {
                $returnMethod = 'HTML';
                $template = $params;
                $found =true;
                if(strpos($template,'.') === false) $template.='.htm';
                if(is_file($this->_rootpath."/ADNBP/templates/CloudFrameWork/".$template)) {
                   $value = file_get_contents ( $this->_rootpath."/ADNBP/templates/CloudFrameWork/".$template );
                } else if(is_file($this->_webapp."/templates/CloudFrameWork/".$template)) {
                   $value = file_get_contents ( $this->_webapp."/templates/CloudFrameWork/".$template );
                } else $found = false;
                
                if(!$found) {
                    $error = 404;
                    $errorMsg ="<html><body>template not found</body></html>"; 
                } else {
                    // Do substitutions
                    unset($matchs);
                    preg_match_all('/{([^}]*)}/', $value,$matchs);
                    if(is_array($matchs[0])) for($i=0,$tr=count($matchs[0]);$i<$tr;$i++) {
                        if(isset($_GET[$matchs[1][$i]])) {
                            $value = str_replace($matchs[0][$i], '<!-- '.$matchs[0][$i].' -->'.$_GET[$matchs[1][$i]], $value);
                        }
                    }
                    die($value);
                }        
        }
        break;
    default:
        $error=405;
        break;
} 