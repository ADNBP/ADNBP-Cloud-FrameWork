<?php

// CloudSQL Class v10
if (!defined ("_Performance_CLASS_") ) {
    define ("_Performance_CLASS_", TRUE);
	
	class Performance {
		var $data;
		function Performance() {
			// Performance Vars
			$this->data['initMicrotime'] = microtime(true);
			$this->data['lastMicrotime'] = $this->data['initMicrotime'];
			$this->data['initMemory'] = memory_get_usage() / (1024 * 1024);
			$this->data['lastMemory'] = $this->data['initMemory'];
			$this->data['lastIndex']++;
			$this->data['info'][] = 'File: ' . str_replace($_SERVER['DOCUMENT_ROOT'], '',__FILE__);
			$this->data['info'][] = 'Init Memory Usage: ' .number_format(round($this->data['initMemory'], 4), 4) .  'Mb';
		}
		
		function add() {
			// Hidding full path (security)
			$file = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);
		
			$line = $this->data['lastIndex'].' [';
			if($type=='note') $line .= $type;
			
			if (strlen($file)) $file = " ($file)";
			if ($type=='all' || $type=='memory' || $_GET['data'] == $this->data['lastIndex']) {
				 $line .=  number_format(round(memory_get_usage() / (1024 * 1024) - $this->data['lastMemory'], 3), 3) . ' Mb';
				$this->data['lastMemory'] = memory_get_usage() / (1024 * 1024);
			}
			if ($type=='all' || $type=='time' || $_GET['data'] == $this->data['lastIndex']) {
				$line .= (($line=='[')?'':', ').  (round(microtime(true) - $this->data['lastMicrotime'], 3)) . ' secs';
				$this->data['lastMicrotime'] = microtime(true);
			}
			$line .= '] '.$title;
			$this->data['info'][] = (($type != 'note')?'[' . number_format(round(memory_get_usage() / (1024 * 1024), 3), 3) . ' Mb, '
				. (round(microtime(true) - $this->data['initMicrotime'], 3))
				.' secs] / ':'').$line.$file;
			
			if(false && $type != 'note')
				$this->data['info'][] =   'tot[' . number_format(round(memory_get_usage() / (1024 * 1024), 3), 3) . ' Mb, '
				. (round(microtime(true) - $this->data['initMicrotime'], 3))
				.' secs]' ;
			
			if (isset($_GET['data']) && $_GET['data'] == $this->data['lastIndex']) {
				__sp();
				exit ;
			}
			$this->data['lastIndex']++;
			
		}
	}
}

$__p = new Performance();
	


// Performance Functions

function __sp($title = '', $top = "<!--\n", $bottom = "\n-->") {
	global $__p, $adnbp;
	
	if(isset($_GET['debug'])) {
		if(is_object($adnbp)) {
			$__p->data['info'][] = 'Object ADNBP';
			$__p->data['info'][] = $adnbp;
		}
		$__p->data['info'][] = '$_SERVER';
		$__p->data['info'][] = $_SERVER;
	}
	echo $top;
	echo $title;
	foreach ($__p->data['info'] as $key => $value) {
		echo (is_array($value))?print_r($value,true):$value."\n";
	}
	echo $addhtml;
	echo $bottom;
}

function __p($title=null, $file = null, $type = 'all') {
	 global $__p;
	 if($title === null && $file==null) return $__p->data;
	 else $__p->add($title, $file, $type); 
}
function __print($args) {
	echo "<pre>";
	for ($i = 0, $tr = count($args); $i < $tr; $i++) {
		if ($args[$i] === "exit")
			exit ;
		echo "\n<li>[$i]: ";
		if (is_array($args[$i]))
			echo print_r($args[$i], true);
		else if (is_object($args[$i]))
			echo var_dump($args[$i]);
		else if (is_bool($args[$i]))
			echo($args[$i]) ? 'true' : 'false';
		else if (is_null($args[$i]))
			echo 'NULL';
		else
			echo $args[$i];
	}
	echo "</pre>";
}
function _print() { __print(func_get_args());}
function _printe() { __print(array_merge(func_get_args(), array('exit')));}