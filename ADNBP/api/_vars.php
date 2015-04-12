<?php

$api->checkMethod('GET');
if(!$api->error) {
	$api->setReturnData($_SERVER);
}