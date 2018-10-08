<?php

    // File Name: ajaxHandlerEvents.php

?>

<?php

require_once('config.php');
require_once ('functions_event.php');

/* --------------------- Event Update ------------------------- */

if(isset($_POST['modalhealthcareid']) && isset($_POST['action']) && $_POST['action'] == "eventupdate"){
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

    if($_POST['commentid']){
        $updateArrComments = array(
                            'comments' => $_POST['modalcomments'],
                            'eventid' => $_POST['modalid'],
                            'user_id' => $_POST['userid'],
                            'updated_date' => date("Y-m-d H:i:s"),
                        );
        $where = array('id' => $_POST['commentid']);
        updateTable($db, 'tblcomments', $updateArrComments, $where );
    }else{
        $addArrComments = array(
                            'comments' => $_POST['modalcomments'],
                            'eventid' => $_POST['modalid'],
                            'user_id' => $_POST['userid'],
                            'created_date' => date("Y-m-d H:i:s"),
                            'updated_date' => date("Y-m-d H:i:s"),
                        );
        insertIntoTable($db, 'tblcomments', $addArrComments);
    }
    
}

/* --------------------- Get Comment ------------------------- */

if(isset($_POST['eventid']) && isset($_POST['action']) && $_POST['action'] == "getComment"){
    $eventId = $_POST['eventid'];
    $query = "SELECT com.*, user.email, user.firstname, user.lastname, rep.first_name, rep.last_name "
            ."FROM `tblcomments` com "
            ."LEFT JOIN `tbluser` user ON com.user_id = user.Guid_user "
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