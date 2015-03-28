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

		function generateAuthToken($id,$value) {
			global $adnbp;
			
			if(empty($id) || empty($value)) {
				$this->setError('invalid id and token in setAuthToken function',503);
			} elseif(!strlen($adnbp->getConf('X-CLOUDFRAMEWORK-ID-'.$id))) {
				$api->setError('Missing conf-var X-CLOUDFRAMEWORK-ID-'.$id);
			} else {
				//$dataToken['fingerprint'] = $adnbp->getFingerPrint();
				$dataToken['data'] = $value;
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
					if(!strlen($this->getInputHeader('X-CLOUDFRAMEWORK-ID'))) 
						$this->setAuth(false,'Missing X-CLOUDFRAMEWORK-ID');
					elseif(!strlen($adnbp->getConf('X-CLOUDFRAMEWORK-ID-'.$this->getInputHeader('X-CLOUDFRAMEWORK-ID'))))
						$this->setAuth(false,'Missing conf-var X-CLOUDFRAMEWORK-ID-'.$this->getInputHeader('X-CLOUDFRAMEWORK-ID'));
					elseif(!strlen($this->getInputHeader('X-CLOUDFRAMEWORK-TOKEN'))) 
						$this->setAuth(false,'Missing X-CLOUDFRAMEWORK-TOKEN');
					else {
						if(!isset($_SESSION['X-CLOUDFRAMEWORK-INFOTOKEN-'.$this->getInputHeader('X-CLOUDFRAMEWORK-ID')]))
							$this->setAuth(false,"Token '".$this->getInputHeader('X-CLOUDFRAMEWORK-ID')."' ID not created or has expired!");
						elseif($_SESSION['X-CLOUDFRAMEWORK-INFOTOKEN-'.$this->getInputHeader('X-CLOUDFRAMEWORK-ID')]['token'] != $this->getInputHeader('X-CLOUDFRAMEWORK-TOKEN')) {
							$this->setAuth(false,"Token '".$this->getInputHeader('X-CLOUDFRAMEWORK-TOKEN')."' does not match");
						} else return true;
					}
					return false;
					break;
				default:
						include_once(dirname(__FILE__).'/RESTful/checkAuth.php');
						if(strlen($msgerror)) { $msg.=$msgerror;return(false); }
						else return(true);		
					
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
		
		function setReturnData($value) { $this->returnData['data'] = array($value); }
		function addReturnData($value) {
			 if($this->returnData===null) $this->setReturnData($value);
			 else $this->returnData['data'][] = $value; 
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