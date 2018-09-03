<?php
require_once('config.php');

if(isset($_POST["title"]))
{
 $insertArr = array(
                'title'  => $_POST['title'],
                'start_event' => $_POST['start'],
                'end_event' => $_POST['end'],
                'salesrepid' => $_POST['salesrepId'],
                'accountid' => $_POST['accountId'],
                'comments' => $_POST['comments']
               );
 insertIntoTable($db,'tblevents',$insertArr);
}
?>