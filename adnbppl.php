<?php
// Copy this file in the upper folder of ADNBP framework folder
// v1.0 Sep 2013

require_once("./ADNBP/class/ADNBP.php");
$adnbp = new ADNBP();
$adnbp->run();

if($_GET['debug']) {
    echo "<h1>Debug mode Ver2.</h1>";
    echo "<pre>Object:\n\n".print_r($adnbp,true)."</pre>";
    echo "<pre>_SERVER:\n\n".print_r($_SERVER,true)."</pre>";
}


?>