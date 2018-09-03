<?php 
    require_once('settings.php'); 
    require_once('config.php'); 

    $showMsg = doLogin($db);

    if(isUserLogin()){
        Leave('dashboard.php');
    }
    
    require_once 'login.php';
?>

