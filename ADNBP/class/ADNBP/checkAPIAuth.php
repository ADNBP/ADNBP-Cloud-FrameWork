<?php
$headers = getallheaders(); 
$msgerror ='';

// Auth Process
if(!strlen($headers['X-Cloudservice-Id'])) $msgerror.="(Missing X-Cloudservice-Id Header)";
else {
	$token = $this->getConf("CloudServiceToken-".$headers['X-Cloudservice-Id']);
	if(!strlen($token)) $msgerror.="(X-Cloudservice-Id doesn't exist)";
}

if(!strlen($headers['X-Cloudservice-Date'])) $msgerror.="(Missing X-Cloudservice-Date Header)";
else {
	$date = $headers['X-Cloudservice-Date'];
	// Check control if Date is too old (more than 10 min for example.) PENDING
}

if(!strlen($headers['X-Cloudservice-Signature'])) $msgerror.="(Missing X-Cloudservice-Signature Header)";
else if(!strlen($msgerror)){
	$signature = $headers['X-Cloudservice-Signature'];
	$signaureCreate = strtoupper(sha1($headers['X-Cloudservice-Id'].$date.$token));
	if($signature != $signaureCreate) {
		if(!(strlen($headers['X-Cloudservice-Mastersignature']) 
		   && strlen($this->getConf("adminPassword"))
		   && $this->checkPassword($headers['X-Cloudservice-Mastersignature'], $this->getConf("adminPassword")))
		)
		$msgerror.="(Signature doesnt match)";
	}
}	
?>