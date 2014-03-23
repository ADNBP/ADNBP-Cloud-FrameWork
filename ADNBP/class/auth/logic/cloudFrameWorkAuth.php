<?php
// It requires $this->getConf("requireAuthLogic") pointing to this logic File

if($_POST[CloudUser] == "admin" && $_POST[CloudPassword] == "admin" ){
        $this->setAuthUserData("name","User Admin");  
} else if($this->getConf("AllowOauth") && is_array($_SESSION[opauth]) && strlen($_SESSION[opauth][auth][info][name])) {
    
    $_CloudFrameWorkData[DirectoryUsersOauth_Id] = $_SESSION[opauth][auth][uid];
    $_CloudFrameWorkData[DirectoryUsersOauth_Strategy] = $_SESSION[opauth][auth][provider];
    $_CloudFrameWorkData[DirectoryUsersOauth_Domain] = $_SERVER[SERVER_NAME];
    $_CloudFrameWorkData[DirectoryUsersOauth_IP] = $_SERVER[REMOTE_ADDR];
    $_CloudFrameWorkData[DirectoryUsersOauth_FullName] = $_SESSION[opauth][auth][info][name];
    $_CloudFrameWorkData[DirectoryUsersOauth_Email] = $_SESSION[opauth][auth][info][email];
    $_CloudFrameWorkData[DirectoryUsersOauth_SerializedData] = serialize($_SESSION[opauth]);
    
    $this->loadClass("db/Mysql");
    $db = new Mysql();
    
    if($db->connect()) {
        $db->CloudFrameWork("replace",$_CloudFrameWorkData);
        $db->close();
    } 
    
    if($db->error()) {
        $output =  "<li>ERROR: ".$db->getError();
            die($output);
    }
    /*
    $this->setAuthUserData("name",$_SESSION[opauth][auth][info][name]);
        $this->setAuthUserData("opauth",$_SESSION);
        unset($_SESSION[opauth]);
        
    }   
     * 
     */    
}
?>