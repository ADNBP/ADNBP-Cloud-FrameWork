<?php
global $__p;
$api->checkMethod('GET');


if($this->getConf("dbName")) {
	  $__p->init('test','db/CloudSQL connect');
	  $this->loadClass("db/CloudSQL");
	  $db = new CloudSQL();
	  $db->connect();
	  if(!$db->error()) $db->close();
	  $__p->end('test','db/CloudSQL connect',!$db->error(),$db->getError());
} else {
	$api->addReturnData(array('db/CloudSQL connect'=>'no DB configured'));
}


if(strlen($this->getConf("LocalizePath"))) {
	  $__p->init('test','LocalizePath');
      $ret = scandir($this->getConf("LocalizePath"));
	  $errMsg='';
	  if(!is_array($ret)) $errMsg = error_get_last();
	  $__p->end('test','LocalizePath',is_array($ret),$errMsg);
}

if(strlen($this->getCloudServiceUrl())) {
	  $__p->init('test','Cloud Service Url');
	  $ret = $this->getCloudServiceResponse('_version');
	  
	  $retOk = !$this->error;
	  $retErr = $this->errorMsg;
	  if($retOk) {
	  	$ret = json_decode($ret);
		$retOk = $ret->success;
		if(!$retOk) $retErr = json_encode($ret);
	  }
	  $__p->end('test','Cloud Service Url',$retOk,$this->getCloudServiceUrl().' '.$retErr);
} else {
	$api->addReturnData(array('Cloud Service Url'=>'no CloudServiceUrl configured'));
}

if(is_file($this->_webapp.'/logic/_test.php')) include($this->_webapp.'/logic/_test.php');

if(isset($__p->data['init']))
	$api->addReturnData($__p->data['init']['test']);

