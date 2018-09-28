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
 * Generates random string.
 *
 * @param int $length
 * @return string
 */
function str_random($length = 16)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
	$randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}

/**
*  Generate a token string
*
* @return $str
*/
function generateTokenString(){
   // generate as random of a token as possible for lower PHP versions
   $str = sha1(uniqid(sha1(PASSWORD_SALT), true) . time() . str_random(20));

   return $str;
}
function escape($str){
    switch (gettype($str))
    {
	case 'string' : $str = addslashes(stripslashes($str));
	break;
	case 'boolean' : $str = ($str === FALSE) ? 0 : 1;
	break;
	default : $str = ($str === NULL) ? 'NULL' : $str;
	break;
    }

    return $str;
}
function Clean($str) {
    if (is_array($str)) {
	$return = array();
	foreach ($str as $k => $v) {
	    $return[Clean($k)] = Clean($v);
	}
	return $return;
    } else {
	$str = @trim($str);
	if (get_magic_quotes_gpc()) {
	    $str = stripslashes($str);
	}
	return mres($str);
    }
}
function CleanXSS($str) {
    if (is_array($str)) {
	$return = array();
	foreach ($str as $k => $v) {
	    $return[CleanXSS($k)] = CleanXSS($v);
	}
	return $return;
    } else {
	$str = @trim($str);
	$str = preg_replace('#<script(.*?)>(.*?)</script(.*?)>#is', '', $str);
	$str = preg_replace('#<style(.*?)>(.*?)</style(.*?)>#is', '', $str);
	if (get_magic_quotes_gpc()) {
	    $str = stripslashes($str);
	}
	return mres($str);
    }
}
function mres($value) {
    $search = array("\\", "\x00", "\n", "\r", "'", '"', "\x1a");
    $replace = array("\\\\", "\\0", "\\n", "\\r", "\'", '\"', "\\Z");

    //return str_replace($search, $replace, $value);
    return $value;
}
function remove_accent($str) {
    $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', '‘');
    $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', '\'');
    return str_replace($a, $b, $str);
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
 * Deactivate User by user id
 * @param type $db
 * @param type $Guid_user
 * @return boolean
 */
function deactivateUser($db, $Guid_user){
    updateTable($db, 'tbluser', array('status'=>'0'), array('Guid_user'=>$Guid_user));
    return TRUE;
}
/**
 * Delete User By given ID
 * @param type $userID
 * @return boolean
 */
function deleteUserByID($db, $userID){
    deleteByField($db, 'tbladmins','Guid_user', $userID);
    deleteByField($db, 'tblsalesrep','Guid_user', $userID);
    deleteByField($db, 'tblprovider','Guid_user', $userID);
    deleteByField($db, 'tblpatient','Guid_user', $userID);
    //delete from roles
    deleteByField($db, 'tbluserrole','Guid_user', $userID);
    //delet from users table
    deleteByField($db, 'tbluser','Guid_user', $userID);
    return TRUE;
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

function getUserFullInfo($db, $userID){
    $query = "SELECT * FROM tblrole r LEFT JOIN tbluserrole u ON r.Guid_role = u.Guid_role WHERE u.Guid_user = $userID";

    $userInfo = $db->row($query);
    $result = "";

    if ($userInfo['role']=='Sales Rep' || $userInfo['role']=='Sales Manager') {
	$query = "SELECT * FROM `tblsalesrep` WHERE Guid_user=:Guid_user";
	$salesrepInfo = $db->row($query, array("Guid_user"=>$userID));
	if(!empty($salesrepInfo)){
	    $result = $salesrepInfo;
	}
    } elseif ($userInfo['role']=='Physician') {
	$query = "SELECT * FROM `tblprovider` WHERE Guid_user=:Guid_user";
	$providerInfo = $db->row($query, array("Guid_user"=>$userID));
	if(!empty($providerInfo)){
	    $result = $providerInfo;
	}
    } elseif ($userInfo['role']=='Admin') {
	$query = "SELECT * FROM `tbladmins` WHERE Guid_user=:Guid_user";
	$providerInfo = $db->row($query, array("Guid_user"=>$userID));
	if(!empty($providerInfo)){
	    $result = $providerInfo;
	}
    } else {

	$query = "SELECT *, firstname AS first_name, lastname AS last_name FROM `tblpatient` WHERE Guid_user=:Guid_user";
	$patientInfo = $db->row($query, array("Guid_user"=>$userID));
	if(!empty($patientInfo)){
	    $result = $patientInfo;
	}
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
    $query = "SELECT * FROM `tbl_mdl_options` WHERE key_id=:key";
    $accessRole = $db->row($query, array("key"=>$accessKey));
    if(isset($accessRole['value'])){
	$accessRoleIDs = unserialize($accessRole['value']);
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
    $roleIds = unserialize($accessRole['value']);
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
    $roleIds = unserialize($accessRole['value']);
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
	    $updateData = array('value'=>serialize($saveData), 'type'=>'table');
	    updateTable($db,'tbl_mdl_options', $updateData, array('key_id'=>$tableKey));

	}else{
	    //insert option
	    $insertData = array('key_id'=>$tableKey, 'value'=>serialize($saveData), 'type'=>'table');
	    insertIntoTable($db, 'tbl_mdl_options', $insertData);
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
		$updateData = array('value'=>serialize($saveData), 'type'=>'page');
		updateTable($db,'tbl_mdl_options', $updateData, array('key_id'=>$pageKey));

	    }else{
		//insert option
		$insertData = array('key_id'=>$pageKey, 'value'=>serialize($saveData), 'type'=>'page');
		insertIntoTable($db, 'tbl_mdl_options', $insertData);
	    }
	}
    }
}
function getOption($db, $key){
    $query = "SELECT * FROM `tbl_mdl_options` WHERE key_id=:key_id";
    $result = $db->row($query, array('key_id'=>$key));

    return $result;
}
function setOption($db, $key, $val, $type='page'){
    //check if key exists
    $checkKey = getOption($db, $key);
    if($checkKey){ //update existing
	$where = array('key_id'=>$key);
	updateTable($db, 'tbl_mdl_options', array('key_id'=>$key,'value'=>$val,'type'=>$type), $where);
    } else { //insert new key and value
	insertIntoTable($db, 'tbl_mdl_options', array('key_id'=>$key,'value'=>$val,'type'=>$type));
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
function getUserDetails($db, $userRole, $userID, $patientID=""){
    $userDetail = "";
    $q = "";
    if($userRole == 'Admin'){
	$q = "SELECT first_name, last_name, photo_filename FROM tbladmins";
    } elseif ($userRole == 'Sales Rep' || $userRole == 'Sales Manager') {
	$q = "SELECT first_name, last_name, photo_filename FROM tblsalesrep";
    } elseif ($userRole == 'Physician') {
	$q = "SELECT first_name, last_name, photo_filename FROM tblprovider";
    } elseif ($userRole == 'Patient') {
	$q = "SELECT firstname as first_name, lastname as last_name FROM tblpatient";
    }

    if($q!=""){
	$q .= " WHERE Guid_user=:Guid_user";
	$userDetail = $db->row($q, array("Guid_user"=>$userID));
    } else {//when patient is not exists on users table
	if($patientID!=""){
	    $q = "SELECT firstname as first_name, lastname as last_name FROM tblpatient  WHERE Guid_patient=:Guid_patient";
	    $userDetail = $db->row($q, array("Guid_patient"=>$patientID));
	}
    }

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
/**
 * Move user to proper table if user role is changed
 * @param type $db
 * @param type $thisRole
 * @param type $prevRole
 */
//this function under development
function moveUserData($db,$userData,$thisRole,$prevRole,$Guid_user,$Guid_patient=''){
    var_dump($userData);die;
    if($thisRole != $prevRole){

	if($thisRole=='1'){ //Admin

	} elseif ($thisRole=='2') { //Physician

	} elseif ($thisRole=='3') { //Patient

	} elseif ($thisRole=='4' || $prevRole=='5') { //Sales Rep OR Sales Manager

	}

	if($prevRole=='1'){ //Admin

	} elseif ($prevRole=='2') { //Physician

	} elseif ($prevRole=='3') { //Patient

	} elseif ($prevRole=='4' || $prevRole=='5') { //Sales Rep OR Sales Manager

	}


    }
    return FALSE;
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
    if($row['account']==$accountID || $accountID=="0"){
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
    $query = "SELECT * FROM `tbl_mdl_options` WHERE key_id=:key";
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


function validateProviderId($db, $data, $provider_id){
    extract($data);
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
/**
 * format Money Function
 * @param type $number
 * @return string
 */
function formatMoney($number){
    $pieces = explode(".", $number);
    $startPiece = $pieces[0];
    if(isset($pieces[1])){
	$endPiece = $pieces[1];
    }
    $n=number_format(abs($startPiece));
    $newNum = $n;
    if(isset($endPiece)&&$endPiece!=""){
	$newNum .= ".".$endPiece;
	if(strlen($endPiece)=='1'){
	    $newNum .= "0";
	}
    } else {
	$newNum .= ".00";
    }

    return $newNum;
}



function get_status_names($db, $Guid_status, $Guid_user, $Log_group){
    $statusStr = "";
    $ids = "";
    $statusIds = array();
    //first $Guid_status is parent so we need it in first item of array
    array_push($statusIds, $Guid_status);

    $nestedIDs = get_nested_status_ids($db, $Guid_status, $Guid_user, $Log_group);

    foreach($nestedIDs as $k=>$v){
	array_push($statusIds, $v['Guid_status']);
    }
    if(!empty($statusIds)){
	foreach ($statusIds as $k=>$v){
	    $ids .= $v.', ';
	}
	$ids = rtrim($ids, ', ');
	//var_dump("SELECT Guid_status, status FROM tbl_mdl_status WHERE Guid_status IN($ids)");
	$statuses = $db->query("SELECT Guid_status, status FROM tbl_mdl_status WHERE Guid_status IN($ids) ");
	foreach ($statuses as $k=>$v){
	    if($k==0 ){
		$statusStr .= $v['status'];
		if(count($statuses)>1){
		    $statusStr .= ": ";
		}
	    } else {
		$statusStr .= $v['status'].", ";
	    }
	}
    }
    $statusStr = rtrim($statusStr, ', ');
    return $statusStr;
}

function get_nested_status_ids($db, $Guid_status, $Guid_user, $Log_group) {

    $statusIDS = array();
    $statQ = "SELECT sl.Guid_status FROM tbl_mdl_status_log sl
			    LEFT JOIN tblpatient p ON sl.Guid_patient=p.Guid_user
			    Left Join tbl_mdl_status s ON sl.Guid_status=s.Guid_status
			    WHERE sl.Guid_user=$Guid_user AND s.parent_id=$Guid_status AND Log_group=$Log_group";
    //var_dump($statQ);
    $statuses = $db->query($statQ);

    if ( $statuses ) {
	array_push($statusIDS, $statuses['0']);
	foreach ( $statuses as $status ) {
	    $parentID = $status['Guid_status'];
	    $checkCildren = $db->query("SELECT sl.Guid_status FROM tbl_mdl_status_log sl
			    LEFT JOIN tblpatient p ON sl.Guid_patient=p.Guid_user
			    Left Join tbl_mdl_status s ON sl.Guid_status=s.Guid_status
			    WHERE sl.Guid_user=$Guid_user  AND s.parent_id=$parentID  AND Log_group=$Log_group");


	    if ( !empty($checkCildren) ) {
		array_push($statusIDS, $checkCildren['0']);
		get_nested_status_ids( $db, $status['Guid_status'], $Guid_user, $Log_group);
	    }
	}
    }
    return $statusIDS;
    //return $statusesIDs;
}


function get_selected_log_dropdown($db, $Log_group, $parent="0") {
    $selectedStatuses = $db->query("SELECT Guid_status FROM tbl_mdl_status_log WHERE `Log_group`= ".$Log_group);
    $content = "";
    foreach ($selectedStatuses as $k => $v){
	$getParent = $db->row("SELECT parent_id FROM tbl_mdl_status WHERE Guid_status=:Guid_status", array('Guid_status'=>$v['Guid_status']));
	$statuses = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$getParent['parent_id']." AND visibility='1' ORDER BY order_by ASC, Guid_status ASC");
	$content .= '<div class="f2 valid ">
			<div class="group">
			    <select data-parent="'.$parent.'" required class="status-dropdown" name="status[]" id="">
				<option value="0">Select Status</option>';
				    if ( $statuses ) {
					foreach ( $statuses as $status ) {
					    $checkCildren = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$status['Guid_status']);

					    $optionClass = '';
					    if ( !empty($checkCildren) ) {
						$optionClass = 'has_sub_menu';
					    }
					    $selected = isStatusSelected($status['Guid_status'],$selectedStatuses) ? " selected": "";
					    $content .= "<option ".$selected." value='".$status['Guid_status']."' class='".$optionClass."'>".$status['status'];

					    $content .= '</option>';
					}
				    }
	$content .= '</select><p class="f_status"><span class="status_icons"><strong></strong></span>';
	$content .= '</p></div></div>';
    }
    return $content;
}

function isStatusSelected($status, $selectedStatuses){
    foreach ($selectedStatuses as $k=>$v){
	 if($status == $v['Guid_status']){
	    return TRUE;
	}
    }

    return FALSE;
}

function saveStatusLog($db,$statusIDs, $statusLogData){
    $i = 1;
    foreach ($statusIDs as $k=>$status){
	$statusLogData ['Guid_status'] = $status;

	if($i==1){
	    $insertStatusLog = insertIntoTable($db, 'tbl_mdl_status_log', $statusLogData);
	    $insertID = $insertStatusLog['insertID'];
	    if($insertID!=""){
		$LogGroupID=$insertID;
		$LogGroupData['Log_group']=$insertID;
		$where['Guid_status_log']=$insertID;
		//setting first insert id as a log group id
		updateTable($db, 'tbl_mdl_status_log', $LogGroupData, $where);
	    }
	    $i++;
	} else {
	    //after first insert seting first insert id as logGroupID
	    $statusLogData['Log_group']=$LogGroupID;
	    $insertStatusLog = insertIntoTable($db, 'tbl_mdl_status_log', $statusLogData);
	}
    }
    return TRUE;
}

function updateCurrentStatusID($db, $Guid_patient,$visibility=TRUE){
    //SELECT statuses.*, statuslogs.`Guid_status_log`, statuslogs.`Guid_patient`, statuslogs.`Log_group`, statuslogs.`order_by`, statuslogs.`Date`
    $q  =   "SELECT *
	    FROM `tbl_mdl_status` statuses
	    LEFT JOIN `tbl_mdl_status_log` statuslogs
	    ON statuses.`Guid_status`= statuslogs.`Guid_status` ";
    if($visibility){
    $q  .=  "WHERE statuses.`visibility`='1' ";
    }
    $q  .=  "AND statuslogs.`Guid_status_log`<>''
	    AND statuses.parent_id='0'
	    AND statuslogs.Guid_patient=$Guid_patient
	    ORDER BY statuslogs.`Date` DESC, statuses.order_by DESC LIMIT 1";
    $result = $db->row($q);

    updateTable($db, 'tbl_mdl_status_log', array('currentstatus'=>'N'), array('Guid_patient'=>$Guid_patient));
    updateTable($db, 'tbl_mdl_status_log', array('currentstatus'=>'Y'), array('Log_group'=>$result['Log_group']));

    return $result['Guid_status_log'];
}

function get_status_dropdown($db, $parent='0') {
    $statuses = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$parent." AND visibility='1' ORDER BY order_by ASC, Guid_status ASC");

    $content = '<div class="f2  ">
		    <div class="group">
			<select data-parent="'.$parent.'" required class="status-dropdown" name="status[]" id="">
			    <option value="0">Select Status</option>';
    if ( $statuses ) {
	foreach ( $statuses as $status ) {
	    $checkCildren = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$status['Guid_status']);

	    $optionClass = '';
	    if ( !empty($checkCildren) ) {
		$optionClass = 'has_sub_menu';
	    }
	    $content .= "<option value='".$status['Guid_status']."' class='".$optionClass."'>".$status['status'];

	    $content .= '</option>';
	}
    }
    $content .= '</select><p class="f_status"><span class="status_icons"><strong></strong></span>
			    </p></div></div>';

    return $content;
}

function get_nested_status_dropdown($db, $parent = 0) {
    $statuses = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$parent." AND visibility='1' ORDER BY order_by ASC, Guid_status ASC");

    $content = '<select class="no-selection" name="parent_id" id="parent">
			    <option value="0">Select Status Parent</option>';
    if ( $statuses ) {
	foreach ( $statuses as $status ) {
	    $checkCildren = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$status['Guid_status']);

	    $optionClass = '';
	    if ( !empty($checkCildren) ) {
		$optionClass = 'has_sub_menu';
	    }
	    $content .= "<option value='".$status['Guid_status']."' class='".$optionClass."'>".$status['status'];
	    if ( !empty($checkCildren) ) {
		$content .= get_option_of_nested_status( $db, $status['Guid_status'], "-&nbsp;" );
	    }
	    $content .= '</option>';
	}
    }
    $content .= "</select>";

    return $content;
}
function get_option_of_nested_status($db, $parent = 0,  $level = '', $checkboxes=FALSE) {
    $statuses = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$parent." ORDER BY order_by ASC, Guid_status ASC");
    if ( $statuses ) {
	$content ='';
	$prefix = 0;
	foreach ( $statuses as $status ) {

	    $checkCildren = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$status['Guid_status']);
	    $optionClass = '';

	    if ( !empty($checkCildren) ) {
		$optionClass = 'has_sub';
	    }
	    if($checkboxes){
		//used for mdl-stat-details-config.php config field popup status select list
		$getOption = getOption($db, 'stat_details_config');
		$fieldId = $_GET['field_id'];
		$fieldOptions = unserialize($getOption['value']);
		$thisStatuses = isset($fieldOptions[$fieldId]['statuses'])? $fieldOptions[$fieldId]['statuses'] : "";
		$isSelected = "";
		if(isset($fieldOptions[$fieldId]) && $thisStatuses!=""){
		    $isSelected = in_array($status['Guid_status'], $thisStatuses)? " checked": "";
		}
		$content .= $level."<input ".$isSelected." type='checkbox' name=stauses[] value='".$status['Guid_status']."' class='".$optionClass."'> " .$status['status'].'</br>';
	    }else{
		$content .= "<option value='".$status['Guid_status']."' class='".$optionClass."'>".$level . " " .$status['status'];
	    }
	    if ( !empty($checkCildren) ) {
		$prefix .= '-';
		if(!$checkboxes){
		    $content .= get_option_of_nested_status( $db, $status['Guid_status'], $level . "-&nbsp;" );
		} else {
		    $content .= get_option_of_nested_status( $db, $status['Guid_status'], $level . "-&nbsp;", TRUE);
		}
	    }
	    if(!$checkboxes){
		$content .= '</option>';
	    }
	}
    }

    return $content;
}

function get_nested_ststus_editable_rows($db, $parent = 0, $level = '') {
    $statuses = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$parent." ORDER BY order_by ASC, Guid_status ASC");
    $content = "";
    if ( $statuses ) {
	$content ='';
	$prefix = 0;

	foreach ( $statuses as $status ) {
	    echo "<tr>";
	    $checkCildren = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$status['Guid_status']);
	    $optionClass = '';

	    if ( !empty($checkCildren) ) {
		$optionClass = 'has_sub';
	    }

	    $content .= "<td  class='".$optionClass."'>".$level . " ";
	    $content .= "<input type='hidden' name=status[Guid_status][] value='".$status['Guid_status']."' />";
	    $content .= "<input type='text' name=status[name][] value='".$status['status']."' />";
	    $content .= '</td>';
	    $selectedY = ($status['visibility']=='1') ? " selected" : "";
	    $selectedN = ($status['visibility']=='0') ? " selected" : "";
	    $content .= "<td><input type='number' name='status[order][]' value='".$status['order_by']."' ></td>";
	    $content .= "<td><select name='status[visibility][]'>
				<option ".$selectedY." value='1'>Yes</option>
				<option ".$selectedN." value='0'>No</option>
			    </select>
			</td>";
	    $content .= "</tr>";
	    if ( !empty($checkCildren) ) {
		$prefix .= '-';
	    $prefix .= '-';

		$content .= get_nested_ststus_editable_rows( $db, $status['Guid_status'], $level . "-&nbsp;" );
	    }
	}

    }

    return $content;
}

/**
 * Get Mark as test user ids
 * @param type $db
 * @return type String 11,55,22,45
 */
function getMarkedTestUserIDs($db){
    $getTestUsers = $db->query("SELECT Guid_user FROM `tbl_ss_qualify` WHERE mark_as_test=:mark_as_test GROUP BY Guid_user", array('mark_as_test'=>'1'));
    $userIds = "";
    foreach ($getTestUsers as $k=>$v){
	$userIds .= $v['Guid_user'].', ';
    }
    $markedTestUserIds = rtrim($userIds, ', ');

    return $markedTestUserIds;
}

function getTestUserIDs($db){
    $q = "SELECT p.Guid_user FROM tblpatient p
	    WHERE CONCAT(p.firstname, ' ', p.lastname) LIKE '%test%'
	    OR CONCAT(p.firstname, ' ', p.lastname) LIKE '%John Smith%'
	    OR CONCAT(p.firstname, ' ', p.lastname) LIKE '%John Doe%'
	    OR CONCAT(p.firstname, ' ', p.lastname) LIKE '%Jane Doe%' ";
    $getTestUsers = $db->query($q);
    $userIds = "";
    foreach ($getTestUsers as $k=>$v){
	$userIds .= $v['Guid_user'].', ';
    }
    $testUserIds = rtrim($userIds, ', ');

    return $testUserIds;

}

function formatDate($date){
    return date("n/j/Y", strtotime($date));
}

/**
 * Get Stats info
 * @param type $db
 * @param type $statusID
 * @return type array ('count'=>5, 'info'=>array())
 */
function get_stats_info($db, $statusID, $hasChildren=FALSE, $searchData=array()){

    //exclude test users
    $markedTestUserIds = getMarkedTestUserIDs($db);
    $testUserIds = getTestUserIDs($db);
    //$testUserIds = '';
    $q = "SELECT statuses.*, statuslogs.*,
	    mdlnum.mdl_number as mdl_number
	    FROM `tbl_mdl_status` statuses
	    LEFT JOIN `tbl_mdl_status_log` statuslogs ON statuses.`Guid_status`= statuslogs.`Guid_status`
	    LEFT JOIN `tbl_mdl_number` mdlnum ON statuslogs.Guid_user=mdlnum.Guid_user ";

    $q .=  "WHERE  statuslogs.`currentstatus`='Y' ";

    if(!empty($searchData)){
	if (strlen($searchData['from_date']) && $searchData['to_date']) {
	    if ($searchData['from_date'] == $searchData['to_date']) {
		$q .= " AND statuslogs.Date LIKE '%" . date("Y-m-d", strtotime($searchData['from_date'])) . "%'";
	    } else {
		$q .= " AND statuslogs.Date BETWEEN '" . date("Y-m-d", strtotime($searchData['from_date'])) . "' AND '" . date("Y-m-d", strtotime($searchData['to_date'])) . "'";
	    }
	}
	if(isset($searchData['mdl_number']) && $searchData['mdl_number']!=""){
	    $q .= " AND mdl_number='".$searchData['mdl_number']."' ";
	}
	if(isset($searchData['Guid_salesrep']) && $searchData['Guid_salesrep']!=""){
	    $q .= " AND statuslogs.Guid_salesrep='".$searchData['Guid_salesrep']."' ";
	}
	if(isset($searchData['Guid_account']) && $searchData['Guid_account']!=""){
	    $q .= " AND statuslogs.Guid_account='".$searchData['Guid_account']."' ";
	}
    }

    $q .=  " AND statuslogs.`Guid_status_log`<>''
	    AND statuslogs.Guid_status=$statusID ";
    if($markedTestUserIds!=""){
    $q .=  " AND statuslogs.Guid_user NOT IN(".$markedTestUserIds.") ";
    }
    if($testUserIds!=""){
    $q .=  " AND statuslogs.Guid_user NOT IN(".$testUserIds.") ";
    }
    $q .=  " AND statuslogs.Guid_patient<>'0'
	    ORDER BY statuslogs.`Date` DESC, statuses.`order_by` DESC";

    $stats = $db->query($q);
    $result['count'] = 0;
    if(!empty($stats)){
	$result['count'] = count($stats);
	$result['serarch'] = $searchData;
	$result['info'] = $stats;
    }

    return $result;
}

function get_status_table_rows($db, $parent = 0, $searchData=array()) {

    $statusQ = "SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$parent." ORDER BY order_by ASC";
    $statuses = $db->query($statusQ);

    $content = '';
    if ( $statuses ) {
	foreach ( $statuses as $status ) {
	    $checkCildren = $db->query("SELECT * FROM tbl_mdl_status "
		    . "WHERE `parent_id` = ".$status['Guid_status']);
	    $stats = get_stats_info($db, $status['Guid_status'], FALSE, $searchData);
	    if($stats['count']!=0){
		$optionClass = '';
		if ( !empty($checkCildren) ) {
		    $optionClass = 'has_sub';
		}
		$content .= "<tr id='".$status['Guid_status']."' class='parent ".$optionClass."'>";
		$content .= "<td class='text-left'><span>".$status['status'].'</span></td>';
		$content .= '<td><a href="'.SITE_URL.'/mdl-stat-details.php?status_id='.$status['Guid_status'].'">'.$stats['count'].'</a></td>';
		if ( !empty($checkCildren) ) {
		    $content .= get_status_child_rows( $db, $status['Guid_status'], "&nbsp;", $searchData );
		}
		$content .= "</tr>";
	    }
	}
    }

    return $content;
}
function get_status_child_rows($db, $parent = 0,  $level = '', $searchData=array()) {
    $statuses = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$parent."  ORDER BY order_by ASC");
    if ( $statuses ) {
	$content ='';
	$prefix = 0;
	foreach ( $statuses as $status ) {
	    $checkCildren = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$status['Guid_status']);
	    $optionClass = '';
	    $stats = get_stats_info($db, $status['Guid_status'], TRUE, $searchData);
	    if($stats['count']!=0){
		if ( !empty($checkCildren) ) {
		    $optionClass = 'parent has_sub';
		}
		$content .= "<tr id='".$status['Guid_status']."' data-parent-id='".$parent."' class='sub ".$optionClass."'>";
		$content .= "<td class='text-left'><span>".$level . " " .$status['status'].'</span></td>';
		$content .= '<td><a href="'.SITE_URL.'/mdl-stat-details.php?status_id='.$status['Guid_status'].'&parent='.$parent.'">'.$stats['count']. '</a></td>';
		if ( !empty($checkCildren) ) {
		    $prefix .= '&nbsp;';
		    $content .= get_status_child_rows( $db, $status['Guid_status'], $level . "&nbsp;" );
		}
		 $content .= "</tr>";
	    }
	}
    }

    return $content;
}


function get_status_table_rows___($db, $parent = 0) {
    $q ='SELECT statuses.*, statuslogs.`Guid_status_log`, statuslogs.`Guid_patient`, statuslogs.`Log_group`, statuslogs.`order_by`, statuslogs.`Date`
	FROM `tbl_mdl_status` statuses
	LEFT JOIN `tbl_mdl_status_log` statuslogs
	ON statuses.`Guid_status`= statuslogs.`Guid_status`
	WHERE `visibility`="1"
	AND statuslogs.`Guid_status_log`<>""
	AND parent_id="'.$parent.'"
	ORDER BY statuslogs.`Date` DESC, statuses.`order_by` DESC';

    $statuses = $db->query($q);
    $content  = "";

    if(!empty($statuses)){
	foreach ($statuses as $k=>$v){
	    $qChild ='SELECT statuses.*, statuslogs.`Guid_status_log`, statuslogs.`Guid_patient`, statuslogs.`Log_group`, statuslogs.`order_by`, statuslogs.`Date`
	    FROM `tbl_mdl_status` statuses
	    LEFT JOIN `tbl_mdl_status_log` statuslogs
	    ON statuses.`Guid_status`= statuslogs.`Guid_status`
	    WHERE `visibility`="1"
	    AND statuslogs.`Guid_status_log`<>""
	    AND parent_id="'.$v['Guid_status'].'"
	    ORDER BY statuslogs.`Date` DESC, statuses.`order_by`  DESC';
	    $checkChildren = $db->query($qChild);

	    $optionClass = '';
	    if ( !empty($checkChildren) ) {
		$optionClass = 'has_sub';

	    }
	    $content .= "<tr id='".$v['Guid_status']."' class='parent ".$optionClass."'>";
	    $content .= "<td class='text-left'><span>".$v['status'].'</span></td>';
	    $content .= '<td><a href="'.SITE_URL.'/mdl-stat-details.php?status_id='.$v['Guid_status'].'">'.$v['Guid_patient'].'</a></td>';
	    if ( !empty($checkChildren) ) {
		$content .= get_status_child_rows( $db, $v['Guid_status'],"&nbsp;" );
	    }
	    $content .= "</tr>";
	}
    }

    return $content;
}

function get_status_child_rows__($db, $parent = 0,  $level = '') {

    $q ='SELECT statuses.*, statuslogs.`Guid_status_log`, statuslogs.`Guid_patient`, statuslogs.`Log_group`, statuslogs.`order_by`, statuslogs.`Date`
	FROM `tbl_mdl_status` statuses
	LEFT JOIN `tbl_mdl_status_log` statuslogs
	ON statuses.`Guid_status`= statuslogs.`Guid_status`
	WHERE `visibility`="1"
	AND statuslogs.`Guid_status_log`<>""
	AND parent_id="'.$parent.'"
	ORDER BY statuslogs.`Date` DESC, statuslogs.`order_by` DESC';
    $statuses = $db->query($q);

    $content = "";
    if ( !empty($statuses) ) {
	$prefix = 0;
	foreach ( $statuses as $status ) {
	    $qChild = 'SELECT statuses.*, statuslogs.`Guid_status_log`, statuslogs.`Guid_patient`, statuslogs.`Log_group`, statuslogs.`order_by`, statuslogs.`Date`
			FROM `tbl_mdl_status` statuses
			LEFT JOIN `tbl_mdl_status_log` statuslogs
			ON statuses.`Guid_status`= statuslogs.`Guid_status`
			WHERE `visibility`="1"
			AND statuslogs.`Guid_status_log`<>""
			AND parent_id="'.$status['Guid_status'].'"
			ORDER BY statuslogs.`Date` DESC, statuslogs.`order_by`  DESC';
	    $checkCildren = $db->query($qChild);
	    $optionClass = '';


	    if ( !empty($checkCildren) ) {
		$optionClass = 'parent has_sub';
	    }
	    $content .= "<tr id='".$status['Guid_status']."' data-parent-id='".$parent."' class='sub ".$optionClass."'>";
	    $content .= "<td class='text-left'><span>".$level . " " .$status['status'].'</span></td>';
	    $content .= '<td><a href="'.SITE_URL.'/mdl-stat-details.php?status_id='.$status['Guid_status'].'">hhhh</a></td>';
	    if ( !empty($checkCildren) ) {
		$prefix .= '&nbsp;';
		$content .= get_status_child_rows( $db, $status['Guid_status'], $level . "&nbsp;" );
	    }
	    $content .= "</tr>";

	}
    }

    return $content;
}

function get_status_table_rows_($db, $parent = 0) {

    $q ='SELECT count(*) AS count, statuses.*, statuslogs.`Guid_status_log`, statuslogs.`Guid_patient`, statuslogs.`Log_group`, statuslogs.`order_by`, statuslogs.`Date` FROM `tbl_mdl_status` statuses
	LEFT JOIN `tbl_mdl_status_log` statuslogs
	ON statuses.`Guid_status`= statuslogs.`Guid_status`
	WHERE `visibility`="1"
	AND statuslogs.`Guid_status_log`<>""
	AND parent_id="'.$parent.'"
	ORDER BY statuslogs.`Date`, statuslogs.`order_by` DESC';
    $statuses = $db->query($q);

    $content = '';
    if ( $statuses ) {
	foreach ( $statuses as $status ) {
	    $q ='SELECT count(*) AS count, statuses.*, statuslogs.`Guid_status_log`, statuslogs.`Guid_patient`, statuslogs.`Log_group`, statuslogs.`order_by`, statuslogs.`Date` FROM `tbl_mdl_status` statuses
		LEFT JOIN `tbl_mdl_status_log` statuslogs
		ON statuses.`Guid_status`= statuslogs.`Guid_status`
		WHERE `visibility`="1"
		AND statuslogs.`Guid_status_log`<>""

		AND parent_id="'.$status['Guid_status'].'"
		ORDER BY statuslogs.`Date`, statuslogs.`order_by` DESC';
	    $checkCildren = $db->query($q);

	    if( isset($status['count']) && $status['count']!=0){
		$optionClass = '';
		if ( !empty($checkCildren) ) {
		    $optionClass = 'has_sub';
		}
		$content .= "<tr id='".$status['Guid_status']."' class='parent ".$optionClass."'>";
		$content .= "<td class='text-left'><span>".$status['status'].'</span></td>';
		$content .= '<td><a href="'.SITE_URL.'/mdl-stat-details.php?status_id='.$status['Guid_status'].'">'.$status['count'].'</a></td>';
		if ( !empty($checkCildren) ) {
		    $content .= get_status_child_rows( $db, $status['Guid_status'], "&nbsp;" );
		}
		$content .= "</tr>";
	    }
	}
    }

    return $content;
}

function getStatusName($db, $Guid_status, $parent){
    $name =""; $parentName="";
    $status = $db->row("SELECT `status` FROM tbl_mdl_status WHERE Guid_status=:Guid_status", array('Guid_status'=>$Guid_status));
    if($parent != ""){
	$parentRow = $db->row("SELECT `status` FROM tbl_mdl_status WHERE Guid_status=:Guid_status", array('Guid_status'=>$parent));
	$name .= $parentRow['status']." - ";
    }
    if($status){
	$name .= $status['status'];
    }

    return $name;
}
function getStatusParentNames($db, $Guid_status){
    $status = $db->row("SELECT `status` FROM tbl_mdl_status WHERE Guid_status=:Guid_status AND parent_id='0' ", array('Guid_status'=>$Guid_status));
    $names = "";
    if($status){
	$names .= $status['status'].'; ';
    }
    return $names;
}
function getRoleName($db, $roleID){
    $role = $db->row("SELECT role FROM `tblrole` WHERE Guid_role=:Guid_role", array('Guid_role'=>$roleID));
    $names = "";
    if($role){
	$names .= $role['role'];
    }
    return $names;
}
/**
 * Checking if field status assigned for given field
 * Statuses configurable from MDL details config page(mdl-stat-details-config.php)
 * Returns True if status assigned to that field
 * @param type $db
 * @param type $fieldID
 * @param type $statusID
 * @return boolean
 */
function isFieldVisibleForStatus($db, $fieldID, $statusID){
    $configOptions = getOption($db, 'stat_details_config');
    $optionVal = unserialize($configOptions['value']);
    if(isset($optionVal[$fieldID]['statuses']) && !empty($optionVal[$fieldID]['statuses'])){
	if(in_array($statusID, $optionVal[$fieldID]['statuses'])){
	    return TRUE;
	}
	return FALSE;
    }
    return FALSE;
}
/**
 * Checking if role assigned for given filed
 * Roles configurable from MDL details config page(mdl-stat-details-config.php)
 * Returns True if role assigned to that field
 * @param type $db
 * @param type $fieldID
 * @param type $roleID
 * @return boolean
 */
function isFieldVisibleForRole($db, $fieldID, $roleID){
    $configOptions = getOption($db, 'stat_details_config');
    $optionVal = unserialize($configOptions['value']);
    if(isset($optionVal[$fieldID]['roles']) && !empty($optionVal[$fieldID]['roles'])){
	if(in_array($roleID, $optionVal[$fieldID]['roles'])){
	    return TRUE;
	}
	return FALSE;
    }
    return FALSE;
}
/**
 * Get Revenue Details for stats page
 * @param type $db
 * @param type $Guid_user
 * @return type array
 */
function getRevenueStat($db, $Guid_user){
    $revenueData = array();
    $getPayorQ = "SELECT r.Guid_payor, p.name FROM `tbl_revenue` r "
		. "LEFT JOIN tbl_mdl_payors p ON r.Guid_payor=p.Guid_payor "
		. "WHERE r.Guid_payor<>'' AND r.Guid_user=:Guid_user "
		. "GROUP BY r.Guid_payor ORDER BY r.Guid_payor ";
    $payors = $db->query($getPayorQ, array('Guid_user'=>$Guid_user));

    $paidPatient = 0;
    $paidInsurance = 0;
    $total = 0;
    $insuranceName = "";
    if(!empty($payors)){
	foreach ($payors as $k=>$v){
	    $Guid_payor = $v['Guid_payor'];
	    $revenueAmmountQ = "SELECT r.amount FROM `tbl_revenue` r
				LEFT JOIN `tbl_mdl_payors` p ON r.`Guid_payor`=p.`Guid_payor`
				WHERE r.`Guid_payor`= $Guid_payor
				ORDER BY r.`Guid_payor` ";
	    $revenueAmmount = $db->query($revenueAmmountQ);
	    foreach ($revenueAmmount as $amount){
		if($Guid_payor=='1'){ // Payor ID with 1 is Patients
		    $paidPatient += $amount['amount'];
		}else{ //insurance
		    $paidInsurance += $amount['amount'];
		}
		$total += $amount['amount'];
	    }
	    if($Guid_payor!='1'){
		$insuranceName .= $v['name']."; ";
	    }
	}
    }
    $insuranceName = rtrim($insuranceName, '; ');
    $revenueData['patient_paid'] =  $paidPatient;
    $revenueData['insurance_paid'] =  $paidInsurance;
    $revenueData['total'] =  $total;
    $revenueData['insurance_name'] =  $insuranceName;

    return $revenueData;
}
/**
 * Get Totals for given status
 * @param type $db
 * @param type $Guid_status
 * @return type array
 */
function getStatusRevenueTotals($db, $Guid_status){
    $usersQ = "SELECT * FROM `tbl_mdl_status_log` WHERE Guid_status=$Guid_status AND currentstatus='Y'";
    $users = $db->query($usersQ);

    $patientTotal = 0;
    $insuranceTotal = 0;
    $total = 0;
    $revenueTotalsData = array();
    if(!empty($users)){
	foreach ($users as $user){
	    $revenuDetails = getRevenueStat($db, $user['Guid_user']);
	    if(!empty($revenuDetails)){
		$patientTotal += $revenuDetails['patient_paid'];
		$insuranceTotal += $revenuDetails['insurance_paid'];
		$total += $revenuDetails['total'];
	    }
	}
    }
    $revenueTotalsData['patient_total'] = $patientTotal;
    $revenueTotalsData['insurance_total'] = $insuranceTotal;
    $revenueTotalsData['total'] = $total;

    return $revenueTotalsData;
}