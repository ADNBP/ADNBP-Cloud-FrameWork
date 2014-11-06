<?php

// You have to look in GoogleAppEngineLauncher where is the API Url if you want to work in local
// The credentials has been created in the https://console.developers.google.com/project/apps~{dataset-id}/apiui/credential 


$google_api_config = array(
  'namespace' => 'adnbp',
  'application-id' => $this->getConf("GoogleCloudProjectName"),
  'service-account-name' => $this->getConf("ServiceAccountEmailAddress"),
  'private-key' => is_file($this->_webapp.'/config/key.p12')?file_get_contents($this->_webapp.'/config/key.p12'):'',
  'dataset-id' => $this->getConf("GoogleCloudProjectId")
);

$this->loadClass("io/DatastoreService");

//$ds = new DatastoreService($google_api_config);


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

$output .= file_get_contents(__FILE__);

?>