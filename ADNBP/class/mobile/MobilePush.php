<?php

class MobilePush {
	
	/* ANDROID GCM CONFIGURATION*/
	private $authentication_key = 'GCM_authentication_key';     				 // GCM application authentication key
	private $gcm_destination_url = 'https://android.googleapis.com/gcm/send';    // GCM destination URL 
	
	/* APNS CONFIGURATION  (SANDBOX)*/
	private $apns_cert_passphrase = 'nivel2';					     // APNS certicate passphrase
	private $apns_cert_path = '/var/www/testPush/aps_development.pem'; 		 				 // APNS certicate path
	private $apns_destination_url = 'tls://gateway.sandbox.push.apple.com:2195'; // APNS destination URL (Sandbox - development)
	
	/* APNS CONFIGURATION  (PRODUCTION)*/
	/*private $apns_cert_passphrase = 'nivel2';					     // APNS certicate passphrase
	private $apns_cert_path = '/var/www/testPush/aps_production.pem'; 		 				 // APNS certicate path
	private $apns_destination_url = 'tls://gateway.push.apple.com:2195'; 		 // APNS destination URL (Production)*/
	
	public $error = false;
	public $errorMessage = '';
	private $apns = array();
	private $gcm = array();
	
	
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
	 * 
	 * Send a message($str_push_mssg) to all the registration Ids (Android - GCM) in $regIds array and the device token(IOS - APNS) defined in $deviceToken
	 * 
	 * @param array $regIds
	 * @param String $deviceToken
	 * @param Integer $badge
	 * @param string $str_push_mssg 
	 */
	public function send_push($regIds,$deviceToken,$badge,$str_push_mssg){
		
		$this->_send_message_apns($deviceToken, $str_push_mssg, (int)$badge);  
		
		//if $str_push_mssg is defined as loc_key in the IOS app, you have to use _send_message_apns function
		//$this->_send_message_loc_key_apns($deviceToken, $str_push_mssg, (int)$badge);
		
		$this->_sendMessagesGCM($regIds, $str_push_mssg);
		
	}
	
	/**
	 * Send a message to the the APNS with $deviceToken destination 
	 *
	 */
	function _send_message_apns($deviceToken, $mssg, $badge){
		if(is_file($this->apns['cert'])) {
			$ctx = stream_context_create();
			stream_context_set_option($ctx, 'ssl', 'local_cert', $this->apns['cert']);
			stream_context_set_option($ctx, 'ssl', 'passphrase', $this->apns['phrase']); 
			// Open a connection to the APNS server
			$fp = stream_socket_client(
					$this->apns_destination_url, $err,
					$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
			if (!$fp)
				exit("Failed to connect: $err $errstr" . PHP_EOL);
			echo 'Connected to APNS' . PHP_EOL;
			// Create the payload body
			$body['aps'] = array(
					'alert' => array('body' => $mssg),
					'sound' => 'default',
					'badge' => $badge
			);
			// Encode the payload as JSON
			$payload = json_encode($body);
			//echo $payload;
			// Build the binary notification
			$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
			// Send it to the server
			$result = fwrite($fp, $msg, strlen($msg));
			if (!$result)
				echo 'Message not delivered' . PHP_EOL;
			else
				echo 'Message successfully delivered' . PHP_EOL;
			// Close the connection to the server
			fclose($fp);
		}
	}
	
	/**
	 * Send a LOCALIZED KEY message to the the APNS with $deviceToken destination
	 *
	 */
	function _send_message_loc_key_apns($deviceToken, $locKey, $badge){
		// Put your private key's passphrase here:
		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', $this->apns_cert_path);
		stream_context_set_option($ctx, 'ssl', 'passphrase', $this->apns_cert_passphrase);
		// Open a connection to the APNS server
		$fp = stream_socket_client(
				$this->apns_destination_url, $err,
				$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
		if (!$fp)
			exit("Failed to connect: $err $errstr" . PHP_EOL);
		//echo 'Connected to APNS' . PHP_EOL;
		// Create the payload body
		$body['aps'] = array(
				'alert' => array('loc-key' => $locKey),
				'sound' => 'default',
				'badge' => $badge
		);
		// Encode the payload as JSON
		$payload = json_encode($body);
		//echo $payload;
		// Build the binary notification
		$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
		// Send it to the server
		$result = fwrite($fp, $msg, strlen($msg));
		/*if (!$result)
			echo 'Message not delivered' . PHP_EOL;
		else
			echo 'Message successfully delivered' . PHP_EOL;*/
		// Close the connection to the server
		fclose($fp);
	}
	
	/**
	 * Send a message to Android devices 
	 *
	 */
	function _sendMessagesGCM($regId, $pushMssg){
		$ch = curl_init($this->gcm_destination_url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FAILONERROR, false);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		//var_dump($ch);
		$params = array(
				"registration_ids" => $regId, // array of registration Ids , max = 1000
				"collapse_key" => $pushMssg,
				"data" => array(
						"message" => $pushMssg
				)
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-length: '.strlen(json_encode($params)),
				'Authorization: key='.$this->authentication_key
		));
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
		//var_dump($params);
		//var_dump(json_encode($params));
		$response = curl_exec($ch);
		//var_dump($response);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//var_dump($code);
		return $response;
	}
}