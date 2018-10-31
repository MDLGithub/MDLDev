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
    $accIds = explode(',', $_POST['acc']);
    $colors = array('#00713D','#89CB46','#3065B1','#00B7D0','#7C55A5', '#89BD46', '#00D7D0', '#30DDB1', '#7B11A5', '#01CB46', '#89ADD6', '#222B46', '#89CCCC', '#6035B1', '#306BBB');
    $i=0;
    foreach ($accIds as $acc) {
        $query = "SELECT COUNT(*) AS count, "
        . "(SELECT acc.name FROM tblaccount acc WHERE acc.account = l.account ) as accname "
        . "FROM `tbl_mdl_status_log` l "
        . "LEFT JOIN tbluser u ON l.Guid_user = u.Guid_user "
        . "WHERE l.Guid_status ='1' AND l.account=:account AND u.marked_test='0' AND DATE(l.Date) BETWEEN DATE(:startdate)  AND DATE(:enddate) GROUP BY l.account";
        $result = $db->query($query,array("startdate"=>$datecreated, 'enddate'=>$_POST['enddate'], "account" => $acc));
        
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
    $total_submitted = 0;
    foreach ($piedata as $item) {
        $total_submitted += $item['value'];
    }

    for($i=0; $i<5;$i++) {
        $els2 = $piedata;
        foreach ($els2 as &$el) {
            $el['color'] = $colors[$i];
            $el['value'] = round(($el['value']/$total_submitted) * 100);
            $i++;
        }
        unset($el);
    }

    $data = array(  'type' => 'pie',
                    'data' => $els2
            );
    echo json_encode($data);
}

/* --------------------- Dashboard Bar Chart ------------------------- */

if(isset($_POST['action']) && $_POST['action'] == 'getBarChart'){
    $count = 1;
    $submitted = $regSalereps = array();
    $salesrepids = explode(',', $_POST['ids']);
    $sDate = $_POST['startdate'];
    $eDate = $_POST['enddate'];

    //foreach ($salesrepids as $s) {
        $q = "SELECT count(*) as submittedCnt, CONCAT(l.salesrep_fname,' ',l.salesrep_lname) as SNames "
                . "FROM tbl_mdl_status_log l "
                . "LEFT JOIN tblevents e ON DATE(e.start_event) = DATE(l.Date) AND l.Guid_salesrep = e.salesrepid "
                . "AND l.Guid_account = e.accountid WHERE DATE(e.start_event) >= :sDate AND DATE(e.start_event) < :eDate "
                . "AND l.Guid_status = 1 AND l.Guid_salesrep in (".$_POST['ids'].") GROUP BY l.Guid_salesrep order by submittedCnt desc limit 5";
        $result = $db->query($q, array('sDate'=>$sDate, 'eDate'=>$eDate));

        foreach($result as $row){
            $submitted[] = (int)$row['submittedCnt'];
            $regSalereps[] = $row['SNames'];
        }
    //}
    $data = array(
            'series' => array ([
                    'name'=> 'Submitted',
                    'data'=> $submitted,
                    'color'=> "#3a8a5f",
                    'labels'=> array('visible' => true),
                ]
            ),
            'categories' => $regSalereps 
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

    $query1 = "SELECT count(*) as cnt, evt.* FROM tblevents evt "
            . "INNER JOIN tblsalesrep sp "
            . "ON evt.salesrepid = sp.Guid_salesrep "
            . "WHERE evt.title = 'BRCA Day' AND YEARWEEK(evt.start_event)=YEARWEEK(:datecreated) "
            . "GROUP BY evt.salesrepid ORDER  BY cnt DESC LIMIT 1";
    $result1 = $db->query($query1, array("datecreated"=>$startdate));
    foreach($result1 as $row){
        $data['topbrcacount'] =  $row['cnt'];
                
    }

    $query2 = "SELECT count(*) as cnt FROM tblevents evt "
            . "INNER JOIN tblsalesrep sp "
            . "ON evt.salesrepid = sp.Guid_salesrep "
            . "WHERE evt.title = 'Health Care Fair' AND YEARWEEK(evt.start_event)=YEARWEEK(:datecreated) "
            . "GROUP BY evt.salesrepid ORDER  BY cnt DESC LIMIT 1";
    $result2 = $db->query($query2, array("datecreated"=>$startdate));
    foreach($result2 as $row){
        $data['topeventcount'] = $row['cnt'];
    }

    $query3 = "SELECT SUM(IF(l.Guid_status=28, 1, 0)) AS cnt "
        . "FROM `tbl_mdl_status_log` l "
        . "INNER JOIN tbluser u ON l.Guid_user = u.Guid_user "
        . "INNER JOIN tblevents e ON e.salesrepid = l.Guid_salesrep and e.accountid = l.Guid_account AND DATE(e.start_event) = DATE(l.Date) "
        . "WHERE l.Guid_status = 28 AND u.marked_test='0' AND YEARWEEK(l.Date) = YEARWEEK(:datecreated) GROUP BY l.Guid_salesrep ORDER BY cnt DESC LIMIT 1";
    $result3 = $db->query($query3,array("datecreated"=>$startdate));
    foreach($result3 as $row){
        $data['topregisteredcount'] =  $row['cnt'];
    }

    $query4 = "SELECT SUM(IF(l.Guid_status=29, 1, 0)) AS cnt "
        . "FROM `tbl_mdl_status_log` l "
        . "INNER JOIN tbluser u ON l.Guid_user = u.Guid_user "
        . "INNER JOIN tblevents e ON e.salesrepid = l.Guid_salesrep and e.accountid = l.Guid_account AND DATE(e.start_event) = DATE(l.Date) "
        . "WHERE l.Guid_status = 29 AND u.marked_test='0' AND YEARWEEK(l.Date) = YEARWEEK(:datecreated) GROUP BY l.Guid_salesrep ORDER BY cnt DESC LIMIT 1";
    $result4 = $db->query($query4,array("datecreated"=>$startdate));
    foreach($result4 as $row){
        $data['topqualifiedcount'] =  $row['cnt'];
    }


    $query5 = "SELECT SUM(IF(l.Guid_status=36, 1, 0)) AS cnt "
        . "FROM `tbl_mdl_status_log` l "
        . "INNER JOIN tbluser u ON l.Guid_user = u.Guid_user "
        . "INNER JOIN tblevents e ON e.salesrepid = l.Guid_salesrep and e.accountid = l.Guid_account AND DATE(e.start_event) = DATE(l.Date) "
        . "WHERE l.Guid_status = 36 AND u.marked_test='0' AND YEARWEEK(l.Date) = YEARWEEK(:datecreated) GROUP BY l.Guid_salesrep ORDER BY cnt DESC LIMIT 1";
    $result5 = $db->query($query5,array("datecreated"=>$startdate));
    foreach($result5 as $row){
        $data['topcompletedcount'] =  $row['cnt'];
    }

    $query5 = "SELECT SUM(IF(l.Guid_status=1, 1, 0)) AS cnt "
        . "FROM `tbl_mdl_status_log` l "
        . "INNER JOIN tbluser u ON l.Guid_user = u.Guid_user "
        . "INNER JOIN tblevents e ON e.salesrepid = l.Guid_salesrep and e.accountid = l.Guid_account AND DATE(e.start_event) = DATE(l.Date) "
        . "WHERE l.Guid_status = 1 AND u.marked_test='0' AND YEARWEEK(l.Date) = YEARWEEK(:datecreated) GROUP BY l.Guid_salesrep ORDER BY cnt DESC LIMIT 1";
    $result5 = $db->query($query5,array("datecreated"=>$startdate));
    foreach($result5 as $row){
        $data['topsubmittedcount'] =  $row['cnt'];
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


/* --------------------- Dashboard Table Stats ------------------------- */

if(isset($_POST['action']) && $_POST['action'] == 'tableStats'){
    $count = 1;
    $acc_ids = explode(',', $_POST['acc']);
    $registered = $completed = $qualified = $submitted = $brcaCnt = $hcfCnt = 0;
    foreach ($acc_ids as $acc) {
       
        $regQuery = "SELECT COUNT(*) AS regCount "
        . "FROM `tbl_mdl_status_log` l "
        . "LEFT JOIN tbluser u ON l.Guid_user = u.Guid_user "
        . "LEFT JOIN tblevents e ON e.accountid = l.Guid_account AND DATE(e.start_event) = DATE(l.Date) "
        . "WHERE l.Guid_status=28 AND l.account=:account AND u.marked_test='0' AND DATE(l.Date) >= :startdate AND DATE(e.start_event) < :enddate GROUP BY l.account";
        $regResult = $db->query($regQuery,array("startdate"=>$_POST['startdate'], 'enddate'=>$_POST['enddate'], "account" => $acc));
        
        $comQuery = "SELECT COUNT(*) AS comCount "
        . "FROM `tbl_mdl_status_log` l "
        . "LEFT JOIN tbluser u ON l.Guid_user = u.Guid_user "
        . "LEFT JOIN tblevents e ON e.accountid = l.Guid_account AND DATE(e.start_event) = DATE(l.Date) "
        . "WHERE l.Guid_status=36 AND l.account=:account AND u.marked_test='0' AND DATE(e.start_event) BETWEEN DATE(:startdate)  AND DATE(:enddate) GROUP BY l.account";
        $comResult = $db->query($comQuery,array("startdate"=>$_POST['startdate'], 'enddate'=>$_POST['enddate'], "account" => $acc));

        $quaQuery = "SELECT COUNT(*) AS quaCount "
        . "FROM `tbl_mdl_status_log` l "
        . "LEFT JOIN tbluser u ON l.Guid_user = u.Guid_user "
        . "LEFT JOIN tblevents e ON e.accountid = l.Guid_account AND DATE(e.start_event) = DATE(l.Date) "
        . "WHERE l.Guid_status=29 AND l.account=:account AND u.marked_test='0' AND DATE(e.start_event) BETWEEN DATE(:startdate)  AND DATE(:enddate) GROUP BY l.account";
        $quaResult = $db->query($quaQuery,array("startdate"=>$_POST['startdate'], 'enddate'=>$_POST['enddate'], "account" => $acc));

        $subQuery = "SELECT COUNT(*) AS subCount "
        . "FROM `tbl_mdl_status_log` l "
        . "LEFT JOIN tbluser u ON l.Guid_user = u.Guid_user "
        . "LEFT JOIN tblevents e ON e.accountid = l.Guid_account AND DATE(e.start_event) = DATE(l.Date) "
        . "WHERE l.Guid_status=1 AND l.account=:account AND u.marked_test='0' AND DATE(e.start_event) BETWEEN DATE(:startdate)  AND DATE(:enddate) GROUP BY l.account";
        $subResult = $db->query($subQuery,array("startdate"=>$_POST['startdate'], 'enddate'=>$_POST['enddate'], "account" => $acc));

        foreach($regResult as $reg){
            $registered += $reg['regCount'];  
        }
        foreach($comResult as $com){
            $completed += $com['comCount'];  
        }
        foreach($quaResult as $qua){
            $qualified += $qua['quaCount'];  
        }
        foreach($subResult as $sub){
            $submitted += $sub['subCount'];  
        } 
    }
    if(isset($_POST['salesreps']) && $_POST['salesreps'] != '' || $_POST['salesreps']!=null):
        $brcaQuery = "SELECT COUNT(*) as brcaCount FROM tblevents WHERE title='BRCA DAY' AND salesrepid =:salesreps AND DATE(start_event) between DATE(:startdate) AND DATE(:enddate) GROUP BY title";
        $brcaResult = $db->query($brcaQuery,array("startdate"=>$_POST['startdate'], 'enddate'=>$_POST['enddate'], 'salesreps'=>$_POST['salesreps']));
        foreach($brcaResult as $row){
            $brcaCnt += $row['brcaCount'];  
        }
        $hcfQuery = "SELECT COUNT(*) as hcfCount FROM tblevents WHERE title<>'BRCA DAY' AND salesrepid =:salesreps AND DATE(start_event) between DATE(:startdate) AND DATE(:enddate) GROUP BY title";
        $hcfResult = $db->query($hcfQuery,array("startdate"=>$_POST['startdate'], 'enddate'=>$_POST['enddate'], 'salesreps'=>$_POST['salesreps']));
        foreach($hcfResult as $row){
            $hcfCnt += $row['hcfCount'];  
        }
    endif;
    
    echo json_encode(array(
        'reg' => $registered,
        'com' => $completed,
        'qua' => $qualified,
        'sub' => $submitted,
        'brca'=> $brcaCnt,
        'hcf' => $hcfCnt
    ));
}


/* Get Dynamic Consultant List */
if(isset($_GET['action']) && $_GET['srepids'] != 0 && $_GET['action'] == 'getconsultant'){
    $ids = $_GET['srepids'];
    //print_r($ids);
    $q = "SELECT t.Guid_salesrep, CONCAT(t.first_name,' ',t.last_name) as sNames FROM tblsalesrep t WHERE t.Guid_salesrep IN ($ids)  ";
    $result = $db->query($q);
    foreach($result as $row){
        $names[] = $row['sNames'];
        $sIds[] = $row['Guid_salesrep'];
    }

    
    echo json_encode(array('names' => $names, 'ids' => $sIds));
}


/* Summary Stats */
if(isset($_GET['_']) && isset($_GET['start'])){
    
    $result = getSummaryEvents($db);

    foreach($result as $row)
    {
     $data[] = array(
          'evtCnt'   => $row['evtCnt'],
          'start'   => $row['start_event'],
          'registeredCnt' => $row['registeredCnt'],
          'qualifiedCnt' => $row['qualifiedCnt'],
          'completedCnt' => $row['completedCnt'],
          'submittedCnt'   => $row['submittedCnt'],  
          );
    }

    echo json_encode($data);

}


if(isset($_POST['action']) && $_POST['action'] = 'genconValues'){
    $query = "SELECT e.salesrepid, concat(l.salesrep_fname, ' ' ,l.salesrep_lname) as snames
            FROM tblevents e 
            left join tbl_mdl_status_log l on l.Guid_salesrep = e.salesrepid
            WHERE DATE(e.start_event) between '".$_POST['startdate']."' and '".$_POST['enddate']."' 
            group by e.salesrepid ";
    $result = $db->query($query);
    echo json_encode($result);
    /*foreach ($result as $row) {
        $data[] = $row['']
    }*/

}