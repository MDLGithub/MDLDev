<?php
require_once('config.php');

$datecreated = isset($_REQUEST['startdate'])? $_REQUEST['startdate'] : 0;

$query = "SELECT sp.Guid_salesrep, CONCAT(sp.first_name, ' ', sp.last_name) as salerepname, "
            . "(SELECT  count(*) FROM tblqualify tblqf "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE tblsrep.Guid_salesrep = sp.Guid_salesrep AND YEARWEEK(tblqf.Date_created)=YEARWEEK(:datecreated1) ) as registeredCnt, "
                . "(SELECT count(*) FROM tbl_ss_qualify tblqfss "
                        . "LEFT JOIN tblqualify tblqf ON tblqfss.Guid_qualify = tblqf.Guid_qualify "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE tblsrep.Guid_salesrep = sp.Guid_salesrep "
                        . "AND tblqfss.qualified = 'Yes' AND YEARWEEK(tblqf.Date_created)=YEARWEEK(:datecreated2) ) as qualifiedCnt, "
                . "(SELECT count(*) FROM tbl_ss_qualify tblqfss "
                        . "LEFT JOIN tblqualify tblqf ON tblqfss.Guid_qualify = tblqf.Guid_qualify "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE tblsrep.Guid_salesrep = sp.Guid_salesrep "
                        . "AND tblqfss.qualified IN ('Yes','No','Unknown') AND YEARWEEK(tblqf.Date_created)=YEARWEEK(:datecreated3) ) as completedCnt, "

                . "(SELECT count(*) FROM tbl_ss_qualify tblqfss "
                        . "LEFT JOIN tblqualify tblqf ON tblqfss.Guid_qualify = tblqf.Guid_qualify "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE tblsrep.Guid_salesrep = sp.Guid_salesrep "
                        . "AND tblqfss.qualified IN ('Yes','No','Unknown') AND YEARWEEK(tblqf.Date_created)=YEARWEEK(:datecreated3) ) as submittedCnt "
            . "FROM tblsalesrep sp "
            . "GROUP BY sp.Guid_salesrep  ORDER BY registeredCnt DESC LIMIT 5";

$result = $db->query($query,array("datecreated1"=>$datecreated,"datecreated2"=>$datecreated,"datecreated3"=>$datecreated));

foreach($result as $row){
    
    $registered[] = (int)$row['registeredCnt'];
    $qualified[] = (int)$row['qualifiedCnt'];
    $completed[] = (int)$row['completedCnt'];
    $submitted[] = (int)$row['submittedCnt'];
    $salereps[] = $row['salerepname'];
}
$data = array( 'series' => array ( [
                                'name'=> "Registered",
                                'data'=> $registered,
                                'color'=> "#bce273"
                           ],[
                                'name'=> "Completed",
                                'data'=> $completed,
                                'color'=> "#263805"
                            ],[
                                'name'=> "Qualified",
                                'data'=> $qualified,
                                'color'=> "#5b870a"
                            ],[
                                'name'=> 'Submitted',
                                'data'=> $submitted,
                                'color'=> "#919191"
                            ]
                           ),
                'categories' => $salereps 
    );
echo json_encode($data);
?>