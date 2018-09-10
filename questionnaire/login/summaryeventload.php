<?php
require_once('config.php');
require_once ('functions_event.php');

$result = getSummaryEvents($db);

foreach($result as $row)
{
 $data[] = array(
  'evtCnt'   => $row['evtCnt'],
  'start'   => $row['start_event'],
  );
}

echo json_encode($data);
?>