<?php
require_once('config.php');

$startdate = isset($_REQUEST['startdate'])? $_REQUEST['startdate'] : 0;

$query = "SELECT count(*) as cnt FROM tbl_ss_qualify tblqfss "
        . "LEFT JOIN tblqualify tblqf ON tblqfss.Guid_qualify = tblqf.Guid_qualify "
        . "WHERE "
        . "tblqf.account_number <> 'NULL' AND YEARWEEK(tblqf.Date_created)=YEARWEEK(:datecreated) "
        . "AND tblqfss.qualified = 'Yes' "
        . "GROUP BY tblqf.account_number ORDER BY cnt DESC LIMIT 1";

$result = $db->query($query, array("datecreated"=>$startdate));

foreach($result as $row){
    $data[] = array(
                'topqualifiedcount' => $row['cnt']
            );
}
echo json_encode($data);
?>