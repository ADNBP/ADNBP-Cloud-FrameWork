<?php
		$docServicesTitle = "API";
		$docServicesSubtitle = "Write the descriptions of your API using ADNBP/logic/apiDoc.php as a template and write it in: <webapp>/logic/CloudFrameWorkService/apiDoc.php";
		
		$docServices['templates']['title']='Templates';
		$docServices['templates']['description']='Retrieve all templates available';
		$docServices['templates']['api'][0]['title'] = 'Templates Collection';		
		$docServices['templates']['api'][0]['api'] = '/templates';	
		$docServices['templates']['api'][0]['GET']='Retrieve all templates available';


		$docServices['templates']['api'][1]['title'] = 'Retreive a Template';		
		$docServices['templates']['api'][1]['api'] = '/templates/{templateName}';	
		$docServices['templates']['api'][1]['GET']='Retrieve the template defined by string';
		$docServices['templates']['api'][1][0]['paramName']='templateName';
		$docServices['templates']['api'][1][0]['paramDescription']='templateName';
		$docServices['templates']['api'][1][0]['paramDetails']='templateName';

		$docServices['checkAPIAuth']['title']='CheckApiAuth';
		$docServices['checkAPIAuth']['description']='Return the source code of a template';
		$docServices['checkVersion']['title']='checkVersion';
		$docServices['checkVersion']['description']='Return the source code of a template';
		$docServices['myIP']['title']='myIP';
		$docServices['myIP']['description']='Return the source code of a template';
		$docServices['genPassword']['title']='genPassword';
		$docServices['genPassword']['description']='Return the source code of a template';
		$docServices['fetchURL']['title']='fetchURL';
		$docServices['fetchURL']['description']='Return the source code of a template';
	
?>