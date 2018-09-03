<?php
require_once('config.php');

if(isset($_POST["modalid"]))
{
    $updateArr = array(
                'title'  => $_POST['modaltitle'],
                'start_event' => $_POST['modalstart'],
                'end_event' => $_POST['modalend'],
                'salesrepid' => $_POST['modalsalesrepId'],
                'accountid' => $_POST['modalaccountId'],
                'comments' => $_POST['modalcomments']              
               );
    $where = array('id' => $_POST['modalid']);
    
    updateTable($db,'tblevents',$updateArr,$where);
}

?>
