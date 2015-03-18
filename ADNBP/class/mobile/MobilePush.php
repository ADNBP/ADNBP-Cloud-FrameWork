<?php

class MobilePush {
	public $error = false;
	public $errorMessage = '';
	private $apns = array();
	private $gcm = array();
	public $lastGCMResult=null;
	
	
	function __construct(){
		
	}
	
	function setAPNS($phrase,$cert,$url='tls://gateway.sandbox.push.apple.com:2195') {
		if(is_file($cert)) {
			$this->apns['phrase'] = $phrase;
			$this->apns['cert'] = $cert;
			$this->apns['url'] = $url;
			return(true);
		} else {
			$this->error = true;
			$this->errorMessage .= $cert.' does not exist'."\n";
			return(false);
		}
	}

	function setGCM($key,$url='https://android.googleapis.com/gcm/sen') {
		$this->gcm['key'] = $key;
		$this->gcm['url'] = $url;
		return(true);
	}
	
	
	
	/**
	 * Send a message to the the APNS with $deviceToken destination 
	 * Based on: http://www.tagwith.com/question_138013_ssl-connect-to-apns-server-in-local-environment-stream-socket-client-failed
	 *
	 */
	function _sendAPNSMessage($type,$deviceToken, $txt, $badge){
		if($this->error) return(false);
		
		if(is_file($this->apns['cert']) && strlen($this->apns['url'])) {
			$ctx = stream_context_create();
			stream_context_set_option($ctx, 'ssl', 'local_cert', $this->apns['cert']);
			stream_context_set_option($ctx, 'ssl', 'passphrase', $this->apns['phrase']); 
			// Open a connection to the APNS server
			$fp = stream_socket_client(
					$this->apns['url'], $err,
					$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
			if (!$fp) {
				$this->error = true;
				$this->errorMessage = "Failed to connect: $err $errstr";
			} else { 
				// Create the payload body
				if($type=='msg') {
					$body['aps'] = array(
							'alert' => array('body' => $txt),
							'sound' => 'default',
							'badge' => $badge
					);
				} else {
					$body['aps'] = array(
							'alert' => array('loc-key' => $txt),
							'sound' => 'default',
							'badge' => $badge
					);					
				}
				// Encode the payload as JSON
				$payload = json_encode($body);
				//echo $payload;
				// Build the binary notification
				$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
				// Send it to the server
				$result = fwrite($fp, $msg, strlen($msg));
				if (!$result) {
					$this->error = true;
					$this->errorMessage = 'Message not delivered';
				} 
				
				// Close the connection to the server
				fclose($fp);
			}
		}

		return(!$this->error);
	}
	
	function sendAPNSMessage($deviceToken, $mssg, $badge){
		return($this->_sendAPNSMessage('msg',$deviceToken, $mssg, $badge));
	}
	function sendAPNSLocKey($deviceToken, $locKey, $badge){
		return($this->_sendAPNSMessage('locKey',$deviceToken, $mssg, $badge));
	}
	
	/**
	 * Android Messages
	 *
	 */	
	function sendGCMMessage($regIds, $pushMssg){
		if($this->error) return(false);
		
		if( strlen($this->gcm['url']) && strlen($this->gcm['key'])) {
			if(!is_array($regIds)) $regIds = array($regIds);
			$fields = array('registration_ids'  => $regIds,"collapse_key" => $pushMssg,'data'=> array( "message" => $pushMssg ));

			
			$headers  = 'Authorization: key=' . $this->gcm['key']. "\r\n";
			$headers .= 'Content-Type: application/json'. "\r\n";
			$headers .= 'Connection: close' . "\r\n";
			
			// use key 'http' even if you send the request to https://...
			$options = array(
			    'http' => array(
			        'header'=> $headers ,
			        'method'  => 'POST',
			        'content' => json_encode($fields),
			    ),
			);
			//_printe($options);
			$context  = stream_context_create($options);
			$result = @file_get_contents($this->gcm['url'], false, $context);
			if($result===false) {
				$this->error=true;
				$this->errorMessage = error_get_last();
			} else {
				$result = json_decode($result);
				$this->lastGCMResult = $result;
				if(!$result->success) {
					$this->error=true;
				} 
			}
			
			// var_dump($result);	
		}
		
		return(!$this->error);
	}	
}