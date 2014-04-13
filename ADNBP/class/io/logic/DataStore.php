<?php

// You have to look in GoogleAppEngineLauncher where is the API Url if you want to work in local
// The credentials has been created in the https://console.developers.google.com/project/apps~{dataset-id}/apiui/credential 


$google_api_config = [
  'namespace' => 'adnbp',
  'application-id' => $this->getConf("GoogleCloudProjectName"),
  'service-account-name' => $this->getConf("ServiceAccountEmailAddress"),
  'private-key' => file_get_contents('./config/key.p12'),
  'dataset-id' => $this->getConf("GoogleCloudProjectId")
];

$this->loadClass("io/DatastoreService");

//$ds = new DatastoreService($google_api_config);


$this->loadClass("io/DatastoreService");


function create_entity() {
  $entity = new Google_Service_Datastore_Entity();
  $entity->setKey(createKeyForTestItem());
  $string_prop = new Google_Service_Datastore_Property();
  $string_prop->setStringValue("test field string value");
  $property_map = [];
  $property_map["testfield"] = $string_prop;
  $entity->setProperties($property_map);
  return $entity;
}

function createKeyForTestItem() {
  $path = new Google_Service_Datastore_KeyPathElement();
  $path->setKind("testkind");
  $path->setName("testkeyname");
  $key = new Google_Service_Datastore_Key();
  $key->setPath([$path]);
  return $key;
}

function create_test_request() {
  $entity = create_entity();

  $mutation = new Google_Service_Datastore_Mutation();
  $mutation->setUpsert([$entity]);

  $req = new Google_Service_Datastore_CommitRequest();
  $req->setMode('NON_TRANSACTIONAL');
  $req->setMutation($mutation);
  return $req;
}

function create_cf_request() {
  $entity = create_entity();

  $mutation = new Google_Service_Datastore_Mutation();
  $mutation->setUpsert([$entity]);

  $req = new Google_Service_Datastore_CommitRequest();
  $req->setMode('NON_TRANSACTIONAL');
  $req->setMutation($mutation);
  return $req;
}

$scopes = [
    "https://www.googleapis.com/auth/datastore",
    "https://www.googleapis.com/auth/userinfo.email",
  ];
  
   

$client = new Google_Client();
$client->setApplicationName($google_api_config['application-id']);
$client->setAssertionCredentials(new Google_Auth_AssertionCredentials(
  $google_api_config['service-account-name'],
  $scopes, $google_api_config['private-key']));

$service = new Google_Service_Datastore($client);
$service_dataset = $service->datasets;
$dataset_id = $google_api_config['dataset-id'];

try {
    // test the config and connectivity by creating a test entity, building
    // a commit request for that entity, and creating/updating it in the datastore
    $req = create_test_request();
    $service_dataset->commit($dataset_id, $req, []);
    
    $req = create_cf_request();
    //$service_dataset->commit($dataset_id, $req, []);
}
catch (Google_Exception $ex) {
 syslog(LOG_WARNING, 'Commit to Cloud Datastore exception: ' . $ex->getMessage());
 echo $ex->getMessage();
 echo "There was an issue -- check the logs.";
 return;
}

$output =  "Connected!";


$output .= file_get_contents(__FILE__);

?>