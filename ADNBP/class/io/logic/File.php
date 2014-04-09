<?php

use \google\appengine\api\app_identity\AppIdentityService;
use \google\appengine\api\cloud_storage\CloudStorageTools;

$msg = "Default storage at: ".CloudStorageTools::getDefaultGoogleStorageBucketName()."\n";
 
if(!strlen($this->getConf("GoogleCloudProjectId")))  {
    $this->setConf("GoogleCloudProjectId",AppIdentityService::getApplicationId());
}

 
$_bucket = 'gs://'.CloudStorageTools::getDefaultGoogleStorageBucketName().'/';

if(file_put_contents($_bucket.'hello.txt', 'Hello') === false) {
    $msg .=  "ERROR writing  in Default Bucket hello.txt";
} else {
    $msg .=  "OK writing in Default Bucket hello.txt with content: <a href='".CloudStorageTools::getPublicUrl($_bucket.'hello.txt',false)."'>".file_get_contents($_bucket.'hello.txt').'</a>';  
}


if(strlen($this->getConf("GoogleCloudStoreBucket"))) {
    
    $_bucket = 'gs://'.$this->getConf("GoogleCloudStoreBucket").'/';
    
    if(file_put_contents($_bucket.'hello.txt', 'Hello') === false) {
        $msg .=  "\nERROR writing in ".$_bucket." Bucket hello.txt";
    } else {
        $msg .=  "\nOK writing in in ".$_bucket."  Bucket hello.txt with content:  <a href='".CloudStorageTools::getPublicUrl($_bucket.'hello.txt',false)."'>".file_get_contents($_bucket.'hello.txt').'</a>';
    }
    
} else {
    $msg .= "NO PRIVATE BUCKET CREATED";
}

if(strlen($this->getConf("GoogleCloudStorePublicBucket"))) {
    
    $_bucket = 'gs://'.$this->getConf("GoogleCloudStorePublicBucket").'/';
    
    if(file_put_contents($_bucket.'hello.txt', 'Hello') === false) {
        $msg .=  "\nERROR writing in ".$_bucket." Bucket hello.txt";
    } else {
        $msg .=  "\nOK writing in in ".$_bucket."  Bucket hello.txt with content:  <a href='".CloudStorageTools::getPublicUrl($_bucket.'hello.txt',false)."'>".file_get_contents($_bucket.'hello.txt').'</a>';
    }
    
} else {
    $msg .= "NO PUBLIC BUCKET CREATED";
}

$output .= file_get_contents(__FILE__);

?>