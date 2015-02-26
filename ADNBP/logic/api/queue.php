<?php
// Task to use in background
use google\appengine\api\taskqueue\PushTask;

$_url = str_replace('queue/', '', $_SERVER['REQUEST_URI']);
$_POST['cloudframework_queued'] = true;
$_POST['cloudframework_queued_id'] = uniqid('queue',true);
$value['url_queued'] =$_url;
$value['data_sent'] =$_POST;
$task = new PushTask($_url, $_POST);
$task_name = $task->add();

// API old compatibility
$error = $api->error;
$errorMsg = $api->errorMsg;