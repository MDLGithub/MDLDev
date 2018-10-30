<?php
require_once('config.php');

$startdate = isset($_REQUEST['startdate'])? $_REQUEST['startdate'] : 0;

$query = "SELECT count(*) as cnt FROM tblevents evt "
        . "INNER JOIN tblsalesrep sp "
        . "ON evt.salesrepid = sp.Guid_salesrep "
        . "WHERE YEARWEEK(evt.start_event)=YEARWEEK(:datecreated) "
        . "GROUP BY evt.salesrepid ORDER  BY cnt DESC LIMIT 1";

$result = $db->query($query, array("datecreated"=>$startdate));

foreach($result as $row){
    $data[] = array(
                'topeventcount' => $row['cnt']
            );
}
echo json_encode($data);
?>