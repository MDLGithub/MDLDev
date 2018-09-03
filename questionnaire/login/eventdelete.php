<?php
require_once('config.php');
if(isset($_POST["id"]))
{
    deleteById($db,'tblevents',$_POST['id']);
}

?>
