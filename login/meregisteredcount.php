<?php
require_once('config.php');

$userId = isset($_REQUEST['userid'])? $_REQUEST['userid'] : 0;
$startdate = isset($_REQUEST['startdate'])? $_REQUEST['startdate'] : 0;

$query = "SELECT count(*) as cnt FROM tblqualify tblqf "
            . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
            . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
            . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
            . "WHERE tblsrep.Guid_user =:userid AND YEARWEEK(tblqf.Date_created) = YEARWEEK(:datecreated)";
$result = $db->query($query, array("userid"=>$userId,"datecreated"=>$startdate));

foreach($result as $row){
    $data[] = array(
                'meregisteredcount' => $row['cnt']
            );
}
echo json_encode($data);
?>