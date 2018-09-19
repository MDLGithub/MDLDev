<?php
ob_start();
require_once('config.php');
require_once('settings.php');
require_once('header.php');
if (!login_check($db)) {
    Leave(SITE_URL);
}
if (isset($_GET['logout'])) {
    logout();
    Leave(SITE_URL);
}

$userID = $_SESSION['user']["id"];
$roleInfo = getRole($db, $userID);
$role = $roleInfo['role'];

if($role!="Admin"){
    Leave(SITE_URL."/no-permission.php");
}

if(isset($_GET['migrate']) && $_GET['migrate']=='1'){
    $oldStats = "";
    if(isset($_GET['from'])){
	$oldTableName = $_GET['from'];
	$oldStats = $db->selectAll($oldTableName);
    }
    if($oldStats!=""){
	foreach ($oldStats as $k=>$v){

	    $Guid_patient = $v['Guid_patient'];

	    $query = "SELECT q.*, p.* FROM tbl_ss_qualify q
		     LEFT JOIN tblpatient p ON q.Guid_user = p.Guid_user
		     WHERE p.`Guid_patient`=$Guid_patient
		     GROUP BY p.Guid_user ORDER BY q.Date_created DESC";
	    $patientInfo = $db->row($query);

	    if(isset($patientInfo['account_number']) && $patientInfo['account_number'] !=""){
		$account = $patientInfo['account_number'];
		$accountQ = "SELECT a.Guid_account, a.account, a.name AS account_name, "
			    . "sr.Guid_salesrep, sr.first_name AS salesrep_fname, sr.last_name AS salesrep_lname, CONCAT(sr.first_name, ' ', sr.last_name) AS salesrep_name "
			    . "FROM tblaccount a "
			    . "LEFT JOIN tblaccountrep ar ON a.Guid_account=ar.Guid_account "
			    . "LEFT JOIN tblsalesrep sr ON ar.Guid_salesrep = sr.Guid_salesrep "
			    . "WHERE a.account = '" . $account . "'";
		$accountInfo = $db->row($accountQ);
	    } else {
		$accountInfo = FALSE;
		$providers = FALSE;
	    }


	    $statusLogData = array(
		'Guid_user' => $patientInfo['Guid_user'],
		'Guid_patient'=> $Guid_patient,
		'Guid_account' => $accountInfo['Guid_account'],
		'account' => $accountInfo['account'],
		'Guid_salesrep' => $accountInfo['Guid_salesrep'],
		'salesrep_fname' => $accountInfo['salesrep_fname'],
		'salesrep_lname' => $accountInfo['salesrep_lname'],
		'Recorded_by' => $_SESSION['user']['id'],
		'Date'=>($v['date']!="")?date('Y-m-d h:i:s',strtotime($v['date'])):"",
		'Date_created'=>date('Y-m-d h:i:s')
	    );

	    if(!empty($v['status_ids'])){
		$statusIDs = unserialize($v['status_ids']);
		foreach ($statusIDs as $key => $value) {
		    $statusLogData['Log_group'] = "";
		    $statusLogData['Guid_status'] = $value;
		    $statusLogData['currentstatus'] = "";
		}
	    }




	    //$newStats = insertIntoTable($db, 'tbl_mdl_stats', $newStatsData);
	    var_dump($statusLogData);



	}
    }


}







?>


<?php require_once('scripts.php');?>

<?php require_once('footer.php');?>