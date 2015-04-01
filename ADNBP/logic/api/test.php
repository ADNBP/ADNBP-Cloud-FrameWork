<?php
// Automatically you have $api accesible as a object of ADBP/class/api/RESTful.php
// This object ins created by ADBP/logic/api.php
$api->checkMethod('GET,POST'); // allowed methods to receive GET,POST etc..
$api->setReturnFormat('JSON'); // allowed methos¡ds to send info: JSON, TEXT, HTML

// Auth privileges
if(!$api->error && $api->params[0]=='checkauth') {
	// X-CLOUDFRAMEWORK-TOKEN auth
	if(strlen($this->getHeader('X-CLOUDFRAMEWORK-ID'))) {
		$api->setAuth($this->checkAuthToken('CLOUDFRAMEWORK'));
	} elseif(strlen($api->formParams['API_KEY'])) {
		$api->setAuth($this->checkAuthToken('HTTP_REFERER'));
	} else
		$api->setAuth(false,'require right headers or right API_KEY. Cloud FrameWord support several auth ways.');
}

// Mandatory variables for end-points.
if($this->getAPIMethod() =='POST' && $api->params[0]=='auth'){
		$api->checkMandatoryFormParam('id','missing id form-param');
		$api->checkMandatoryFormParam('user','missing user form-param');
		$api->checkMandatoryFormParam('password','missing password form-param');
		$api->checkMandatoryFormParam('clientfingerprint','missing clientfingerprint form-param');
		if(!$api->error) {
			if(!strlen($api->formParams['user']) || !strlen($api->formParams['password']) || !strlen($api->formParams['id'])) {
				$api->setError('User not found. id,user and password can no be empty.',404);
			} 
		}
}

// if the methods are supported
if(!$api->error) {
	switch ($this->getAPIMethod()) {
		case 'GET':
			$api->addReturnData('GET method'); // multi-type return data
			switch ($api->params[0]) {
				case 'checkauth':
					if(strlen($this->getHeader('X-CLOUDFRAMEWORK-ID'))) 
						$api->addReturnData(array('tokenInfo'=>$this->getAuthToken($this->getHeader('X-CLOUDFRAMEWORK-ID'),$this->getHeader('X-CLOUDFRAMEWORK-TOKEN'))));
					else if(strlen($api->formParams['API_KEY'])) {
						$api->addReturnData(array('HTTP_REFERER'=>$api->referer));
						$api->addReturnData(array('allowed-domains'=>$this->getConf('API_KEY-'.$api->formParams['API_KEY'])));
					}
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
			$api->addReturnData('POST method'); // multi-type return data
			switch ($api->params[0]) {
				case 'auth':
					$token = $this->generateAuthToken($api->formParams['id'],array('user'=>$api->formParams['user']),$api->formParams['clientfingerprint']);
					if(!$this->error) {
						$api->addReturnData(array('token'=>$token));
					}
					break;					
				default:
					if(strlen($api->params[0])) $this->setError('unknown call');
					break;
			}
			break;	
	}
	
}