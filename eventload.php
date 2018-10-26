<?php
require_once('config.php');
require_once ('functions_event.php');

$salesRepId = isset($_REQUEST['salerepId'])? $_REQUEST['salerepId'] : 0;
$accountId = isset($_REQUEST['accountId'])? $_REQUEST['accountId'] : 0;
$reqdashboard = isset($_REQUEST['reqdashboard'])? $_REQUEST['reqdashboard'] : 0;

$result = getEventSchedule($db,$salesRepId,$accountId,$reqdashboard);

foreach($result as $row)
{
 $data[] = array(
  'id'   => $row['id'],
  'title'   => $row['title'],
  'start'   => $row['start_event'],
  'end'   => $row['end_event'],
  'salesrep' => $row['salesrep'],
  'logo' => $row['logo'],
  'account' => $row['account'],
  'name' => $row['name'],  
  'hltname' => $row['hltname'],
  'street1' => $row['street1'],
  'street2' => $row['street2'],
  'city' => $row['city'],
  'state' => $row['state'],
  'zip' => $row['zip'],
  'healthcareid' => $row['healthcareid'], 
  'salesrepid' => $row['salesrepid'],   
  'accountid' => $row['accountid'],
  'color' => $row['color'],
  'registeredCnt' => $row['registeredCnt'],
  'qualifiedCnt' => $row['qualifiedCnt'],
  'completedCnt' => $row['completedCnt'],
  'comments' => $row['commentCnt'],
 );
}
echo json_encode($data);
?>