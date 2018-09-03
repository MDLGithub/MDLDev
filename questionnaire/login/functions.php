<?php
/**
*  Redirect url
*/
function Leave($url) {
    header("Location: $url");exit;
}
/**
 * Clean String
 * @param type $string
 * @return type
 */
function cleanString($string) {
   $string = str_replace(' ', '', $string); // Replaces all spaces with empty.
   $string = str_replace('-', '', $string); // Replaces all hyphens with empty.

   return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

/**
*  Encode given text to hash using md5
*/
function encode_password($password) {
    return md5($password);
}

/**
*  Do Login Function
*/
function doLogin($db){
    $showMsg = "";
    if(isset($_POST['login'])){
        $email = $_POST['email'];
        $db->bind("email",$email);
        $user   =  $db->row("SELECT * FROM `".DB_PREFIX."user` WHERE email = :email");        
        if ($user AND ( $user["password"] == encode_password($_POST['password']))){
            $showMsg = false;			
            $_SESSION['user']['id'] = $user['Guid_user'];
            $_SESSION['user']['type'] = $user['user_type'];
            $_SESSION['user']['email'] = $user['email'];
        }else{
            $showMsg = true;
        }	

        return $showMsg;	
    }	
}
/**
*  Do Logout Function
*/
function doLogout(){
    unset($_SESSION['user']);
}
/**
*  Check if user logged in
*/
function isUserLogin(){
    if( isset($_SESSION['user']['email']) && $_SESSION['user']['email'] != ''){
        return true;
    }
    return false;
}
/**
*   Return page url
*/
function thisUrl(){
    if(isset($_SERVER['HTTPS'])){
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    }
    else{
        $protocol = 'http';
    }
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}
/**
*   Return Base url
*/
function baseUrl(){
    if(isset($_SERVER['HTTPS'])){
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    }
    else{
        $protocol = 'http';
    }
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . "/dev/login";
}
/**
 * Get logged in user INFO
 * @return boolean
 */
function getThisUserInfo(){
    if(isset($_SESSION['user'])){
        return $_SESSION['user'];
    } else {
        return FALSE;
    }
}
/**
 * Get Logged in user ID
 * @return boolean
 */
function getThisUserID(){
    if(isset($_SESSION['user']['id']) && $_SESSION['user']['id'] != ''){
        return $_SESSION['user']['id'];
    } else {
        return FALSE;
    }
}
/**
 * Is user has given role
 * @param type $db
 * @param type $role
 * @return boolean
 */
function isUser($db, $role){
    $userID = $_SESSION['user']['id'];    
    $query = "SELECT r.Guid_role, r.role FROM tblrole r LEFT JOIN tbluserrole u ON r.Guid_role = u.Guid_role WHERE u.Guid_user = " . $userID;
    $userInfo = $db->row($query);
    if( $userInfo['role'] == $role ){
        return TRUE;
    }    
    return FALSE;  
}
/**
 * Get logged in user name, if not found take user type
 * used for menu to show "Welcome, username" message
 * @param type $db
 * @param type $userID
 * @return type
 */
function getUserName($db, $userID){    
    $query = "SELECT * FROM tblrole r LEFT JOIN tbluserrole u ON r.Guid_role = u.Guid_role WHERE u.Guid_user = " . $userID;
    $userInfo = $db->row($query);
    
    if($userInfo['role']=='Patient'){
        $query = "SELECT * FROM `tblpatient` WHERE Guid_user=:Guid_user";
        $patientInfo = $db->row($query, array("Guid_user"=>$userInfo['Guid_user']));
        if($patientInfo){
            $result = $patientInfo['firstname'];
        } else {
            $result = $_SESSION['user']['type'];
        }
    } elseif ($userInfo['role']=='Sales Rep' || $userInfo['role']=='Sales Manager') {
        $query = "SELECT * FROM `tblsalesrep` WHERE Guid_user=:Guid_user";
        $salesrepInfo = $db->row($query, array("Guid_user"=>$userInfo['Guid_user']));
        if($salesrepInfo){
            $result = $salesrepInfo['first_name'];
        } else {
            $result = $_SESSION['user']['type'];
        }
    } elseif ($userInfo['role']=='Physician') {
        $query = "SELECT * FROM `tblprovider` WHERE Guid_user=:Guid_user";
        $providerInfo = $db->row($query, array("Guid_user"=>$userInfo['Guid_user']));
        if($providerInfo){
            $result = $providerInfo['first_name'];
        } else {
            $result = $_SESSION['user']['type'];
        }
    } elseif ($userInfo['role']=='Admin') {
        $query = "SELECT * FROM `tbladmins` WHERE Guid_user=:Guid_user";
        $providerInfo = $db->row($query, array("Guid_user"=>$userInfo['Guid_user']));
        if($providerInfo){
            $result = $providerInfo['first_name'];
        } else {
            $result = $_SESSION['user']['type'];
        }
    }
    else {
        $result = $_SESSION['user']['type'];
    }
    
    return $result;
}
/**
 * Check if current user has access to the fields
 * All configurations doing from Access Roles page
 * @param type $db
 * @param type $accessKey
 * @param type $userID
 * @return boolean
 */
function isUserHasAccess($db, $accessKey, $userID){
    $role = getRole($db, $userID);    
    $roleKey = $role['role'];
    $roleID = $role['Guid_role'];    
    if($roleKey == 'Admin'){
        return TRUE;
    }    
    $query = "SELECT * FROM `tblaccessbyroles` WHERE key_id=:key";
    $accessRole = $db->row($query, array("key"=>$accessKey));
    if(isset($accessRole['role_ids'])){
        $accessRoleIDs = unserialize($accessRole['role_ids']);
        $accessRoleIDs = explode(';', $accessRoleIDs['ids']);
        if(in_array($roleID, $accessRoleIDs)){
            return TRUE;
        } else {
            return FALSE;
        }        
    }
}

function isCheckedRoleTableCheckbox($tableID, $fieldKey, $roelID, $action){    
    $accessRole = getAccessRoleByKey($tableID);    
    $roleIds = unserialize($accessRole['role_ids']);
    //var_dump($roleIds);
    $ids = array();
    if($roleIds) {  
        
        if(isset($roleIds[$fieldKey][$action])){           
            $ids[$action] = explode(";",$roleIds[$fieldKey][$action]);
        } 
    } 
    
    if($ids){
        if(in_array($roelID, $ids[$action])){
            return " checked";
        } 
    }    
    return FALSE;  
}

function isChecked($pageKey, $roelID, $action){    
    $accessRole = getAccessRoleByKey($pageKey); 
    $roleIds = unserialize($accessRole['role_ids']);   
    $ids = array();
    if($roleIds) {     
        if(isset($roleIds[$action])){
            $ids[$action] = explode(";",$roleIds[$action]);
        } 
    }    
    if($ids){
        if(in_array($roelID, $ids[$action])){
            return " checked";
        } 
    }    
    return "";  
}
function isUserHasAnyAccess($data, $thisUserRole, $action) {
    if($thisUserRole==1){
        return TRUE;
    }
    $i = 0;
    if($data){
        foreach ($data as $k=>$v){
            if(stripos($v[$action], $thisUserRole) !== false){
               $i++;
            }        
        }    
    }
    if($i>0){
        return TRUE;
    }
    return FALSE;
}
function isFieldVisibleByRole($data, $thisUserRole){
    if($thisUserRole==1){
        return TRUE;
    }
    if(stripos($data, $thisUserRole) !== false){
        return TRUE;
    }
    return FALSE;
}

function saveTableAccessRole($db, $data){
    extract($data); 
    $actions = array('view', 'add', 'edit', 'delete');
    foreach ($data as $tableKey => $tableFields){
        if(!empty($tableFields)){
            $saveData = array(); 
            foreach ($tableFields as $fieldK=>$fieldV){
                foreach ($actions as $k=>$action){
                    $roleIds= "";
                    if(isset($fieldV[$action]) && !empty($fieldV[$action])){
                        foreach ($fieldV[$action] as $k=>$v) {
                            $roleIds .= $k.";";
                        }
                    }
                    $saveData[$fieldK][$action] = rtrim($roleIds, ';');
                }
            }            
            if( getAccessRoleByKey($tableKey) ){ 
                //update option
                $updateData = array('role_ids'=>serialize($saveData), 'type'=>'table');
                updateTable($db,'tblaccessbyroles', $updateData, array('key_id'=>$tableKey));         

            }else{ 
                //insert option
                $insertData = array('key_id'=>$tableKey, 'role_ids'=>serialize($saveData), 'type'=>'table');
                insertIntoTable($db, 'tblaccessbyroles', $insertData);
            } 
        }
    }
    
     
}
function savePageAccessRole($db, $data){
    extract($data);   
    $actions = array('view', 'add', 'edit', 'delete');
    foreach ($data as $pageKey => $accessData){
        if(!empty($accessData)){
            $saveData = array(); 
            foreach ($actions as $k=>$action){
                $roleIds= "";
                if(isset($accessData[$action]) && !empty($accessData[$action])){
                    foreach ($accessData[$action] as $k=>$v) {
                        $roleIds .= $k.";";
                    }
                }
                $saveData[$action] = rtrim($roleIds, ';');
            }        
            if( getAccessRoleByKey($pageKey) ){ 
                //update option
                $updateData = array('role_ids'=>serialize($saveData), 'type'=>'page');
                updateTable($db,'tblaccessbyroles', $updateData, array('key_id'=>$pageKey));         

            }else{ 
                //insert option
                $insertData = array('key_id'=>$pageKey, 'role_ids'=>serialize($saveData), 'type'=>'page');
                insertIntoTable($db, 'tblaccessbyroles', $insertData);
            } 
        }
    }
    
     
}
function getUsersAndRoles($db){
    $query = "SELECT u.*, r.* FROM `tbluser` u
                LEFT JOIN `tbluserrole` urole ON u.Guid_user=urole.Guid_user
                LEFT JOIN `tblrole` r ON r.Guid_role=urole.Guid_role";
    $users = $db->query($query);
    return $users;
}
function getUserAndRole($db, $userID){
    $query = "SELECT u.*, r.* FROM `tbluser` u
                LEFT JOIN `tbluserrole` urole ON u.Guid_user=urole.Guid_user
                LEFT JOIN `tblrole` r ON r.Guid_role=urole.Guid_role 
                WHERE u.Guid_user=:Guid_user";    
    $user = $db->row($query, array("Guid_user"=>$userID));
    
    return $user;
}

function getRole($db, $userID){
    $query = "SELECT r.Guid_role, r.role FROM tblrole r "
            . "LEFT JOIN tbluserrole u ON r.Guid_role = u.Guid_role "
            . "WHERE u.Guid_user=:Guid_user";
   
    $result = $db->row($query, array("Guid_user"=>$userID));
    return $result;
}
function getUserDetails($db, $userRole, $userID){
    $userDetail = "";
    $q = "";
    if($userRole == 'Admin'){
        $q = "SELECT first_name, last_name, photo_filename FROM tbladmins";
    } elseif ($userRole == 'Sales Rep' || $userRole == 'Sales Manager') {
        $q = "SELECT first_name, last_name, photo_filename FROM tblsalesrep";
    } elseif ($userRole == 'Physician') {
        $q = "SELECT first_name, last_name, photo_filename FROM tblprovider";    
    } elseif ($userRole == 'Patient') {
        $q = "SELECT firstname as first_name, lastname as latst_name  FROM tblpatient";
    }
    if($q!=""){
        $q .= " WHERE Guid_user=:Guid_user";
        $userDetail = $db->row($q, array("Guid_user"=>$userID));
    }
    var_dump($userRole);
    return $userDetail;    
}
function saveUserRole($db, $Guid_user, $Guid_role) {
    $query = "SELECT * FROM tbluserrole WHERE Guid_user=:Guid_user";
    $result = $db->row($query, array("Guid_user"=>$Guid_user));
    if($result){ //update role connection
        $update = updateTable($db, 'tbluserrole', array('Guid_role'=>$Guid_role), array('Guid_user'=>$Guid_user));
        return $update;
    } else { //insert role connection
        $insert = insertIntoTable($db, 'tbluserrole', array('Guid_role'=>$Guid_role, 'Guid_user'=>$Guid_user));
        return $insert['status'];        
    }
    return FALSE;
}

function saveUserDetails($db, $Guid_user, $Guid_role, $userDetails){
    if($Guid_role=='1'){//admin
        $userQ = "SELECT Guid_user FROM tbladmins WHERE Guid_user=:Guid_user";
        $isUserExists = $db->row($userQ, array('Guid_user'=>$Guid_user));
        if($isUserExists){
            updateTable($db, 'tbladmins', $userDetails, array('Guid_user'=>$Guid_user));
        }else{
            $userDetails['Guid_user'] = $Guid_user;
            insertIntoTable($db, 'tbladmins', $userDetails);
        }
    } elseif ($Guid_role=='2') {//provider(Physician)
        $userQ = "SELECT Guid_user FROM tblprovider WHERE Guid_user=:Guid_user";
        $isUserExists = $db->row($userQ, array('Guid_user'=>$Guid_user));
        if($isUserExists){
            updateTable($db, 'tblprovider', $userDetails, array('Guid_user'=>$Guid_user));
        }else{
            $userDetails['Guid_user'] = $Guid_user;
            insertIntoTable($db, 'tblprovider', $userDetails);
        }        
    } elseif ($Guid_role=='3') {//patient
        $userQ = "SELECT Guid_user FROM tblpatient WHERE Guid_user=:Guid_user";
        $isUserExists = $db->row($userQ, array('Guid_user'=>$Guid_user));
        if($isUserExists){
            updateTable($db, 'tblpatient', $userDetails, array('Guid_user'=>$Guid_user));
        }else{
            $userDetails['Guid_user'] = $Guid_user;
            insertIntoTable($db, 'tblpatient', $userDetails);
        } 
    } elseif ($Guid_role=='4' || $Guid_role=='5') {//salesrep & salesmgr
        $userQ = "SELECT Guid_user FROM tblsalesrep WHERE Guid_user=:Guid_user";
        $isUserExists = $db->row($userQ, array('Guid_user'=>$Guid_user));
        if($isUserExists){
            updateTable($db, 'tblsalesrep', $userDetails, array('Guid_user'=>$Guid_user));
        }else{
            $userDetails['Guid_user'] = $Guid_user;
            insertIntoTable($db, 'tblsalesrep', $userDetails);
        } 
    }
    
}

function verify_input(&$error) {	
	if (strlen($_POST['from_date']) && (!strlen($_POST['to_date']))) {
		$error['to_date'] = 1;
	} elseif ((!strlen($_POST['from_date'])) && strlen($_POST['to_date'])) {
		$error['from_date'] = 1;
	} elseif (strlen($_POST['from_date']) && strlen($_POST['to_date']) && (strtotime($_POST['to_date']) < strtotime($_POST['from_date']))) {		
		$error['from_date'] = 1;
		$error['to_date'] = 1;
	}
}
function getUrlConfigurations($db, $userID){ 
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
                    WHERE tblurlconfig.Guid_user=:Guid_user 
                    ORDER BY tblurlconfig.id DESC LIMIT 5";
    $urlConfigs = $db->query($query, array("Guid_user"=>$userID));
 
    return $urlConfigs;
}

function get_active_providers($db, $field, $id){
    $queryProviders = "SELECT * FROM tblprovider WHERE $field=:id";
    $providers = $db->query($queryProviders, array("id"=>$id));
    return $providers;
}
function get_provider_info($db, $provider_guid){
    $queryProvider = "SELECT * FROM tblprovider WHERE Guid_provider=:id";
    $provider = $db->row($queryProvider, array("id"=>$provider_guid));
    return $provider;
}
function get_provider_user_info($db, $provider_guid){
    $queryProvider = "SELECT p.*, u.Guid_user, u.email AS provider_email, u.password AS provider_password FROM tblprovider p 
                        LEFT JOIN tbluser u ON p.Guid_user=u.Guid_user
                        WHERE Guid_provider=:id";
    $provider = $db->row($queryProvider, array("id"=>$provider_guid));
    return $provider;
}
function get_row($db, $table, $where=1){
    $query = "SELECT * FROM $table $where";
    $row = $db->query($query);
    return $row;
}
function getDevicesBySalesrepID($db,$Guid_salesrep){
    $query = "SELECT tbldevice.*, tbldeviceinv.* FROM tbldevice "
                        . " LEFT JOIN `tbldeviceinv` ON tbldevice.deviceid  = tbldeviceinv.deviceid"
                        . " WHERE url_flag = :url_flag"
                        . " AND Guid_salesrep =:Guid_salesrep"
                        . " ORDER BY tbldeviceinv.serial_number DESC";
    $result = $db->query($query, array("url_flag"=>'1', "Guid_salesrep"=>$Guid_salesrep));
    return $result;
}
function getDevicesWithSalesRepInfo($db, $flag=FALSE){
    $query = "SELECT "
                    . "tbldevice.device_name, "
                    . "tbldeviceinv.id, tbldeviceinv.deviceid, tbldeviceinv.serial_number, tbldeviceinv.comment, tbldeviceinv.inservice_date, tbldeviceinv.outservice_date, "
                    . "tblsalesrep.first_name, tblsalesrep.last_name "
                    . "FROM tbldevice LEFT JOIN `tbldeviceinv` "
                    . "ON tbldevice.deviceid = tbldeviceinv.deviceid "
                    . "LEFT JOIN `tblsalesrep` "
                    . "ON tbldeviceinv.Guid_salesrep = tblsalesrep.Guid_salesrep ";    
    if($flag){
        $query.= "WHERE url_flag = :url_flag ";
    }
    $query.= "ORDER BY tblsalesrep.first_name, tblsalesrep.last_name, tbldevice.device_name, tbldeviceinv.serial_number  DESC";
    
    if($flag){
        $result = $db->query($query, array("url_flag"=>$flag));
    }else{
        $result = $db->query($query);
    }
    
    return $result;
}
function getDeviceInvsWithSalesRepInfo($db, $flag=FALSE){
    $query = "SELECT "
                    . "tbldevice.device_name, "
                    . "tbldeviceinv.id, tbldeviceinv.deviceid, tbldeviceinv.serial_number, tbldeviceinv.comment, tbldeviceinv.inservice_date, tbldeviceinv.outservice_date, "
                    . "tblsalesrep.first_name, tblsalesrep.last_name "
                    . "FROM tbldeviceinv LEFT JOIN `tbldevice` "
                    . "ON tbldevice.deviceid = tbldeviceinv.deviceid "
                    . "LEFT JOIN `tblsalesrep` "
                    . "ON tbldeviceinv.Guid_salesrep = tblsalesrep.Guid_salesrep ";    
    if($flag){
        $query.= "WHERE url_flag = :url_flag ";
    }
    $query.= "ORDER BY tblsalesrep.first_name, tblsalesrep.last_name, tbldevice.device_name, tbldeviceinv.serial_number  DESC";
    
    if($flag){
        $result = $db->query($query, array("url_flag"=>$flag));
    }else{
        $result = $db->query($query);
    }
    
    return $result;
}
function getDeviceinves($db){
    $query = "SELECT tbldeviceinv.*, tbldevice.device_name, tbldevice.url_flag"
            . " FROM tbldeviceinv "
            . " LEFT JOIN `tbldevice` ON tbldeviceinv.deviceid = tbldevice.deviceid";
    $result = $db->query($query);
    return $result;
}
function getAccountAndSalesrep($db, $accountGuid=NULL, $getRow=NULL){
    $query = "SELECT tblaccount.*, "
            . "tblsalesrep.Guid_salesrep, tblsalesrep.first_name AS salesrepFName, tblsalesrep.last_name AS salesrepLName,"
            . "tblsalesrep.email AS salesrepEmail, tblsalesrep.phone_number AS salesrepPhone , "
            . "tblsalesrep.region AS salesrepRegion, tblsalesrep.title AS salesrepTitle, "
            . "tblsalesrep.address AS salesrepAddress, tblsalesrep.city AS salesrepCity, "
            . "tblsalesrep.state AS salesrepState, tblsalesrep.zip AS salesrepZip, tblsalesrep.photo_filename AS salesrepPhoto "
            . "FROM tblaccount "
            . "LEFT JOIN tblaccountrep ON tblaccount.Guid_account = tblaccountrep.Guid_account "
            . "LEFT JOIN tblsalesrep ON tblsalesrep.Guid_salesrep=tblaccountrep.Guid_salesrep";          
    
    if($accountGuid){
        $query .= " WHERE tblaccount.Guid_account=:id";
        $result = $db->query($query, array("id"=>$accountGuid));
    }
    elseif ($getRow) {
        $result = $db->row($query);
    }else{
        $result = $db->query($query);
    }
    
    return $result;
}

function getProviderSalesRep($db, $providerID) {
    $query = "SELECT 
	p.`Guid_provider`, p.`account_id`, 
	a.`Guid_account`, asp.`Guid_salesrep`,
	s.* 
	FROM `tblprovider` p
        LEFT JOIN `tblaccount` a
        ON p.account_id = a.account
        LEFT JOIN `tblaccountrep` asp
        ON a.`Guid_account` = asp.`Guid_account`
        LEFT JOIN `tblsalesrep` s
        ON asp.`Guid_salesrep` = s.`Guid_salesrep`
        WHERE p.`Guid_user` =:providerID";
    $row = $db->row($query, array('providerID'=>$providerID));
    
    return $row;
}


function ifAccountIDValid($accountID, $Guid_account = NULL){
    $db = new Db(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
    $query = "";
    if($Guid_account){
       $query = "SELECT `account` FROM tblaccount WHERE Guid_account<>:Guid_account AND account=:account";
       $row = $db->row($query, array("Guid_account"=>$Guid_account,"account"=>$accountID));
    } else {
        $query = "SELECT `account` FROM tblaccount WHERE account=:account";
        $row = $db->row($query, array("account"=>$accountID));
    } 
    if($row['account']==$accountID){
        return FALSE;
    } 
    return TRUE;
}
function ifDeviceSerialValid($serial_number, $deviceID=NULL){
    $db = new Db(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
    $query = "";
    if($serial_number){
       $query = "SELECT `serial_number` FROM tbldeviceinv WHERE id<>:id AND serial_number=:serial_number";
       $row = $db->row($query, array("id"=>$deviceID, 'serial_number'=>$serial_number));
    } else {
        $query = "SELECT `serial_number` FROM tbldeviceinv WHERE serial=:serial";
        $row = $db->row($query, array("serial"=>$serial_number));
    } 
    if($row['serial_number']==$serial_number){
        return FALSE;
    } 
    return TRUE;
}

function getAccessRoleByKey($keyID){
    $db = new Db(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
    $query = "SELECT * FROM `tblaccessbyroles` WHERE key_id=:key";
    $row = $db->row($query, array("key"=>$keyID));
    return $row;
}



function getAcount($db, $accountId){
    $query = "SELECT * FROM tblaccount WHERE account=:id";
    $row = $db->query($query, array("id"=>$accountId));
    return $row;
}

function get_field_value($db, $table, $extractFieldValue, $where=1){
    $field = $db->row("SELECT $extractFieldValue FROM `".$table."` $where");
    return $field;
}
function get_value($db, $table, $extractFieldValue, $where=array()){
    
    $field = $db->row("SELECT $extractFieldValue FROM `".$table."` $where");
    return $field;
}


function validateProviderId($db, $data){
    extract($data);    
    $provider_id = $_POST['provider_id']; 
    $providers = array();
    $query = "";
    if($action=='update'){
        $query = "SELECT `provider_id` FROM tblprovider WHERE `provider_id`=$provider_id AND Guid_provider<>$Guid_provider";
    } else {
        if($provider_id!=""){
            $query = "SELECT `provider_id` FROM tblprovider WHERE `provider_id`=$provider_id ";
        }
    }
    if($query){
        $providers = $db->query($query);
    } else {
        $providers= FALSE;
    }

    if(!$providers){
        return array('status'=>1, 'msg'=>'Provider ID Valid.');
    } else {
        return array('status'=>0, 'msg'=>'Provider ID already exists.');
    }
       
    
}

function validateSettings($db, $data){
    $isMatching = 0;
    extract($data);
    $query = "SELECT * FROM tblurlconfig "
            . "WHERE Guid_user=:currentUserId "
            . "AND geneveda=:geneveda "
            . "AND account=:account "
            . "AND location=:location "
            . "AND pin=:pin "
            . "AND device_id=:device_id";
    $row = $db->query($query, array(
                                    "currentUserId" => $currentUserId,
                                    "geneveda" => $geneveda,
                                    "account" => $account,
                                    "location" => $location,
                                    "account" => $account,
                                    "pin" => $pin,
                                    "device_id" => $device_id,
                    ));
                    
    if(empty($row)){
        $isMatching = 1;
    } 
    
    return $isMatching;
}

function saveUrlSettings($db, $data){
    extract($data);    
    $query = "INSERT INTO `".DB_PREFIX."urlconfig`"
            . "(Guid_user, geneveda, account, location, pin, device_id) VALUES"
            . "(:Guid_user,:geneveda, :account, :location, :pin, :device_id )";
    $insert = $db->query(
                $query, 
                array(
                    "Guid_user"=>"$currentUserId",
                    "geneveda"=>"$geneveda", 
                    "account"=>"$account", 
                    "location"=>"$location",
                    "pin"=>"$pin",
                    "device_id"=>"$device_id"                    
                ));
    
    if($insert > 0 ) {
        return array(
            'message'=>'Table View Succesfully created!',
            'status'=>'1'
        );
    } else {
        return array(
            'message'=>'Insert Issue',
            'status'=>'0'
        );
    } 
}

function insertDevice($db, $data){
    extract($data);    
    $query = "INSERT INTO `".DB_PREFIX."device`"
            . "(serial_number, Guid_salesrep, device_type, device_name, url_flag) VALUES"
            . "(:serial_number,:Guid_salesrep, :device_type, :device_name, :url_flag)";
    $insert = $db->query(
                $query, 
                array(
                    "serial_number"=>"$serial_number",
                    "Guid_salesrep"=>"$Guid_salesrep", 
                    "device_type"=>"$device_type", 
                    "device_name"=>"$device_name",
                    "url_flag"=>"$url_flag"                   
                ));    
    if($insert > 0 ) {
        return array(
            'message'=>'Table View Succesfully created!',
            'status'=>'1'
        );
    } else {
        return array(
            'message'=>'Insert Issue',
            'status'=>'0'
        );
    } 
}

function updateDevice($db, $data){
    //var_dump($data);
    extract($data);
    if($id && $id!=''){
        $query = "UPDATE `".DB_PREFIX."device` SET serial_number=:serial_number, Guid_salesrep=:Guid_salesrep, device_type=:device_type, device_name=:device_name, url_flag=:url_flag WHERE id = :id";
        $update = $db->query($query, array("serial_number"=>"$serial_number", "Guid_salesrep"=>"$Guid_salesrep", "device_type"=>"$device_type", "device_name"=>"$device_name", "url_flag"=>"$url_flag", "id"=>"$id"));

        if($update) {
          return array(
                  'message'=>'Table View Succesfully updated!',
                  'status'=>'1'
                );
        } else {
          return array(
                  'message'=>'Update Issue',
                  'status'=>'0'
                );
        }
    }
}

/**
 * 
 * ex. getTableRow($db, 'tblaccountrep', array('Guid_account'=>$_POST['Guid_account']))
 * @param type $db
 * @param type $table
 * @param type $where
 * @return type
 */
function getTableRow($db, $table, $where){
    
    $fields = "";
    $fieldsFlag = "";
    $executeArray = array();
    foreach ($where as $key => $val) {
        $whereStr = " WHERE `$key`=:$key";
        $executeArray["$key"] = $val;
    }
    
    $query = "SELECT * FROM `".$table."` $whereStr";
   
    $result = $db->row($query, $executeArray);
    return $result;    
}

/**
 * Update Table function
 * @param type $db
 * @param type $table - string table name
 * @param type $data - array which would be updated 
 * @param type $where - array with key and value 
 * @return type array with status and message
 */
function updateTable($db, $table, $data, $where ){   
    $updateFields = "";
    $whereStr = "";
    $executeArray = array();
    foreach ($data as $key => $val) {
        $updateFields .= "`$key`=:$key, ";
        $executeArray["$key"] = $val;
    }
    $updateFields = rtrim($updateFields,", ");   
    
    foreach ($where as $key => $val) {
        $whereStr = " WHERE `$key`=:$key";
        $executeArray["$key"] = $val;
    }     
    $query = "UPDATE `$table` SET $updateFields $whereStr";
    $update = $db->query($query, $executeArray);
    
    return $update;  
}
/**
 * Insert Table function
 * @param type $db
 * @param type $table - string table name
 * @param type $data - array which would be updated 
 * @param type $where - array with key and value 
 * @return type array with status and message
 */
function insertIntoTable($db, $table, $data, $msg=NULL ){   
    $insertFields = "";
    $insertFieldsFlag = "";
    $executeArray = array();
    foreach ($data as $key => $val) {
        $insertFields .= "$key, ";
        $insertFieldsFlag .= ":$key, ";
        $executeArray["$key"] = $val;
    }
    $insertFields = rtrim($insertFields,", ");   
    $insertFieldsFlag = rtrim($insertFieldsFlag,", "); 
       
    $query = "INSERT INTO `$table` ($insertFields) VALUES ($insertFieldsFlag)";
    $insert = $db->query( $query, $executeArray);    
    if($insert > 0 ) {
        $messageSuccess = isset($msg['success']) ? $msg['success'] : 'Data Succesfully created!';
        return array(
            'insertID' => $db->lastInsertId(),
            'message'=>$messageSuccess,
            'status'=>'1'
        );
    } else {
        $messageError = isset($msg['error']) ? $msg['error'] : 'Insert Issue';
        return array(
            'message'=>$messageError,
            'status'=>'0'
        );
    }    
}
/**
 * deleteRowByField where
 * @param type $db
 * @param type $table
 * @param type $where
 * @return boolean
 */
function deleteRowByField($db, $table, $where){
    $whereStr = "";
    foreach ($where as $key => $val) {
        $whereStr = " WHERE `$key`=:$key";
        $executeArray["$key"] = $val;
    }
    $query = "DELETE FROM `$table` $whereStr";
    $delete = $db->query($query, $executeArray);
    return FALSE;
}
function getLastUrlConfig($db){
    $lastConfig = $db->row("SELECT * FROM `tblurlconfig` ORDER BY id DESC");
    
    return $lastConfig;
}

function deleteUrlConfig($db, $id){
    $query = "DELETE FROM `".DB_PREFIX."urlconfig` WHERE id=:id";
    $delete = $db->query($query, array( "id"=>"$id"));
    return FALSE;
}

function deleteById($db, $table, $id){
    $query = "DELETE FROM `$table` WHERE id=:id";
    $delete = $db->query($query, array( "id"=>"$id"));
    return FALSE;
}

function deleteByField($db, $table, $field, $value){
    $query = "DELETE FROM `$table` WHERE $field=:$field";
    $delete = $db->query($query, array( "$field"=>"$value"));
    return FALSE;
}

function deleteAccountById($db, $table, $id){
    $query = "DELETE FROM `$table` WHERE Guid_account=:id";
    $delete = $db->query($query, array( "id"=>"$id"));
    return FALSE;
}


function uploadFile($uploadName, $uploadFolder=NULL){
    if(!$uploadFolder){
        $target_dir = "images/practice/";
    } else {
        $target_dir = $uploadFolder;
    }
    
    $target_file = $target_dir . basename($_FILES["$uploadName"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    $message = array();
    // Check if image file is a actual image or fake image
    if(isset($_FILES)) {
        $check = getimagesize($_FILES["$uploadName"]["tmp_name"]);
        if($check !== false) {
            $message['msg'] = "File is an image - " . $check["mime"] . ".";
            $message['status'] = "1";
            $uploadOk = 1;
        } else {
            $message['msg'] =  "File is not an image.";
            $message['status'] = "0";
            $uploadOk = 0;
        }
    }
    // Check if file already exists
    if (file_exists($target_file)) {
        $message['msg'] = "Sorry, file <span>".$_FILES["$uploadName"]["name"]."</span> already exists.";
        $message['status'] = "0";
        $uploadOk = 0;
    }
    // Check file size
    if ($_FILES["$uploadName"]["size"] > 500000) {
        $message['msg'] =  "Sorry, your file is too large.";
        $message['status'] = "0";
        $uploadOk = 0;
    }
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        $message['msg'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $message['status'] = "0";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $message['status'] = "0";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["$uploadName"]["tmp_name"], $target_file)) {
            $message['msg'] = "The file ". basename( $_FILES["$uploadName"]["name"]). " has been uploaded.";
            $message['status'] = "1";            
        } else {
            $message['msg'] = "Sorry, there was an error uploading your file.";
            $message['status'] = "0";
        }
    }
    
    return $message;
}

function getEventSchedule($db,$id=""){
    if($id){
        $query = "SELECT evt.id, evt.title, evt.start_event, evt.end_event,"
                . " CONCAT(salerep.first_name, ' ', salerep.last_name) as salesrep,"
                . " acct.logo, acct.account, acct.name, evt.comments"
                . "  FROM tblevents evt "
                . "LEFT JOIN tblsalesrep salerep ON evt.salesrepid = salerep.Guid_salesrep "
                . "LEFT JOIN tblaccount acct ON evt.accountid = acct.Guid_account "
                . "WHERE evt.id =:id "
                . "ORDER BY evt.id";
        
        $result = $db->query($query, array("id"=>$id));
    }
    else{
        $query = "SELECT evt.id, evt.title, evt.start_event, evt.end_event,"
                . " CONCAT(salerep.first_name, ' ', salerep.last_name) as salesrep,"
                . " acct.logo, acct.account, acct.name, evt.comments"
                . "  FROM tblevents evt "
                . "LEFT JOIN tblsalesrep salerep ON evt.salesrepid = salerep.Guid_salesrep "
                . "LEFT JOIN tblaccount acct ON evt.accountid = acct.Guid_account "
                . "ORDER BY evt.id";
        $result = $db->query($query);
    }
    
    return $result;
}

