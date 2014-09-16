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
                    $_expr = "((?!}}).)*";
                    preg_match_all('/{{('.$_expr.')}}/s', $value,$matchs);
                    if(is_array($matchs[0])) for($i=0,$tr=count($matchs[0]);$i<$tr;$i++) {
                        
                        // Let's see if there is texts translated
                        if(strpos($matchs[1][$i],'lang:') !== false) {
                            // I take a translation only if lang param is passed
                            if(strlen($lang)) {
                                $_defaultIndex = 1;
                                $_selectedIndex = -1;
                                
                                // Lets find the language to show
                                unset($langs);
                                $_expr = "((?!}}).)*";
                                
                                $langs = explode('lang:',$matchs[0][$i]);
                                // preg_match_all('/lang:(.+)/', $matchs[1][$i],$langs);
                                for($j=1,$tr2 = count($langs);$j<$tr2;$j++) {
                                    if(preg_match('/^(default|.*,default\[\[)/', $langs[$j])) 
                                        $_defaultIndex = $j;
                                    if(preg_match('/^('.$lang.'|.*,'.$lang.'\[\[)/', $langs[$j])) 
                                        $_selectedIndex = $j;
                                }
                                if($_selectedIndex < 0) $_selectedIndex = $_defaultIndex;
                                
                                // Extract the text of that language
                                unset($text);
                                $_expr = "((?!\]\]).)*";
                                preg_match('/\[\[('.$_expr.')\]\]/s', $langs[$_selectedIndex],$text);
                                $value = str_replace($matchs[0][$i], $text[1], $value);
                            }
                        // if not there is Variables
                        } else {
                            if(isset($_GET[$matchs[1][$i]]))
                                $value = str_replace($matchs[0][$i], $_GET[$matchs[1][$i]], $value);
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