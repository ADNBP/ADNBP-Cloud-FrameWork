<?php
$date = date("Y-m-d H:i:s");
$value=array('default_timezone'=>date_default_timezone_get(),'datetime'=>$date);
// GET , PUT, UPDATE, DELETE, COPY...
switch ($api->method) {
    case 'GET':
        list($continent,$city,$date) = explode('/',$params);
        if(!strlen($continent))
            $value['timezones'] =  timezone_identifiers_list();
        else {
            date_default_timezone_set($continent.'/'.$city);
            $value['new_timezone']=date_default_timezone_get();
            $date = date("Y-m-d H:i:s");
            $value['new_datetime']=$date;
            
        }
        break;
    default:
        $api->error=400;
        break;
} 

// Compatibility until migration
$error = $api->error;
$errorMsg = $api->errorMsg;
?>