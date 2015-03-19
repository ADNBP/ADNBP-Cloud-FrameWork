<?php
/* @var $this ADNBP */

// Task to use in background
use google\appengine\api\taskqueue\PushTask;

$_url = str_replace('queue/', '', $this -> _url);
unset($api->formParams['_raw_input_']);

$api->formParams['cloudframework_queued'] = true;
$api->formParams['cloudframework_queued_id'] = uniqid('queue',true);
$value['url_queued'] =$_url;
$value['method'] =$api->method;
$value['data_sent'] = $api->formParams;

if(isset($api->formParams['test'])) {
	$_url = (($_SERVER['HTTPS']=='off')?'http':'https').'://'.$_SERVER['HTTP_HOST'].$_url;
	$value['url_queued'] =$_url;
	
	$value['test_mode'] = true;
	$value['data_received'] = $this->getCloudServiceResponse($_url,$api->formParams,$api->method);
	if($value['data_received']===false) $value['data_received'] = $this->errorMsg;
	else $value['data_received'] = json_decode($value['data_received']);
} else {
	$task = new PushTask($_url, $api->formParams,array('method'=>$api->method));
	$task_name = $task->add();	
}


// API old compatibility
$error = $api->error;
$errorMsg = $api->errorMsg;