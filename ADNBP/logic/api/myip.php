<?php
$value=array();
// GET , PUT, UPDATE, DELETE, COPY...
switch ($api->method) {
    case 'GET':
        $value['ip'] =  $_SERVER['REMOTE_ADDR'];
        break;
    default:
        $api->error=400;
        break;
} 

// Compatibility until migration
$error = $api->error;
$errorMsg = $api->errorMsg;