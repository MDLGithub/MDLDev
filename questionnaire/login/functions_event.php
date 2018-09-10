<?php
function getEventSchedule($db,$salesRepId,$accountId){
    if($salesRepId != 0 || $accountId != 0){
        $query = "SELECT evt.id, evt.healthcareid, evt.salesrepid, evt.accountid, evt.title, evt.start_event, evt.end_event,"
                . " CONCAT(salerep.first_name, ' ', salerep.last_name) as salesrep, salerep.color, "
                . " acct.logo, acct.account, acct.name, evt.comments,"
                . " hltcare.name as hltname, hltcare.street1, hltcare.street2, hltcare.city, hltcare.state, hltcare.zip"
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
                . " hltcare.name as hltname, hltcare.street1, hltcare.street2, hltcare.city, hltcare.state, hltcare.zip"
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
    $query = "SELECT count(*) as evtCnt, DATE(start_event) as start_event FROM tblevents GROUP BY DATE(start_event)";
    $result = $db->query($query);
    return $result;    
}

?>