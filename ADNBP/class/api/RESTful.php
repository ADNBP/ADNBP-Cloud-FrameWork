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
		
		var $service ='';
		var $serviceParam ='';
        
        function RESTful ($apiUrl='/api') {
        	
			// $this->requestHeaders = apache_request_headers();
			$this->method = (strlen($_SERVER['REQUEST_METHOD']))?$_SERVER['REQUEST_METHOD']:'GET';
		    if($this->method=='GET' )
			  $this->formParams = &$_GET;
			else {
			   if(count($_GET))  $this->formParams = array_merge($this->formParam,$_GET);
			   if(count($_POST))  $this->formParams = array_merge($this->formParams,$_POST);
			   $input = file_get_contents("php://input");
			   if(strlen($input)) {
			   		$this->formParams['raw'] = $input;
				   	$this->formParams = array_merge($this->formParams,json_decode($input,true));
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
			$this->params =  explode('/',$this->serviceParam);
			
        }
		
		function checkAuth(&$msg) {
			include_once(dirname(__FILE__).'/RESTful/checkAuth.php');
			if(strlen($msgerror)) { $msg.=$msgerror;return(false); }
			else return(true);		
		}
		

			
		function checkMethod($methods,$msg='') {
		    if (strpos(strtoupper($methods), $this->method)===false) {
		    	$this->error = 405;
				$this->errorMsg = ($msg=='')?'Method '.$this->method.' is not supported':$msg;
		    }
		    return($this->error === 0);	
		}
		
		function checkMandatoryFormParam($key,$msg='') {
			if(!isset($this->formParams[$key]) || !strlen($this->formParams[$key])) {
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
				default:
					header("Content-type: text/html");
					break;
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

    } // Class
}
?>