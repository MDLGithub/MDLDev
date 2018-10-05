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
    __status_dropdown($db, $_POST['parent_id']);
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
if(isset($_POST['get_loced_user_data'])){
    get_loced_user_log($db, $_POST['email']);
}
if(isset($_POST['unlock_this_user'])){
    unlock_user($db, $_POST['email']);
}

function  unlock_user($db, $email){
    deleteByField($db, 'tbluser_login_attempts', 'email',  $_POST['email']);
    echo json_encode(array('delete'=>TRUE));
}

function get_loced_user_log($db, $email){    
    $userLoginLog = $db->query('SELECT ip, time FROM tbluser_login_attempts WHERE email=:email', array('email'=>$email));
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
 * save save_specimen_into_logs
 * @param type $db
 * @param type $date
 * @param type $Guid_user
 * @param type $account
 * @param type $status
 */
function save_specimen_not_collected_into_logs($db, $date, $Guid_user, $account, $status){
    
    if($account && $account!=""){
        $accountQ = "SELECT a.Guid_account, a.account, a.name AS account_name, "
                    . "sr.Guid_salesrep, sr.first_name AS salesrep_fname, sr.last_name AS salesrep_lname, CONCAT(sr.first_name, ' ', sr.last_name) AS salesrep_name "
                    . "FROM tblaccount a "
                    . "LEFT JOIN tblaccountrep ar ON a.Guid_account=ar.Guid_account "
                    . "LEFT JOIN tblsalesrep sr ON ar.Guid_salesrep = sr.Guid_salesrep "
                    . "WHERE a.account = '" . $account . "'";
        $accountInfo = $db->row($accountQ);
        $statusLogData = array(
            'Guid_account' => $accountInfo['Guid_account'],
            'account' => $accountInfo['account'],
            'Guid_salesrep' => $accountInfo['Guid_salesrep'],
            'salesrep_fname' => $accountInfo['salesrep_fname'],
            'salesrep_lname' => $accountInfo['salesrep_lname']
        );
    }
    $statusLogData['Guid_user'] = $Guid_user;
    $statusLogData['Guid_status'] = '1';
    
    $patient = $db->row("SELECT * FROM tblpatient WHERE Guid_user=:Guid_user", array('Guid_user'=>$Guid_user));
    $statusLogData['Guid_patient'] = $patient['Guid_patient'];    
    
    $statusLogData['Recorded_by'] = $_SESSION['user']['id'];                
    $statusLogData['Date'] = ($date!="")?date('Y-m-d h:i:s',strtotime($date)):"";
    $statusLogData['Date_created'] = date('Y-m-d h:i:s');
    
    if($status!='37'){
        $statuses[] = '37';
        $statuses[] = $status;
    }else{
        $statuses[] = '37';
    }
       
    $Guid_patient = $patient['Guid_patient'];
    $q  =   "SELECT * 
            FROM `tbl_mdl_status` statuses
            LEFT JOIN `tbl_mdl_status_log` statuslogs
            ON statuses.`Guid_status`= statuslogs.`Guid_status`
            AND statuslogs.`Guid_status_log`<>''
            AND statuses.parent_id='0'
            AND statuslogs.Guid_patient=$Guid_patient
            ORDER BY statuslogs.`Date` DESC, statuses.order_by DESC LIMIT 1";
    $result = $db->row($q);
   
    $thisLog = $db->row("SELECT * FROM tbl_mdl_status_log WHERE Guid_status=:Guid_status AND Guid_patient=:Guid_patient", array('Guid_status'=>'37', 'Guid_patient'=>$Guid_patient));    
    if(!empty($thisLog)){
        $LogGroup = $thisLog['Log_group'];
        //delete old log
        deleteByField($db, 'tbl_mdl_status_log', 'Log_group', $LogGroup); 
    }
    
    //insert log
    $saveStats = saveStatusLog($db, $statuses, $statusLogData);    
    updateTable($db, 'tblpatient', array('specimen_collected'=>'No'), array('Guid_patient'=>$patient['Guid_patient']));
    
    
    $statID = updateCurrentStatusID($db, $Guid_patient, FALSE);
            
    echo json_encode(array('log_data'=>$statusLogData, 'updateCurrentStatusID'=>$statID, 'q'=>$q, 'result'=>$result));
    exit();    
}

//save save_specimen_into_logs
function save_specimen_into_logs($db, $date, $Guid_user, $account){
    if($account && $account!=""){
        $accountQ = "SELECT a.Guid_account, a.account, a.name AS account_name, "
                    . "sr.Guid_salesrep, sr.first_name AS salesrep_fname, sr.last_name AS salesrep_lname, CONCAT(sr.first_name, ' ', sr.last_name) AS salesrep_name "
                    . "FROM tblaccount a "
                    . "LEFT JOIN tblaccountrep ar ON a.Guid_account=ar.Guid_account "
                    . "LEFT JOIN tblsalesrep sr ON ar.Guid_salesrep = sr.Guid_salesrep "
                    . "WHERE a.account = '" . $account . "'";
        $accountInfo = $db->row($accountQ);
        $statusLogData = array(
            'Guid_account' => $accountInfo['Guid_account'],
            'account' => $accountInfo['account'],
            'Guid_salesrep' => $accountInfo['Guid_salesrep'],
            'salesrep_fname' => $accountInfo['salesrep_fname'],
            'salesrep_lname' => $accountInfo['salesrep_lname']
        );
    }
    $statusLogData['Guid_user'] = $Guid_user;
    $statusLogData['Guid_status'] = '1';
    
    $patient = $db->row("SELECT * FROM tblpatient WHERE Guid_user=:Guid_user", array('Guid_user'=>$Guid_user));
    $statusLogData['Guid_patient'] = $patient['Guid_patient'];    
    
    $statusLogData['Recorded_by'] = $_SESSION['user']['id'];                
    $statusLogData['Date'] = ($date!="")?date('Y-m-d h:i:s',strtotime($date)):"";
    $statusLogData['Date_created'] = date('Y-m-d h:i:s');
    
    //get log group if exists
    $logRow = $db->row("SELECT * FROM tbl_mdl_status_log WHERE Guid_user=:Guid_user", array('Guid_user'=>$Guid_user));
       
    $insert = insertIntoTable($db, 'tbl_mdl_status_log', $statusLogData);
    if($insert['insertID']){
        updateTable($db, 'tbl_mdl_status_log', array('Log_group'=>$insert['insertID']), array('Guid_status_log'=>$insert['insertID']));
        updateTable($db, 'tblpatient', array('specimen_collected'=>'Yes'), array('Guid_patient'=>$patient['Guid_patient']));
        updateCurrentStatusID($db, $patient['Guid_patient']);
    }   
    
    echo json_encode(array('log_data'=>$statusLogData));
    exit();    
}

//get status dropdown
function __status_dropdown($db, $parent) {
    $statuses = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$parent." ORDER BY order_by ASC, Guid_status ASC");
    $content = "";
    if ( !empty($statuses) ) {
    $content .= '<div class="f2  ">
                    <div class="group">
                        <select data-parent="'.$parent.'" required class="status-dropdown" name="status[]" id="">
                           ';    
    
        foreach ( $statuses as $status ) {  
            $checkCildren = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$status['Guid_status']);
             
            $optionClass = '';
            if ( !empty($checkCildren) ) { 
                $optionClass = 'has_sub_menu';                 
            }            
            $content .= "<option value='".$status['Guid_status']."' class='".$optionClass."'>".$status['status'];
            
            $content .= '</option>';
        }
    
    $content .= "</select>";
    $content .= '<p class="f_status">
                                <span class="status_icons"><strong></strong></span>
                            </p></div></div>';
    }
   
    echo json_encode( array('content'=>$content)); 
    exit();
}


//Homepage filter account, get correlation between account, providers, sales reps
if(isset($_POST['get_account_correlations']) && $_POST['get_account_correlations']!=''){
    $name = $_POST['get_account_correlations'];
    $id = $_POST['id'];
    $selectedIds = array(
        'account' => $_POST['account'],
        'provider' => $_POST['provider'],
        'salesrep' => $_POST['salesRep']
    );
    get_account_correlations($db, $id, $name, $selectedIds);
}

function get_account_correlations($db, $id, $name, $selectedIds){    
    
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
   
    $queryAccounts .= "  ORDER BY account ASC";
    $accounts = $db->query($queryAccounts, $whereAccount);    
    $accountsHtml = '<option value="">Account</option>';
    foreach ($accounts as $k=>$v){
       $selected = ($id===$v['account']) ? " selected='selected'": "";
       $accountsHtml .= '<option '.$selected.' value="'.$v['account'].'">'.$v['account'] ." - ". $v['name'].'</option>';
    }
    
    
    echo json_encode( array(
                        'q'=>array('account'=>$queryAccounts, 'provider'=>$queryProviders, 'salesrep'=>$querySalesreps),
                        'w'=>array('account'=>$whereAccount, 'provider'=>$whereProvider,'salesrep'=>$whereSalesRep),
                        'id' => $id,
                        'name'=>$name,
                        'accounts_html'=>$accountsHtml, 
                        'provider_html'=>$providerHtml, 
                        'salesrep_html'=>$salesrepHtml
                    ));  
    exit();
}

function get_account_correlations__($db, $id, $name, $selectedIds){    
    
    $queryAccounts = "";
    $queryProviders = "SELECT * FROM tblprovider";    
    $querySalesreps = "SELECT tblaccount.*, tblsalesrep.* FROM tblaccount "
                    . "LEFT JOIN tblaccountrep ON tblaccount.Guid_account = tblaccountrep.Guid_account "
                    . "LEFT JOIN tblsalesrep ON tblsalesrep.Guid_salesrep=tblaccountrep.Guid_salesrep ";
        
    $whereProvider = array();
    $whereSalesRep = array();
    $whereAccount  = array();
    $accountID = ""; 
    $providerID =""; 
    $salesRepID = "";
    
    if($name=='account'){        
        $accountID = $id;  
        if($id != ""){                    
            $querySalesreps .= " WHERE tblaccount.account=:id AND tblsalesrep.Guid_user != ''";            
            $queryProviders .= " WHERE account_id=:id"; 
            $whereProvider = array("id"=>$id);            
        } else {            
            $querySalesreps = "SELECT * FROM tblsalesrep";            
        }        
        $whereSalesRep = array("id"=>$id);
    }
    
    if($name=='provider'){        
        if($id != ""){ 
            $providerID = $id;
            $queryAccounts  = "SELECT a.account FROM tblaccount a "
                            . "LEFT JOIN tblprovider p ON a.account = p.account_id "
                            . "WHERE p.Guid_provider=:id";
            $whereAccount = array("id"=>$id);
            $account = $db->row($queryAccounts, $whereAccount);
            $accountID = $account['account'];
            $queryProviders .= " WHERE account_id=:id";         
            $querySalesreps .= " WHERE tblaccount.account=:id AND tblsalesrep.Guid_user != ''";
            $whereProvider = array("id"=>$accountID);
            $whereSalesRep = array("id"=>$accountID); 
        } else {            
            $querySalesreps = "SELECT * FROM tblsalesrep"; 
            $queryProviders = "SELECT * FROM tblprovider";
            $whereProvider = array();
            $whereSalesRep = array();
        }         
    }
        
    if($name=='salesrep'){
        $salesRepID = $id;
        if($id != ""){ 
            $querySalesreps .= " WHERE tblsalesrep.Guid_user=:id AND tblsalesrep.Guid_user != ''";
            $whereSalesRep = array("id"=>$id);
            $salesreps = $db->row($querySalesreps, $whereSalesRep);        
            $accountID = $salesreps['account'];          
            $queryProviders .= " WHERE account_id=:id ";
            $whereProvider = array("id"=>$accountID);   
        }         
    }  
       
    $providers = $db->query($queryProviders, $whereProvider); 
    $providerHtml = '<option value="">Provider</option>';
    foreach ($providers as $k=>$v){
       $selected = ($providerID==$v['Guid_provider']) ? " selected='selected'": "";
       $providerHtml .= '<option '.$selected.' value="'.$v['Guid_provider'].'">'.$v['first_name'].' '.$v['last_name'].'</option>';
    }  
    $querySalesreps .= ' GROUP BY tblsalesrep.Guid_user';
    
    $salesreps = $db->query($querySalesreps, $whereSalesRep);    
    $salesrepHtml = '<option value="">Genetic Consultant</option>';
    foreach ($salesreps as $k=>$v){
       $selected = ($salesRepID==$v['Guid_user']) ? " selected='selected'": "";
       $salesrepHtml .= '<option '.$selected.' value="'.$v['Guid_user'].'">'.$v['first_name'].' '.$v['last_name'].'</option>';
    }
    
    echo json_encode( array(
                        'id' => $id,
                        'name'=>$name,
                        'accountID'=>$accountID,
                        'providerID'=>$providerID,
                        'salesRepID'=>$salesRepID,
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
    $urlConfigs = $db->query($query, array("id"=>$id));    
    
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


