<?php

// From api.php I receive: $service, (string)$params 
list($urlParams,$key) = explode('/',$params);

$value=array();
if(isset($_GET['HTTP_REFERER']) && strlen($_GET['HTTP_REFERER'])) 
    $value['referer_domain']  = $_GET['HTTP_REFERER'];
else 
    $value['referer_domain'] = (isset($_SERVER['HTTP_REFERER']))?$_SERVER['HTTP_REFERER']:'not provided';

// $error = 0;Initialitated in api.php
// GET , PUT, UPDATE, DELETE, COPY...
switch ($api->method) {
    case 'GET':
        switch ($urlParams) {
            case 'checkapikey':
                if(strlen($key) ) {
                    $ret =$this->getConf('API_KEY_'.$key);
                    if(is_array($ret)) {
                        $auth = true;
                        if(!isset($ret['allowed_domains'])) $ret['allowed_domains'][]='*';
                        if(is_array($ret['allowed_domains'])) {
                            
                           // if there is no * to allow any domain.. 
                           if(array_search('*',$ret['allowed_domains'])===false) {
                               if( $value['referer_domain'] =='not provided') {
                                   $auth=false;
                                   $errorMsg='Your referer_domain is not in the allowed domains. Add * to allow any domain';
                               } else {
                                   $auth=false;
                                   foreach ($ret['allowed_domains'] as $key => $content) if($content!='*'){
                                       preg_match('/'.str_replace('/', '\\/', $content).'/',$value['referer_domain'] ,$match);
                                       if(count($match)) {
                                           $auth = true;
                                           $value['domain_matchs'][] = $content;
                                       }
                                   }
                                   
                               }
                           }
                        }
                        
                        if(!$auth) {
                            $error = 401;   
                             $errorMsg='Your referer_domain '.$value['referer_domain'].' is not in the allowed domains. Add more domains to allow any domain'; 
                        } else $value['data'] = $ret;
                   
                        
                    } else {
                        $value['validated'] = false;
                        $value['message'] = 'api_key '.$key.' doesn\'t exist';
                    }
                    // $value['urlencoded_crypt'] = urlencode($this->crypt($_GET['password']));
                } else {
                    $error=400;
                    $errorMsg='Required method is not received. use checkapikey/{api_key}';
                }
                break;

                                
            default:
                $error=400;
                if(!strlen($params))
                    $errorMsg='Missing actions. Use: checkapikey';
                else 
                    $errorMsg='No recognized action '.$params.'. Use: checkapikey';
                break;
        }
        break;
    
    default:
        $error=405;
        break;
} 
