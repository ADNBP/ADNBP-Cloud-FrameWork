<?php
$date = date("Y-m-d H:i:s");
$value=array('default_timezone'=>date_default_timezone_get(),'datetime'=>$date);
// GET , PUT, UPDATE, DELETE, COPY...
switch ($this->getAPIMethod()) {
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
        $error=400;
        break;
} 
?>