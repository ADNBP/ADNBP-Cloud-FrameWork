<?php

// You have to look in GoogleAppEngineLauncher where is the API Url if you want to work in local
// The credentials has been created in the https://console.developers.google.com/project/apps~{dataset-id}/apiui/credential 

$google_api_config = array(
  'namespace' => 'adnbp',
  'application-id' => $this->getConf("GoogleCloudProjectName"),
  'service-account-name' => $this->getConf("ServiceAccountEmailAddress"),
  'private-key' => is_file($this->getConf("GoogleCloudPrivateKey"))?file_get_contents($this->getConf("GoogleCloudPrivateKey")):'',
  'dataset-id' => $this->getConf("GoogleCloudProjectId")
);

if(!strlen($google_api_config['private-key'])) die('private-key missing');
if(!strlen($google_api_config['dataset-id'])) die('dataset-id missing');
if(!strlen($google_api_config['service-account-name'])) die('service-account-name missing');
if(true) {

    $this->loadClass("Google/Client");
    $this->loadClass("io/gds/Gateway");
    $this->loadClass("io/gds/Schema");
    $this->loadClass("io/gds/Entity");
    $this->loadClass("io/gds/Store");
    $this->loadClass("io/gds/Mapper");
    
    
    $obj_client = GDS\Gateway::createGoogleClient($this->getConf("GoogleCloudProjectId"), $this->getConf("ServiceAccountEmailAddress"), $this->getConf("GoogleCloudPrivateKey"));
    $obj_gateway = new GDS\Gateway($obj_client,$this->getConf("GoogleCloudProjectId"),'adnbp' );
    
    
    $obj_schema = (new GDS\Schema('Logs'))
       ->addInteger('DirectoryOrganization_Id')
       ->addString('App')
       ->addString('Cat')
       ->addString('Subcat')
       ->addString('Title')
       ->addDatetime('DateTime')
       ->addString('Action')
       ->addString('Info',false)
       ->addString('IP')
       ->addString('FingerPrint')
              ;
   $obj_log_store = new GDS\Store($obj_gateway, $obj_schema);
    
    $obj_log = new GDS\Entity();
    $obj_log->DirectoryOrganization_Id = 1;
    $obj_log->App = $this->url['host'];
    $obj_log->Cat = 'TESTING';
    $obj_log->Subcat = $this->url['url'];
    $obj_log->Title = 'Manual insert for test';
    $obj_log->DateTime = new DateTime();
    $obj_log->Action = 'insert';
    $obj_log->Info = 'Testing data store';
    $obj_log->IP = $this->_ip;
    $obj_log->FingerPrint = json_encode($this->getRequestFingerPrint());
            
        // Write it to Datastore
    $obj_log_store->upsert($obj_log);
    _print( $obj_log_store->fetchAll());
    
    /*
    $obj_store->fetchOne();     // Gets the first book
    $obj_store->fetchAll();     // Gets all books
    $obj_store->fetchPage(10);  // Gets the first 10 books
    */
    
    _printe('end');


}


$this->loadClass("io/DatastoreService");

function create_entity() {
  $entity = new Google_Service_Datastore_Entity();
  $entity->setKey(createKeyForTestItem());
  $string_prop = new Google_Service_Datastore_Property();
  $string_prop->setStringValue("test field string value");
  $property_map = array();
  $property_map["testfield"] = $string_prop;
  $entity->setProperties($property_map);
  return $entity;
}
function createKeyForTestItem() {
  $path = new Google_Service_Datastore_KeyPathElement();
  $path->setKind("testkind");
  $path->setName("testkeyname");
  $key = DatastoreService::getInstance()->createKey();
  $key->setPath(array($path));
  return $key;
}
function create_test_request() {
  $entity = create_entity();
  $mutation = new Google_Service_Datastore_Mutation();
  $mutation->setUpsert(array($entity));
  $req = new Google_Service_Datastore_CommitRequest();
  $req->setMode('NON_TRANSACTIONAL');
  $req->setMutation($mutation);
  return $req;
}
if(strlen($google_api_config['private-key'])) {
        
            
        
    $output =  "Connected!\n\n";
    
    DatastoreService::setInstance(new DatastoreService($google_api_config));
    try {
        // test the config and connectivity by creating a test entity, building
        // a commit request for that entity, and creating/updating it in the datastore
        
        $req = create_test_request();
        DatastoreService::getInstance()->commit($req);
    }
    catch (Google_Exception $ex) {
     $output = "There was an issue -- check the logs: ".$ex->getMessage();
     //syslog(LOG_WARNING, 'Commit to Cloud Datastore exception: ' . $ex->getMessage());
     return;
    }

}

_printe($output);
$output .= file_get_contents(__FILE__);

?>