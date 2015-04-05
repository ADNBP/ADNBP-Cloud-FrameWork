<?php
$api->checkMethod('GET');
if(!$api->error){
	$api->addReturnData(array('_timeZoneSystemDefault'=>$this->_timeZoneSystemDefault));
	$api->addReturnData(array('timeZone'=>$this->_timeZone));
}