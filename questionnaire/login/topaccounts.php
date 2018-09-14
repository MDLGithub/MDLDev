<?php
require_once('config.php');

$query = "SELECT acc.Guid_account, CONCAT(acc.name) as accname, "
            . "(SELECT  count(*) FROM tblqualify tblqf "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE tblacc.Guid_account = acc.Guid_account) as registeredCnt, "
                . "(SELECT count(*) FROM tbl_ss_qualify tblqfss "
                        . "LEFT JOIN tblqualify tblqf ON tblqfss.Guid_qualify = tblqf.Guid_qualify "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE tblacc.Guid_account = acc.Guid_account "
                        . "AND tblqfss.qualified = 'Yes') as qualifiedCnt, "
                . "(SELECT count(*) FROM tbl_ss_qualify tblqfss "
                        . "LEFT JOIN tblqualify tblqf ON tblqfss.Guid_qualify = tblqf.Guid_qualify "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE tblacc.Guid_account = acc.Guid_account "
                        . "AND tblqfss.qualified IN ('Yes','No','Unknown')) as completedCnt "
            . "FROM tblaccount acc "
            . "GROUP BY acc.Guid_account  ORDER BY registeredCnt DESC LIMIT 5";

$result = $db->query($query);

foreach($result as $row){
    $acc = substr(ucwords(strtolower($row['accname'])),0,10) . "...";
    $piedata[] = array('category' => $acc, 'value' => (int)$row['registeredCnt']);
}
$data = array(  'type' => 'pie',
                'data' => $piedata
        );
/*
$data = array(
                    'type'=> "pie",
                    'data'=> array([
                        'category'=> "Football",
                        'value'=> 35
                    ],[
                        'category'=> "Basketball",
                        'value'=> 25
                    ],[
                        'category'=> "Volleyball",
                        'value'=> 20
                    ],[
                        'category'=> "Rugby",
                        'value'=> 10
                    ],[
                        'category'=> "Tennis",
                        'value'=> 10
                    ])
            );
*/
echo json_encode($data);
?>
