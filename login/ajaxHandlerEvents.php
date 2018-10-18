<?php

    // File Name: ajaxHandlerEvents.php

?>

<?php

require_once('config.php');
require_once ('functions_event.php');
require_once ('functions.php');

/* --------------------- Save Event ------------------------- */

if(isset($_POST["action"]) && $_POST["action"] == 'eventinsert')
{

 if(isset($_POST['full_name'])){
    $healthCare = array(
        'name' => $_POST['full_name'],
        'street1' => $_POST['street1'],
        'street2' => $_POST['street2'],
        'city' => $_POST['city'],
        'state' => $_POST['state'],
        'zip' => $_POST['zip'],
    );
    $insresult = insertIntoTable($db,'tblhealthcare',$healthCare);
 } 
 $healthId = 0;
 if(isset($insresult['insertID'])){
     $healthId = $insresult['insertID'];
 }

$insertArr = array(
                'title'  => $_POST['title'],
                'start_event' => $_POST['start'],
                'end_event' => $_POST['end'],
                'salesrepid' => $_POST['salesrepId'],
                'accountid' => $_POST['accountId'],
                'healthcareid' => $healthId,
               );
$insresult2 = insertIntoTable($db,'tblevents',$insertArr);
    if( isset($insresult2['insertID']) && $_POST['comments'] != '' ){
        $insertarrComment = array(
                            'comments' => $_POST['comments'],
                            'user_id' => $_POST['userid'],
                            'eventid' => $insresult2['insertID'],
                            'created_date' => date("Y-m-d H:m:s"),
                            'updated_date' => date("Y-m-d H:m:s"),
                        );
        insertIntoTable($db, 'tblcomments', $insertarrComment);
    }
}

/* --------------------- Event Update ------------------------- */

if(isset($_POST['modalhealthcareid']) && isset($_POST['action']) && $_POST['action'] == "healthEventupdate"){
    
    $healthCare = array(
         'name' => $_POST['full_name'],
         'street1' => $_POST['street1'],
         'street2' => $_POST['street2'],
         'city' => $_POST['city'],
         'state' => $_POST['state'],
         'zip' => $_POST['zip'],
     );
     $where = array('Guid_healthcare' => $_POST['modalhealthcareid']);
    
     updateTable($db,'tblhealthcare',$healthCare,$where);
    $startdate = $_POST['modalstart'];
    $enddate = $_POST['modalend'];
    $updateArr = array(
                'title'  => $_POST['modaltitle'],
                'start_event' => $startdate,
                'end_event' => $enddate,
                'salesrepid' => $_POST['modalsalesrepId'],
                'accountid' => $_POST['modalaccountId'],  
               );
    $where = array('id' => $_POST['modalid']);
    
    updateTable($db,'tblevents',$updateArr,$where);
     if($_POST['modalcomments'] != ""):
         if($_POST['commentid'] && $_POST['commentid'] !=""){
            $updateArrComments = array(
                                'comments' => $_POST['modalcomments'],
                                'eventid' => $_POST['modalid'],
                                'user_id' => $_POST['userid'],
                                'updated_date' => $_POST['updated_date'],
                            );
            $where = array('id' => $_POST['commentid']);
            updateTable($db, 'tblcomments', $updateArrComments, $where );
        }else{
            $addArrComments = array(
                                'comments' => $_POST['modalcomments'],
                                'eventid' => $_POST['modalid'],
                                'user_id' => $_POST['userid'],
                                'created_date' => $_POST['updated_date'],
                                'updated_date' => $_POST['updated_date'],
                            );
            insertIntoTable($db, 'tblcomments', $addArrComments);
        }
    endif;
}

if(isset($_POST["modalid"]) && isset($_POST['action']) && $_POST['action'] == "eventupdate")
{

    $startdate = $_POST['modalstart'];
    $enddate = $_POST['modalend'];
    $updateArr = array(
                'title'  => $_POST['modaltitle'],
                'start_event' => $startdate,
                'end_event' => $enddate,
                'salesrepid' => $_POST['modalsalesrepId'],
                'accountid' => $_POST['modalaccountId'],  
               );
    $where = array('id' => $_POST['modalid']);
    
    updateTable($db,'tblevents',$updateArr,$where);
    
    /* ------- Update Comment ------- */
    if($_POST['modalcomments'] != ""):
        if($_POST['commentid'] && $_POST['commentid'] != ""){
            $updateArrComments = array(
                                'comments' => $_POST['modalcomments'],
                                'eventid' => $_POST['modalid'],
                                'user_id' => $_POST['userid'],
                                'updated_date' => $_POST['updated_date'],
                            );
            $where = array('id' => $_POST['commentid']);
            updateTable($db, 'tblcomments', $updateArrComments, $where );
        }else{
            $addArrComments = array(
                                'comments' => $_POST['modalcomments'],
                                'eventid' => $_POST['modalid'],
                                'user_id' => $_POST['userid'],
                                'created_date' => $_POST['updated_date'],
                                'updated_date' => $_POST['updated_date'],
                            );
            insertIntoTable($db, 'tblcomments', $addArrComments);
        }
    endif;  
}

/* --------------------- Get Comment ------------------------- */

if(isset($_POST['eventid']) && isset($_POST['action']) && $_POST['action'] == "getComment"){
    $eventId = $_POST['eventid'];

    $query = "SELECT  rep.first_name as repfname, rep.last_name as replname, admin.first_name as adminfname, admin.last_name as adminlname, com.* "
            ."FROM `tblcomments` com "
            ."LEFT JOIN `tbladmins` admin ON com.user_id = admin.Guid_user "
            ."LEFT JOIN `tblsalesrep` rep ON com.user_id = rep.Guid_user "
            ."WHERE com.eventid = ".$eventId." ORDER BY `id` DESC";
    $result = $db->query($query);
    
    echo json_encode($result);
}

/* --------------------- Delete Comment ------------------------- */

if(isset($_POST['commentid']) && isset($_POST['action']) && $_POST['action'] == "commentDelete"){
    $commentid = $_POST['commentid'];
    $query = "DELETE FROM `tblcomments` WHERE id=".$commentid;
    $result = $db->query($query);
    echo json_encode($result);
}


/* --------------------- Event Stats ------------------------- */

if(isset($_POST['account']) && isset($_POST['action']) && $_POST['action'] == "getStates"){

    $reg = getAccountStatusCount($db, $_POST['account'], $_POST['regitered'], '2018-10-03');
    $qua = getAccountStatusCount($db, $_POST['account'], $_POST['qualified'], '2018-10-03');
    $com = getAccountStatusCount($db, $_POST['account'], $_POST['completed'], '2018-10-03');
    $sub = getAccountStatusCount($db, $_POST['account'], $_POST['submitted'], '2018-10-03');
    $result = array("reg"=>$reg, "qua"=>$qua, "com"=>$com, "sub"=>$sub);
    echo json_encode($result);
}


/* --------------------- Render Piechart Data ------------------------- */

if( isset($_POST['action']) && $_POST['action'] == 'piechart' ){

    $datecreated = isset($_POST['startdate'])? $_POST['startdate'] : 0;
    
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
                            . "AND tblqfss.qualified IN ('Yes','No','Unknown')) as completedCnt, "
                    . "(SELECT count(*) FROM tblpatient tblpat "
                            //."LEFT JOIN tblevents tblevt ON tblevt.title = 'BRCA DAY' "
                            . "WHERE tblpat.specimen_collected = 'Yes' "
                            . "AND YEARWEEK(tblpat.Date_created) = YEARWEEK(:datecreated) ) as submittedCnt "
                . "FROM tblaccount acc "
                . "GROUP BY acc.Guid_account  ORDER BY registeredCnt DESC LIMIT 5";

    $result = $db->query($query,array("datecreated"=>$datecreated));

    $colors = array('#00713D','#89CB46','#3065B1','#00B7D0','#7C55A5');
    $i = 0;
    foreach($result as $row){
        $acc = wordwrap(ucwords(strtolower($row['accname'])), 40, "\n");
        $piedata[] = array('category' => $acc, 'value' => (int)$row['registeredCnt'], 'color'=> $colors[$i]);
        $i++;
    }
    $data = array(  'type' => 'pie',
                    'data' => $piedata
            );
    echo json_encode($data);
}


/* --------------------- BRCA Days Member Account  ------------------------- */

if(isset($_POST['action']) && $_POST['action'] == 'mebrcacount'){

    $userId = isset($_POST['userid'])? $_POST['userid'] : 0;
    $startdate = isset($_POST['startdate'])? $_POST['startdate'] : 0;

    $query = "SELECT count(*) as cnt FROM tblevents evt "
            . "INNER JOIN tblsalesrep sp "
            . "ON evt.salesrepid = sp.Guid_salesrep "
            . "WHERE evt.title = 'BRCA Day' AND sp.Guid_user =:userid "
            . "AND YEARWEEK(evt.start_event)=YEARWEEK(:datecreated)";

    $result = $db->query($query, array("userid"=>$userId,"datecreated"=>$startdate));

    foreach($result as $row){
        $data[] = array(
                    'mebrcacount' => $row['cnt']
                );
    }
    echo json_encode($data);
}

/* --------------------- BRCA Days Top Account  ------------------------- */

if(isset($_POST['action']) && $_POST['action'] == 'topbrcacount'){
    $startdate = isset($_POST['startdate'])? $_POST['startdate'] : 0;

    $query = "SELECT count(*) as cnt, evt.* FROM tblevents evt "
            . "INNER JOIN tblsalesrep sp "
            . "ON evt.salesrepid = sp.Guid_salesrep "
            . "WHERE evt.title = 'BRCA Day' AND YEARWEEK(evt.start_event)=YEARWEEK(:datecreated) "
            . "GROUP BY evt.salesrepid ORDER  BY cnt DESC LIMIT 1";

    $result = $db->query($query, array("datecreated"=>$startdate));

    foreach($result as $row){
        $data[] = array(
                    'topbrcacount' => $row['cnt']
                );
    }
    echo json_encode($data);
}

/* --------------------- Dashboard Event Count  ------------------------- */

if(isset($_POST['action']) && $_POST['action'] == 'meeventcount'){
    $userId = isset($_POST['userid'])? $_POST['userid'] : 0;
    $startdate = isset($_POST['startdate'])? $_POST['startdate'] : 0;

    $query = "SELECT count(*) as cnt FROM tblevents evt "
            . "INNER JOIN tblsalesrep sp "
            . "ON evt.salesrepid = sp.Guid_salesrep "
            . "WHERE sp.Guid_user =:userid "
            . "AND YEARWEEK(evt.start_event)=YEARWEEK(:datecreated)";

    $result = $db->query($query, array("userid"=>$userId,"datecreated"=>$startdate));

    foreach($result as $row){
        $data[] = array(
                    'meeventcount' => $row['cnt']
                );
    }
    echo json_encode($data);
}

/* --------------------- Account Setup Popup  ------------------------- */

if(isset($_POST['action']) && $_POST['action'] == "getAccountSetup"){
    $account_id = $_POST['id'];
    $options = "";
    $accounts = $db->selectAll('tblaccount', ' ORDER BY `account` ASC');
    $accountInfo = "";
    $i=0;
    foreach ($accounts as $k=>$v){
        $selected = ( isset($account_id) && $account_id == $v['Guid_account'] ) ? " selected='selected'" : "";
        $i++;
        $options .='<option '. $selected .' data-guid="'. $v['Guid_account'] .'" value="'. $v['account'] .'">'. $v['account']." - ".ucwords(strtolower($v['name'])).'</option>';
    }

    $data = array(
                    'options' => $options
                );

    echo json_encode($data);
}

if(isset($_POST['action']) && $_POST['action'] == "getLogo")
{
    $id = $_POST['account_id']; 
    $query = "SELECT logo FROM tblaccount WHERE Guid_account =:id ";
    $result = $db->query($query, array("id"=>$id));
    echo json_encode($result);
}