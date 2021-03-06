<?php
require_once('config.php');
require_once ('functions_event.php');

//$startdate = $_POST['modalstart'] . " " . date("H:m:s");
//$enddate = $_POST['modalend'] . " " . date("H:m:s");
$startdate = $_POST['modalstart'];
$enddate = $_POST['modalend'];


if(isset($_POST['modalhealthcareid'])){
    $healthCare = array(
         'name' => $_POST['full_name'],
         'street1' => $_POST['street1'],
         'street2' => $_POST['street2'],
         'city' => $_POST['city'],
         'state' => $_POST['state'],
         'zip' => $_POST['zip'],
     );
     $where = array('Guid_healthcare' => $_POST['modalhealthcareid']);
    
     updateTable($db,'tblhealthcare',$healthCare,$where);
}

if(isset($_POST["modalid"]))
{
    $updateArr = array(
                'title'  => $_POST['modaltitle'],
                'start_event' => $startdate,
                'end_event' => $enddate,
                'salesrepid' => $_POST['modalsalesrepId'],
                'accountid' => $_POST['modalaccountId'],  
               );
    $where = array('id' => $_POST['modalid']);
    
    updateTable($db,'tblevents',$updateArr,$where);

    $addArrComments = array(
                            'comments' => $_POST['modalcomments'],
                            'eventid' => $_POST['modalid'],
                            'user_id' => $_POST['userid'],
                            'created_date' => date("Y-m-d H:i:s"),
                            'updated_date' => date("Y-m-d H:i:s"),
                        );
    insertIntoTable($db, 'tblcomments', $addArrComments);
    
}

?>
