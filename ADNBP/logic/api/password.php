<?php

// From api.php I receive: $service, (string)$params 
list($urlParams,$key) = explode('/',$params);

$value=array();
// $error = 0;Initialitated in api.php
// GET , PUT, UPDATE, DELETE, COPY...
switch ($api->method) {
    case 'GET':
        switch ($params) {
            case 'crypt':
                if(isset($_GET['password']) || strlen($_GET['password']) ) {
                    $value['password'] = $_GET['password'];
                    $value['crypt'] = $this->crypt($_GET['password']);
                    // $value['urlencoded_crypt'] = urlencode($this->crypt($_GET['password']));
                } else {
                    $api->error=400;
                    $api->errorMsg='Required parameter  is not received. Send password parameter';
                }
                break;

            case 'crypt/validate':
                if(isset($_GET['password']) || strlen($_GET['password']) || isset($_GET['password_crypt']) || strlen($_GET['password_crypt'])) {
                    $value['password'] = $_GET['password'];
                    $value['password_crypt'] = $_GET['password_crypt'];
                    $value['validated'] = $this->checkPassword($_GET['password'],$_GET['password_crypt']);
                } else {
                    $api->error=400;
                    $api->errorMsg='Required parameters are not received. Send password,password_crypt parameters';
                }
                break;
                                
            default:
                $api->error=400;
                if(!strlen($params))
                    $api->errorMsg='Missing actions. Use: crypt';
                else 
                    $api->errorMsg='No recognized action '.$params.'. Use: crypt';
                break;
        }
        break;
    
    default:
        $api->error=405;
        break;
} 

// Compatibility until migration
$error = $api->error;
$errorMsg = $api->errorMsg;
