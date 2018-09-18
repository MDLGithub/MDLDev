<?php
require_once('config.php');

$salesRepId = isset($_REQUEST['salerepId'])? $_REQUEST['salerepId'] : 0;
$accountId = isset($_REQUEST['accountId'])? $_REQUEST['accountId'] : 0;

if($salesRepId != 0){
    $query = "SELECT DISTINCT(tacc.account),tacc.* FROM tblaccount tacc INNER JOIN tblaccountrep tacrep "
            . "ON tacc.Guid_account = tacrep.Guid_account "
            . " WHERE tacrep.Guid_salesrep =:salesrepid ORDER BY account";

    $result = $db->query($query, array("salesrepid"=>$salesRepId));
}else{
    $query = "SELECT DISTINCT(tacc.account),tacc.* FROM tblaccount tacc INNER JOIN tblaccountrep tacrep "
        . "ON tacc.Guid_account = tacrep.Guid_account ORDER BY account";
        
    $result = $db->query($query);
}
foreach($result as $row){
    $name = $row['account'] . ' - ' . ucwords(strtolower($row['name']));
    $data[] = array(
        'id' => $row['Guid_account'],
        'name' => $name
    );
}
echo json_encode($data);
?>