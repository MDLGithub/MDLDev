<?php
require_once('config.php');

$accountId = isset($_REQUEST['accountId'])? $_REQUEST['accountId'] : 0;

if($accountId != 0){
    $query = "SELECT DISTINCT(first_name), tsrep.* FROM tblsalesrep tsrep INNER JOIN tblaccountrep tacrep "
            . "ON tsrep.Guid_salesrep = tacrep.Guid_salesrep "
            . " WHERE tacrep.Guid_account =:accountid ORDER BY first_name, last_name";

    $result = $db->query($query, array("accountid"=>$accountId));
}else{
    $query = "SELECT DISTINCT(first_name), tsrep.* FROM tblsalesrep tsrep INNER JOIN tblaccountrep tacrep "
            . "ON tsrep.Guid_salesrep = tacrep.Guid_salesrep ORDER BY first_name, last_name";
        
    $result = $db->query($query);
}
foreach($result as $row){
    $name = $row['first_name'] . ' ' . $row['last_name'];
    $data[] = array(
        'id' => $row['Guid_salesrep'],
        'name' => $name
    );
}
echo json_encode($data);
?>