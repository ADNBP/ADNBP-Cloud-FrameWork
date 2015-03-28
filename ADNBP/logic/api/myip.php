<?php
$api->checkMethod('GET');

if(!$api->error) switch ($this->getAPIMethod()) {
    case 'GET':
		$api->addReturnData(array('ip'=>$_SERVER['REMOTE_ADDR']));
		unset($value);
        break;
}