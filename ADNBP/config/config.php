<?php

// ADNBP Methodology  - May 2014
// Keep here variables with URLs o Passwords or other critical information
// Try to keep safe this file and don't share with anyone
// Use $this->setConf("<var>",<value>) for all config vars.

// CloudServiceUrl. Default is: http://cloud.adnbp.com/api. Uncomment to point local server
// $this->setConf("CloudServiceUrl","/api");

// $this->setConf("GoogleMapsAPI",true);
// $this->setConf("GooglePublicAPICredential","AIzaSyARDfk6bgUxrCZbg2n68--f0LL6k8b_mjg"); 


$this->setConf("portalHTMLTop",'CloudFrameWorkTop.php');
$this->setConf("portalHTMLBottom",'CloudFrameWorkBottom.php');
$this->setConf("portalTitle",'ADNBP Cloud FrameWork '.date("Y"));
$this->setConf("portalDescription",'Cloud Framwork to develop Cloud Solutions.');
$this->setConf("portalNavColor",'navbar-inverse');

// VERSION
$this->setConf("CloudFrameWorkVersion",$this->_version);

// AUTH API KEYs
$this->setConf("API_KEY_APIKEYTEST",array('organization'=>'Test Organization','allowed_domains'=>array('localhost*')));

// Google Single Sign on: https://console.developers.google.com/project/apps~bloombees-web-v1/apiui/credential
// $this->setConf("GooglePublicAPICredential","{Write here your Key}"); 
// $this->setConf("GoogleServerAPICredential","{Write here your Key}"); 

// API HEADER AUTH FOR TESTING
$this->setConf('CLOUDFRAMEWORK-ID-test',array('data'=>'for test'));
$this->setConf('API_KEY-test',array('test.com'));

?>