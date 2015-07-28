<?php
// CloudSQL Class v10
if (!defined ("_CloudServiceReporting_CLASS_") ) {
    define ("_CloudServiceReporting_CLASS_", TRUE);
    
    class CloudServiceReporting {
        var $error = false;
        var $errorMsg = array();
        var $data = array();

        
        function CloudServiceReporting() {

        }
        function add($type,$info) {
            //if(is_array($info)) _printe((object)$info);
            $this->data[] = array('type'=>$type,'data'=> is_array($info)?(object)$info:json_decode($info));
            
        }
        function output() {
        	global $adnbp;
			$_tables = false;
            ob_start();
            foreach ($this->data as $key => $data) {
                $type = $data['type'];
                $data = $data['data'];
                if($type=='header') 
                    include __DIR__.'/templates/header.php';
                elseif($type=='table') {
                	$simple = false;
                    include __DIR__.'/templates/table.php';
					$_tables = true;
                }
                elseif($type=='simpleTable') {
                	$simple = true;
                    include __DIR__.'/templates/table.php';
					$_tables = true;
                }
            }
			if($_tables) include __DIR__.'/templates/tablejsbottom.php';
            return ob_get_clean();

        }

    }
} 