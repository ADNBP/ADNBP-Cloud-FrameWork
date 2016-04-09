<?php
// Task to use in background
use google\appengine\api\taskqueue\PushTask;

class API extends RESTful
{
	function main()
	{
		$_url = str_replace('queue/', '', urldecode($this->core->system->url['url']));
		unset($this->formParams['_raw_input_']);

		$this->formParams['cloudframework_queued'] = true;
		$this->formParams['cloudframework_queued_id'] = uniqid('queue', true);
		$this->formParams['cloudframework_queued_ip'] = $this->core->system->ip;
		$this->formParams['cloudframework_queued_fingerprint'] = json_encode($this->core->system->getRequestFingerPrint(), JSON_PRETTY_PRINT);

		$headers = $this->getHeaders();

		$value['url_queued'] = $_url;
		$value['method'] = $this->method;
		$value['data_sent'] = $this->formParams;

// CALL URL and wait until the response is received
		if (isset($this->formParams['interative'])) {
			// Requires to create a complete URL
			$_url = (($_SERVER['HTTPS'] == 'off') ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . $_url;
			$value['url_queued'] = $_url;
			$value['interative'] = true;
			$value['headers'] = $this->getHeaders();
			$value['data_received'] = $this->getCloudServiceResponse($_url, $this->formParams, $this->method, $this->getHeaders());
			if ($value['data_received'] === false) $value['data_received'] = $this->core->errors->data;
			else $value['data_received'] = json_decode($value['data_received']);
		} // RUN THE TASK
		else {
			$options = array('method' => $this->method);
			foreach ($headers as $key => $value2) if (strpos($key, 'CONTENT_') === false) {
				$options['header'] .= $key . ': ' . $value2 . "\r\n";
			}
			$value['options'] = $options;
			$task = new PushTask($_url, $this->formParams, $options);
			$task_name = $task->add();
		}

		$this->addReturnData($value);
	}
}