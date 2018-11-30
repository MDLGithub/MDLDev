<?php
require_once('config.php');
require_once ('functions_event.php');

//$startdate = $_POST['start'] . " " . date("H:m:s");
//$enddate = $_POST['end'] . " " . date("H:m:s");
$startdate = $_POST['start'];
$enddate = $_POST['end'];

if(isset($_POST["title"]))
{
 if(isset($_POST['full_name'])){
    $healthCare = array(
        'name' => $_POST['full_name'],
        'street1' => $_POST['street1'],
        'street2' => $_POST['street2'],
        'city' => $_POST['city'],
        'state' => $_POST['state'],
        'zip' => $_POST['zip'],
    );
    $insresult = insertIntoTable($db,'tblhealthcare',$healthCare);
 } 
 $healthId = 0;
 if(isset($insresult['insertID'])){
     $healthId = $insresult['insertID'];
 }

$insertArr = array(
                'title'  => $_POST['title'],
                'start_event' => $startdate,
                'end_event' => $enddate,
                'salesrepid' => $_POST['salesrepId'],
                'accountid' => $_POST['accountId'],
                'healthcareid' => $healthId,
               );
$insresult2 = insertIntoTable($db,'tblevents',$insertArr);
    if( isset($insresult2['insertID']) ){
        $insertarrComment = array(
                            'comments' => $_POST['comments'],
                            'user_id' => $_POST['userid'],
                            'eventid' => $insresult2['insertID'],
                            'created_date' => date("Y-m-d H:m:s"),
                            'updated_date' => date("Y-m-d H:m:s"),
                        );
        insertIntoTable($db, 'tblcomments', $insertarrComment);
    }
}
die;
?>