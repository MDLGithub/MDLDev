<?php
function getEventSchedule($db,$salesRepId,$accountId){
    if($salesRepId != 0 || $accountId != 0){
        $query = "SELECT evt.id, evt.healthcareid, evt.salesrepid, evt.accountid, evt.title, evt.start_event, evt.end_event,"
                . " CONCAT(salerep.first_name, ' ', salerep.last_name) as salesrep, salerep.color, "
                . " acct.logo, acct.account, acct.name, evt.comments,"
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
                        . "AND tblqfss.qualified IN ('Yes','No','Unknown')) as completedCnt "
                . "  FROM tblevents evt "
                . "LEFT JOIN tblsalesrep salerep ON evt.salesrepid = salerep.Guid_salesrep "
                . "LEFT JOIN tblaccount acct ON evt.accountid = acct.Guid_account "
                . "LEFT JOIN tblhealthcare hltcare ON evt.healthcareid = hltcare.Guid_healthcare "
                . "WHERE evt.salesrepid =:salesrepid "
                . "AND evt.accountid =:accountid "
                . "ORDER BY evt.id";
        
        $result = $db->query($query, array("salesrepid"=>$salesRepId,"accountid"=>$accountId));
    }
    else{
        $query = "SELECT evt.id, evt.healthcareid, evt.salesrepid, evt.accountid, evt.title, evt.start_event, evt.end_event,"
                . " CONCAT(salerep.first_name, ' ', salerep.last_name) as salesrep, salerep.color, "
                . " acct.logo, acct.account, acct.name, evt.comments,"
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
                        . "AND tblqfss.qualified IN ('Yes','No','Unknown')) as completedCnt "
                . "  FROM tblevents evt "
                . "LEFT JOIN tblsalesrep salerep ON evt.salesrepid = salerep.Guid_salesrep "
                . "LEFT JOIN tblaccount acct ON evt.accountid = acct.Guid_account "
                . "LEFT JOIN tblhealthcare hltcare ON evt.healthcareid = hltcare.Guid_healthcare "
                . "ORDER BY evt.id";
        $result = $db->query($query);
    }
    
    return $result;
}

function getSummaryEvents($db){
    $query = "SELECT count(*) as evtCnt, DATE(evt.start_event) as start_event, "
            . "(SELECT  count(*) FROM tblqualify tblqf "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE DATE(tblqf.Date_created) = evt.start_event) as registeredCnt, "
                . "(SELECT count(*) FROM tbl_ss_qualify tblqfss "
                        . "LEFT JOIN tblqualify tblqf ON tblqfss.Guid_qualify = tblqf.Guid_qualify "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE DATE(tblqf.Date_created) = evt.start_event "
                        . "AND tblqfss.qualified = 'Yes') as qualifiedCnt, "
                . "(SELECT count(*) FROM tbl_ss_qualify tblqfss "
                        . "LEFT JOIN tblqualify tblqf ON tblqfss.Guid_qualify = tblqf.Guid_qualify "
                        . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                        . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                        . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                        . "WHERE DATE(tblqf.Date_created) = evt.start_event "
                        . "AND tblqfss.qualified IN ('Yes','No','Unknown')) as completedCnt "
            . "FROM tblevents evt "
            . "GROUP BY DATE(evt.start_event)";
    $result = $db->query($query);
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

?>