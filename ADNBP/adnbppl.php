<?php
// Copy this file in the upper folder of ADNBP framework folder
// v9.0 Feb 2015

// Performance Vars
$__performance['initMicrotime'] = microtime(true);
$__performance['lastMicrotime'] = $__performance['initMicrotime'];
$__performance['initMemory'] = memory_get_usage() / (1024 * 1024);
$__performance['lastMemory'] = $__performance['initMemory'];
$__performance['lastIndex']++;
$__performance['info'][] = 'File: ' . __FILE__;
$__performance['info'][] = 'Init Memory Usage: ' .number_format(round($__performance['initMemory'], 4), 4) .  'Mb';

// Performance Functions

function __showPerformance($title = '', $top = "<!--\n", $bottom = "\n-->") {
	global $__performance, $adnbp;
	
	if(isset($_GET['debug'])) {
		if(is_object($adnbp)) {
			$__performance['info'][] = 'Object ADNBP';
			$__performance['info'][] = $adnbp;
		}
		$__performance['info'][] = '$_SERVER';
		$__performance['info'][] = $_SERVER;
	}
	echo $top;
	echo $title;
	print_r($__performance);
	echo $addhtml;
	echo $bottom;
}

function __addPerformance($title, $file = '', $type = 'all') {
	global $__performance;

	$line = $__performance['lastIndex'].' [';
	if (strlen($file)) $file = " ($file)";
	
	if ($type=='all' || $type=='memory' || $_GET['__performance'] == $__performance['lastIndex']) {
		 $line .=  number_format(round(memory_get_usage() / (1024 * 1024) - $__performance['lastMemory'], 3), 3) . ' Mb';
		$__performance['lastMemory'] = memory_get_usage() / (1024 * 1024);
	}
	if ($type=='all' || $type=='time' || $_GET['__performance'] == $__performance['lastIndex']) {
		$line .= (($line=='[')?'':', ').  (round(microtime(true) - $__performance['lastMicrotime'], 3)) . ' sec';
		$__performance['lastMicrotime'] = microtime(true);
	}
	$line .= '] '.$title;
	$__performance['info'][] = $line.$file;
	$__performance['info'][] =   'tot[' . number_format(round(memory_get_usage() / (1024 * 1024), 3), 3) . ' Mb, '
	. (round(microtime(true) - $__performance['initMicrotime'], 3))
	.' secs]' ;
	
	if (isset($_GET['__performance']) && $_GET['__performance'] == $__performance['lastIndex']) {
		__showPerformance();
		exit ;
	}
	$__performance['lastIndex']++;

}

require_once ("ADNBP/class/ADNBP.php");
$adnbp = new ADNBP();
$adnbp -> run();

if (isset($_GET['__performance'])) __showPerformance();
?>