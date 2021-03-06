<?php
require_once('config.php');
require_once('settings.php');

if(isset($_POST['url_config']) && $_POST['url_config']=='1'){
    load_url_config($db, $_POST['id']);
}
if(isset($_POST['get_providers']) && $_POST['get_providers']=='1'){
    get_providers($db, $_POST['accountId']);
}
if(isset($_POST['get_account_info']) && $_POST['get_account_info']=='1'){
    get_account_info($db, $_POST['account_id']);
}
if(isset($_POST['get_account_full_info']) && $_POST['get_account_full_info']=='1'){
    get_account_and_salesrep($db, $_POST['account_id']);
}
if(isset($_POST['get_account_provider']) && $_POST['get_account_provider']=='1'){
    get_provider_by_guid($db, $_POST['provider_guid']);
}
if(isset($_POST['status_dropdown']) && $_POST['status_dropdown']=='1'){
    get_this_status_dropdown($db, $_POST['parent_id']);
}
if(isset($_POST['save_specimen_into_logs'])){
    save_specimen_into_logs($db, $_POST['date'], $_POST['Guid_user'], $_POST['account']);
}
if(isset($_POST['save_specimen_not_collected_into_logs'])){
    save_specimen_not_collected_into_logs($db, $_POST['date'], $_POST['Guid_user'], $_POST['account'], $_POST['status']);
}
if(isset($_POST['deleteUser'])){
    delete_user($db, $_POST['userType'], $_POST['Guid_user']);
}
if (isset($_POST['updateAccounts'])) {
    updateAccounts($db, $_POST['salesrep']);
}
if (isset($_POST['date_type'])) {
    switch ($_POST['date_type']) {
	case 'week':
	    rangeWeek();
	    break;
	case 'month':
	    rangeMonth();
	    break;
	case 'quarter':
	    rangeQuarter();
	    break;
	case 'year':
	    rangeYear();
	    break;
	}
}
if(isset($_POST['deleteMarkedTestUsers'])){
    delete_marked_test_users($db);
}
if(isset($_POST['get_loced_user_data'])){
    get_loced_user_log($db, $_POST['email']);
}
if(isset($_POST['unlock_this_user'])){
    unlock_user($db, $_POST['email']);
}
if (isset($_POST['exportUsers'])) {
    exportUsers($db);
}
if (isset($_POST['get_patient_info_providers'])) {
    get_providers_dropdown_options($db,$_POST['account_id']);
}
if (isset($_POST['get_salutation_message'])) {
    salutationMessage($db,$_POST['userRole'], $_POST['userId'], $_POST['userTimeZone']);
}
if (isset($_POST['openPdf'])) {
    fillPdf($db, $_POST['pdf_name'], $_POST['patientInfo']);
}
if (isset($_POST['unlinkPdf'])) {
    removeTmpPdf($db, $_POST['pdf_name']);
}
if (isset($_POST['show_categroy_dropdown'])) {
    showCategoryDropdown($db);
}

function showCategoryDropdown($db){    
    $cnt = '<div class="selectCategory">
                <label class="title"><span>Select Category</span></label>';
    $categories = $db->query("SELECT * FROM `tbl_mdl_category` ");   
    
                foreach ($categories as $k=>$v) {
                $cnt .= '<div>
                            <input id="'.$v['Guid_category'].'" type="checkbox" name="Guid_category[]" value="'.$v['Guid_category'].'">
                            <label for="'.$v['Guid_category'].'">'. $v['name'].'</label>
                        </div>';                              
                }
    $cnt .= '</div>';    
    echo json_encode(array('content'=>$cnt)); 
    exit();
}

/**
 * salutation function for logged in Physicians
 * Good morning, Dr (if the title is MD in db) [last name]!
 * After 12:00 PM -> "Good afternoon"
 * After 5:00 PM -> "Good evening"
 * @param type $role
 * @param type $userID
 * @return json
 */
function salutationMessage($db, $role, $userID, $timezone){
    date_default_timezone_set($timezone); 
    $salutation = ''; 
    $title ='';      
    if($role=='Admin'){
        $admin = $db->row("SELECT first_name FROM `tbladmins` WHERE Guid_user=:Guid_user", array('Guid_user'=>$userID));
        $title = $admin['first_name'].'!';
    }elseif($role=='Physician'){  
        $physician = $db->row("SELECT title, last_name FROM `tblprovider` WHERE Guid_user=:Guid_user", array('Guid_user'=>$userID));
        if($physician['title']=='MD' || $physician['title']==''){
            $title = "Dr. ".$physician['last_name'].'!';
        }else{
            $title = $physician['last_name'].'!';
        }               
    } elseif($role=='Sales Rep' || $role=='Sales Manager'){  
        $salesrep = $db->row("SELECT first_name FROM `tblsalesrep` WHERE Guid_user=:Guid_user", array('Guid_user'=>$userID));
        $title = $salesrep['first_name'].'!';                     
    } elseif($role=='Patient' || $role=='MDL Patient'){ 
        $salesrep = $db->row("SELECT AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%') as firstname FROM `tblpatient` WHERE Guid_user=:Guid_user", array('Guid_user'=>$userID));
        $title = $salesrep['firstname'].'!';
    }    
    // 24-hour format of an hour without leading zeros (0 through 23)
    $Hour = date('G');
    if ( $Hour >= 5 && $Hour <= 11 ) {
        if($title!=""){
            $salutation = "Good morning, ".$title;
        }
    } else if ( $Hour >= 12 && $Hour <= 18 ) {
        if($title!=""){
            $salutation = "Good afternoon, ".$title;
        }
    } else if ( $Hour >= 19 || $Hour <= 4 ) {
        if($title!=""){
            $salutation = "Good evening, ".$title;
        }
    } 
    
    echo json_encode(array('salutation'=>$salutation));
}
/**
 * Get provider <select> dropdown options by given account id
 * @param type $db
 * @param type $accountID
 */
function get_providers_dropdown_options($db,$accountID){
    $option = '<option value="">Select Provider</option>';
    $tblproviders = $db->query('SELECT pr.* FROM tblprovider pr '                                
                                . 'LEFT JOIN tbluser u ON u.`Guid_user`=pr.`Guid_user`'
                                . ' WHERE account_id='.$accountID.' AND u.status="1" ');
    foreach ($tblproviders as $k=>$v){ 
        $option .= '<option value="'. $v['Guid_provider'].'" >'.$v['first_name'].' '.$v['last_name'].'</option>';
    }    
    echo json_encode(array('options'=>$option, 'providers'=>$tblproviders));                        
}
/**
 * Unlock users by email
 * Used in user management screen, click to unlock user button
 * @param type $db
 * @param type $email
 */
function  unlock_user($db, $email){
    deleteByField($db, 'tbluser_login_attempts', 'email',  $_POST['email']);
    echo json_encode(array('delete'=>TRUE));
}
/**
 * Get log for locked users
 * @param type $db
 * @param type $email
 */
function get_loced_user_log($db, $email){
    $userLoginLog = $db->query('SELECT ip, time FROM tbluser_login_attempts WHERE email=:email ORDER BY `time` DESC', array('email'=>$email));
    $content = "";
    foreach ($userLoginLog as $k=>$v){
	$date = date('Y-m-d H:i:s', $v['time']);
	$ip = $v['ip'];
	$content.="<tr><td>".$ip."</td><td>".$date."</td></tr>";
    }
    echo json_encode(array('content'=>$content));
}
/**
 * Delete User
 * @param type $db
 * @param string $type
 * @param type $Guid_user
 */
function delete_user($db, $type, $Guid_user){
    //tbl_ss_qualify
    $SSQualify = $db->row("SELECT Guid_qualify FROM `tbl_ss_qualify` WHERE Guid_user=:Guid_user ORDER BY Date_created DESC LIMIT 1", array('Guid_user'=>$Guid_user));
    $clinupSSQualifyTables = array(
	'tbl_ss_qualifyfam',
	'tbl_ss_qualifypers',
	'tbl_ss_qualifyans',
	'tbl_ss_qualifygene',
    );
    if(isset($SSQualify['Guid_qualify'])){
	$Guid_qualify = $SSQualify['Guid_qualify'];
	foreach ($clinupSSQualifyTables as $k=>$thisTable){
	    $sql = "DELETE FROM $thisTable WHERE Guid_qualify=".$Guid_qualify;
	    if ($db->query($sql)) {
		$arrMsg[] = "#".$Guid_qualify." user from ".$thisTable." table deleted successfully.";
	    } else {
		$arrMsg[] = "Error deleting #".$Guid_qualify." user from ".$thisTable." table. ";
	    }
	}
    }
    //tblqualify
    $Qualify = $db->row("SELECT Guid_qualify FROM `tblqualify` WHERE Guid_user=:Guid_user ORDER BY Date_created DESC LIMIT 1", array('Guid_user'=>$Guid_user));
    $clinupQualifyTables = array(
	'tblqualifyfam',
	'tblqualifypers',
	'tblqualifyans',
	'tblqualifygene',
    );
    if(isset($Qualify['Guid_qualify'])){
	$Guid_qualify = $Qualify['Guid_qualify'];
	foreach ($clinupQualifyTables as $k=>$thisTable){
	    $sql = "DELETE FROM $thisTable WHERE Guid_qualify=".$Guid_qualify;
	    if ($db->query($sql)) {
		$arrMsg[] = "#".$Guid_qualify." user from ".$thisTable." table deleted successfully";
	    } else {
		$arrMsg[] = "Error deleting #".$Guid_qualify." user from ".$thisTable." table. ";
	    }
	}
    }
    //both users 'test-user' and 'mdl-user' are in patients table,
    //but have different role ids in user table (Guid_role)
    // Delete the history of given user
    $arrMsg = array();
    $cleanupTables = array(
	'tbl_ss_qualify',
	'tblqualify',
	'tblurlconfig',
	'tbl_deductable_log',
	'tbl_mdl_note',
	'tbl_mdl_number',
	'tbl_revenue',
	'tbl_mdl_status_log'
    );
    foreach ($cleanupTables as $k=>$thisTable){
	$sql = "DELETE FROM $thisTable WHERE Guid_user=".$Guid_user;
	$delete =  $db->query($sql);
	if ($delete) {
	    $arrMsg[] = "#".$Guid_user." user from ".$thisTable." table deleted successfully";
	} else {
	    $arrMsg[] = "Error deleting #".$Guid_user." user from ".$thisTable." table. ";
	}
    }
    //If user is test user
    if($type=='test-user'){ //Delete user data
	$userTables = array(
	    'tbluser',
	    'tblpatient'
	);
	foreach ($userTables as $k=>$thisTable){
	    $sql = "DELETE FROM $thisTable WHERE Guid_user=".$Guid_user;
	    if ($db->query($sql)) {
		$arrMsg[] = "#".$Guid_user." user from ".$thisTable." table deleted successfully";
	    } else {
		$arrMsg[] = "Error deleting #".$Guid_user." user from ".$thisTable." table. ";
	    }
	}
    }
    echo json_encode(array('message'=>$arrMsg, '$Qualify'=>$Qualify, '$SSQualify'=>$SSQualify));
    exit();
}
/**
 * Delete Mark As Test type users
 * @param type $db
 */
function delete_marked_test_users($db){
    //tbl_ss_qualify
    $SSQualifyQuery = "SELECT Guid_qualify, Guid_user FROM `tbl_ss_qualify` "
	    . "WHERE Guid_user IN(SELECT Guid_user FROM `tbluser` WHERE marked_test='1')";
    $SSQualify = $db->query($SSQualifyQuery);
    $clinupSSQualifyTables = array(
	'tbl_ss_qualifyfam',
	'tbl_ss_qualifypers',
	'tbl_ss_qualifyans',
	'tbl_ss_qualifygene'
    );
    if(!empty($SSQualify)){
	foreach ($SSQualify as $key=>$val){
	    $Guid_qualify = $val['Guid_qualify'];
	    foreach ($clinupSSQualifyTables as $k=>$thisTable){
		$sql = "DELETE FROM $thisTable WHERE Guid_qualify=".$Guid_qualify;
		if ($db->query($sql)) {
		    $arrMsg[] = "#".$Guid_qualify." user from ".$thisTable." table deleted successfully.";
		} else {
		    $arrMsg[] = "Error deleting #".$Guid_qualify." user from ".$thisTable." table. ";
		}
	    }
	}
    }
    //tblqualify
    $qualifyQuery = "SELECT Guid_qualify, Guid_user FROM `tblqualify` "
	    . "WHERE Guid_user IN(SELECT Guid_user FROM  `tbluser` WHERE marked_test='1')";
    $Qualify = $db->row($qualifyQuery);
    $clinupQualifyTables = array(
	'tblqualifyfam',
	'tblqualifypers',
	'tblqualifyans',
	'tblqualifygene',
    );
    if(!empty($Qualify)){
	foreach ($Qualify as $key=>$val){
	    if(isset($val['Guid_qualify'])){
		$Guid_qualify = $val['Guid_qualify'];
		foreach ($clinupQualifyTables as $k=>$thisTable){
		    $sql = "DELETE FROM $thisTable WHERE Guid_qualify=".$Guid_qualify;
		    if ($db->query($sql)) {
			$arrMsg[] = "#".$Guid_qualify." user from ".$thisTable." table deleted successfully";
		    } else {
			$arrMsg[] = "Error deleting #".$Guid_qualify." user from ".$thisTable." table. ";
		    }
		}
	    }
	}
    }
    // Delete Marked Test Users
    $arrMsg = array();
    $cleanupTables = array(
	'tbl_ss_qualify',
	'tblqualify',
	'tblurlconfig',
	'tbl_deductable_log',
	'tbl_mdl_note',
	'tbl_mdl_number',
	'tbl_revenue',
	'tbl_mdl_status_log'
    );

    foreach ($cleanupTables as $k=>$thisTable){
	$sql = "DELETE FROM $thisTable WHERE Guid_user "
		. "IN(SELECT Guid_user FROM  `tbluser` WHERE marked_test='1')";
	$delete =  $db->query($sql);
	if ($delete) {
	    $arrMsg[] = "Marked Test users from ".$thisTable." table deleted successfully";
	} else {
	    $arrMsg[] = "Error Marked Test users deleting from ".$thisTable." table. ";
	}
    }
    //Delete patient
    $deletePatient = $db->query("DELETE FROM tblpatient WHERE Guid_user "
		. "IN(SELECT Guid_user FROM  `tbluser` WHERE marked_test='1' AND Guid_role<>'6')");
    //delete from users table
    $deleteUsers = $db->query("DELETE FROM `tbluser` WHERE marked_test='1' and Guid_role<>6");
    echo json_encode(array('message'=>$arrMsg));
    exit();
}


//get status dropdown
function get_this_status_dropdown($db, $parent) {
    $statuses = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$parent." ORDER BY order_by ASC, Guid_status ASC");
    $content = "";
    $hasSub = "";
    $statusID = "";
    if ( !empty($statuses) ) {
	$content .= '<div class="f2  ">
		    <div class="group">
			<select data-parent="'.$parent.'" required class="status-dropdown" name="status[]" id="">
			   ';
	$i = 1;
	foreach ( $statuses as $status ) {
	    $checkCildren = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$status['Guid_status']);
	    $optionClass = '';
	    if ( !empty($checkCildren) ) {
		$optionClass = 'has_sub_menu';
		if($i==1){//checking if first option has sub in order to generate next dropdown
		    $hasSub = '1';
		    $statusID = $status['Guid_status'];
		}
	    }
	    $content .= "<option value='".$status['Guid_status']."' class='".$optionClass."'>".$status['status'];

	    $content .= '</option>';
	    $i++;
	}

    $content .= "</select>";
    $content .= '<p class="f_status">
				<span class="status_icons"><strong></strong></span>
			    </p></div></div>';
    }

    echo json_encode( array('content'=>$content, 'hasSub'=>$hasSub, 'statusID'=>$statusID));
    exit();
}


//Homepage filter account, get correlation between account, providers, sales reps
if(isset($_POST['get_account_correlations']) && $_POST['get_account_correlations']!=''){
    $name = $_POST['get_account_correlations'];
    $id = $_POST['id'];
    $userRole = $_POST['userRole'];
    $selectedIds = array(
	'account' => $_POST['account'],
	'provider' => $_POST['provider'],
	'salesrep' => $_POST['salesRep']
    );
    get_account_correlations($db, $id, $name, $userRole, $selectedIds);
}

function get_account_correlations($db, $id, $name, $userRole, $selectedIds){

    $queryAccounts  = "SELECT * FROM tblaccount";
    $queryProviders = "SELECT * FROM tblprovider";
    $querySalesreps = "SELECT tblaccount.*, tblsalesrep.* FROM tblaccount "
			. "LEFT JOIN tblaccountrep ON tblaccount.Guid_account = tblaccountrep.Guid_account "
			. "LEFT JOIN tblsalesrep ON tblsalesrep.Guid_salesrep=tblaccountrep.Guid_salesrep WHERE tblsalesrep.Guid_user<>'' ";

    $whereProvider = array();
    $whereSalesRep = array();
    $whereAccount  = array();

    if($name=='account'){
	//account query
	$queryAccounts .= " WHERE account=:id";
	$whereAccount = array('id'=>$id);
	if($id != ""){ //if account is not empty val
	    //providers
	    $queryProviders .= " WHERE account_id=:id";
	    $whereProvider = array("id"=>$id);
	    //salesreps
	    $querySalesreps .= " AND tblaccount.account=:id AND tblsalesrep.Guid_user<>''";
	    $whereSalesRep = array("id"=>$id);
	} else { //set all to initial values
	    $queryAccounts  = "SELECT * FROM tblaccount";
	    $whereAccount  = array();
	}
    }

    if($name=='provider'){
	if($id != ""){
	   if($selectedIds['account'] == ""){//get this provider accounts and salesreps
		//Accounts
		$queryAccounts  = "SELECT * FROM tblaccount a "
				. "LEFT JOIN tblprovider p ON a.account = p.account_id AND p.account_id<>' ' "
				. "WHERE p.Guid_provider=:id";
		$whereAccount = array("id"=>$id);
		$account = $db->row($queryAccounts, $whereAccount);
		$accountID = $account['account'];
		//Providers
		$queryProviders .= " WHERE account_id=:id";
		$whereProvider = array("id"=>$accountID);
		//Slaesreps
		$querySalesreps .= " AND tblaccount.account=:id AND tblsalesrep.Guid_user <> ''";
		$whereSalesRep = array("id"=>$accountID);
	   }
	} else {

	}
    }

    if($name=='salesrep'){
	if($id != ""){
	    if($selectedIds['account'] == ""){
		$querySalesreps .= "  AND tblsalesrep.Guid_user=:id";
		$whereSalesRep = array('id'=>$id);
		$salesreps = $db->row($querySalesreps, $whereSalesRep);
		$accountID = $salesreps['account'];
		$queryAccounts .= " WHERE account=:id";
		$whereAccount = array('id'=>$accountID);
	    }
	    if($selectedIds['provider'] == ""){
		$querySalesreps .= "  AND tblsalesrep.Guid_user=:id";
		$whereSalesRep = array('id'=>$id);
		$salesreps = $db->row($querySalesreps, $whereSalesRep);
		$accountID = $salesreps['account'];
		$queryProviders .= " WHERE account_id=:id ";
		$whereProvider = array("id"=>$accountID);
	   }
	} else {

	}

    }
    


    $queryProviders .= " GROUP BY first_name";
    $providers = $db->query($queryProviders, $whereProvider);
    $providerHtml = '<option value="">Provider</option>';
    foreach ($providers as $k=>$v){
       $selected = ($id==$v['Guid_provider']) ? " selected='selected'": "";
       $providerHtml .= '<option '.$selected.' value="'.$v['Guid_provider'].'">'.$v['first_name'].' '.$v['last_name'].'</option>';
    }
    //$querySalesreps .= ' GROUP BY tblsalesrep.Guid_user';

    $querySalesreps .= '  GROUP BY tblsalesrep.first_name';
    $salesreps = $db->query($querySalesreps, $whereSalesRep);
    $salesrepHtml = '<option value="">Genetic Consultant</option>';
    foreach ($salesreps as $k=>$v){
       $selected = ($id==$v['Guid_user']) ? " selected='selected'": "";
       $salesrepHtml .= '<option '.$selected.' value="'.$v['Guid_user'].'">'.$v['first_name'].' '.$v['last_name'].'</option>';
    }
    
    $userCategories = $db->query("SELECT Guid_category FROM `tbl_mdl_category_user_link` WHERE Guid_user=:Guid_user", array('Guid_user'=>$_SESSION['user']['id'])); 
    $userLinks = '';
    if(!empty($userCategories)){
        foreach ($userCategories as $k=>$v){
            $userLinks .= $v['Guid_category'].', ';
        }
        $userLinks = rtrim($userLinks, ', ');
    } 
    if($userRole=='Sales Manager'){
        if($userLinks!=''){
            $wherAccount = strpos($queryAccounts, 'WHERE') ? " AND " : " WHERE ";
            $queryAccounts .= $wherAccount . " Guid_category IN (" . $userLinks . ") ";
        }
    }
    
    $queryAccounts .= "  ORDER BY account ASC";
    $accounts = $db->query($queryAccounts, $whereAccount);
    $accountsHtml = '<option value="">Account</option>';
    foreach ($accounts as $k=>$v){
       $selected = ($id===$v['account']) ? " selected='selected'": "";
       $accountsHtml .= '<option '.$selected.' value="'.$v['account'].'">'.$v['account'] ." - ". formatAccountName($v['name']).'</option>';
    }


    echo json_encode( array(
			'q'=>array('account'=>$queryAccounts, 'provider'=>$queryProviders, 'salesrep'=>$querySalesreps),
			'w'=>array('account'=>$whereAccount, 'provider'=>$whereProvider,'salesrep'=>$whereSalesRep),
			'id' => $id,
			'name'=>$name,
			'thisUserRole'=>$userRole,
			'accounts_html'=>$accountsHtml,
			'provider_html'=>$providerHtml,
			'salesrep_html'=>$salesrepHtml
		    ));
    exit();
}

function get_account_and_salesrep($db, $accountGuid=NULL, $getRow=NULL){
    $query = "SELECT tblaccount.*, "
	    . "tblsalesrep.Guid_salesrep, tblsalesrep.first_name AS salesrepFName, tblsalesrep.last_name AS salesrepLName,"
	    . "tblsalesrep.email AS salesrepEmail, tblsalesrep.phone_number AS salesrepPhone , "
	    . "tblsalesrep.region AS salesrepRegion, tblsalesrep.title AS salesrepTitle, "
	    . "tblsalesrep.address AS salesrepAddress, tblsalesrep.city AS salesrepCity, "
	    . "tblsalesrep.state AS salesrepState, tblsalesrep.zip AS salesrepZip, tblsalesrep.photo_filename AS salesrepPhoto "
	    . "FROM tblaccount "
	    . "LEFT JOIN tblaccountrep ON tblaccount.Guid_account = tblaccountrep.Guid_account "
	    . "LEFT JOIN tblsalesrep ON tblsalesrep.Guid_salesrep=tblaccountrep.Guid_salesrep ";

    if($accountGuid){
	$query .= "WHERE tblaccount.account=:id";
	$result = $db->query($query, array("id"=>$accountGuid));
    }
    elseif ($getRow) {
	$result = $db->row($query);
    }else{
	$result = $db->query($query);
    }

    $queryProviders = "SELECT * FROM tblprovider WHERE account_id=:id";
    $providers = $db->query($queryProviders, array("id"=>$accountGuid));

    echo json_encode( array('guid'=>$accountGuid, 'accountInfo'=>$result, 'providers'=>$providers, 'q'=>$query) );  exit();


}

function load_url_config($db, $id){

    $query = "SELECT
		    tblurlconfig.id, tblurlconfig.Guid_user, tblurlconfig.geneveda, tblurlconfig.pin,
		    tblaccount.*,
		    tblsource.description, tblsource.Guid_source, tblsource.code,
		    tbldevice.deviceid AS device_id,
		    tbldeviceinv.serial_number As serial_number
		    FROM tblurlconfig
		    LEFT JOIN `tblaccount` ON tblurlconfig.account = tblaccount.Guid_account
		    LEFT JOIN `tblsource` ON tblurlconfig.location = tblsource.code
		    LEFT JOIN `tbldevice` ON tblurlconfig.device_id = tbldevice.deviceid
		    LEFT JOIN `tbldeviceinv` ON tbldevice.deviceid  = tbldeviceinv.deviceid
		    WHERE tblurlconfig.id=:id
		    ORDER BY tblurlconfig.id DESC";
    $urlConfigs = $db->row($query, array("id"=>$id));

    echo json_encode( array('error'=>true, 'post'=>$_POST, 'urlConfigs'=>$urlConfigs, 'q'=>$query) );  exit();
}

function get_providers($db, $id){
    $queryProviders = "SELECT * FROM tblprovider WHERE account_id=:id";
    $providers = $db->query($queryProviders, array("id"=>$id));
    echo json_encode( array('providers'=>$providers) );  exit();
}

function get_provider_by_guid($db, $provider_guid){
    $queryProvider = "SELECT * FROM tblprovider WHERE Guid_provider=:id";
    $provider = $db->row($queryProvider, array("id"=>$provider_guid));
    echo json_encode( array('provider'=>$provider) );  exit();
}
function get_account_info($db, $accountId){
    $query = "SELECT * FROM tblaccount WHERE account=:id";
    $acountInfo = $db->query($query, array("id"=>$accountId));

    $queryProviders = "SELECT * FROM tblprovider WHERE account_id=:id";
    $providers = $db->query($queryProviders, array("id"=>$accountId));

    echo json_encode( array('accountInfo'=>$acountInfo, 'providers'=>$providers) );  exit();
}

function updateAccounts($db, $salesrep) {
    $query = "SELECT tblaccount.*                   
        FROM tblsalesrep 
        LEFT JOIN `tblaccountrep` ON  tblsalesrep.Guid_salesrep = tblaccountrep.Guid_salesrep
        LEFT JOIN `tblaccount` ON tblaccountrep.Guid_account = tblaccount.Guid_account                    
        WHERE tblsalesrep.Guid_salesrep=:salesrep AND tblaccount.Guid_account IS NOT NULL";

    $accounts = $db->query($query, array("salesrep"=>$salesrep));

    $accountsHtml = '<option value="">Account</option>';
    foreach ($accounts as $k=>$v){
       $accountsHtml .= '<option value="'.$v['account'].'">'.$v['account'] ." - ". $v['name'].'</option>';
    }

    echo json_encode(['accounts_html' => $accountsHtml]);  
    exit();
}

function exportUsers($db) {
    $testsSql = "SELECT q.Date_created AS date, CONCAT(srep.first_name, ' ', srep.last_name) as 'sales', mdl.mdl_number as 'mdl',
    (SELECT MAX(sp.Date) FROM tbl_mdl_status_log sp WHERE sp.account = a.account AND sp.Guid_patient = p.Guid_patient AND sp.Guid_status = 2) as 'accessioned',
    a.account as 'account',
    a.name as 'account_name',
    q.qualified as 'med_necessity',
    q.source as 'event',
    q.Guid_user as 'user_id',
    srep.color_matrix as 'sales_color',
    q.Date_created,
    p.Guid_patient as 'patient_id'
    
    FROM tbl_ss_qualify q 
    LEFT JOIN tblpatient p ON q.Guid_user = p.Guid_user 
    LEFT JOIN tbluser u ON q.Guid_user = u.Guid_user
    LEFT JOIN tblaccount a ON a.account = q.account_number 
    LEFT JOIN tblaccountrep ar ON ar.Guid_account = a.Guid_account 
    LEFT JOIN tblsalesrep srep ON ar.Guid_salesrep = srep.Guid_salesrep
    LEFT JOIN tbl_mdl_number mdl ON q.Guid_user = mdl.Guid_user
    
    WHERE  u.marked_test = '0' 
    
    AND q.account_number = ". ($_POST['account'] == '' ? "a.account" : ":account") ."
    AND a.Guid_account = ar.Guid_account AND ar.Guid_salesrep = ". ($_POST['consultant'] == '' ? "srep.Guid_salesrep" : ":consultant") ." 
     ". ($_POST['from'] == '' ? " " : "AND q.Date_created >=:from") ." 
     ". ($_POST['to'] == '' ? " " : "AND q.Date_created <=:to") ." 
    AND mdl.mdl_number IS NOT NULL AND mdl.mdl_number != '' AND mdl.mdl_number != 0
    
    AND CONCAT(AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%'), ' ', AES_DECRYPT(p.lastname_enc, 'L@stn@m3&%#')) NOT LIKE '%test%' 
    AND CONCAT(AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%'), ' ', AES_DECRYPT(p.lastname_enc, 'L@stn@m3&%#')) NOT LIKE '%John Smith%' 
    AND CONCAT(AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%'), ' ', AES_DECRYPT(p.lastname_enc, 'L@stn@m3&%#')) NOT LIKE '%John Doe%' 
    AND CONCAT(AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%'), ' ', AES_DECRYPT(p.lastname_enc, 'L@stn@m3&%#')) NOT LIKE '%Jane Doe%'
    
    GROUP BY q.Guid_qualify ORDER BY sales ASC, account ASC, accessioned ASC";

    $params = [];
    if ($_POST['account']) {
	    $params['account'] = $_POST['account'];
    }
    if ($_POST['consultant']) {
	    $params['consultant'] = $_POST['consultant'];
    }
    if ($_POST['from']) {
	    $params['from'] = dbDateFormat($_POST['from']);
    }
    if ($_POST['to']) {
	    $params['to'] = dbDateFormat($_POST['to']);
    }

    $tests = $db->query($testsSql, $params);

    $objPHPExcel = new \PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);

    $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true)->setSize(16);
    $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Geneveda Matrix');

    $rowCount = 3;
    $headerStyleArray = array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => '989898')
        ),
        'font' => array(
            'bold' => true,
            'color' => array('rgb' => '00009c')
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        )
    );

    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);

    $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ':O' . $rowCount)->applyFromArray($headerStyleArray);
    $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, '#');
    $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, 'Accessioned');
    $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, 'MDL #');
    $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, 'Test Ordered');
    $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, 'Sales Rep');
    $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, 'Account #');
    $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, 'Account Name');
    $objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, 'Med Necessity');
    $objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, 'Event');
    $objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, 'Insurance App/Dec');
    $objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, 'Reported');
    $objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, 'Test Paid');
    $objPHPExcel->getActiveSheet()->SetCellValue('M' . $rowCount, 'Last Paid');
    $objPHPExcel->getActiveSheet()->SetCellValue('N' . $rowCount, 'Payer(s)');
    $objPHPExcel->getActiveSheet()->SetCellValue('O' . $rowCount, 'Total Paid');

    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(11);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(60);

    $totalSum = 0;
    if (!empty($tests)) {
      foreach ($tests as $data) {
        $revenueQuery = 'SELECT r.*, p.name AS payor, cpt.code '
                . 'FROM tbl_revenue r '
                . 'LEFT JOIN tbl_mdl_payors p ON r.Guid_payor=p.Guid_payor '
                . 'LEFT JOIN tbl_mdl_cpt_code cpt ON r.Guid_cpt=cpt.Guid_cpt '
                . 'WHERE Guid_user=:Guid_user AND p.name != \'Patient\'';
        $revenues = $db->query($revenueQuery, array('Guid_user'=>$data['user_id']));

        $revSum = 0;
        $revPayers = [];
        if (!empty($revenues)) {
            foreach ($revenues as $k=>$v) {
                if (!in_array($v['payor'], $revPayers)) {
                    $revPayers[] = $v['payor'];
                }
                if($v['amount']!=""){
                    $revSum += $v['amount'];
                }
            }
        }

        $payersNames = implode(', ', $revPayers);
        $totalSum += $revSum;

        $rowCount++;

        if (isset($data['sales_color'])) {
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':O'.$rowCount)->getFill()->applyFromArray(array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array(
                    'rgb' => (!empty($data['sales_color'])) ? substr($data['sales_color'], 1) : '#ffffff'
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            ));
        }

        $account_name = formatAccountName($data['account_name']);

        $reported = "SELECT MAX(trr.Date) as 'reported' FROM tbl_mdl_status_log trr WHERE trr.account = {$data['account']} AND trr.Guid_patient = {$data['patient_id']} AND trr.Guid_status = 22"; 
        $reportedResp = $db->query($reported);
        $test_paid = "SELECT tp.status as 'test_paid' FROM tbl_mdl_status_log tplog
         LEFT JOIN tbl_mdl_status tp ON tplog.Guid_status = tp.Guid_status
         WHERE tplog.account = {$data['account']} AND tplog.Guid_patient = {$data['patient_id']} AND tp.parent_id = 52 ORDER BY tplog.Date_created DESC LIMIT 1";
        $testPaidResp = $db->query($test_paid);
        $test_ordered = "SELECT aps.status as 'test_ordered' FROM tbl_mdl_status_log ap 
         LEFT JOIN tbl_mdl_status aps ON ap.Guid_status = aps.Guid_status
         WHERE ap.account = {$data['account']} AND ap.Guid_patient = {$data['patient_id']} AND aps.parent_id = 2 ORDER BY ap.Date_created DESC LIMIT 1"; 
        $testOrdereResp = $db->query($test_ordered);
        $last_paid = "SELECT MAX(pr.Date) as 'last_paid' FROM tbl_mdl_status_log pr WHERE pr.account = {$data['account']} AND pr.Guid_patient = {$data['patient_id']} AND pr.Guid_status = 53";
        $lastPaidResp = $db->query($last_paid);
        $insurance_app = "SELECT CASE WHEN (SELECT MAX(pr.Date) FROM tbl_mdl_status_log pr WHERE pr.account = {$data['account']} AND pr.Guid_patient = {$data['patient_id']} AND pr.Guid_status = 9) THEN 'Declined'
             WHEN (SELECT MAX(pr.Date) FROM tbl_mdl_status_log pr WHERE pr.account = {$data['account']} AND pr.Guid_patient = {$data['patient_id']} AND pr.Guid_status = 18) THEN 'Approved'
             ELSE '' END as 'insurance_app' from tbl_mdl_status_log";
        $insuranceAppResp = $db->query($insurance_app);

        $data['reported'] = !empty($reportedResp[0]['reported']) ? $reportedResp[0]['reported'] : '';
        $data['test_paid'] = !empty($testPaidResp[0]['test_paid']) ? $testPaidResp[0]['test_paid'] : '';
        $data['test_ordered'] = !empty($testOrdereResp[0]['test_ordered']) ? $testOrdereResp[0]['test_ordered'] : '';
        $data['last_paid'] = !empty($lastPaidResp[0]['last_paid']) ? $lastPaidResp[0]['last_paid'] : '';
        $data['insurance_app'] = !empty($insuranceAppResp[0]['insurance_app']) ? $insuranceAppResp[0]['insurance_app'] : '';

        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':O'.$rowCount)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        ));

        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $rowCount - 3);
        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, formatDate($data['accessioned']));
        $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $data['mdl']);
        $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $data['test_ordered']);
        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $data['sales']);
        $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $data['account']);
        $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $account_name);
        $objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, substr($data['med_necessity'], 0, 1));
        $objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $data['event']);
        $objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $data['insurance_app']);
        $objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, formatDate($data['reported']));
        $objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, $data['test_paid']);
        $objPHPExcel->getActiveSheet()->SetCellValue('M' . $rowCount, formatDate($data['last_paid']));
        $objPHPExcel->getActiveSheet()->SetCellValue('N' . $rowCount, $payersNames);
        $objPHPExcel->getActiveSheet()->SetCellValue('O' . $rowCount, $revSum);
        $objPHPExcel->getActiveSheet()->getStyle('O' . $rowCount)->getNumberFormat()->setFormatCode('#,##0.00');
      }
    }

    $footerStyleArray = array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => '989898')
        ),
        'font' => array(
            'bold' => true,
            'color' => array('rgb' => 'ffffff')
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
        )
    );

    $styleArray = array(
	'borders' => array(
	  'allborders' => array(
	    'style' => PHPExcel_Style_Border::BORDER_THIN
	  )
	));

    $objPHPExcel->getActiveSheet()->SetCellValue('A' . ++$rowCount, 'Total:');
    $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowCount . ':N' . $rowCount);

    $objPHPExcel->getActiveSheet()->SetCellValue('O' . $rowCount, $totalSum);
    $objPHPExcel->getActiveSheet()->getStyle('O' . $rowCount)->getNumberFormat()->setFormatCode('"$" #,##0.00');

    $objPHPExcel->getActiveSheet()->getStyle('A' . $rowCount . ':O' . $rowCount)->applyFromArray($footerStyleArray);
    $objPHPExcel->getActiveSheet()->getStyle('A3:O' . $rowCount)->applyFromArray($styleArray);

    $filename = date('his', time()).'_geneveda_matrix.xlsx';
    $directory = SITE_ROOT . '/uploads/';
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    ob_start();
    $objWriter->save($directory . $filename);
    ob_end_clean();

    echo json_encode(['file' => SITE_URL.'/uploads/'.$filename]);
    exit();
}

function rangeMonth () {
    date_default_timezone_set (date_default_timezone_get());
    $datestr = date("Y-m-d H:i:s");
    $dt = strtotime ($datestr);
    echo json_encode(array(
      "start" => date ('n/j/Y', strtotime ('first day of this month', $dt)),
      "end" => date ('n/j/Y', strtotime ('now', $dt))
    ));
    exit();
  }

function rangeYear () {
    date_default_timezone_set (date_default_timezone_get());
    $datestr = date("Y-m-d H:i:s");
    $dt = strtotime ($datestr);
    echo json_encode(array (
      "start" => date ('n/j/Y', strtotime ('first day of this year', $dt)),
      "end" => date ('n/j/Y', strtotime ('now', $dt))
    ));
    exit();
  }

  function rangeWeek () {
    date_default_timezone_set (date_default_timezone_get());
    $datestr = date("Y-m-d H:i:s");
    $dt = strtotime ($datestr);

    echo json_encode(array(
      "start" => date ('N', $dt) == 1 ? date ('n/j/Y', $dt) : date ('n/j/Y', strtotime ('last monday', $dt)),
      "end" => date('N', $dt) == 7 ? date ('n/j/Y', $dt) : date ('n/j/Y', strtotime ('now', $dt))
    ));
    exit();
  }

  function rangeQuarter($quarter = 'current', $year = null, $format = null)
    {
        if ( !is_int($year) ) {        
            $year = (new DateTime)->format('Y');
        }
        $current_quarter = ceil((new DateTime)->format('n') / 3);
        switch (  strtolower($quarter) ) {
            case 'this':
            case 'current':
                $quarter = ceil((new DateTime)->format('n') / 3);
                break;
            case 'previous':
                $year = (new DateTime)->format('Y');
                if ($current_quarter == 1) {
                    $quarter = 4;
                    $year--;
                } else {
                    $quarter =  $current_quarter - 1;
                }
                break;
            case 'first':
                $quarter = 1;
                break;
            case 'last':
                $quarter = 4;
                break;
            default:
                $quarter = (!is_int($quarter) || $quarter < 1 || $quarter > 4) ? $current_quarter : $quarter;
                break;
        }
        if ( $quarter === 'this' ) {
            $quarter = ceil((new DateTime)->format('n') / 3);
        }
        $start = new DateTime($year.'-'.(3*$quarter-2).'-1 00:00:00');
        $end = new DateTime($year.'-'.(3*$quarter).'-'.($quarter == 1 || $quarter == 4 ? 31 : 30) .' 23:59:59');

        echo json_encode(array(
            'start' => $start->format('n/j/Y'),
            'end' => $end->format('n/j/Y')
        ));
        exit();
    }

    function fillPdf($db, $pdf, $patientInfo) {
        $data = json_decode($patientInfo);
        $physician_info = $data->physician_name . ', ' . 
                            $data->physician_address . ', ' . 
                            $data->physician_city . ', ' . 
                            $data->physician_state . ', ' . 
                            $data->physician_zip;


        $directory = SITE_ROOT . '/forms/'; 
        $pdftk = new Pdf($directory . $pdf);

        $pdftk->fillForm([
                'untitled59' => $physician_info,
                'untitled6' => $data->lastname . ' ' . $data->firstname,
                'untitled8' => $data->address,
                'untitled9' => $data->city,
                'untitled10' => $data->state,
                'untitled11' => $data->zip,
                'untitled3' => $data->dob,
                'untitled14' => $data->phone_number,
                'untitled30' => $data->insurance_name != '' ? $data->insurance_name : $data->other_insurance,
                'untitled34' => $data->insurance_policy_number,
                'untitled4' => $data->gender == 'Male' ? 1 : 0
            ])
            ->execute();
   
        $content = file_get_contents( (string) $pdftk->getTmpFile() );

        header('Content-Type: application/pdf');
    
        die($content);
    }