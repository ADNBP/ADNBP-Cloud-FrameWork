<?php
$api->checkMethod('GET');

$data = ['vars.for.testing'=>[
    'basic_user'=>'User to send in a basic Auth',
    'basic_password'=>'password to send in a basic Auth. Can be empty',
    'api_key'=>'api_key to require a X-CLOUDFRAMEWORK-API-KEY header',
    'server_key_id'=>'key_id to require a X-CLOUDFRAMEWORK-SECURITY header'

]];
if($api->params[0]=='test') {

    if(strlen($api->formParams['basic_user'])) {
        $data['Basic.Auth'] = ['method' => '$passed =  $this->checkBasicAuth(\''.$api->formParams['basic_user'].'\',\''.$api->formParams['basic_password'].'\')'];
        $data['Basic.Auth']['notes'] = 'you have also: (bool)$this->existBasicAuth() and (array)$this->getDataFromBasicAuth()';
        $data['Basic.Auth']['passed'] = $this->checkBasicAuth($api->formParams['basic_user'], $api->formParams['basic_password']);


        if (!$data['Basic.Auth']['passed']) $data['Basic.Auth']['message'] = "Error. Send Basic Auth with the following info: user={$api->formParams['basic_user']} and password={$api->formParams['basic_password']}";
    }

    if(strlen($api->formParams['api_key'])) {
        $data['Client.Auth'] = ['method'=>'$passed = $this->checkCloudFrameWork(3600,\''.$api->formParams["server_key_id"].'\',$secret)]'];
        $data['Client.Auth']['notes'] = 'The key can be generated using: $this->generateCloudFrameWorkSecurityString($api->formParams[\'server_key_id\'],\'\',$secret)';

        $data['Client.Auth']['passed'] = $this->checkCloudFrameWorkSecurity(3600,$api->formParams['server_key_id'],$secret);
        if (!$data['Client.Auth']['passed'])
            $data['Client.Auth']['message'] = "Error. Send X-CLOUDFRAMEWORK-SECURITY header with the following info: "
                .$this->generateCloudFrameWorkSecurityString($api->formParams["server_key_id"],'',$secret);

    }


    if(strlen($api->formParams['server_key_id'])) {
        $secret='WerErty';
        $data['Server.Auth'] = ['method'=>'$passed = $this->checkCloudFrameWorkSecurity(3600,\''.$api->formParams["server_key_id"].'\',$secret)]'];
        $data['Server.Auth']['notes'] = 'The key can be generated using: $this->generateCloudFrameWorkSecurityString($api->formParams[\'server_key_id\'],\'\',$secret)';

        $data['Server.Auth']['passed'] = $this->checkCloudFrameWorkSecurity(3600,$api->formParams['server_key_id'],$secret);
        if (!$data['Server.Auth']['passed'])
            $data['Server.Auth']['message'] = "Error. Send X-CLOUDFRAMEWORK-SECURITY header with the following info: "
                  .$this->generateCloudFrameWorkSecurityString($api->formParams["server_key_id"],'',$secret);

    }

}

$api->addReturnData($data);
