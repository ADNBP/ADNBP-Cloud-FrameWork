<?php
require_once 'google/appengine/api/cloud_storage/CloudStorageTools.php';
use google\appengine\api\cloud_storage\CloudStorageTools;

// CloudSQL Class v10
if (!defined ("_bucket_CLASS_") ) {
    define ("_bucket_CLASS_", TRUE);
    
    class File {
		
    	var $bucket = '';
		var $error = false;
		var $errorMsg = array();
		var $max = array();
		var $uploadedFiles = array();
		var $uploaded = false;
		
   		function File($bucket='') {
   				
   			if(strlen($bucket)) $this->bucket = $bucket;
			else $this->bucket = CloudStorageTools::getDefaultGoogleStorageBucketName();
			
			$this->vars['upload_max_filesize'] = ini_get('upload_max_filesize');
			$this->vars['max_file_uploads'] = ini_get('max_file_uploads');
			$this->vars['file_uploads'] = ini_get('file_uploads');
			$this->vars['default_bucket'] = $this->bucket;

			
			if(count($_FILES) && count($_FILES['uploaded_files'])) {
				$this->uploadedFiles = $_FILES['uploaded_files'];
				$this->uploaded = true;
			}
			
   		}
		
		function manageUploadFiles() {
			// $gs_name = $_FILES['uploaded_files']['tmp_name'];
			// move_uploaded_file($gs_name, 'gs://my_bucket/new_file.txt');
			if($this->uploaded)  {
				if(!$this->uploadedFiles['error']) {
					try {
						if(@rename($this->uploadedFiles['tmp_name'],'gs://'.$this->bucket.'/'.$this->uploadedFiles['name']) === false) {
							$this->setError(error_get_last());
						} else {
							$this->uploadedFiles['renamed'] = $this->uploadedFiles['tmp_name'].' -> '.'gs://'.$this->bucket.'/'.$this->uploadedFiles['name'];
							$this->uploadedFiles['publicUrl'] = $this->getPublicUrl($this->uploadedFiles['name']);
						}
					}catch(Exception $e) {
							$this->setError($e->getMessage());
							$this->setError(error_get_last());
					}
				}
			}
		}
		
		function getPublicUrl($file) {
			$ret = 'bucket missing';
			if(strlen($this->bucket)) $ret =  CloudStorageTools::getPublicUrl('gs://'.$this->bucket.'/'.$file,false);
			return $ret;
		}
		function getFiles() {
			return(scandir('gs://'.$this->bucket));
		}

		function deleAllFiles() {
			$files = $this->getFiles();
			foreach ($files as $key => $value) {
				
				$value = 'gs://'.$this->bucket.'/'.$value;
				if(is_file($value)) {
					unlink($value);
				}
			}
		}
				
		function putContents($file, $data, $ctype = 'text/plain' ) {
			
			$options = array('gs' => array('Content-Type' => $ctype));
			$ctx = stream_context_create($options);
			
			$ret = false;
			try{
				if(@file_put_contents('gs://'.$this->bucket.'/'.$file, $data,0,$ctx) === false) {
					$this->setError(error_get_last());
				}
			} catch(Exception $e) {
					$this->setError($e->getMessage());
					$this->setError(error_get_last());
			}
		}

		function getContents($file) {
			$ret = '';
			try{
				$ret = @file_get_contents('gs://'.$this->bucket.'/'.$file);
				if($ret=== false) {
					$this->setError(error_get_last());
				}
			} catch(Exception $e) {
					$this->setError($e->getMessage());
					$this->setError(error_get_last());
			}
			return($ret);
		}
			
		function getUploadUrl() {
			global $adnbp;
			$options = array( 'gs_bucket_name' => $this->bucket );
			$upload_url = CloudStorageTools::createUploadUrl($adnbp->_url, $options);
			return($upload_url);
		}	
		
		function setError($msg) {
			$this->error = true;
			$this->errorMsg[] = $msg;
		}
	}
} 