<?php
    require_once('settings.php');
    require_once('config.php');

    $showMsg = doLogin($db);

    if(isUserLogin()){
	Leave('url-configuration.php');
    }

    require_once 'login.php';
?>
