<?php
use \google\appengine\api\app_identity\AppIdentityService;
use \google\appengine\api\cloud_storage\CloudStorageTools;

if(!strlen($_GET['path'])) {
    
    $msg = "Default storage at: ".CloudStorageTools::getDefaultGoogleStorageBucketName()."\n";
     
    if(!strlen($this->getConf("GoogleCloudProjectId")))  {
        $this->setConf("GoogleCloudProjectId",AppIdentityService::getApplicationId());
    }
    
    $_defaultBucket=CloudStorageTools::getDefaultGoogleStorageBucketName();
    if(!strlen($_defaultBucket)) $_defaultBucket = $_SERVER[DEFAULT_VERSION_HOSTNAME];
    
    $_bucket = 'gs://'.$_defaultBucket.'/';
    
    if(strlen($_defaultBucket)) {
        
        if(file_put_contents($_bucket.'hello.txt', 'Hello') === false) {
            $msg .=  "ERROR writing  in $_bucket Bucket hello.txt";
        } else {
            $msg .=  "OK writing in Default Bucket hello.txt with content: <a href='".CloudStorageTools::getPublicUrl($_bucket.'hello.txt',false)."'>".file_get_contents($_bucket.'hello.txt').'</a>';  
        }
    } else {
            $msg .= "\nERROR Google Cloud Storage not activated";
    }
        
    
    
    if(strlen($this->getConf("GoogleCloudStoreBucket"))) {
        
        $_bucket = 'gs://'.$this->getConf("GoogleCloudStoreBucket").'/';
        
        if(file_put_contents($_bucket.'hello.txt', 'Hello') === false) {
            $msg .=  "\nERROR writing in ".$_bucket." Bucket hello.txt";
        } else {
            $msg .=  "\nOK writing in in ".$_bucket."  Bucket hello.txt with content:  <a href='".CloudStorageTools::getPublicUrl($_bucket.'hello.txt',false)."'>".file_get_contents($_bucket.'hello.txt').'</a>';
        }
        
    } else {
        $msg .= "\nNO PRIVATE BUCKET CREATED in GoogleCloudStoreBucket conf var";
    }
    
    if(strlen($this->getConf("GoogleCloudStorePublicBucket"))) {
        
        $_bucket = 'gs://'.$this->getConf("GoogleCloudStorePublicBucket").'/';
        
        if(file_put_contents($_bucket.'hello.txt', 'Hello') === false) {
            $msg .=  "\nERROR writing in ".$_bucket." Bucket hello.txt";
        } else {
            $msg .=  "\nOK writing in in ".$_bucket."  Bucket hello.txt with content:  <a href='".CloudStorageTools::getPublicUrl($_bucket.'hello.txt',false)."'>".file_get_contents($_bucket.'hello.txt').'</a>';
        }
        
    } else {
        $msg .= "\nNO PUBLIC BUCKET CREATED in GoogleCloudStorePublicBucket conf var";
    }
} else {
    $path = $_GET['path'];
    if(strpos($path, 'gs://')===false) $path = 'gs://'.$path;
    
    if(!is_file($path)) $msg = $path . ' doesn\'t exist';
	elseif(strpos($path, 'config.php')) $msg='THIS FILE NEVER HAS TO BE SHOWN. THIS IS A VIOLATION OF SECURITY AND YOUR ACCESS WILL BE NOTIFIED';
    else $msg=file_get_contents($path);
     
}
$output .= file_get_contents(__FILE__);

?>