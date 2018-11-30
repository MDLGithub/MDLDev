<?php
require_once('config.php');
require_once ('functions_event.php');

if(isset($_POST["id"]))
{
    deleteById($db,'tblevents',$_POST['id']);
}

?>
