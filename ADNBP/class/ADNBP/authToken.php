<?php
// we receive: $command and $data
switch ($command) {
	case 'check':
		
		if(strlen($this->getHeader('X-CLOUDFRAMEWORK-ID'))) $type ='CLOUDFRAMEWORK';
		elseif(strlen($_REQUEST['API_KEY'])) $type = 'API_KEY';
		else {
			$this->setAuth(false);
			$this->addLog( 'we spect a X-CLOUDFRAMEWORK-ID or API_KEY form-param. User session has been destroyed.');	
			return false;		
		}
		switch ($type) {
				case 'CLOUDFRAMEWORK':
				    // Every request has a Request Fingerprint
					$_hasFingerPrint = sha1(json_encode($this->getRequestFingerPrint()));
					
					if($this->getConf('CLOUDFRAMEWORK-ID-'.$this->getInputHeader('X-CLOUDFRAMEWORK-ID')) ===null) {
						$this->addLog('Missing conf-var CLOUDFRAMEWORK-ID-'.$this->getInputHeader('X-CLOUDFRAMEWORK-ID'));
					} elseif(!strlen($this->getInputHeader('X-CLOUDFRAMEWORK-TOKEN'))) {
						$this->addLog('Missing X-CLOUDFRAMEWORK-TOKEN');
					} elseif(!$this->isAuth()) {
							$this->addLog( "Token '".$this->getInputHeader('X-CLOUDFRAMEWORK-ID')."' ID not created or has expired!.Get a new token.");	
					} elseif(!strlen($this->getAuthUserData('token')) || $this->getAuthUserData('token') != $this->getInputHeader('X-CLOUDFRAMEWORK-TOKEN')) {
							$this->addLog("Token '".$this->getInputHeader('X-CLOUDFRAMEWORK-TOKEN')."' does not match.");
							$this->setAuth(false);
					} elseif($this->getAuthUserData('tokenData')->hash_fingerprint != $_hasFingerPrint) {
							$this->addLog("Fingerprint doesn't match. Security violation. This call will generate security protocol to evaluate an attack.");
							$this->setAuth(false);
					} else {
						return(true);
					}
					return false;
					break;
					
				case 'API_KEY':
					$referer = 	$this->_referer;
					if(!strlen($referer)) {
						$this->addLog("HTTP_REFERER unknown. Pass a HTTP_REFERER form-var to evaluate");
					} else {
						$key = $_REQUEST['API_KEY'];
						if(!strlen($key)) {
							$this->addLog("API_KEY form-var is missing");
						} elseif($this->getConf('API_KEY-'.$key) === null || !is_array($this->getConf('API_KEY-'.$key))) {
							$this->addLog('Missing array conf-var API_KEY-'.$key.' with  valid domains');
						} else {
							if($this->isAuth()) {
								if($this->getAuthUserData('API_KEY') == $key) 
									return true;
								$this->addLog("$key does not match. User session has been destroyed.");
								$this->setAuth(false);
							} else  foreach ($this->getConf('API_KEY-'.$key) as $index => $content) {
								if($content=="*" || strpos($referer,$content)!==false) {
									$this->setAuthUserData('API_KEY',$key);
									
									$dataToken = array("HTTP_REFERER"=>$referer,"data"=>$this->getConf('API_KEY-'.$key));
									$dataToken = json_encode($dataToken);
									$this->setAuthUserData('API_KEY_DATA',json_decode($dataToken));
									return(true);
								}
								$this->addLog("HTTP_REFERER '$referer' does not match with valid domains.");
							}
							return(false);
						}
					}
					return(false);		
					break;
				default:
					$this->addLog("Method $type no supported");
					return(false);		
					break;
			}
		return null;
		break;
		case 'generate':
			$this->setAuth(false);
			$id = $data[0];
			$clientfingerprint = (isset($data[1]))?$data[1]:null;

			if(empty($id)) {
				$this->addLog('invalid id and token in setAuthToken function',503);
			} elseif($this->getConf('CLOUDFRAMEWORK-ID-'.$id) === null) {
				$this->addLog('Missing conf-var CLOUDFRAMEWORK-ID-'.$id);
			} else {
				$dataToken['fingerprint'] = $this->getRequestFingerPrint();
				$dataToken['hash_fingerprint'] = sha1(json_encode($dataToken['fingerprint']));
				$dataToken['clientfingerprint'] = $clientfingerprint;
				$dataToken['id'] = $id;
				$dataToken['created'] = date('Y-m-d h:i:s');
				$token = sha1(json_encode($dataToken));
				$dataToken['token'] = $token;
				$dataToken =json_encode($dataToken);
				
				$this->setAuthUserData('token',$token);
				$this->setAuthUserData('tokenData',json_decode($dataToken));
			}
			
			return($this->isAuth());
		break;
}
return false;