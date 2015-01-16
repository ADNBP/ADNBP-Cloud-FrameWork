<?php

if(!$api->error) $api->checkMethod('GET');
$ip = $api->formParams['ip'];
if(!strlen($ip)) $ip = $_SERVER['REMOTE_ADDR'];



// Param Control
// Ejecuting
if(!$api->error) {
	switch ($api->method) {
		case 'GET':
			$value['data']['ip'] = $ip;
			$this->readGeoData($ip,true);
			if($this->_geoData[$ip] === null) {
				$api->error = 503;
			} else {
 			    $value['data']['localization'] = $this->_geoData[$ip];
			}
			break;
	}
}

$error = $api->error;
$errorMsg = $api->errorMsg;
?>