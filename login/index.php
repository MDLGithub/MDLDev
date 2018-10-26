<?php 
    require_once('config.php'); 
    require_once('settings.php'); 
    
    $checkLogin = login_check($db);
    if ($checkLogin['status']) {        
        Leave(SITE_URL.'/dashboard.php');        
    } else {
        require_once 'login.php';
    }  
    
?>

