<?php 
require_once('config.php');

if(isset($_POST['eventid'])){
	$eventId = $_POST['eventid'];
	$query = "SELECT com.*, user.email, user.firstname, user.lastname, rep.first_name, rep.last_name "
			."FROM `tblcomments` com "
			."LEFT JOIN `tbluser` user ON com.user_id = user.Guid_user "
			."LEFT JOIN `tblsalesrep` rep ON com.user_id = rep.Guid_user "
			."WHERE com.eventid = ".$eventId." ORDER BY `id` DESC";
	$result = $db->query($query);
	
	echo json_encode($result);
	
	/*
	$query = "SELECT com.*, user.email, user.firstname, user.lastname FROM `tblcomments` com LEFT JOIN `tbluser` user ON com.user_id = user.Guid_user WHERE com.eventid = ".$eventId." ORDER BY `id` DESC";
	$result = $db->query($query);
	
	echo json_encode($result);*/
}

if(isset($_POST['commentid'])){
	$commentid = $_POST['commentid'];
	$query = "DELETE FROM `tblcomments` WHERE id=".$commentid;
	$result = $db->query($query);
	echo json_encode($result);
}
die;