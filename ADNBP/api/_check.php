<?php
global $__performance;
$api->checkMethod('GET');

$mt = microtime(true);
$api->addReturnData(array('init_memory'=>number_format(round(memory_get_usage() / (1024 * 1024) , 3), 3) . ' Mb'));
$api->addReturnData(array('init_time'=>(round($mt - $__performance['initMicrotime'], 3)) . ' sec'));

if($this->getConf("dbName")) {
	  $this->loadClass("db/CloudSQL");
	  $db = new CloudSQL();
	  $db->connect();
	  if(!$db->error()) $db->close();
}

$api->addReturnData(array('db_connect'=>($db->error())?'ERROR: '.$db->getError():'OK'));
$api->addReturnData(array('db_connect_time'=>(round(microtime(true) - $mt , 3)) . ' sec'));


