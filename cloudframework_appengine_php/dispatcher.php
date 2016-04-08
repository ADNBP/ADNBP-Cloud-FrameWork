<?php
// Copy this file in the upper folder of ADNBP framework folder
// v1 Apr. 2016
include_once (__DIR__ . "/Core.php"); //

$core = new Core();
$core->run();
if($core->errors->lines) {
    _printe($core->errors->data);
}
?>