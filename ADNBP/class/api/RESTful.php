<?php

// CloudSQL Class v10
if (!defined ("_RESTfull_CLASS_") ) {
    define ("_RESTfull_CLASS_", TRUE);
	
	class RESTful {
		
		var $formParams = array();
		var $rawData = array();
		var $params = array();
		var $error = 0;
		var $ok = 200;
		var $errorMsg = '';
		var $header = '';
		var $requestHeaders = array();
		var $method = '';
		var $contentTypeReturn = 'JSON';
		var $url ='';
		var $urlParams ='';
		var $returnData=null;
		var $auth = true;
		var $referer = null;
		
		var $service ='';
		var $serviceParam ='';
        
        function RESTful ($apiUrl='/api') {
        	
			// Rules for Cross-Domain AJAX
			// https://www.getpostman.com/collections/f6c73fa68b03add49d09
			header("Access-Control-Allow-Origin: *");
			header("Access-Control-Allow-Methods: POST,GET,PUT");
			header("Access-Control-Allow-Headers: Content-Type");
			header('Access-Control-Max-Age: 1000');
        	
			
			// $this->requestHeaders = apache_request_headers();
			$this->method = (strlen($_SERVER['REQUEST_METHOD']))?$_SERVER['REQUEST_METHOD']:'GET';
		    if($this->method=='GET' )
			  $this->formParams = &$_GET;
			else {
			  
			   if(count($_GET))  $this->formParams = (count($this->formParams))?array_merge($this->formParam,$_GET):$_GET;
			   if(count($_POST))  $this->formParams = (count($this->formParams))?array_merge($this->formParams,$_POST):$_POST;
			   if(strlen($_POST['_raw_input_'])) $this->formParams = (count($this->formParams))?array_merge($this->formParams,json_decode($_POST['_raw_input_'],true)):json_decode($_POST['_raw_input_'],true);
			   if(strlen($_GET['_raw_input_'])) $this->formParams = (count($this->formParams))?array_merge($this->formParams,json_decode($_GET['_raw_input_'],true)):json_decode($_GET['_raw_input_'],true);
			   $input = file_get_contents("php://input");
			   if(strlen($input)) {
			   		$this->formParams['_raw_input_'] = $input;
				    if(is_object(json_decode($input)))
				    	$input_array = json_decode($input,true); 
					else
				    	parse_str($input,$input_array); 
					
				    if(is_array($input_array))
				   		$this->formParams = array_merge($this->formParams, $input_array);
					else {
						$this->setError('Wrong JSON: '.$input,400);
					}
					unset($input_array);
					/*
				   if(strpos($this->requestHeaders['Content-Type'], 'json')) {
				   }
					 * 
					 */
			   }
			}

			// HTTP_REFERER
			$this->referer = $_SERVER['HTTP_REFERER'];
			if(!strlen($this->referer)) $this->referer = $_SERVER['SERVER_NAME'];
					
			// URL splits
			list($this->url,$this->urlParams) = explode('?',$_SERVER['REQUEST_URI'],2);
			
			// API URL Split
			list($foo,$url) = explode($apiUrl.'/',$this->url,2);
			
			list($this->service,$this->serviceParam) = explode('/',$url,2);
			$this->service = strtolower($this->service);
			$this->params =  explode('/',$this->serviceParam);
			
        }

		function setAuth($val,$msg='') {
			if(!$val) {
				$this->setError($msg,401);
			}
		}

		function generateAuthToken($id,$value,$clientfingerprint=null) {
			global $adnbp;
			
			if(empty($id) || empty($value)) {
				$this->setError('invalid id and token in setAuthToken function',503);
			} elseif($adnbp->getConf('CLOUDFRAMEWORK-ID-'.$id) === null) {
				$api->setError('Missing conf-var CLOUDFRAMEWORK-ID-'.$id);
			} else {
				$dataToken['fingerprint'] = $adnbp->getRequestFingerPrint();
				$dataToken['hash_fingerprint'] = sha1(json_encode($dataToken['fingerprint']));
				$dataToken['clientfingerprint'] = $clientfingerprint;
				$token = sha1(json_encode($dataToken));
				$dataToken['token'] = $token;
				unset($_SESSION['X-CLOUDFRAMEWORK-INFOTOKEN-'.$id]);
				$_SESSION['X-CLOUDFRAMEWORK-INFOTOKEN-'.$id] = $dataToken;
				return($token);
			}
			return null;
		}
		
		function getAuthToken() {
			$id = $this->getInputHeader('X-CLOUDFRAMEWORK-ID');
			$token = $this->getInputHeader('X-CLOUDFRAMEWORK-TOKEN');
			if(!strlen($id) || !strlen($token) || !isset($_SESSION['X-CLOUDFRAMEWORK-INFOTOKEN-'.$id])) {
				$this->setError("getAuthToken() Error. Missing right values: $id,$token",503);
			} else {
				if($_SESSION['X-CLOUDFRAMEWORK-INFOTOKEN-'.$id]['token'] != $token) {
					$this->setError('Token is not correct');
				} else {
					return($_SESSION['X-CLOUDFRAMEWORK-INFOTOKEN-'.$id]);
				}
			}
			return null;
		}
		
		function checkAuth($type) {
			global $adnbp;
			switch ($type) {
				case 'CLOUDFRAMEWORK':
				    // Every request has a Request Fingerprint
					$_hasFingerPrint = sha1(json_encode($adnbp->getRequestFingerPrint()));
					
					if(!strlen($this->getInputHeader('X-CLOUDFRAMEWORK-ID'))) 
						$this->setAuth(false,'Missing X-CLOUDFRAMEWORK-ID');
					elseif($adnbp->getConf('CLOUDFRAMEWORK-ID-'.$this->getInputHeader('X-CLOUDFRAMEWORK-ID')) ===null)
						$this->setAuth(false,'Missing conf-var CLOUDFRAMEWORK-ID-'.$this->getInputHeader('X-CLOUDFRAMEWORK-ID'));
					elseif(!strlen($this->getInputHeader('X-CLOUDFRAMEWORK-TOKEN'))) 
						$this->setAuth(false,'Missing X-CLOUDFRAMEWORK-TOKEN');
					else {
						if(!isset($_SESSION['X-CLOUDFRAMEWORK-INFOTOKEN-'.$this->getInputHeader('X-CLOUDFRAMEWORK-ID')]))
							$this->setAuth(false,"Token '".$this->getInputHeader('X-CLOUDFRAMEWORK-ID')."' ID not created or has expired!.Get a new token.");
						elseif($_SESSION['X-CLOUDFRAMEWORK-INFOTOKEN-'.$this->getInputHeader('X-CLOUDFRAMEWORK-ID')]['token'] != $this->getInputHeader('X-CLOUDFRAMEWORK-TOKEN')) {
							// Delete Token
							unset($_SESSION['X-CLOUDFRAMEWORK-INFOTOKEN-'.$this->getInputHeader('X-CLOUDFRAMEWORK-ID')]);
							$this->setAuth(false,"Token '".$this->getInputHeader('X-CLOUDFRAMEWORK-TOKEN')."' does not match.");
						} elseif($_hasFingerPrint != $_SESSION['X-CLOUDFRAMEWORK-INFOTOKEN-'.$this->getInputHeader('X-CLOUDFRAMEWORK-ID')]['hash_fingerprint']) {
							unset($_SESSION['X-CLOUDFRAMEWORK-INFOTOKEN-'.$this->getInputHeader('X-CLOUDFRAMEWORK-ID')]);
							$this->setAuth(false,"Fingerprint doesn't match. Security violation. This call will generate security protocol to evaluate an attack.");
						} else return true;
					}
					return false;
					break;
				case 'HTTP_REFERER':
					$referer = 	$this->referer;
					if(!strlen($referer)) {
						$this->setAuth(false,"HTTP_REFERER unknown. Pass a HTTP_REFERER form-var to evaluate");
					} else {
						$key = $this->formParams['API_KEY'];
						if(!strlen($key)) 
							$this->setAuth(false,"API_KEY form-var is missing");
						elseif($adnbp->getConf('API_KEY-'.$key) ===null && is_array($adnbp->getConf('API_KEY-'.$key)))
							$this->setAuth(false,'Missing array conf-var API_KEY-'.$key.' with he valid domains');
						else {
							foreach ($adnbp->getConf('API_KEY-'.$key) as $key => $content) {
								if($content=="*" || strpos($referer,$content)!==false) {
									return(true);
								}
							}
							$this->setAuth(false,"HTTP_REFERER '$referer' does not match with valid domains");
						}
					}
					return(false);		
					break;
				default:
					$this->setAuth(false,"Method $type no supported");
					
					return(false);		
					break;
			}
			return null;
		}
		

			
		function checkMethod($methods,$msg='') {
		    if (strpos(strtoupper($methods), $this->method)===false) {
		    	$this->error = 405;
				$this->errorMsg = ($msg=='')?'Method '.$this->method.' is not supported':$msg;
		    }
		    return($this->error === 0);	
		}
		
		function checkMandatoryFormParam($key,$msg='') {
			if(!isset($this->formParams[$key])) {
				$this->error = 400;
				$this->errorMsg = ($msg=='')?'form-param missing':$msg;
			}
		    return($this->error === 0);	
		}	
		
		function checkMandatoryParam($pos,$msg='') {
			if(!isset($this->params[$pos]) || !strlen($this->params[$pos])) {
				$this->error = 400;
				$this->errorMsg = ($msg=='')?'Method '.$this->method.' is not supported':$msg;
			}
		    return($this->error === 0);	
		}	

		function setError($value,$key=400) {
			$this->error = $key;
			$this->errorMsg = $value;
		}

		function addHeader($key,$value) {
			$this->header[$key] = $value;
		}
	
		function setReturnFormat($method) {
			switch ($method) {
				case 'JSON':
				case 'TEXT':
				case 'HTML':
					$this->contentTypeReturn = $method;
					break;
				default:
					$this->contentTypeReturn = 'JSON';
					break;
			}
		}
		function sendHeaders() {
			$header = $this->getHeader();
			if(strlen($header)) header($header);
			switch ($this->contentTypeReturn) {
				case 'JSON':
					header("Content-type: application/json");
					
					break;
				case 'TEXT':
					header("Content-type: text/plain");
					
					break;
				case 'HTML':
					header("Content-type: text/html");
					
					break;
				default:
					header("Content-type: text/html");
					break;
			}
			
			
		}
		
		function setReturnResponse($value) {
			 $this->returnData = $value; 
		}

		function setReturnData($value) {
			 $this->returnData['data'] = $value; 
		}
		function addReturnData($value) {
			 if($this->returnData===null) $this->setReturnData($value);
			 else {
			 	if(!is_array($value)) $value = array($value);
			 	if(!is_array($this->returnData['data'])) $this->returnData['data'] = array($this->returnData['data']);
			 	$this->returnData['data'] = array_merge( $this->returnData['data'],$value);
			 }
		}
		
		function getReturnCode() { return(($this->error)?$this->error:$this->ok); }
		function getHeader() {
			 switch ($this->getReturnCode()) {
	            case 201:
	                $ret = ("HTTP/1.0 201 Created");
	                break;
	            case 204:
	                $ret = ("HTTP/1.0 204 No Content");
	                break;
	            default:
					$ret = ("HTTP/1.0 200 OK");
	                break;
	            case 405:
	                $ret = ("HTTP/1.0 405 Method Not Allowed");
	                break;
	            case 400:
	                $ret = ("HTTP/1.0 400 Bad Request");
	                break;  
	            case 401:
	                $ret = ("HTTP/1.0 401 Unauthorized");
	                break;  
	            case 404:
	                $ret = ("HTTP/1.0 404 Not Found");
	                break;
	            case 503:
	                $ret = ("HTTP/1.0 503 Service Unavailable");
	                break;
	            default:
	                if($this->error) $ret = ("HTTP/1.0 ".$this->error );
					else $ret = ("HTTP/1.0 200 OK");
	                break;
	    	}
			return($ret);
		}	

		function getInputHeader($str) {
			$str = strtoupper($str);
			$str = str_replace('-', '_', $str);
			return ((isset($_SERVER['HTTP_' . $str])) ? $_SERVER['HTTP_' . $str] : '');
		}
	

    } // Class
}
?>