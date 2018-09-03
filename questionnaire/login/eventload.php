<?php
require_once('config.php');
$result = getEventSchedule($db);
foreach($result as $row)
{
 $data[] = array(
  'id'   => $row["id"],
  'title'   => $row["title"],
  'start'   => $row["start_event"],
  'end'   => $row["end_event"],
  'salesrep' => $row["salesrep"],
  'logo' => $row['logo'],
  'account' => $row['account'],
  'name' => $row['name'],
  'comments' => $row['comments']   
 );
}
echo json_encode($data);
?>