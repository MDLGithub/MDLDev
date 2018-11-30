<?php
require_once('config.php');

$startdate = isset($_REQUEST['startdate'])? $_REQUEST['startdate'] : 0;

$query = "SELECT count(*) as cnt FROM tblqualify "
        . "WHERE account_number <> 'NULL' AND YEARWEEK(Date_created)=YEARWEEK(:datecreated) "
        . "GROUP BY account_number "
        . "ORDER BY cnt DESC LIMIT 1"; 
$result = $db->query($query, array("datecreated"=>$startdate));

foreach($result as $row){
    $data[] = array(
                'topregisteredcount' => $row['cnt']
            );
}
echo json_encode($data);
?>