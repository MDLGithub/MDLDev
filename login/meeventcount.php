<?php
require_once('config.php');

$userId = isset($_REQUEST['userid'])? $_REQUEST['userid'] : 0;
$startdate = isset($_REQUEST['startdate'])? $_REQUEST['startdate'] : 0;

$query = "SELECT count(*) as cnt FROM tblevents evt "
        . "INNER JOIN tblsalesrep sp "
        . "ON evt.salesrepid = sp.Guid_salesrep "
        . "WHERE sp.Guid_user =:userid "
        . "AND YEARWEEK(evt.start_event)=YEARWEEK(:datecreated)";

$result = $db->query($query, array("userid"=>$userId,"datecreated"=>$startdate));

foreach($result as $row){
    $data[] = array(
                'meeventcount' => $row['cnt']
            );
}
echo json_encode($data);
?>