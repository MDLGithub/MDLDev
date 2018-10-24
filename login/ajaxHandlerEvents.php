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

    $reg = getAccountStatusCount($db, $_POST['account'], $_POST['regitered'],$_POST['selectedDate']);
    $qua = getAccountStatusCount($db, $_POST['account'], $_POST['qualified'],$_POST['selectedDate']);
    $com = getAccountStatusCount($db, $_POST['account'], $_POST['completed'],$_POST['selectedDate']);
    $sub = getAccountStatusCount($db, $_POST['account'], $_POST['submitted'],$_POST['selectedDate']);
    $result = array("reg"=>$reg, "qua"=>$qua, "com"=>$com, "sub"=>$sub);
    echo json_encode($result);
}

/* --------------------- Render Piechart Data ------------------------- */

if( isset($_POST['action']) && $_POST['action'] == 'piechart' ){

    $datecreated = isset($_POST['startdate'])? $_POST['startdate'] : 0;
    $piedata = [];
    
    /*$query = "SELECT acc.Guid_account, CONCAT(acc.name) as accname, "
                    . "(SELECT count(*) FROM tblqualify tblqf "
                            . "INNER JOIN tblaccount tblacc ON tblqf.account_number = tblacc.account "
                            . "INNER JOIN tblaccountrep tblaccrep ON tblacc.Guid_account = tblaccrep.Guid_account "
                            . "INNER JOIN tbl_mdl_status_log tbllog ON tblacc.account = tbllog.account "
                            . "INNER JOIN tblsalesrep tblsrep ON tblsrep.Guid_salesrep = tblaccrep.Guid_salesrep "
                            . "WHERE tblacc.Guid_account = acc.Guid_account AND tbllog.Guid_status = '1' "
                            . "AND YEARWEEK(tblqf.Date_created) = YEARWEEK(:datecreated) ) as submittedCnt "
                . "FROM tblaccount acc "
                . "GROUP BY acc.Guid_account DESC LIMIT 5 ";*/
    $colors = array('#00713D','#89CB46','#3065B1','#00B7D0','#7C55A5');
    $i=0;
    foreach ($_POST['acc'] as $acc) {
        //echo $i."<br>";
        $query = "SELECT COUNT(*) AS count, "
        . "(SELECT acc.name FROM tblaccount acc WHERE acc.account = l.account ) as accname "
        . "FROM `tbl_mdl_status_log` l "
        . "LEFT JOIN tbluser u ON l.Guid_user = u.Guid_user "
        . "WHERE l.Guid_status ='1' AND l.account=:account AND u.marked_test='0' AND DATE(l.Date) BETWEEN DATE(:startdate)  AND DATE(:enddate) GROUP BY l.account DESC LIMIT 5";
        $result = $db->query($query,array("startdate"=>$datecreated, 'enddate'=>$_POST['enddate'], "account" => $acc));
        //print_r($result);
        foreach($result as $row){
            if($row['accname'] != null){
                $acc = wordwrap(ucwords(strtolower($row['accname'])), 40, "\n");
                $piedata[] = array('category' => $acc, 'value' => (int)$row['count']);
            }
        }
    }

    function method1($a,$b) 
    {
        return ($a["value"] <= $b["value"]) ? 1 : -1;
    }
    usort($piedata, "method1");
    
    $array = array_slice($piedata, 0, 5);
    for($i=0; $i<5;$i++) {
        $els2 = $array;
        foreach ($els2 as &$el) {
            $el['color'] = $colors[$i];
            $i++;
        }
        unset($el);
    }
    $data = array(  'type' => 'pie',
                    'data' => $els2
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




if(isset($_POST['action']) && $_POST['action'] == 'getBarChart'){
    $count = 1;
    $registered = $salereps = $completed = $qualified = $submitted = $regSalereps = [];
    foreach ($_POST['ids'] as $id) {
       
        $query = "select CONCAT(t.salesrep_fname, ' ', t.salesrep_lname) as names,
                   (select count(*) from tbl_mdl_status_log t1 where t1.Guid_status = '28' and t1.Guid_salesrep = t.Guid_salesrep and DATE(Date) BETWEEN DATE(:startdate)  AND DATE(:enddate)) as 'registeredCnt',
                   (select count(*) from tbl_mdl_status_log t1 where t1.Guid_status = '29' and t1.Guid_salesrep = t.Guid_salesrep and DATE(Date) BETWEEN DATE(:startdate)  AND DATE(:enddate)) as 'qualifiedCnt',
                   (select count(*) from tbl_mdl_status_log t1 where t1.Guid_status = '36' and t1.Guid_salesrep = t.Guid_salesrep and DATE(Date) BETWEEN DATE(:startdate)  AND DATE(:enddate)) as 'completedCnt',
                   (select count(*) from tbl_mdl_status_log t1 where t1.Guid_status = '1' and t1.Guid_salesrep = t.Guid_salesrep and DATE(Date) BETWEEN DATE(:startdate)  AND DATE(:enddate)) as 'submittedCnt'
                   
            from tbl_mdl_status_log t
            LEFT JOIN tbluser u ON t.Guid_user = u.Guid_user
            INNER JOIN tblevents e ON t.Guid_salesrep = e.salesrepid 
            WHERE e.salesrepid =:salesrepid and u.marked_test = '0' GROUP By Guid_salesrep ORDER BY 'registeredCnt' LIMIT 5";

        $result = $db->query($query, array("salesrepid"=>$id, 'startdate'=>$_POST['startdate'], 'enddate'=>$_POST['enddate']));

        foreach($result as $row){
            $registered[] = (int)$row['registeredCnt'];
            $qualified[] = (int)$row['qualifiedCnt'];
            $completed[] = (int)$row['completedCnt'];
            $submitted[] = (int)$row['submittedCnt'];
            $salereps[] = $row['names'];
        }
    } 
    //print_r($registered);
    $data = array( 
            'series' => array ( [
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
}