<?php
function getEventSchedule($db,$salesRepId,$accountId,$reqdashboard){
    if($salesRepId != 0 || $accountId != 0){
        $query = "SELECT evt.id, evt.healthcareid, evt.salesrepid, evt.accountid, evt.title, evt.start_event, evt.end_event,"
                . " CONCAT(salerep.first_name, ' ', salerep.last_name) as salesrep, salerep.color, "
                . " acct.logo, acct.account, acct.name, "
                . " hltcare.name as hltname, hltcare.street1, hltcare.street2, hltcare.city, hltcare.state, hltcare.zip, "
                . "(SELECT  count(*) FROM tblqualify tblqf "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE tblsrep.Guid_salesrep = evt.salesrepid "
                        . "AND tblacc.Guid_account = evt.accountid "
                        . "AND DATE(tblqf.Date_created) = evt.start_event) as registeredCnt, "
                . "(SELECT count(*) FROM tbl_ss_qualify tblqfss "
                        . "LEFT JOIN tblqualify tblqf ON tblqfss.Guid_qualify = tblqf.Guid_qualify "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE tblsrep.Guid_salesrep = evt.salesrepid "
                        . "AND tblacc.Guid_account = evt.accountid "
                        . "AND DATE(tblqf.Date_created) = evt.start_event "
                        . "AND tblqfss.qualified = 'Yes') as qualifiedCnt, "
                . "(SELECT count(*) FROM tbl_ss_qualify tblqfss "
                        . "LEFT JOIN tblqualify tblqf ON tblqfss.Guid_qualify = tblqf.Guid_qualify "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE tblsrep.Guid_salesrep = evt.salesrepid "
                        . "AND tblacc.Guid_account = evt.accountid "
                        . "AND DATE(tblqf.Date_created) = evt.start_event "
                        . "AND tblqfss.qualified IN ('Yes','No','Unknown')) as completedCnt, "
                . "(SELECT tblcom.comments FROM tblcomments tblcom WHERE tblcom.eventid = evt.id ORDER BY updated_date DESC LIMIT 1 ) as commentCnt "
                . "  FROM tblevents evt "
                . "LEFT JOIN tblsalesrep salerep ON evt.salesrepid = salerep.Guid_salesrep "
                . "LEFT JOIN tblaccount acct ON evt.accountid = acct.Guid_account "
                . "LEFT JOIN tblhealthcare hltcare ON evt.healthcareid = hltcare.Guid_healthcare ";

                if($salesRepId != 0 && $accountId != 0){
                    $query .= "WHERE evt.salesrepid =:salesrepid "
                            . "AND evt.accountid =:accountid ";
                }

                if($salesRepId == 0 && $accountId != 0){
                    $query .= "WHERE evt.accountid =:accountid "
                            . "OR evt.salesrepid =:salesrepid ";
                }

                if($salesRepId != 0 && $accountId == 0 && $reqdashboard == 0){
                    $query .= "WHERE evt.salesrepid =:salesrepid ";
                            //. "OR evt.accountid =:accountid ";
                }

                if($salesRepId != 0 && $accountId == 0 && $reqdashboard == 1){
                    $query .= "WHERE evt.salesrepid =:salesrepid ";
                            //. "OR evt.accountid =:accountid ";
                }
                
                
                $query .= "ORDER BY evt.id";
        if($accountId != 0)
            $result = $db->query($query, array("salesrepid"=>$salesRepId,"accountid"=>$accountId));
        elseif($accountId == 0)
            $result = $db->query($query, array("salesrepid"=>$salesRepId));
    }
    else{
        $query = "SELECT evt.id, evt.healthcareid, evt.salesrepid, evt.accountid, evt.title, evt.start_event, evt.end_event,"
                . " CONCAT(salerep.first_name, ' ', salerep.last_name) as salesrep, salerep.color, "
                . " acct.logo, acct.account, acct.name, "
                . " hltcare.name as hltname, hltcare.street1, hltcare.street2, hltcare.city, hltcare.state, hltcare.zip, "
                . "(SELECT  count(*) FROM tblqualify tblqf "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE tblsrep.Guid_salesrep = evt.salesrepid "
                        . "AND tblacc.Guid_account = evt.accountid "
                        . "AND DATE(tblqf.Date_created) = evt.start_event) as registeredCnt, "
                . "(SELECT count(*) FROM tbl_ss_qualify tblqfss "
                        . "LEFT JOIN tblqualify tblqf ON tblqfss.Guid_qualify = tblqf.Guid_qualify "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE tblsrep.Guid_salesrep = evt.salesrepid "
                        . "AND tblacc.Guid_account = evt.accountid "
                        . "AND DATE(tblqf.Date_created) = evt.start_event "
                        . "AND tblqfss.qualified = 'Yes') as qualifiedCnt, "
                . "(SELECT count(*) FROM tbl_ss_qualify tblqfss "
                        . "LEFT JOIN tblqualify tblqf ON tblqfss.Guid_qualify = tblqf.Guid_qualify "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE tblsrep.Guid_salesrep = evt.salesrepid "
                        . "AND tblacc.Guid_account = evt.accountid "
                        . "AND DATE(tblqf.Date_created) = evt.start_event "
                        . "AND tblqfss.qualified IN ('Yes','No','Unknown')) as completedCnt, "
                . "(SELECT tblcom.comments FROM tblcomments tblcom WHERE tblcom.eventid = evt.id ORDER BY updated_date DESC LIMIT 1 ) as commentCnt "
                . " FROM tblevents evt "
                . "LEFT JOIN tblsalesrep salerep ON evt.salesrepid = salerep.Guid_salesrep "
                . "LEFT JOIN tblaccount acct ON evt.accountid = acct.Guid_account "
                . "LEFT JOIN tblhealthcare hltcare ON evt.healthcareid = hltcare.Guid_healthcare "
                . "ORDER BY evt.id";
        $result = $db->query($query);
    }
    
    return $result;
}

function getSalesRepAccount($db, $Guid_salesrep){
    $q = "SELECT account FROM tbl_mdl_status_log WHERE Guid_salesrep = :salesrepid GROUP BY account ";
    $result = $db->query($q, array('salesrepid' => $Guid_salesrep));
    $account = array();
    foreach ($result as $row) {
        array_push($account, $row['account']);
    }

    return implode(',', $account);
}

function getSummaryEvents($db){
       
    $query = "SELECT date(e.start_event) as start, count(e.title) as evtCnt , GROUP_CONCAT(salesrepid) as salesrepid,GROUP_CONCAT(acc.account) as account"
            . ",(Select count(*) FROM tbl_mdl_status_log l "
            . "left join tbluser u on u.Guid_user = l.Guid_user "
            . "left join tblevents evt on date(evt.start_event) = date(l.Date) "
            . "Where date(evt.start_event) = DATE(e.start_event) and u.marked_test = '0' and l.Guid_status=28 ";
    if(isset($_GET['salerepId']))
    $query  .= "and l.Guid_salesrep = ".$_GET['salerepId']." ";
    if(isset($_GET['accountId']) && $_GET['accountId'] != 0)
    $query  .= "and l.Guid_account = ".$_GET['accountId']." ";
    $query  .= "and evt.accountid = l.Guid_account )AS registeredCnt "
              
            . ",(Select count(*) FROM tbl_mdl_status_log l "
            . "left join tbluser u on u.Guid_user = l.Guid_user "
            . "left join tblevents evt on date(evt.start_event) = date(l.Date) "
            . "Where date(evt.start_event) = DATE(e.start_event) and u.marked_test = '0' and l.Guid_status=29 ";
    if(isset($_GET['salerepId']))
    $query  .= "and l.Guid_salesrep = ".$_GET['salerepId']." ";
    if(isset($_GET['accountId']) && $_GET['accountId'] != 0)
    $query  .= "and l.Guid_account = ".$_GET['accountId']." ";
    $query  .= "and evt.accountid = l.Guid_account )AS qualifiedCnt "
            
            . ",(Select count(*) FROM tbl_mdl_status_log l "
            . "left join tbluser u on u.Guid_user = l.Guid_user "
            . "left join tblevents evt on date(evt.start_event) = date(l.Date) "
            . "Where date(evt.start_event) = DATE(e.start_event) and u.marked_test = '0' and l.Guid_status=36 ";
    if(isset($_GET['salerepId']))
    $query  .= "and l.Guid_salesrep = ".$_GET['salerepId']." ";
    if(isset($_GET['accountId']) && $_GET['accountId'] != 0)
    $query  .= "and l.Guid_account = ".$_GET['accountId']." ";
    $query  .= "and evt.accountid = l.Guid_account )AS completedCnt "
            . ",(Select count(*) FROM tbl_mdl_status_log l "
            . "left join tbluser u on u.Guid_user = l.Guid_user "
            . "left join tblevents evt on date(evt.start_event) = date(l.Date) "
            . "Where date(evt.start_event) = DATE(e.start_event) and u.marked_test = '0' and l.Guid_status=1 ";
    if(isset($_GET['salerepId']))
    $query  .= "and l.Guid_salesrep = ".$_GET['salerepId']." ";
    if(isset($_GET['accountId']) && $_GET['accountId'] != 0)
    $query  .= "and l.Guid_account = ".$_GET['accountId']." ";
    $query  .= "and evt.accountid = l.Guid_account )AS submittedCnt "

            . "FROM tblevents e left join tblaccount acc on e.accountid = acc.Guid_account WHERE ";
    if(isset($_GET['salerepId']))
    $query .= "e.salesrepid = ".$_GET['salerepId']." and ";
    if(isset($_GET['accountId']) && $_GET['accountId'] != 0)
    $query  .= "e.accountid = ".$_GET['accountId']." and ";
    $query .= "DATE(e.start_event) between DATE(:sDate) and DATE(:eDate) group by start";
    $result = $db->query($query, array('sDate' => $_GET['start'], 'eDate' => $_GET['end']));
           
    return $result;    
}

function getMeRegistered($db,$userId,$date){
    $query = "SELECT  count(*) as cnt, tblqf.* FROM tblqualify tblqf "
            . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
            . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
            . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
            . "WHERE tblsrep.Guid_user =:userid AND YEARWEEK(tblqf.Date_created) = YEARWEEK(:datecreated)";
    $result = $db->query($query, array("userid"=>$userId,"datecreated"=>$date));
    return $result;  
        
}

function getMeQualified($db,$userId,$date){
    $query = "SELECT * FROM tbl_ss_qualify tblqfss "
            . "LEFT JOIN tblqualify tblqf ON tblqfss.Guid_qualify = tblqf.Guid_qualify "
            . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
            . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
            . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
            . "WHERE tblsrep.Guid_user = 613 AND DATE(tblqf.Date_created) = '2018-07-26' "
            . "AND tblqfss.qualified = 'Yes'";
}

function getMeCompleted($db,$userId,$date){
    $query = "SELECT * FROM tbl_ss_qualify tblqfss "
            . "LEFT JOIN tblqualify tblqf ON tblqfss.Guid_qualify = tblqf.Guid_qualify "
            . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
            . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
            . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
            . "WHERE tblsrep.Guid_user = 613 AND DATE(tblqf.Date_created) = '2018-07-26' "
            . "AND tblqfss.qualified IN ('Yes','No','Unknown')";
}

function getTopRegistered($db,$userId,$date){
    $query = "SELECT count(*) as top,account_number, Date_created FROM tblqualify "
            . "WHERE account_number <> 'NULL' AND YEARWEEK(Date_created)=YEARWEEK('2018-08-10 10:43:54') "
            . "GROUP BY account_number "
            . "ORDER BY top DESC LIMIT 1"; 
}

function getTopQualified($db,$userId,$date){
    $query = "SELECT count(*) as top, tblqf.account_number, tblqf.Date_created FROM tbl_ss_qualify tblqfss "
            . "LEFT JOIN tblqualify tblqf ON tblqfss.Guid_qualify = tblqf.Guid_qualify "
            . "WHERE "
            . "tblqf.account_number <> 'NULL' AND YEARWEEK(tblqf.Date_created)=YEARWEEK('2018-08-10 10:43:54') "
            . "AND tblqfss.qualified = 'Yes' "
            . "GROUP BY tblqf.account_number ORDER BY top DESC LIMIT 1";

}

function getTopCompleted($db,$userId,$date){
    $query = "SELECT count(*) as top, tblqf.account_number, tblqf.Date_created FROM tbl_ss_qualify tblqfss "
            . "LEFT JOIN tblqualify tblqf ON tblqfss.Guid_qualify = tblqf.Guid_qualify "
            . "WHERE "
            . "tblqf.account_number <> 'NULL' AND YEARWEEK(tblqf.Date_created)=YEARWEEK('2018-08-10 10:43:54') "
            . "AND tblqfss.qualified IN ('Yes','No','Unknown') "
            . "GROUP BY tblqf.account_number ORDER BY top DESC LIMIT 1";
 
}

function getAccStatDateRange($db, $Guid_status, $eventStart=NULL, $eventEnd=NULL){     
    $q = "SELECT COUNT(*) AS `count` FROM `tbl_mdl_status_log` l "
        . "LEFT JOIN tbluser u ON l.Guid_user = u.Guid_user "
        . "WHERE l.Guid_status =:Guid_status AND u.marked_test='0' "; 
    if($eventStart && $eventEnd){
        $q .=  "AND DATE(l.Date) >=:startdate AND DATE(l.Date) <:enddate ";
    }
    
    $result = $db->row($q, array('Guid_status'=>$Guid_status,'startdate'=>$eventStart,'enddate'=>$eventEnd));    
    return $result['count'];
}

?>