<?php
$value=array();
// GET , PUT, UPDATE, DELETE, COPY...
switch ($this->getAPIMethod()) {
    case 'GET':
        $value['ip'] =  $_SERVER[REMOTE_ADDR];
        break;
    default:
        $error=400;
        break;
} 
     
