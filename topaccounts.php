<?php
require_once('config.php');

$datecreated = isset($_REQUEST['startdate'])? $_REQUEST['startdate'] : 0;

$query = "SELECT acc.Guid_account, CONCAT(acc.name) as accname, "
            . "(SELECT  count(*) FROM tblqualify tblqf "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE tblacc.Guid_account = acc.Guid_account AND YEARWEEK(tblqf.Date_created)=YEARWEEK(:datecreated) ) as registeredCnt, "
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

$result = $db->query($query,array("datecreated"=>$datecreated));

$colors = array('#00713D','#89CB46','#3065B1','#00B7D0','#7C55A5');
$i = 0;
foreach($result as $row){
    //$acc = substr(ucwords(strtolower($row['accname'])),0,10) . "...";
    $acc = wordwrap(ucwords(strtolower($row['accname'])), 40, "\n");
    $piedata[] = array('category' => $acc, 'value' => (int)$row['registeredCnt'], 'color'=> $colors[$i]);
    $i++;
}
//$piedata = array(0 => array('category'=>'subhan','value'=>10,'color'=>'red'),1 => array('category'=>'javid','value'=>40,'color'=>'blue'));
//$piedata = array(0 => array('category'=>'subhan','value'=>10,'color'=>'red'),1 => array('category'=>'javid','value'=>40,'color'=>'blue'),2 => array('category'=>'subhans','value'=>0,'color'=>'purple'));
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
