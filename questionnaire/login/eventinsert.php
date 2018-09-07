<?php
require_once('config.php');
require_once ('functions_event.php');

$startdate = $_POST['start'] . " " . date("H:m:s");
$enddate = $_POST['end'] . " " . date("H:m:s");

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
                'comments' => $_POST['comments']
               );
 insertIntoTable($db,'tblevents',$insertArr);
}
?>