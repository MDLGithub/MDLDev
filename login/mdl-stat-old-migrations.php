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

$oldStats = $db->selectAll('tbl_mdl_stats_old');


foreach ($oldStats as $k=>$v){
    $whereUser = array('Guid_user'=>$v['Guid_user']);
    $patient = $db->row("SELECT Guid_patient FROM `tblpatient` WHERE Guid_user=:Guid_user", $whereUser);
    $Guid_patient = isset($patient['Guid_patient'])?$patient['Guid_patient']:'';
    
    if($v['account']){
        $whereAccount = array('account'=>$v['account']);
        $account = $db->row("SELECT Guid_account FROM `tblaccount` WHERE account=:account", $whereAccount);
        $Guid_account = isset($account['Guid_account'])?$account['Guid_account']:""; // $v['account']
    }else{
        $Guid_account = "";
    }
    
    $salesrep = $db->row("SELECT Guid_salesrep FROM `tblsalesrep` WHERE Guid_user=:Guid_user", $whereUser);
    $Guid_salesrep = isset($salesrep['Guid_salesrep'])?$salesrep['Guid_salesrep']:"";
    
    $newStatsData = array(
        'Guid_patient'=>$Guid_patient,
        'Guid_status'=>$v['Guid_status'],
        'Guid_account'=>$Guid_account,
        'Guid_salesrep'=>$Guid_salesrep,
        'Guid_user'=>$v['Guid_user'],        
        'Guid_declined_reason'=>$v['Guid_declined_reason'],
        'specimen_collection_date'=>$v['specimen_collection_date'],
        'date_accessioned'=>$v['date_accessioned'],
        'date_reported'=>$v['date_reported']
    );
    
    $mdlNumberData = array(
        'Guid_user' => $v['Guid_user'],
        'mdl_number'=> $v['mdl_number'],
        'comment' => $v['notes']
    );
    
    $newStats = insertIntoTable($db, 'tbl_mdl_stats', $newStatsData);
    var_dump($newStats);
    
    $mdlNum = insertIntoTable($db, 'tbl_mdl_number', $mdlNumberData);
    var_dump($mdlNum);
    
}


?>


<?php require_once('scripts.php');?>

<?php require_once('footer.php');?>