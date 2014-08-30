<?php

// From api.php I receive: $service, (string)$params 
list($urlParams,$key) = explode('/',$params);

$value=array();
// $error = 0;Initialitated in api.php
// GET , PUT, UPDATE, DELETE, COPY...
switch ($this->getAPIMethod()) {
    case 'GET':
        switch ($params) {
            case 'crypt':
                if(isset($_GET['password']) || strlen($_GET['password']) ) {
                    $value['password'] = $_GET['password'];
                    $value['crypt'] = $this->crypt($_GET['password']);
                    // $value['urlencoded_crypt'] = urlencode($this->crypt($_GET['password']));
                } else {
                    $error=400;
                    $errorMsg='Required parameter  is not received. Send password parameter';
                }
                break;

            case 'crypt/validate':
                if(isset($_GET['password']) || strlen($_GET['password']) || isset($_GET['password_crypt']) || strlen($_GET['password_crypt'])) {
                    $value['password'] = $_GET['password'];
                    $value['password_crypt'] = $_GET['password_crypt'];
                    $value['validated'] = $this->checkPassword($_GET['password'],$_GET['password_crypt']);
                } else {
                    $error=400;
                    $errorMsg='Required parameters are not received. Send password,password_crypt parameters';
                }
                break;
                                
            default:
                $error=400;
                if(!strlen($params))
                    $errorMsg='Missing actions. Use: crypt';
                else 
                    $errorMsg='No recognized action '.$params.'. Use: crypt';
                break;
        }
        break;
    
    default:
        $error=405;
        break;
} 


