<?php 
require_once('config.php');

if(isset($_POST)){
	$eventId = $_POST['eventid'];
	$query = "SELECT com.*, user.email FROM `tblcomments` com LEFT JOIN `tbluser` user ON com.user_id = user.Guid_user WHERE com.eventid = ".$eventId." ORDER BY `id` DESC";
	$result = $db->query($query);

	echo json_encode($result);
}
die;