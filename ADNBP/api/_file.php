<?php
$api->checkMethod('GET,POST');
if(!$api->error) {
	$this->loadClass("io/File");
	$file = new File();
	
	switch ($api->method) {
		case 'GET':
			if(!strlen($api->params[0])) {
				$api->addReturnData($file->vars);
			} else switch ($api->params[0]) {
				case 'url':
					$api->addReturnData(array('url_to_upload'=>$file->getUploadUrl()));
					break;
				default:
					$api->setError('user api/_file/upload to get a url of uploading.');
					break;
			}
			break;
		case 'POST':
			if(!$file->uploaded) {
				$api->setError('No file uploaded');
			} else {
				$file->manageUploadFiles();
				$api->addReturnData($file->uploadedFiles);
			}
			break;
	}
}
