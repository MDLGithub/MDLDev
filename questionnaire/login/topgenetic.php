<?php
require_once('config.php');

$query = "SELECT sp.Guid_salesrep, CONCAT(sp.first_name, ' ', sp.last_name) as salerepname, "
            . "(SELECT  count(*) FROM tblqualify tblqf "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE tblsrep.Guid_salesrep = sp.Guid_salesrep) as registeredCnt, "
                . "(SELECT count(*) FROM tbl_ss_qualify tblqfss "
                        . "LEFT JOIN tblqualify tblqf ON tblqfss.Guid_qualify = tblqf.Guid_qualify "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE tblsrep.Guid_salesrep = sp.Guid_salesrep "
                        . "AND tblqfss.qualified = 'Yes') as qualifiedCnt, "
                . "(SELECT count(*) FROM tbl_ss_qualify tblqfss "
                        . "LEFT JOIN tblqualify tblqf ON tblqfss.Guid_qualify = tblqf.Guid_qualify "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE tblsrep.Guid_salesrep = sp.Guid_salesrep "
                        . "AND tblqfss.qualified IN ('Yes','No','Unknown')) as completedCnt "
            . "FROM tblsalesrep sp "
            . "GROUP BY sp.Guid_salesrep  ORDER BY registeredCnt DESC LIMIT 5";

$result = $db->query($query);

foreach($result as $row){
    
    $registered[] = (int)$row['registeredCnt'];
    $qualified[] = (int)$row['qualifiedCnt'];
    $completed[] = (int)$row['completedCnt'];
    $salereps[] = $row['salerepname'];
}
$data = array( 'series' => array ( [
                                'name'=> "Registered",
                                'data'=> $registered,
                                'color'=> "#f3ac32"
                           ],[
                                'name'=> "Qualified",
                                'data'=> $qualified,
                                'color'=> "#b8b8b8"
                            ],[
                                'name'=> "Completed",
                                'data'=> $completed,
                                'color'=> "#bb6e36"
                            ]
                           ),
                'categories' => $salereps 
    );
echo json_encode($data);
?>