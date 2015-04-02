<?php
// Automatically you have $api accesible as a object of ADBP/class/api/RESTful.php
// This object ins created by ADBP/logic/api.php
$api->checkMethod('GET,POST'); 				// allowed methods to receive GET,POST etc..
if(!$api->error) $this->requireAuth('testAuthApi'); 	// Activating Auth mode.

// Auth privileges
if(!$api->error && $api->params[0]=='checkauth') {
	if(!$this->isAuth() || isset($_GET['API_KEY']) || strlen($this->getHeader('X-CLOUDFRAMEWORK-ID')))
		if(!$this->authToken('check')) 
			$api->setError($this->getLog(),401);
}

// Mandatory variables for POST method
if($api->method =='POST' )
	if($api->params[0]=='auth')
		$api->checkMandatoryFormParam(array('id','user','password','clientfingerprint'));
	else if(strlen($api->params[0]))
		$api->setError('POST only admits: test/auth');


// if the methods are supported
if(!$api->error) {
	// Adding Return data
		$api->setReturnData(array('method'=>$api->method)); 
		$api->setReturnData(array('auth'=>$this->isAuth())); 
		
		switch ($api->method) {
		case 'GET':
			switch ($api->params[0]) {
				case 'checkauth':
					if($this->isAuth())
						$api->addReturnData(array('userData'=>$this->getAuthUserData()));
					break;					

				case 'source':
					echo file_get_contents(__FILE__);
					die();
					break;					
				
				default:
					if(strlen($api->params[0])) $this->setError('unknown call');
					break;
			}
			break;
		case 'POST':
			switch ($api->params[0]) {
				case 'auth':
					$this->setAuth(false);
					if(!strlen($api->formParams['user']) || !strlen($api->formParams['password']) || !strlen($api->formParams['id'])) {
						$api->setError('User not found. id,user and password can no be empty.',404);
					} elseif($this->authToken('generate',array($api->formParams['id'],$api->formParams['clientfingerprint']))) {
						$api->addReturnData(array('userAuthData'=>$this->getAuthUserData()));
					} else {
						$api->setError($this->getLog(),401);
					}
					break;					
				default:
					if(strlen($api->params[0])) $api->setError('unknown call');
					break;
			}
			break;	
	}
	
}
$api->setReturnFormat('JSON'); 				// Method to return the data
