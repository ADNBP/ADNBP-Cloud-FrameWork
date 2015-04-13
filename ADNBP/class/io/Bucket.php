<?php
require_once 'google/appengine/api/cloud_storage/CloudStorageTools.php';
use google\appengine\api\cloud_storage\CloudStorageTools;

// CloudSQL Class v10
if (!defined ("_bucket_CLASS_") ) {
    define ("_bucket_CLASS_", TRUE);
    
    class Bucket {
		
    	var $bucket = '';
		var $error = false;
		var $errorMsg = array();
		var $max = array();
		var $uploadedFiles = array();
		var $uploaded = false;
		
   		function Bucket($bucket='') {
   			global $adnbp;
   			if(strlen($bucket)) $this->bucket = $bucket;
			else $this->bucket = CloudStorageTools::getDefaultGoogleStorageBucketName();
			
			$this->vars['upload_max_filesize'] = ini_get('upload_max_filesize');
			$this->vars['max_file_uploads'] = ini_get('max_file_uploads');
			$this->vars['file_uploads'] = ini_get('file_uploads');
			$this->vars['default_bucket'] = $this->bucket;
			$this->vars['retUploadUrl'] = $adnbp->_url;
			
			if(count($_FILES) && count($_FILES['uploaded_files'])) {
				$this->uploadedFiles = $_FILES['uploaded_files'];
				$this->uploaded = true;
			}
			
   		}
		
		function deleteUploadFiles() {
			if(strlen($_FILES['uploaded_files']['tmp_name'])) unlink($_FILES['uploaded_files']['tmp_name']);
		}
		function manageUploadFiles($dest='',$public=true) {
			// $gs_name = $_FILES['uploaded_files']['tmp_name'];
			// move_uploaded_file($gs_name, 'gs://my_bucket/new_file.txt');
			if($this->uploaded)  {
				if(!$this->uploadedFiles['error']) {
					if(!strlen($dest)) $dest = 'gs://'.$this->bucket.'/'.$this->uploadedFiles['name'];
					try {
						if(@move_uploaded_file($this->uploadedFiles['tmp_name'],$dest) === false) {
							$this->setError(error_get_last());
						} else {
							$this->uploadedFiles['movedTo'] = $dest;
							if($public)
								$this->uploadedFiles['publicUrl'] = $this->getPublicUrl($dest);
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
			if(strlen($this->bucket)) {
				if(strpos($file,'gs://')!==0 ) $file = 'gs://'.$this->bucket.'/';
				$ret =  CloudStorageTools::getPublicUrl($file,false);
			} return $ret;
		}
		function scan() {
			$ret = array();
			$tmp = scandir('gs://'.$this->bucket);
			foreach ($tmp as $key => $value) {
				$ret[$value] = array('type'=>(is_file('gs://'.$this->bucket.'/'.$value))?'file':'dir');
				if(isset($_REQUEST['__performance'])) __addPerformance('is_dir: '.'gs://'.$this->bucket.'/'.$value);
			}
			return($ret);
		}
		function fastScan() {
			return(scandir('gs://'.$this->bucket));
		}
		
		function deleAllFiles() {
			$files = $this->fastScan();
			foreach ($files as $key => $value) {
				$value = 'gs://'.$this->bucket.'/'.$value;
				$ret[$value] = 'ignored';
				if(is_file($value)) {
					$ret[$value] = 'deleting: '.unlink($value);
				}
			}
			return($ret);
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
			$options = array( 'gs_bucket_name' => $this->bucket );
			$upload_url = CloudStorageTools::createUploadUrl($this->vars['retUploadUrl'], $options);
			return($upload_url);
		}	
		
		function setError($msg) {
			$this->error = true;
			$this->errorMsg[] = $msg;
		}
	}
} 