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
    $query = "SELECT r.Guid_role, r.role FROM tblrole r LEFT JOIN tbluser u ON r.Guid_role = u.Guid_role WHERE u.Guid_user = " . $userID;
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
    $query = "SELECT * FROM tblrole r LEFT JOIN tbluser u ON r.Guid_role = u.Guid_role WHERE u.Guid_user = " . $userID;
    $userInfo = $db->row($query);
    
    if($userInfo['role']=='Patient'){
        $query = "SELECT aes_decrypt(firstname_enc, 'F1rstn@m3@_%') as firstname FROM `tblpatient` WHERE Guid_user=:Guid_user";
        $patientInfo = $db->row($query, array("Guid_user"=>$userInfo['Guid_user']));
        
        if($patientInfo){
            $result = $patientInfo['firstname'];
        } else {
            $result = $_SESSION['user']['type'];
        }
    } elseif ($userInfo['role']=='Sales Rep' || $userInfo['role']=='Sales Manager') {
        $query = "SELECT first_name FROM `tblsalesrep` WHERE Guid_user=:Guid_user";
        $salesrepInfo = $db->row($query, array("Guid_user"=>$userInfo['Guid_user']));
        if($salesrepInfo){
            $result = $salesrepInfo['first_name'];
        } else {
            $result = $_SESSION['user']['type'];
        }
    } elseif ($userInfo['role']=='Physician') {
        $query = "SELECT first_name FROM `tblprovider` WHERE Guid_user=:Guid_user";
        $providerInfo = $db->row($query, array("Guid_user"=>$userInfo['Guid_user']));
        if($providerInfo){
            $result = $providerInfo['first_name'];
        } else {
            $result = $_SESSION['user']['type'];
        }
    } elseif ($userInfo['role']=='Admin') {
        $query = "SELECT first_name FROM `tbladmins` WHERE Guid_user=:Guid_user";
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
    $query = "SELECT * FROM tblrole r LEFT JOIN tbluser u ON r.Guid_role = u.Guid_role WHERE u.Guid_user = $userID";
    
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
        
        $query = "SELECT *, aes_decrypt(firstname_enc, 'F1rstn@m3@_%') AS first_name, aes_decrypt(lastname_enc, 'L@stn@m3&%#') AS last_name FROM `tblpatient` WHERE Guid_user=:Guid_user";
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
                LEFT JOIN `tblrole` r ON r.Guid_role=u.Guid_role";
    $users = $db->query($query);
    return $users;
}
function getUserAndRole($db, $userID){
    $query = "SELECT u.*, r.* FROM `tbluser` u
                LEFT JOIN `tblrole` r ON r.Guid_role=u.Guid_role 
                WHERE u.Guid_user=:Guid_user";    
    $user = $db->row($query, array("Guid_user"=>$userID));
    
    return $user;
}

function getRole($db, $userID){
    $query = "SELECT r.Guid_role, r.role FROM tblrole r "
            . "LEFT JOIN tbluser u ON r.Guid_role = u.Guid_role "
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
    } elseif ($userRole == 'Patient' || $userRole == 'MDL Patient') {
        $q = "SELECT aes_decrypt(firstname_enc, 'F1rstn@m3@_%') as first_name, aes_decrypt(lastname_enc, 'L@stn@m3&%#') as last_name FROM tblpatient";
    }  
    
    if($q!=""){        
        $q .= " WHERE Guid_user=:Guid_user";
        $userDetail = $db->row($q, array("Guid_user"=>$userID));
    } else {//when patient is not exists on users table
        if($patientID!=""){
            $q = "SELECT aes_decrypt(firstname_enc, 'F1rstn@m3@_%') as first_name, aes_decrypt(lastname_enc, 'L@stn@m3&%#') as last_name FROM tblpatient  WHERE Guid_patient=:Guid_patient";
            $userDetail = $db->row($q, array("Guid_patient"=>$patientID));
        }
    }
    
    return $userDetail;    
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
    } elseif ($Guid_role=='3' || $Guid_role=='6') {//patient or mdl patient
        $userQ = "SELECT Guid_user FROM tblpatient WHERE Guid_user=:Guid_user";
        $isUserExists = $db->row($userQ, array('Guid_user'=>$Guid_user));
        if($isUserExists){
            $fname = $userDetails['firstname_enc'];
            $lname = $userDetails['lastname_enc'];
            
            $query = "UPDATE `tblpatient` SET firstname_enc=AES_ENCRYPT('".$fname."','F1rstn@m3@_%'), lastname_enc=AES_ENCRYPT('".$lname."', 'L@stn@m3&%#') WHERE `Guid_user`=$Guid_user";
            $update = $db->query($query);
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
function moveUserData($db, $Guid_user, $userDetails, $thisRole, $prevRole){
    //we'll work only with users Admin, Sales Rep and Sales Manager (Guid_role -> 1, 4, 5)    
    $fName = (isset($userDetails['first_name'])) ? $userDetails['first_name']: "";
    $lName = (isset($userDetails['last_name'])) ? $userDetails['last_name']: "";
    $filename = (isset($userDetails['photo_filename'])) ? $userDetails['photo_filename']: "";
    
    if($thisRole != $prevRole){        
        if($thisRole=='1'){//Admin
            //we need to move sales rep to admin table
            $salesrepQ = "SELECT * FROM tblsalesrep WHERE Guid_user=:Guid_user";
            $getSalserep = $db->row($salesrepQ, array('Guid_user'=>$Guid_user));           
            
            $adminData = array(
                'Guid_user' => $Guid_user,
                'first_name' => ($fName !="") ? $fName:$getSalserep['first_name'],
                'last_name' => ($lName !="") ? $lName:$getSalserep['last_name'],
                'photo_filename' => ($filename !="") ? $filename:$getSalserep['photo_filename'],
                'phone_number' => $getSalserep['phone_number'],
                'address' => $getSalserep['address'],
                'city' => $getSalserep['city'],
                'state' => $getSalserep['state'],
                'zip' => $getSalserep['zip']                
            );
            $insert = insertIntoTable($db, 'tbladmins', $adminData);
            
            if(isset($insert['insertID'])){
                $Guid_salesrep = $getSalserep['Guid_salesrep'];
                deleteByField($db, 'tblsalesrep', 'Guid_salesrep', $Guid_salesrep);
            }
            
            return $insert;
        } elseif ($thisRole=='4' || $thisRole=='5') {//Sales Rep OR Sales Manager
           
            if($prevRole == '4' || $prevRole == '5'){ 
               
                // need just change role, don't need of moving data
                $is_manager = ($thisRole=='5') ? '1' : '0'; //1-sales Mgr and 0-Sales Rep
                updateTable($db, 'tblsalesrep', array('is_manager'=>$is_manager), array('Guid_user'=>$Guid_user));
                return TRUE;
            } else { //we need to move admin to salesrep table
               
                $adminQ = "SELECT * FROM tbladmins WHERE Guid_user=:Guid_user";
                $getAdmin = $db->row($adminQ, array('Guid_user'=>$Guid_user));               

                $salesrepData = array(
                    'Guid_user' => $Guid_user,
                    'first_name' => ($fName !="") ? $fName:$getAdmin['first_name'],
                    'last_name' => ($lName !="") ? $lName:$getAdmin['last_name'],
                    'photo_filename' => ($filename !="") ? $filename:$getAdmin['photo_filename'],
                    'phone_number' => $getAdmin['phone_number'],
                    'address' => $getAdmin['address'],
                    'city' => $getAdmin['city'],
                    'state' => $getAdmin['state'],
                    'zip' => $getAdmin['zip']                
                );
                if($thisRole=='5'){
                    $salesrepData['is_manager'] = '1';
                }

                $insert = insertIntoTable($db, 'tblsalesrep', $salesrepData);
                if(isset($insert['insertID'])){                
                    $Guid_admin = $getAdmin['Guid_admin'];
                    deleteByField($db, 'tbladmins', 'Guid_admin', $Guid_admin);
                }
                return $insert;
            }
            
        }        
    }
    return FALSE;
}
/**
 * save Category-User Links
 * @param type $db
 * @param type $Guid_user
 * @param type $catIDs
 */
function saveCategoryUserLinks($db, $Guid_user, $catIDs){  
    //delete old links
    $db->query("DELETE FROM `tbl_mdl_category_user_link` WHERE Guid_user=:Guid_user", array('Guid_user'=>$Guid_user));
    if(!empty($catIDs)){  
        foreach ($catIDs as $k=>$Guid_category){
            $userCatData = array('Guid_category'=>$Guid_category, 'Guid_user'=>$Guid_user);
            //insert new links
            insertIntoTable($db,'tbl_mdl_category_user_link', $userCatData);            
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
                    . "tbldeviceinv.id, tbldeviceinv.Guid_salesrep, tbldeviceinv.deviceid, tbldeviceinv.serial_number, tbldeviceinv.comment, tbldeviceinv.inservice_date, tbldeviceinv.outservice_date, "
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
            . "tblsalesrep.state AS salesrepState, tblsalesrep.zip AS salesrepZip, tblsalesrep.photo_filename AS salesrepPhoto, "
            . "tbl_mdl_category.slug AS category_slug, tbl_mdl_category.name AS category_name "
            . "FROM tblaccount "
            . "LEFT JOIN tblaccountrep ON tblaccount.Guid_account = tblaccountrep.Guid_account "
            . "LEFT JOIN tblsalesrep ON tblsalesrep.Guid_salesrep=tblaccountrep.Guid_salesrep "         
            . "LEFT JOIN tbl_mdl_category ON tblaccount.Guid_category=tbl_mdl_category.Guid_category ";         
    $thisUserID = $_SESSION['user']['id'];
    $roleInfo = getRole($db, $thisUserID);
    $thisUserRole = $roleInfo['role'];
    
    if($thisUserRole == "Sales Manager"){
        $userCategories = $db->query("SELECT Guid_category FROM `tbl_mdl_category_user_link` WHERE Guid_user=:Guid_user", array('Guid_user'=>$thisUserID)); 
        $userLinks = '';
        if(!empty($userCategories)){
            foreach ($userCategories as $k=>$v){
                $userLinks .= $v['Guid_category'].', ';
            }
            $userLinks = rtrim($userLinks, ', ');
        }    
        if($userLinks != ''){
            $wherAccount = strpos($query, 'WHERE') ? " AND " : " WHERE ";
            $query .= $wherAccount . " tblaccount.Guid_category IN (" . $userLinks . ") ";
        } 
    }
    
    if($accountGuid){
        $wherAccount = strpos($query, 'WHERE') ? " AND " : " WHERE ";
        $query .= $wherAccount . " tblaccount.Guid_account=:id";
        $result = $db->query($query, array("id"=>$accountGuid));
    }
    elseif ($getRow) {
        $result = $db->row($query);
    }else{
        $query .= " GROUP BY tblaccount.Guid_account"; 
        $result = $db->query($query);
    }   
    
    return $result;
}
function getSalesrepAccounts($db, $Guid_user){
    $salesrepAccountIDs = $db->query("SELECT acc.account FROM tblsalesrep srep
                                        LEFT JOIN tblaccountrep arep ON arep.Guid_salesrep=srep.Guid_salesrep
                                        LEFT JOIN tblaccount acc ON arep.Guid_account=acc.Guid_account
                                        WHERE srep.Guid_user=:Guid_user", array('Guid_user'=>$Guid_user));
    $accountIds = "";
    foreach ($salesrepAccountIDs as $k=>$v){
        $accountIds .= "'".$v['account']."', ";
    }
    $accountIds = rtrim($accountIds, ', ');
    return $accountIds;
}
/**
 * Get Account Status Count By account number
 * @param type $db
 * @param type $account
 * @param type $Guid_status
 * @return type
 */
function getAccountStatusCount($db, $account, $Guid_status, $eventDate=NULL ){    
    $params = array('account'=>$account,'Guid_status'=>$Guid_status);
    $q = "SELECT COUNT(*) AS `count` FROM `tbl_mdl_status_log` l "
        . "LEFT JOIN tbluser u ON l.Guid_user = u.Guid_user "
        . "WHERE l.Guid_status =:Guid_status AND l.account=:account AND u.marked_test='0' "; 
    if($eventDate){
        $q .=  "AND DATE(l.Date)=:eventdate";
        $params['eventdate']=$eventDate;
    }
    
    $result = $db->row($q, $params);    
    return $result['count'];
}


/**
 * Get Slasrep Status count By Guid salesrep
 * @param type $db
 * @param type $Guid_salesrep
 * @param type $Guid_status
 * @return type
 */
function getSalesrepStatusCount($db, $Guid_salesrep, $Guid_status ){     
    $q = "SELECT COUNT(*) AS `count` FROM `tbl_mdl_status_log` l "
        . "LEFT JOIN tbluser u ON l.Guid_user = u.Guid_user "
        . "WHERE l.Guid_status =:Guid_status AND l.Guid_salesrep=:Guid_salesrep AND u.marked_test='0'"; 
    
    $result = $db->row($q, array('Guid_salesrep'=>$Guid_salesrep,'Guid_status'=>$Guid_status));    
    return $result['count'];
}
/**
 * Get Device Status count By Guid_salesrep
 * @param type $db
 * @param type $Guid_salesrep
 * @param type $Guid_status
 * @return type
 */
function getDeviceStatusCount($db, $Guid_salesrep, $Guid_status, $deviceinventoryID ){     
    $q = "SELECT COUNT(*) AS `count` FROM `tbl_mdl_status_log` l "
        . "LEFT JOIN tbluser u ON l.Guid_user = u.Guid_user "
        . "WHERE l.deviceid=:id AND l.Guid_status =:Guid_status AND l.Guid_salesrep=:Guid_salesrep AND u.marked_test='0'"; 
    $where = array(
                'Guid_salesrep'=>$Guid_salesrep,
                'Guid_status'=>$Guid_status,
                'id'=>$deviceinventoryID
            );
    $result = $db->row($q, $where);    
    return $result['count'];
}
/**
 * Get provider Status count by medicalNecessity and providerID 
 * @param type $db
 * @param type $medicalNecessity => Completed(Yes+No+Unknown), Incomplete
 * Registered => Incomplete + Completed(Yes+No+Unknown)
 * Completed => Completed(Yes+No+Unknown)
 * Qualified => Yes
 * Submitted => Specimen collected=>Yes from patients info screen
 * @param type $Guid_provider 
 * @param type $Guid_status
 * @return type
 */
function getProviderStatusCount($db, $medicalNecessity, $Guid_provider){
    
    if($medicalNecessity=='Incomplete'){
        $table = "tblqualify";
        $where = "WHERE NOT EXISTS(SELECT * FROM tbl_ss_qualify qs WHERE q.Guid_qualify=qs.Guid_qualify) "
                . "AND u.marked_test='0' ";
    } else {
        $table = "tbl_ss_qualify";
        $where = "WHERE u.marked_test='0' ";
        $where .= "AND q.`Date_created` = (SELECT MAX(Date_created) FROM tbl_ss_qualify AS m2 WHERE q.Guid_qualify = m2.Guid_qualify)";

    }
    
    $q  = "SELECT COUNT(*) AS count "
            . "FROM `".$table."` q "
            . "LEFT JOIN tbluser u ON q.Guid_user = u.Guid_user ";
    
    $q .= $where; 
    
    if($medicalNecessity=='Yes'){
        $q .= "AND q.qualified = 'Yes' ";
    }
    if($medicalNecessity=='No'){
        $q .= "AND q.qualified = 'No' ";
    }
    if($medicalNecessity=='Unknown'){
        $q .= "AND q.qualified = 'Unknown' ";
    }
    
    $q .= "AND q.provider_id = '" . $Guid_provider . "' ";
    
    $result = $db->row($q);    
    return $result['count'];
}

/**
 * Get Provider Submited count (Specimen Collected => Guid_status=1)
 * @param type $db
 * @param type $Guid_provider
 * @return string
 */
function getProviderSubmitedCount($db, $Guid_provider ){ 
    
    $andQ = "AND q.provider_id = '" . $Guid_provider . "' ";
    $andQ .= "AND q.`Date_created` = (SELECT MAX(Date_created) FROM tbl_ss_qualify AS m2 WHERE q.Guid_qualify = m2.Guid_qualify)";
    
    $completedQ  = "SELECT q.Guid_user "
                    . "FROM `tbl_ss_qualify` q "
                    . "LEFT JOIN tbluser u ON q.Guid_user = u.Guid_user ";
    $completedQ  .= "WHERE u.marked_test='0' ";
    $completedQ  .= $andQ;
    
    $incompleteQ  = "SELECT q.Guid_user "
                    . "FROM `tblqualify` q "
                    . "LEFT JOIN tbluser u ON q.Guid_user = u.Guid_user ";
    $incompleteQ .= "WHERE NOT EXISTS(SELECT * FROM tbl_ss_qualify qs WHERE q.Guid_qualify=qs.Guid_qualify) "
                   . "AND u.marked_test='0' ";
    $incompleteQ .= $andQ;
    
    $completedUsers = $db->query($completedQ);
    $incompleteUsers = $db->query($incompleteQ);
    $userIds = "";
    if(!empty($completedUsers)){
        foreach ($completedUsers as $k=>$v) {
            $userIds .= "'".$v['Guid_user']."', ";
        }
    }
    if(!empty($incompleteUsers)){
        foreach ($incompleteUsers as $k=>$v) {
            $userIds .= "'".$v['Guid_user']."', ";
        }
    }    
    if($userIds!=""){
        $userIds = rtrim($userIds, ', ');
        
        $submitedQ="SELECT COUNT(*) AS count FROM `tbl_mdl_status_log` l
                LEFT JOIN tbluser u ON u.Guid_user=l.Guid_user
                WHERE l.Guid_user IN(".$userIds.")
                AND u.marked_test='0'
                AND l.Guid_status=1";
               // AND l.currentstatus='Y'";
        $result = $db->row($submitedQ);
    }
    
    if(isset($result['count']) && $result['count']!=""){
        $count =  $result['count'];
    } else{
        $count = '0';
    }
    return $count;
    
}


/**
 * 
 * @param type $db
 * @param type $providerID
 * @return type
 */

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


function validateProviderId($db, $data, $npi){
    extract($data);    
    $providers = array();
    $query = "";
    if($action=='update'){
        $query = "SELECT `npi` FROM tblprovider WHERE `npi`=$npi AND Guid_provider<>$Guid_provider";
    } else {
        if($npi!=""){
            $query = "SELECT `npi` FROM tblprovider WHERE `npi`=$npi ";
        }
    }
    if($query){
        $providers = $db->query($query);
    } else {
        $providers= FALSE;
    }

    if(!$providers){
        return array('status'=>1, 'msg'=>'NPI Valid.');
    } else {
        return array('status'=>0, 'msg'=>'NPI already exists.');
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

function formatLastName($lastName){
    $lastNameF = ucwords(strtolower($lastName));
    if(substr($lastNameF, 0, 2)=='Mc'){
        return substr_replace($lastNameF, strtoupper(substr($lastNameF, 2, 1)), 2, 1);
    } else {
        return $lastNameF;
    }
}
function formatAccountName($accountName){
    $accountName = ucwords(strtolower($accountName));
    $accountName = str_replace("Ob/gyn","OB/GYN",$accountName);
    $accountName = str_replace("Obgyn","OB/GYN",$accountName);
    $accountName = str_replace(" md"," MD",$accountName);
    $accountName = str_replace(" Md"," MD",$accountName);
    return $accountName;
}

/**
 * Get nested status names 
 * used in patient info screen 
 * Test Status Change Log Table - status names
 */
function get_nested_statuses($db, $Guid_status, $Guid_user, $Log_group, $i=0){
    $statQ = "SELECT sl.Guid_status, s.status FROM tbl_mdl_status_log sl 
            LEFT JOIN tblpatient p ON sl.Guid_patient=p.Guid_user 
            Left Join tbl_mdl_status s ON sl.Guid_status=s.Guid_status
            WHERE sl.Guid_user=$Guid_user AND s.Guid_status=$Guid_status AND Log_group=$Log_group";
    $status = $db->query($statQ);
    $names = '';
    if(!empty($status)){
        foreach ($status as $k=>$v){   
            $parent = $v['Guid_status'];
            $children = $db->query("SELECT sl.Guid_status, s.status FROM tbl_mdl_status_log sl 
                        LEFT JOIN tblpatient p ON sl.Guid_patient=p.Guid_user 
                        Left Join tbl_mdl_status s ON sl.Guid_status=s.Guid_status
                        WHERE sl.Guid_user=$Guid_user AND s.parent_id=$parent AND Log_group=$Log_group");
            if($i==0 && !empty($children)){
                echo $v['status'].': ';
            } elseif ($i==1 && !empty($children) ) {
                echo $v['status'].', ';
            } else {
                echo $v['status'];
            }            
            if(!empty($children)){
                foreach ($children as $key => $value) {
                    get_nested_statuses($db, $value['Guid_status'], $Guid_user, $Log_group, $i=1);
                }
            } else {
                $i=2;
            }
        }
    }
}

function get_selected_log_dropdown($db, $Log_group, $parent="0") {
    $selectedStatuses = $db->query(
                "SELECT sl.Guid_status, st.status, st.parent_id FROM tbl_mdl_status_log sl "
                . " LEFT JOIN tbl_mdl_status st ON st.Guid_status=sl.Guid_status"                
                . " WHERE `Log_group`= ".$Log_group
                . " ORDER BY st.parent_id ASC, st.order_by ASC"
    );
    $content = "";
    foreach ($selectedStatuses as $k => $v){
        $getParent = $db->row("SELECT parent_id FROM tbl_mdl_status WHERE Guid_status=:Guid_status", array('Guid_status'=>$v['Guid_status']));
        $statuses = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$getParent['parent_id']."  ORDER BY order_by ASC, Guid_status ASC");
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

function updateCurrentStatusID($db, $Guid_patient){
    //SELECT statuses.*, statuslogs.`Guid_status_log`, statuslogs.`Guid_patient`, statuslogs.`Log_group`, statuslogs.`order_by`, statuslogs.`Date` 
    $q  =   "SELECT * 
            FROM `tbl_mdl_status` statuses
            LEFT JOIN `tbl_mdl_status_log` statuslogs
            ON statuses.`Guid_status`= statuslogs.`Guid_status` ";    
    $q  .=  "AND statuslogs.`Guid_status_log`<>''
            AND statuses.parent_id='0'
            AND statuslogs.Guid_patient=$Guid_patient
            ORDER BY statuslogs.`Date` DESC, statuses.order_by DESC LIMIT 1";
    $result = $db->row($q);
 
    updateTable($db, 'tbl_mdl_status_log', array('currentstatus'=>'N'), array('Guid_patient'=>$Guid_patient));
    updateTable($db, 'tbl_mdl_status_log', array('currentstatus'=>'Y'), array('Log_group'=>$result['Log_group']));
    
    return $result['Guid_status_log'];
}

function get_status_dropdown($db, $parent='0', $Guid_status=FALSE) {
    if($Guid_status){
        $statuses = $db->query("SELECT * FROM tbl_mdl_status WHERE `Guid_status` = ".$Guid_status);
        
    }else{
        $statuses = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$parent." AND visibility='1' ORDER BY order_by ASC, Guid_status ASC");
    }
    
    $content = '<div class="f2  ">
                    <div class="group">
                        <select data-parent="'.$parent.'" required class="status-dropdown" name="status[]" id="">
                            <option value="">Select Status</option>';    
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
    $roles= $db->query('SELECT * FROM `tblrole` WHERE `role`<>"Admin" AND `role`<>"Patient" AND `role`<>"MDL Patient"');
    
    $content = "";
    if ( $statuses ) {
        $content ='';
        $prefix = 0;
        
        foreach ( $statuses as $status ) {  
         
            $checkCildren = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$status['Guid_status']);
            $optionClass = '';
           
            if ( !empty($checkCildren) ) { 
                $optionClass = 'has_sub';   
            }         
            
            $content .= "<td>".$status['Guid_status'] . "</td>";
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
            $content .= "<td class='roles'>";
            if($roles){
                $content .= "<p><span class='toggleThisRoles pull-right far fa-eye-slash'></span></p>";
                $content .= "<div class='rolesBlock hidden'>";   //.hidden             
                foreach ($roles as $k => $v) {
                    $checked = "";                    
                    if($status['access_roles'] && $status['access_roles']!=""){
                        $accessRoles = unserialize($status['access_roles']); 
                        if($accessRoles){
                            if(array_key_exists($v['Guid_role'], $accessRoles)){
                                $checked = " checked";
                            }       
                        }
                    }                    
                    $content .= "<p><input name=status[roles][".$status['Guid_status']."][".$v["Guid_role"]."] type='checkbox' ".$checked." />".$v['role']."</p>";
                }
                $content .= "</div>";
            }
            $content .= "</td>";
            $content .= "</tr>";
            if ( !empty($checkCildren) ) {
                $prefix .= '-';            
                $content .= get_nested_ststus_editable_rows( $db, $status['Guid_status'], $level . "-&nbsp;" );
            }
        }
        
    }
   
    return $content;
}
function getDmdlEditableCategories($db) {
    $categories = $db->query("SELECT * FROM tbl_mdl_category");
   
    $content = "";
    if ( !empty($categories) ) {
        
        foreach ( $categories as $k=>$category ) {  
            $content .= "<tr>";
            $content .= "<td>".$category['Guid_category'];
            $content .= "<input type='hidden' name=category[".$category['Guid_category']."][Guid_category] value='".$category['Guid_category']."' />";
            $content .= "</td>";            
            $content .= "<td>";
            $content .= "<input name=category[".$category['Guid_category']."][slug] type='text' value='".$category['slug']."'/>";
            $content .= "</td>";            
            $content .= "<td>";
            $content .= "<input name=category[".$category['Guid_category']."][name] type='text' value='".$category['name']."' />";
            $content .= "</td>";
            $content .= "</tr>";
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
    $getTestUsers = $db->query("SELECT Guid_user FROM `tbluser` WHERE marked_test=:marked_test", array('marked_test'=>'1'));
    $userIds = "";
    foreach ($getTestUsers as $k=>$v){
        $userIds .= $v['Guid_user'].', ';
    }
    $markedTestUserIds = rtrim($userIds, ', ');
    
    return $markedTestUserIds;
}

function getTestUserIDs($db){
    $q = "SELECT p.Guid_user FROM tblpatient p 
            WHERE CONCAT(AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%'), ' ', AES_DECRYPT(p.lastname_enc, 'L@stn@m3&%#')) LIKE '%test%' 
            OR CONCAT(AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%'), ' ', AES_DECRYPT(p.lastname_enc, 'L@stn@m3&%#')) LIKE '%John Smith%' 
            OR CONCAT(AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%'), ' ', AES_DECRYPT(p.lastname_enc, 'L@stn@m3&%#')) LIKE '%John Doe%' 
            OR CONCAT(AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%'), ' ', AES_DECRYPT(p.lastname_enc, 'L@stn@m3&%#')) LIKE '%Jane Doe%' ";
    $getTestUsers = $db->query($q);
    $userIds = "";
    foreach ($getTestUsers as $k=>$v){
        $userIds .= $v['Guid_user'].', ';
    }
    $testUserIds = rtrim($userIds, ', ');
    
    return $testUserIds;    
}

function formatDate($date){
    if (empty($date)) {
	return '';
    } else {
	return date("n/j/Y", strtotime($date));
    }
}

function dbDateFormat($date){
    if (empty($date)) {
	return '';
    } else {
	return date("Y-m-d H:i:s", strtotime($date));
    }
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
    $filterUrlStr = "";
    //$testUserIds = '';
    $q = "SELECT statuses.*, statuslogs.*, 
            account.Guid_category,
            mdlnum.mdl_number as mdl_number
            FROM `tbl_mdl_status` statuses
            LEFT JOIN `tbl_mdl_status_log` statuslogs ON statuses.`Guid_status`= statuslogs.`Guid_status`
            LEFT JOIN `tbl_mdl_number` mdlnum ON statuslogs.Guid_user=mdlnum.Guid_user 
            LEFT JOIN `tblaccount` account ON account.Guid_account=statuslogs.Guid_account ";
    
    $q .=  "WHERE  statuslogs.`currentstatus`='Y' ";
    
    if(!empty($searchData)){ 
        if (isset($searchData['from_date']) && $searchData['from_date']!="" && isset($searchData['to_date']) && $searchData['to_date']!="") {
            if ($searchData['from_date'] == $searchData['to_date']) {
                $q .= " AND statuslogs.Date LIKE '%" . date("Y-m-d", strtotime($searchData['from_date'])) . "%'";
            } else {
                $q .= " AND statuslogs.Date BETWEEN '" . date("Y-m-d", strtotime($searchData['from_date'])) . "' AND '" . date("Y-m-d", strtotime($searchData['to_date'])) . "'";
            }
            $filterUrlStr .= "&from=".date("Y-m-d", strtotime($searchData['from_date']));
            $filterUrlStr .= "&to=".date("Y-m-d", strtotime($searchData['to_date']));
        }
        if(isset($searchData['mdl_number']) && $searchData['mdl_number']!=""){
            $q .= " AND mdl_number='".$searchData['mdl_number']."' ";
            $filterUrlStr .= "&mdnum=".$searchData['mdl_number'];
        }
        if(isset($searchData['Guid_salesrep']) && $searchData['Guid_salesrep']!=""){
            $q .= " AND statuslogs.Guid_salesrep='".$searchData['Guid_salesrep']."' ";
            $filterUrlStr .= "&salesrep=".$searchData['Guid_salesrep'];
        }
        if(isset($searchData['Guid_account']) && $searchData['Guid_account']!=""){
            $q .= " AND statuslogs.Guid_account='".$searchData['Guid_account']."' ";
            $filterUrlStr .= "&account=".$searchData['Guid_account'];
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
    
    $thisUserID = $_SESSION['user']['id'];
    $roleInfo = getRole($db, $thisUserID);
    $thisUserRole = $roleInfo['role'];
    
    if($thisUserRole == "Sales Manager"){
        $userCategories = $db->query("SELECT Guid_category FROM `tbl_mdl_category_user_link` WHERE Guid_user=:Guid_user", array('Guid_user'=>$thisUserID)); 
        $userLinks = '';
        if(!empty($userCategories)){
            foreach ($userCategories as $catK=>$catV){
                $userLinks .= $catV['Guid_category'].', ';
            }
            $userLinks = rtrim($userLinks, ', ');
        }    
        if($userLinks != ''){
            $q .= " AND account.Guid_category IN (" . $userLinks . ") ";
        } 
    }
    
    
    $q .=  " AND statuslogs.Guid_patient<>'0' 
            ORDER BY statuslogs.`Date` DESC, statuses.`order_by` DESC";
    
    $stats = $db->query($q);
    $result['count'] = 0;
    if(!empty($stats)){
        $result['count'] = count($stats);
        $result['filterUrlStr'] = $filterUrlStr;
        $result['info'] = $stats;
    }  
    
    return $result;
}

function get_status_table_rows($db, $parent = 0, $searchData=array(), $linkArr=array()) {   
    
    $statusQ = "SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$parent." ORDER BY order_by ASC";
    $statuses = $db->query($statusQ);
    $filterUrlStr = "";
    $content = '';    
    if ( $statuses ) {
        foreach ( $statuses as $status ) {  
            $checkCildren = $db->query("SELECT * FROM tbl_mdl_status "
                    . "WHERE `parent_id` = ".$status['Guid_status']);
            $stats = get_stats_info($db, $status['Guid_status'], FALSE, $searchData);
            $link = '';
            if($stats['count']!=0){
                $optionClass = '';
                $filterUrlStr = $stats['filterUrlStr'];
                if(empty($linkArr)){
                    $link .= SITE_URL.'/mdl-stat-details.php?status_id='.$status['Guid_status'].$filterUrlStr;
                } else {
                    $link .=  SITE_URL.'/accounts.php?status_id='.$status['Guid_status'].'&';
                    foreach ($linkArr as $k=>$v){
                        $link .= $k.'='.$v.'&';
                    }
                    $link = rtrim($link,'&');                    
                }
                if ( !empty($checkCildren) ) { 
                    $optionClass = 'has_sub';                 
                } 
                $content .= "<tr id='".$status['Guid_status']."' class='parent ".$optionClass."'>";
                $content .= "<td class='text-left'><span>".$status['status'].'</span></td>';            
                $content .= '<td><a href="'.$link.'">'.$stats['count'].'</a></td>';
                if ( !empty($checkCildren) ) {
                    $content .= get_status_child_rows( $db, $status['Guid_status'], "&nbsp;", $searchData, $linkArr );
                }            
                $content .= "</tr>";
            }
        }
    }   
   
    return $content;
}
function get_status_child_rows($db, $parent = 0,  $level = '', $searchData=array(), $linkArr=array()) {
    $statuses = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$parent."  ORDER BY order_by ASC");
    if ( $statuses ) {
        $content ='';
        $prefix = 0;
        foreach ( $statuses as $status ) { 
            $checkCildren = $db->query("SELECT * FROM tbl_mdl_status WHERE `parent_id` = ".$status['Guid_status']);
            $optionClass = '';  
            $stats = get_stats_info($db, $status['Guid_status'], TRUE, $searchData);
            if($stats['count']!=0){
                $filterUrlStr = $stats['filterUrlStr'];
                if ( !empty($checkCildren) ) { 
                    $optionClass = 'parent has_sub';   
                }  
                $link = '';
                if(empty($linkArr)){
                    $link .= SITE_URL.'/mdl-stat-details.php?status_id='.$status['Guid_status'].'&parent='.$parent.$filterUrlStr;
                } else {
                    $link .=  SITE_URL.'/accounts.php?status_id='.$status['Guid_status'].'&parent='.$parent.'&';
                    foreach ($linkArr as $k=>$v){
                        $link .= $k.'='.$v.'&';
                    }
                    $link = rtrim($link,'&');                    
                }
                $content .= "<tr id='".$status['Guid_status']."' data-parent-id='".$parent."' class='sub ".$optionClass."'>";
                $content .= "<td class='text-left'><span>".$level . " " .$status['status'].'</span></td>';
                $content .= '<td><a href="'.$link.'">'.$stats['count']. '</a></td>';
                if ( !empty($checkCildren) ) {
                    $prefix .= '&nbsp;';
                    $content .= get_status_child_rows( $db, $status['Guid_status'], $level . "&nbsp;", $searchData, $linkArr );
                }
                 $content .= "</tr>";
            }
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
                                WHERE r.`Guid_payor`= $Guid_payor AND r.Guid_user=$Guid_user
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
function getStatusRevenueTotals($db, $Guid_status, $searchData=array()){    
    $usersQ = "SELECT sl.*, mn.mdl_number FROM `tbl_mdl_status_log` sl "
            . "LEFT JOIN tbl_mdl_number mn "
            . "ON sl.Guid_user=mn.Guid_user "; 
    
    $usersQ .= " WHERE sl.Guid_status=:Guid_status AND sl.currentstatus='Y' ";
 
    if(!empty($searchData)){
        
        //adding filter conditions 
        if(isset($searchData['Guid_salesrep'])&&$searchData['Guid_salesrep']!=""){
            $usersQ .= 'AND sl.Guid_salesrep='.$searchData['Guid_salesrep'].' ';
        }
        if(isset($searchData['Guid_account'])&&$searchData['Guid_account']!=""){
            $usersQ .= 'AND sl.Guid_account='.$searchData['Guid_account'].' ';
        }
        if(isset($searchData['mdl_number'])&&$searchData['mdl_number']!=""){
            $usersQ .= 'AND mn.mdl_number='.$searchData['mdl_number'].' ';
        }
        if( isset($searchData['from_date']) && isset($searchData['to_date']) ){
            if ($searchData['from_date'] == $searchData['to_date']) {
                $usersQ .= " AND sl.Date LIKE '%" . date("Y-m-d", strtotime($searchData['from_date'])) . "%'";
            } else {
                $usersQ .= " AND sl.Date BETWEEN '" . date("Y-m-d", strtotime($searchData['from_date'])) . "' AND '" . date("Y-m-d", strtotime($searchData['to_date'])) . "'";
            }
        }
    }
    
    $users = $db->query($usersQ, array('Guid_status'=>$Guid_status, ));

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

/**
 * Top Nav Links
 * @param type $role
 * @return string
 */
function topNavLinks($role=FALSE){
    $content = '<a href="'.SITE_URL.'/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>';
    if($role=='Physician'){
        $content .= '<a href="'.SITE_URL.'/accounts.php" class="button homeIcon"></a>';
    }else{
       $content .= '<a href="'.SITE_URL.'/dashboard2.php" class="button homeIcon"></a>'; 
    }    
    $content .= '<a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit smaller_button"><strong>View Questionnaire</strong></a>';

    return $content;
}


function get_status_state($db, $parent = 0, $searchData=array(), $linkArr=array(), $today) {
    $statuses = array('28' => 'Registered Paient', '36' => 'Completed Questionnaire', '16' => 'Insufficient Informatin' , '29' => 'Medically Qualified' );
    $filterUrlStr = "";
    $content = '';    
    foreach ($statuses as $key => $status) {
        $stats1 = get_stats_info($db, $key, FALSE, $searchData);
        $stats2 = get_stats_info_today($db, $key, FALSE, $searchData, $today);
        $content .= "<tr class='parent'>";
        $content .= "<td class='text-left'><span>".$status."</span></td>";            
        $content .= '<td><a>'.$stats2['count'].'</a></td>';
        $content .= '<td><a>'.$stats1['count'].'</a></td>';
        $content .= "</tr>";    
    }    
    return $content;
}


function get_stats_info_today($db, $statusID, $hasChildren=FALSE, $searchData=array(), $today){    
    //exclude test users
    $markedTestUserIds = getMarkedTestUserIDs($db);
    $testUserIds = getTestUserIDs($db);
    $filterUrlStr = "";
    //$testUserIds = '';
    $q = "SELECT patient.insurance_name,
            statuses.*, statuslogs.*,            
            mdlnum.mdl_number as mdl_number
            FROM `tbl_mdl_status` statuses
            LEFT JOIN `tbl_mdl_status_log` statuslogs ON statuses.`Guid_status`= statuslogs.`Guid_status`
            LEFT JOIN `tbl_mdl_number` mdlnum ON statuslogs.Guid_user=mdlnum.Guid_user 
            LEFT JOIN `tblpatient` patient ON patient.Guid_patient=statuslogs.Guid_patient ";

    if(empty($searchData['from_date'])) {
        $q .= "WHERE DATE(statuslogs.`Date`)=:today "; //=:today statuslogs.`currentstatus`='Y' `currentstatus`='Y' AND DATE(statuslogs.`Date`) =:today
    } else {
        $q .= "WHERE statuslogs.`currentstatus`  ";
    }

    if(!empty($searchData)){ 
        if (isset($searchData['from_date']) && $searchData['from_date']!="" && isset($searchData['to_date']) && $searchData['to_date']!="") {
            if ($searchData['from_date'] == $searchData['to_date']) {
                $q .= " AND statuslogs.Date LIKE '%" . date("Y-m-d", strtotime($searchData['from_date'])) . "%'";
            } else {
                $q .= " AND statuslogs.Date BETWEEN '" . date("Y-m-d", strtotime($searchData['from_date'])) . "' AND '" . date("Y-m-d", strtotime($searchData['to_date'])) . "'";
            }
            $filterUrlStr .= "&from=".date("Y-m-d", strtotime($searchData['from_date']));
            $filterUrlStr .= "&to=".date("Y-m-d", strtotime($searchData['to_date']));
        }
        if(isset($searchData['mdl_number']) && $searchData['mdl_number']!=""){
            $q .= " AND mdl_number='".$searchData['mdl_number']."' ";
            $filterUrlStr .= "&mdnum=".$searchData['mdl_number'];
        }
        if(isset($searchData['Guid_salesrep']) && $searchData['Guid_salesrep']!=""){
            $q .= " AND statuslogs.Guid_salesrep='".$searchData['Guid_salesrep']."' ";
            $filterUrlStr .= "&salesrep=".$searchData['Guid_salesrep'];
        }
        if(isset($searchData['Guid_account']) && $searchData['Guid_account']!=""){
            $q .= " AND statuslogs.Guid_account='".$searchData['Guid_account']."' ";
            $filterUrlStr .= "&account=".$searchData['Guid_account'];
        }
    }
  
    $q .=  //" AND statuslogs.`Guid_status_log`<>''
            " AND statuslogs.Guid_status=$statusID ";
    if($markedTestUserIds!=""){
    $q .=  " AND statuslogs.Guid_user NOT IN(".$markedTestUserIds.") ";
    }
    if($testUserIds!=""){
    $q .=  " AND statuslogs.Guid_user NOT IN(".$testUserIds.") ";   
    }
    $q .=  " AND statuslogs.Guid_patient<>'0' 
            ORDER BY statuslogs.`Date` DESC, statuses.`order_by` DESC";

    //echo $q;
    //exit;

    $stats = $db->query($q, array('today'=>$today));
    $result['count'] = 0;
    if(!empty($stats)){
        $result['count'] = count($stats);
        $result['filterUrlStr'] = $filterUrlStr;
        $result['info'] = $stats;
    }  
    
    return $result;
}

function updateOrInsertAccount($db, $apiData){
   
    $checkAccount = $db->row("SELECT * FROM tblaccount WHERE account=:account", array('account'=>$apiData['number']));
    
    if(!empty($checkAccount)){ 
        //update account for empty values only
        //check if it already has value don't update
        $Guid_account = $checkAccount['Guid_account'];

        if(isset($checkAccount['account']) && $checkAccount['account']==''){
            if(isset($apiData['number'])){
                $accountData['account'] = $apiData['number'];
            }
        }
        if(isset($checkAccount['name']) && $checkAccount['name']==''){
            if(isset($apiData['name'])){                
                $accountData['name'] = $apiData['name'];
            }
        }
        if(isset($checkAccount['address']) && $checkAccount['address']==''){
            if(isset($apiData['address'])){        
                $accountData['address'] = $apiData['address'];
            }
        }
        if(isset($checkAccount['address1']) && $checkAccount['address1']==''){ 
            if(isset($apiData['address1'])){              
                $accountData['address1'] = $apiData['address1'];
            }
        }
        if(isset($checkAccount['city']) && $checkAccount['city']==''){ 
            if(isset($apiData['city'])){              
                $accountData['city'] = $apiData['city'];
            }
        }    
        if(isset($checkAccount['state']) && $checkAccount['state']==''){
            if(isset($apiData['state'])){            
                $accountData['state'] = $apiData['state'];
            }
        }
        if(isset($checkAccount['zip']) && $checkAccount['zip']==''){
            if(isset($apiData['zip'])){            
                $accountData['zip'] = $apiData['zip'];
            }
        }
        if(isset($checkAccount['phone_number']) && $checkAccount['phone_number']==''){
            if(isset($apiData['phone_number'])){        
                $accountData['phone_number'] = $apiData['phone_number'];
            }
        }
        if(isset($checkAccount['fax']) && $checkAccount['fax']==''){
            if(isset($apiData['fax'])){
                $accountData['fax'] = $apiData['fax'];
            }
        } 
        if(!empty($accountData)){
            $whereAccount = array('Guid_account'=>$Guid_account);
            $updateAccount = updateTable($db, 'tblaccount', $accountData, $whereAccount);
        }        
    } else {
    //insert new account
        if(isset($apiData['number'])){
            $accountData['account'] = $apiData['number'];
        }
        if(isset($apiData['name'])){                
            $accountData['name'] = $apiData['name'];
        }
        if(isset($apiData['address'])){        
            $accountData['address'] = $apiData['address'];
        }
        if(isset($apiData['address1'])){              
            $accountData['address1'] = $apiData['address1'];
        }
        if(isset($apiData['city'])){              
            $accountData['city'] = $apiData['city'];
        }
        if(isset($apiData['state'])){            
            $accountData['state'] = $apiData['state'];
        }
        if(isset($apiData['zip'])){            
            $accountData['zip'] = $apiData['zip'];
        }
        if(isset($apiData['phone_number'])){        
            $accountData['phone_number'] = $apiData['phone_number'];
        }
        if(isset($apiData['fax'])){
            $accountData['fax'] = $apiData['fax'];
        }
        if(!empty($accountData)){
            $accountData['Loaded']='Y';
            $accountData['Guid_category']='2'; //Guid for Corporate category
            $insertAccount = insertIntoTable($db, 'tblaccount', $accountData);
            $Guid_account = $insertAccount['insertID'];
        }
    }
    
    return $Guid_account;    
}

function updateOrInsertProvider($db,$accountNum, $Guid_account, $Guid_user, $apiProviderData){    
    //check provider   
    $checkProvider = $db->row("SELECT * FROM tblprovider WHERE account_id=:account_id", array('account_id'=>$accountNum)); 
    if(!empty($checkProvider)){ //update fields which are empty                                    
        
        $Guid_provider = $checkProvider['Guid_provider'];
        
        if(isset($checkProvider['npi']) && $checkProvider['npi']==''){
            $providerData['npi'] = $apiProviderData['npi'];
        }
        if(isset($checkProvider['first_name']) && $checkProvider['first_name']==''){
            $providerData['first_name'] = $apiProviderData['FirstName'];
        }
        if(isset($checkProvider['last_name']) && $checkProvider['last_name']==''){
            $providerData['last_name'] = $apiProviderData['LastName'];
        }
        if(isset($checkProvide['title']) && !empty($checkProvider['title'])){
            $providerData['title'] = $apiProviderData['Physician_Title'];
        }
        if(isset($Guid_provider) && !empty($providerData)){
            $updateProvider = updateTable($db, 'tblprovider', $providerData, array('Guid_provider'=>$Guid_provider));
        }                               
    } else { //insert
        $providerData = array(
            'Guid_user'=>$Guid_user,
            'Guid_account'=>$Guid_account,
            'account_id'=>$accountNum,
            'Loaded'=>'Y'
        );
        if(isset($apiProviderData['FirstName'])){
            $providerData['first_name'] = $apiProviderData['FirstName'];
        }
        if(isset($apiProviderData['LastName'])){
            $providerData['last_name'] = $apiProviderData['LastName'];
        }
        if(isset($apiProviderData['npi'])){
            $providerData['npi'] = $apiProviderData['npi'];
        }
        if(isset($apiProviderData['Physician_Title'])){
            $providerData['title'] = $apiProviderData['Physician_Title'];
        }
        $insertProvider = insertIntoTable($db, 'tblprovider', $providerData);
        $Guid_provider = $insertProvider['insertID'];
    }

    return $Guid_provider;
    
}
function loadTableData($db, $tableName, $tableClass='', $tableID=''){
    $columns = $db->query('SHOW COLUMNS FROM ' . $tableName);
    $data = $db->query('SELECT * FROM ' . $tableName);
    
    $cnt = "";
    $cnt .= "<table class='".$tableClass."' id='".$tableID."'>";
    $cnt .= "<thead>";
    $cnt .= "<tr>";
    foreach ($columns as $k=>$v){
        $cnt .= "<th>";
        $cnt .=  $v['Field'];
        $cnt .= "</th>";
    }
    $cnt .= "</tr>";
    $cnt .= "</thead>";
    
    $cnt .= "<tbody>";
    
    foreach ($data as $k=>$rowData){
        $cnt .= "<tr>";
        foreach ($rowData as $key=>$val){
        $cnt .= "<td>";
        $cnt .= $val;
        $cnt .= "</td>";
        }
        $cnt .= "</tr>";
    }
    
    $cnt .= "</tbody>";
    $cnt .= "</table>";
    
    
    return $cnt;
}

/**
 * API Date format month-day-year ex. 07-31-2017
 * Converted date year-month-day ex. 2017-07-31
 * @param type $date
 * @return string
 */
function convertDmdlDate($date){
    $dateExp  = explode("-", $date) ;            
    $convertedDate = $dateExp['2']."-".$dateExp['0']."-".$dateExp['1'];
    
    return $convertedDate;
}

function getPaientPossibleMatch($db,$firstname,$lastname,$Date_Of_Birth){
    $SQuery = "SELECT p.Guid_patient, p.Guid_user, p.dob,"
            . "AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%') as firstname,"
            . "AES_DECRYPT(p.lastname_enc, 'L@stn@m3&%#') as lastname "
            . "FROM tblpatient p "
            . "LEFT JOIN tbluser u ON u.Guid_user = p.Guid_user "
            . "WHERE u.marked_test='0' AND u.Loaded='N' AND p.Linked='N' "
            . "AND (LOWER(CONVERT(AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%') USING 'utf8')) LIKE '%".strtolower($firstname)."%' "
            . "OR LOWER(CONVERT(AES_DECRYPT(p.lastname_enc, 'L@stn@m3&%#') USING 'utf8')) LIKE '%".strtolower($lastname)."%' "
            . "OR p.dob='".convertDmdlDate($Date_Of_Birth)."' )";

    $SGetPatient = $db->query($SQuery);
    
    return $SGetPatient;
}

function getMatchedPatientsDropdown($db, $Guid_MDLNumber, $SGetPatient){
    $sOption = ''; $sContent = ''; $mdl_num = '';
    foreach ($SGetPatient as $k=>$v){
        $matchedPatient=$v; 
        $this_Guid_user = $v['Guid_user'];
        $sqlQualify = "SELECT q.account_number FROM tbl_ss_qualify q WHERE Guid_user=:Guid_user ORDER BY q.`Date_created` DESC LIMIT 1 ";
        $qualifyResult = $db->row($sqlQualify, array('Guid_user'=>$this_Guid_user));

        $mdlNumberResult = $db->query("SELECT `mdl_number` FROM `tbl_mdl_number` WHERE Guid_user=:Guid_user", array('Guid_user'=>$this_Guid_user));

        if(!empty($mdlNumberResult)){
            foreach ($mdlNumberResult as $key=>$mdlNum){
                if($mdlNum['mdl_number']!=''){
                    $mdl_num .= $mdlNum['mdl_number'].', ';
                }
            }           
        }
        
        $DOS_braca = $db->query("SELECT Date(`Date`) as Date FROM `tbl_mdl_status_log` WHERE Guid_status='1' AND Guid_user=:Guid_user",array('Guid_user'=>$this_Guid_user));
        $DOS_braca_date = '';
        if(!empty($DOS_braca)){
            foreach ($DOS_braca as $k=>$date){
                $DOS_braca_date .= $date['Date'].', ';
            }
        }
        $DOS_braca_date = rtrim($DOS_braca_date, ', ');
        $sOption .= "<option value='".$v['Guid_patient']."'>";                        
        $sOption .= ucwords(strtolower($v['firstname']." ".$v['lastname']));
        $sOption .= " (".date("m/d/Y", strtotime($matchedPatient['dob'])).") ";
        if($mdl_num!=''){
            $sOption .= "MDL#: ".$mdl_num;
        }
        if($qualifyResult['account_number']!=''){
        $sOption .= "Acct#: ".$qualifyResult['account_number'].", ";
        }
        $sOption .= "Patient ID: ".$v['Guid_patient'];
        if($DOS_braca_date!=''){
            $sOption .= ", DOS: ".date("m/d/Y", strtotime($DOS_braca_date));
        }
        $sOption .= "</option>";
    }

    $sContent .= "<select name='dmdl[".$Guid_MDLNumber."][Possible_Match]'>";
    $sContent .= "<option value=''>Select From Possible Match</option>";
    $sContent .= "<option value='create_new'>Create New</option>";
    $sContent .= $sOption;
    $sContent .= "</select>";
    return $sContent;
}

function getPaientPerfectMatch($db,$firstname,$lastname,$Date_Of_Birth){
    $dobConverted = convertDmdlDate($Date_Of_Birth);
    $query = "SELECT p.Guid_patient, p.Guid_user, p.dob,"
            . "AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%') as firstname,"
            . "AES_DECRYPT(p.lastname_enc, 'L@stn@m3&%#') as lastname "
            . "FROM tblpatient p "
            . "LEFT JOIN tbluser u ON u.Guid_user = p.Guid_user "
            . "WHERE u.marked_test='0' AND u.Loaded='N' AND p.Linked='N' "
            . "AND LOWER(CONVERT(AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%') USING 'utf8'))='".strtolower($firstname)."' "
            . "AND LOWER(CONVERT(AES_DECRYPT(p.lastname_enc, 'L@stn@m3&%#') USING 'utf8'))='".strtolower($lastname)."' "
            . "AND dob='$dobConverted'";
    //var_dump("getPaientPerfectMatch Query");
    //var_dump($query);
    $getPatient = $db->query($query);
    //var_dump($getPatient);
    return $getPatient;
}

function dmdl_refresh($db){ 
    require_once 'classes/xmlToArrayParser.php';
    ini_set("soap.wsdl_cache_enabled", 0);
    try {
        $opts = array('ssl' => array('ciphers'=>'RC4-SHA'));
        $client = new SoapClient('https://patientpayment.mdlab.com/MDL.WebService/BillingWebService?wsdl',
        array ('stream_context' => stream_context_create($opts),"exceptions"=>0));
    } catch (Exception $e) { 
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        $headers .= 'From: billingcustomerservice@mdlab.com' . "\r\n";
        $message = "faultcode: " . $e->faultcode . ", faultstring: " . $e->faultstring;
        $subject = "SOAP Fault";  
        mail('agokhale@mdlab.com', $subject, $message, $headers);
        trigger_error("SOAP Fault: (faultcode: {$e->faultcode}, faultstring: {$e->faultstring})", E_USER_ERROR);
        return;
    }    
    //Process GET variables to get $start value for LIMIT
    $per_page=50;
    //getting total number of records 
    $res=$db->row("SELECT count(Guid_mdl_dmdl) as count FROM tbl_mdl_dmdl WHERE ToUpdate='Y' AND Linked='N'");
    $total_rows=$res['count'];

    //Process GET variables to get $start value for LIMIT
    if (isset($_GET['page'])) $CUR_PAGE=($_GET['page']); else $CUR_PAGE=1;
    $start=abs(($CUR_PAGE-1)*$per_page);
    
    //skip ToUpdate='N' 
    //select [toupdate = Y] and [time of now - updatedatetime > 1 hour or is null
    /* $dmdlResult = $db->query("SELECT * FROM tbl_mdl_dmdl "
            . "WHERE ToUpdate='Y' AND Linked='N' "
            . "AND UpdateDatetime IS NULL "
            . "OR UpdateDatetime = '' "
            . "OR UpdateDatetime < NOW() - INTERVAL 60 MINUTE "); */
    $dmdlResult = $db->query("SELECT * FROM tbl_mdl_dmdl WHERE ToUpdate='Y' AND Linked='N' LIMIT $start,$per_page");
    $content=""; $match=""; $possibleM ="";
    
    $content .= "<form action='' method='POST'>";
    
    $content .= "<div class='pB-15 text-right'>";
    $content .= "<button name='dmdlUpdate' type='submit' class='botton btn-inline'>Update</button>";
    //$content .= "<button name='dmdlCreateNew' type='submit' class='botton btn-inline'>Create New</button>";    
    $content .= "</div>";
    $content .= "<table id='refresh-log-table' class='table'>";
    $content .= "<thead>";
    $content .= "<tr class='tableTopInfo'>";
    $content .= "<th colspan='7' class='dmdl'>dMDL</th>";
    $content .= "<th colspan='3' class='tbl-borderR braca'>BRCA Admin</th>";
    $content .= "</tr>";
    $content .= "<tr class='tableHeader'>";
    $content .= "<th>Matched</th>";    
    $content .= "<th>Patient F Name</th>";
    $content .= "<th>Patient L Name</th>";
    $content .= "<th>DOB</th>";
    $content .= "<th>MDL#</th>";
    $content .= "<th>Account#</th>";
    $content .= "<th>DOS</th>";
    $content .= "<th class='possiblematchTh tbl-borderR'>Possible Match</th>";
    $content .= "<th>
                    <label class='switch'>
                        <input class='selectAllCheckboxes' type='checkbox'>
                        <span class='slider round'>
                            <span id='switchLabel' class='switchLabel'>Select All</span>
                        </span>
                    </label>
                </th>";
    $content .= "</tr></thead>";
    $content .= "<tbody>";
    //var_dump("dmdlResult => ");
    //var_dump($dmdlResult);
    foreach ( $dmdlResult as $dmdlKey=>$dmdlVal ){
        $param = array(
            "patientId" => $dmdlVal['PatientID'], 
            "physicianId" => $dmdlVal['PhysicianID'],
            "mdlNumber"=>$dmdlVal['MDLNumber']
        );
        $result = (array)$client->GetGeneticResultsMDL($param);       
        $domObj = new xmlToArrayParser($result['GetGeneticResultsMDLResult']); 
        $domArr = $domObj->array; 
        if($domObj->parse_error){ 
            echo $domObj->get_xml_error();            
        } else {             
            $res = $domArr['CombinedResults']['GeneticResults'];
           
            $Guid_MDLNumber = $res['Guid_MDLNumber'];
            $Date_Of_Birth = $res['Date_Of_Birth'];          
            $firstname = $res['Patient_FirstName'];
            $lastname = $res['Patient_LastName'];           
            $accountNumber = $res['ClientID'];  
            $DOS = '';
            if(isset($res['DOS'])){
                $DOS = str_replace('-','/',$res['DOS']);
            }
            
            $dob = str_replace('-','/',$Date_Of_Birth);
            
            $where = array(
                'firstname' => $firstname,
                'lastname' => $lastname,
                'dob' => convertDmdlDate($Date_Of_Birth)
            );
            
            
            $getPatient = getPaientPerfectMatch($db,$firstname,$lastname,$Date_Of_Birth);
         
            $content .= "<tr>";
            if(empty($getPatient)){ //patient not match with dmdl data => ststus=no
                $match = "<td class='mn no'>"
                        . "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][status]' value='no' />"
                        . "No</td>"; 
                $SGetPatient = getPaientPossibleMatch($db,$firstname,$lastname,$Date_Of_Birth);                
                $sContent = "";  $sOption = ""; $matchedPatient=array();
                if(!empty($SGetPatient)){
                    $sContent .= getMatchedPatientsDropdown($db, $Guid_MDLNumber, $SGetPatient);
                } else {
                    //if there is not possible match it should create new records
                    $sContent .= "Create New";
                    $sContent .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][Possible_Match]' value='create_new' />";
                }
                $possibleM = "<td class='tbl-borderR possiblematchTh'>".$sContent."</td>";
            } else {                
                if(count($getPatient)>1){ //duplicate records => status=duplicate                  
                    $match = "<td class='hasDuplicate'>"
                            . "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][status]' value='duplicate' />"
                            . "Duplicate</td>";
                   
                    $SGetPatient = getPaientPossibleMatch($db,$firstname,$lastname,$Date_Of_Birth);

                    $sContent=""; $sOption=""; $mdl_num = ""; 
                    if(!empty($SGetPatient)){
                        $sContent .= getMatchedPatientsDropdown($db, $Guid_MDLNumber, $SGetPatient);
                    } else { //create new records if possibe match not found
                        $sContent .= "Create New";
                        $sContent .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][Possible_Match]' value='create_new' />";
                    }
                    $possibleM = "<td class='tbl-borderR possiblematchTh'>".$sContent."</td>";
                }else{ 
                    //update mdl# for this perfect match => status=yes 
                    $matchedPatient = $getPatient['0'];
                    //update the Physician MDL ID Guid_dmdl_patient
                    $dmdlData = array(
                                    'Guid_dmdl_patient'=>$res['Guid_PatientId'], 
                                    'Guid_dmdl_physician'=>$res['GUID_PhysicianID']
                                );
                    $update_dmdl_patient = updateTable($db, 'tblpatient', $dmdlData, array('Guid_patient'=>$matchedPatient['Guid_patient']));
                    $acctLink = ''; $patientInfoOption=''; $patientInfo = '';
                    if(isset($matchedPatient['Guid_user'])){
                        $this_Guid_user = $matchedPatient['Guid_user'];
                        $sqlQualify = "SELECT q.account_number FROM tbl_ss_qualify q WHERE Guid_user=:Guid_user ORDER BY q.`Date_created` DESC LIMIT 1 ";
                        $qualifyResult = $db->row($sqlQualify, array('Guid_user'=>$this_Guid_user));
                        if(isset($qualifyResult['account_number'])&&$qualifyResult['account_number']!=''){
                            $acctLink = '&account='.$qualifyResult['account_number'];
                        }
                        $mdlNumberResult = $db->query("SELECT `mdl_number` FROM `tbl_mdl_number` WHERE Guid_user=:Guid_user", array('Guid_user'=>$this_Guid_user));
                        $mdl_num = '';
                        if(!empty($mdlNumberResult)){
                            $mdlNumMatch = False;
                            foreach ($mdlNumberResult as $key => $val) {
                                if($val['mdl_number']!=''){
                                    if($val['mdl_number']==$Guid_MDLNumber){
                                        $mdlNumMatch = True;
                                    }
                                    $mdl_num .= $val['mdl_number'].', ';
                                }
                            } 
                            $mdl_num = rtrim($mdl_num, ', ');
                        }
                        $DOS_braca = $db->query("SELECT Date(`Date`) as Date FROM `tbl_mdl_status_log` WHERE Guid_status='1' AND Guid_user=:Guid_user",array('Guid_user'=>$this_Guid_user));
                        $DOS_braca_date = '';
                        if(!empty($DOS_braca)){
                            foreach ($DOS_braca as $k=>$date){
                                $DOS_braca_date .= $date['Date'].', ';
                            }
                            $DOS_braca_date = rtrim($DOS_braca_date, ', ');
                        }
                    }                    
                    if($DOS_braca_date!=''){
                        $bracaDOS = date("m/d/Y", strtotime($DOS_braca_date));
                    } else {
                        $bracaDOS = '';
                    }                    
                    $patientInfoStr = '';    
                    $patientInfoStr .= ucwords(strtolower($matchedPatient['firstname']." ".$matchedPatient['lastname']));
                    $patientInfoOption .= ucwords(strtolower($matchedPatient['firstname']." ".$matchedPatient['lastname']));
                    if($bracaDOS!=''){
                        $patientInfoStr .= " (".$bracaDOS.") ";
                        $patientInfoOption .= " (".$bracaDOS.") ";                        
                    }
                    if($mdl_num!=''){
                        if($mdlNumMatch){
                            $patientInfoStr .= "MDL#: ".$mdl_num.", ";
                            $patientInfoOption .= "MDL#: ".$mdl_num.", ";
                            $patientInfoStr .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][Possible_Match]' value='".$matchedPatient['Guid_patient']."' />";
                        }else{
                            $patientInfoStr .= "<span class='color-red'>MDL#: ".$mdl_num."</span>, ";
                            $patientInfoStr .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][Possible_Match]' value='create_new' />";
                        }
                    }
                    if(isset($qualifyResult['account_number'])&&$qualifyResult['account_number']!=''){
                        $patientInfoStr .= "Acct#: ".$qualifyResult['account_number'].", ";
                        $patientInfoOption .= "Acct#: ".$qualifyResult['account_number'].", ";
                    }
                    $patientInfoStr .= "Patient ID: ".$matchedPatient['Guid_patient'];
                    $patientInfoOption .= "Patient ID: ".$matchedPatient['Guid_patient'];
                    if($bracaDOS!=''){
                        $patientInfoStr .= ", DOS: ".$bracaDOS;
                        $patientInfoOption .= ", DOS: ".$bracaDOS;
                    }                    
                    if( $DOS === $bracaDOS ){    
                        $patientInfo .= "<a href='".SITE_URL."/patient-info.php?patient=".$matchedPatient['Guid_user'].$acctLink."'>";                        
                        $patientInfo .= $patientInfoStr;
                        $patientInfo .= "</a>";
                    } else {
                        $patientInfo .= "<select name='dmdl[".$Guid_MDLNumber."][Possible_Match]'>";
                        $patientInfo .= "<option value=''>Select From Possible Match</option>";
                        $patientInfo .= "<option value='create_new'>Create New</option>";
                        $patientInfo .= "<option value='".$matchedPatient['Guid_patient']."'>".$patientInfoOption."</option>";
                        $patientInfo .= "</select>";
                    }
                    $match = "<td class='mn yes'>"
                            . "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][status]' value='yes' />"
                            . "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][Guid_patient]' value='".$getPatient['0']['Guid_patient']."' />"
                            . "Yes</td>";  
                    $possibleM = "<td class='tbl-borderR possiblematchTh'>".$patientInfo."</td>";
                }
            }
            
            $apiDataLink = SITE_URL."/dmdlPatientInfo.php?patientId=".$dmdlVal['PatientID']."&physicianId=".$dmdlVal['PhysicianID']."&mdlNumber=".$dmdlVal['MDLNumber'];

            $content .= $match; //match status=>yes,no,duplicate  
            $content .= "<td><a target='_blank' href='".$apiDataLink."'>".ucwords(strtolower($firstname))."</a></td>";
            $content .= "<td><a target='_blank' href='".$apiDataLink."'>".ucwords(strtolower($lastname))."</a></td>";
            $content .= "<td>$dob</td>"; 
            if($dmdlVal['MDLNumber'] != $Guid_MDLNumber){
                $content .= "<td class='color-red'>dmdl: $Guid_MDLNumber <br/>csv: ".$dmdlVal['MDLNumber']."</td>";
            } else {
                $content .= "<td>$Guid_MDLNumber</td>";
            }
           
            $content .= "<td>$accountNumber</td>";            
            $content .= "<td>$DOS</td>";            
            $content .= $possibleM;
            
            $content.= "<td class='text-center'>"
                    . "<input name='dmdl[selected][".$Guid_MDLNumber."]' type='checkbox' class='checkboxSelect' />"
                    . "</td>";            
            
            //hidden inputs            
            if(isset($res['Guid_MDLNumber']) && !empty($res['Guid_MDLNumber'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][mdlnumber]' value='". $res['Guid_MDLNumber']."' />";
            }
            if(isset($dmdlVal['MDLNumber']) && !empty($dmdlVal['MDLNumber'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][csv_mdlnumber]' value='". $dmdlVal['MDLNumber']."' />";
            }
            if(isset($dmdlVal['Guid_mdl_dmdl']) && !empty($dmdlVal['Guid_mdl_dmdl'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][Guid_mdl_dmdl]' value='". $dmdlVal['Guid_mdl_dmdl']."' />";
            }
            //patient info
            if(isset($res['Guid_PatientId']) && !empty($res['Guid_PatientId'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][Guid_PatientId]' value='".$res['Guid_PatientId']."' />";
            }
            if(isset($res['Patient_FirstName']) && !empty($res['Patient_FirstName'])){
                $content .=  "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][firstname]' value='".$res['Patient_FirstName']."' />";
            }
            if(isset($res['Patient_LastName']) && !empty($res['Patient_LastName'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][lastname]' value='".$res['Patient_LastName']."' />";
            }
            if(isset($res['Patient_Ethnicity']) && !empty($res['Patient_Ethnicity'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][ethnicity]' value='".$res['Patient_Ethnicity']."' />";
            }
            if(isset($res['Date_Of_Birth'])&&!empty($res['Date_Of_Birth'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][dob]' value='".convertDmdlDate($Date_Of_Birth)."' />";
            }
            if(isset($res['Patient_CellPhone'])&&!empty($res['Patient_CellPhone'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][phone_number]' value='".$res['Patient_CellPhone']."' />";
            }
            if(isset($res['Patient_Homephone'])&&!empty($res['Patient_Homephone'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][phone_number_home]' value='".$res['Patient_Homephone']."' />";
            }
            if(isset($res['Patient_Gender'])&&!empty($res['Patient_Gender'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][gender]' value='".$res['Patient_Gender']."' />";
            }
            if(isset($res['Patient_Address1'])&&!empty($res['Patient_Address1'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][address]' value='".$res['Patient_Address1']."' />";
            }
            if(isset($res['Patient_Address2'])&&!empty($res['Patient_Address2'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][address1]' value='".$res['Patient_Address2']."' />";
            }
            if(isset($res['Patient_City'])&&!empty($res['Patient_City'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][city]' value='".$res['Patient_City']."' />";
            }
            if(isset($res['Patient_State'])&&!empty($res['Patient_State'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][state]' value='".$res['Patient_State']."' />";
            }
            if(isset($res['Patient_Zip'])&&!empty($res['Patient_Zip'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][zip]' value='".$res['Patient_Zip']."' />";
            }
            
            //Physician Info
            if(isset($res['GUID_PhysicianID'])&&!empty($res['GUID_PhysicianID'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][Physician][GUID_PhysicianID]' value='".$res['GUID_PhysicianID']."' />";
            }
            if(isset($res['Physician_NPI'])&&!empty($res['Physician_NPI'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][Physician][npi]' value='".$res['Physician_NPI']."' />";
            }
            if(isset($res['Physician_FirstName'])&&!empty($res['Physician_FirstName'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][Physician][FirstName]' value='".$res['Physician_FirstName']."' />";
            }
            if(isset($res['Physician_LastName'])&&!empty($res['Physician_LastName'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][Physician][LastName]' value='".$res['Physician_LastName']."' />";
            }
			if(isset($res['Physician_Title'])&&!empty($res['Physician_Title'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][Physician][Physician_Title]' value='".$res['Physician_Title']."' />";
            }
            
            //Account Info
            if(isset($res['ClientID']) && !empty($res['ClientID'])){ //account number
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][account][number]' value='".$res['ClientID']."' />";
            }
            if(isset($res['ClientName']) && !empty($res['ClientName'])){ //account name
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][account][name]' value='".$res['ClientName']."' />";
            }
            if(isset($res['ClientAddress1'])&&!empty($res['ClientAddress1'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][account][address]' value='".$res['ClientAddress1']."' />";
            }
            if(isset($res['ClientAddress2']) && !empty($res['ClientAddress2'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][account][address1]' value='".$res['ClientAddress2']."' />";
            }
            if(isset($res['ClientCity']) && !empty($res['ClientCity'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][account][city]' value='".$res['ClientCity']."' />";
            }
            if(isset($res['ClientState']) && !empty($res['ClientState'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][account][state]' value='".$res['ClientState']."' />";
            }
            if(isset($res['ClientZip'])&&!empty($res['ClientZip'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][account][zip]' value='".$res['ClientZip']."' />";
            }
            if(isset($res['ClientPhone'])&&!empty($res['ClientPhone'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][account][phone_number]' value='".$res['ClientPhone']."' />";
            }
            if(isset($res['ClientFax'])&&!empty($res['ClientFax'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][account][fax]' value='".$res['ClientFax']."' />";
            }
            
            //payor details
            if(isset($res['Insurance_Company']) && !empty($res['Insurance_Company'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][payor][name]' value='".$res['Insurance_Company']."' />";
            }
            if(isset($res['Payer']) && !empty($res['Payer'])){ //abbreviation of ayor name
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][payor][PayID]' value='".$res['Payer']."' />";
            }
            
            //BillingDetail
            if(isset($res['BillingDetail']['invoiceDetail']) && !empty($res['BillingDetail']['invoiceDetail'])){
                foreach ($res['BillingDetail']['invoiceDetail'] as $invKey => $invDetail){
                    if(isset($invDetail['InvoiceID'])){
                        $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][invoiceDetail][".$invKey."][InvoiceID]' value='".$invDetail['InvoiceID']."' />";
                    }
                    if(isset($invDetail['PayID'])){
                        $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][invoiceDetail][".$invKey."][PayID]' value='".$invDetail['PayID']."' />";
                    }
                    if(isset($invDetail['TestCode'])){
                        $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][invoiceDetail][".$invKey."][TestCode]' value='".$invDetail['TestCode']."' />";
                    }
                    if(isset($invDetail['CPT'])){
                        $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][invoiceDetail][".$invKey."][CPT]' value='".$invDetail['CPT']."' />";
                    }
                    if(isset($invDetail['DatePaid'])){
                        $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][invoiceDetail][".$invKey."][DatePaid]' value='".$invDetail['DatePaid']."' />";
                    }
                    
                    $amount = 0;
                    if(isset($invDetail['Total_PtPaid'])){
                        $amount += $invDetail['Total_PtPaid'];
                    }
                    if(isset($invDetail['Total_InsPaid'])){
                        $amount += $invDetail['Total_InsPaid'];
                    }
                    $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][invoiceDetail][".$invKey."][amount]' value='".$amount."' />";
                    
                }
            }
            
            //statuses
			//1. Specimen Collected
            if(isset($res['DOS']) && !empty($res['DOS'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][SpecimenCollected][Date]' value='".convertDmdlDate($res['DOS'])."' />";
            }
			//2. Specimen Accessioned, 2.1 Test Codes
            if(isset($res['Date_Accessioned']) && !empty($res['Date_Accessioned'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][SpecimenAccessioned][Date]' value='".convertDmdlDate($res['Date_Accessioned'])."' />";
            } 
			if(isset($res['Test_Ordered']) && !empty($res['Test_Ordered'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][SpecimenAccessioned][Test_Ordered]' value='".$res['Test_Ordered']."' />";
            }   
			//3.1 Insurance Preauthorization: Pending: Eligibility Review
			if(isset($res['IPP_EligibilityReview']) && !empty($res['IPP_EligibilityReview'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][IPP_EligibilityReview][Date]' value='".$res['IPP_EligibilityReview']."' />";
            }
			//3.2 Insurance Preauthorization: Pending: Preauthorization Review
			if(isset($res['IPP_PreauthorizationReview']) && !empty($res['IPP_PreauthorizationReview'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][IPP_PreauthorizationReview][Date]' value='".$res['IPP_PreauthorizationReview']."' />";
            }
			//3.3 Insurance Preauthorization: Pending: Legal Policy
			if(isset($res['IPP_LegalPolicy']) && !empty($res['IPP_LegalPolicy'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][IPP_LegalPolicy][Date]' value='".$res['IPP_LegalPolicy']."' />";
            }
			//3.4 Insurance Preauthorization: Not Required
			if(isset($res['IP_NotRequired']) && !empty($res['IP_NotRequired'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][IP_NotRequired][Date]' value='".$res['IP_NotRequired']."' />";
            }
			//3.5 Insurance Preauthorization: Approved
			if(isset($res['IP_Approved']) && !empty($res['IP_Approved'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][IP_Approved][Date]' value='".$res['IP_Approved']."' />";
            }
			//3.6 Insurance Preauthorization: Declined: Medical Necessity Not Met
			if(isset($res['IP_Declined_MedNecNotMet']) && !empty($res['IP_Declined_MedNecNotMet'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][IP_Declined_MedNecNotMet][Date]' value='".$res['IP_Declined_MedNecNotMet']."' />";
            }
			//3.7 Insurance Preauthorization: Declined: Not a Covered Benefit
			if(isset($res['IP_Declined_NotCovered']) && !empty($res['IP_Declined_NotCovered'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][IP_Declined_NotCovered][Date]' value='".$res['IP_Declined_NotCovered']."' />";
            }
			//3.8 Insurance Preauthorization: Declined: Experimental/Investigational
			if(isset($res['IP_Declined_Experimental']) && !empty($res['IP_Declined_Experimental'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][IP_Declined_Experimental][Date]' value='".$res['IP_Declined_Experimental']."' />";
            }
			//3.9 Insurance Preauthorization: Declined: MDL is OON
			if(isset($res['IP_Declined_MDLisOON']) && !empty($res['IP_Declined_MDLisOON'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][IP_Declined_MDLisOON][Date]' value='".$res['IP_Declined_MDLisOON']."' />";
            }
			//3.10 Insurance Preauthorization: Physician Responsibility: New Requisition Required
			if(isset($res['IP_PhysAct_NewReqRequired']) && !empty($res['IP_PhysAct_NewReqRequired'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][IP_PhysAct_NewReqRequired][Date]' value='".$res['IP_PhysAct_NewReqRequired']."' />";
            }
			//3.11 Insurance Preauthorization: Physician Responsibility: Additional ICD-10 Codes/Info Required
			if(isset($res['IP_PhysAct_AddICD10Required']) && !empty($res['IP_PhysAct_AddICD10Required'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][IP_PhysAct_AddICD10Required][Date]' value='".$res['IP_PhysAct_AddICD10Required']."' />";
            }
			//3.12 Insurance Preauthorization: Physician Action Required: Physician Consultation Required
			if(isset($res['IP_PhysAct_PhysConsultationRequired']) && !empty($res['IP_PhysAct_PhysConsultationRequired'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][IP_PhysAct_PhysConsultationRequired][Date]' value='".$res['IP_PhysAct_PhysConsultationRequired']."' />";
            }
			//3.13 Insurance Preauthorization: Physician Action Required: Precertification Required: AIMs
			if(isset($res['IP_PhysAct_PrecertificationRequiredAIMS']) && !empty($res['IP_PhysAct_PrecertificationRequiredAIMS'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][IP_PhysAct_PrecertificationRequiredAIMS][Date]' value='".$res['IP_PhysAct_PrecertificationRequiredAIMS']."' />";
            } 
			//3.14 Insurance Preauthorization: Physician Action Required: Precertification Required: Beacon
			if(isset($res['IP_PhysAct_PrecertificationRequiredBeacon']) && !empty($res['IP_PhysAct_PrecertificationRequiredBeacon'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][IP_PhysAct_PrecertificationRequiredBeacon][Date]' value='".$res['IP_PhysAct_PrecertificationRequiredBeacon']."' />";
            } 
			//4.1 Patient Responsibility: Assume financial responsibility
			if(isset($res['PR_AssumeFinancialResponsibility']) && !empty($res['PR_AssumeFinancialResponsibility'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][PR_AssumeFinancialResponsibility][Date]' value='".$res['PR_AssumeFinancialResponsibility']."' />";
            }  
			//4.2 Patient Responsibility: New Insurance information
			if(isset($res['PR_NewInsurance']) && !empty($res['PR_NewInsurance'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][PR_NewInsurance][Date]' value='".$res['PR_NewInsurance']."' />";
            } 
			//4.3 Patient Responsibility: Awaiting Lower Deductible
			if(isset($res['PR_LowerDeduct']) && !empty($res['PR_LowerDeduct'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][PR_LowerDeduct][Date]' value='".$res['PR_LowerDeduct']."' />";
            } 
			//4.4 Patient Responsibility: Awaiting Additional Family History
			if(isset($res['PR_AddFamilyHistory']) && !empty($res['PR_AddFamilyHistory'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][PR_AddFamilyHistory][Date]' value='".$res['PR_AddFamilyHistory']."' />";
            } 
			//4.5 Patient Responsibility: Family History Received 
			if(isset($res['PR_FamilyHistoryReceived']) && !empty($res['PR_FamilyHistoryReceived'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][PR_FamilyHistoryReceived][Date]' value='".$res['PR_FamilyHistoryReceived']."' />";
            } 			
			//4.6 & 4.7 Patient Responsibility: Genetic Counseling: Pending, Completed...          
            if(isset($res['Genetic_Counseling_Status']) && !empty($res['Genetic_Counseling_Status'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][Genetic_Counseling][Status]' value='".$res['Genetic_Counseling_Status']."' />";
            }
            if(isset($res['Genetic_Counseling_Status_Date']) && !empty($res['Genetic_Counseling_Status_Date'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][Genetic_Counseling][Date]' value='".convertDmdlDate($res['Genetic_Counseling_Status_Date'])."' />";
            }			
			//5.1 Laboratory Testing Status: Pending
			if(isset($res['Testing_Pending']) && !empty($res['Testing_Pending'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][Testing_Pending][Date]' value='".$res['Testing_Pending']."' />";
            }						
			//5.2 Laboratory Testing Status: In Progress
			if(isset($res['Testing_InProgress']) && !empty($res['Testing_InProgress'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][Testing_InProgress][Date]' value='".$res['Testing_InProgress']."' />";
            }					
			//5.3 Laboratory Testing Status: Complete
			if(isset($res['Testing_Complete']) && !empty($res['Testing_Complete'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][Testing_Complete][Date]' value='".$res['Testing_Complete']."' />";
            }					
			//5.4 Laboratory Testing Status: Recollection Requested
			if(isset($res['Testing_RecollectionRequested']) && !empty($res['Testing_RecollectionRequested'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][Testing_RecollectionRequested][Date]' value='".$res['Testing_RecollectionRequested']."' />";
            }
			//6.1 Test Cancelled: MDL Out-of-Network/High Patient Responsibility
			if(isset($res['TC_MDLOON']) && !empty($res['TC_MDLOON'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][TC_MDLOON][Date]' value='".$res['TC_MDLOON']."' />";
            }
			//6.2 Test Cancelled: MDL In-Network/High 
			if(isset($res['TC_MDLIN']) && !empty($res['TC_MDLIN'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][TC_MDLIN][Date]' value='".$res['TC_MDLIN']."' />";
            }
			//6.3 Test Cancelled: Physician Cancelled Testing 
			if(isset($res['TC_PhysicianCancelled']) && !empty($res['TC_PhysicianCancelled'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][TC_PhysicianCancelled][Date]' value='".$res['TC_PhysicianCancelled']."' />";
            }
			//6.4 Test Cancelled: Incomplete Genetic Counselling
			if(isset($res['TC_IncompleteGC']) && !empty($res['TC_IncompleteGC'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][TC_IncompleteGC][Date]' value='".$res['TC_IncompleteGC']."' />";
            }
			//6.5 Test Cancelled: Patient refused to sign consent form
			if(isset($res['TC_PatientRefused']) && !empty($res['TC_PatientRefused'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][TC_PatientRefused][Date]' value='".$res['TC_PatientRefused']."' />";
            }
			//6.6 Test Cancelled: Replaced by a new MDL
			if(isset($res['TC_NewMDL']) && !empty($res['TC_NewMDL'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][TC_NewMDL][Date]' value='".$res['TC_NewMDL']."' />";
            }
			//6.7 Test Cancelled: Cancelled following Genetic Counselor Consultation
			if(isset($res['TC_GC']) && !empty($res['TC_GC'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][TC_GC][Date]' value='".$res['TC_GC']."' />";
            }
			//6.8 Test Cancelled: Patient did not want to assume OOP costs: Out-Of-Network: Humana
			if(isset($res['TC_Humana']) && !empty($res['TC_Humana'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][TC_Humana][Date]' value='".$res['TC_Humana']."' />";
            }
			//6.9 Test Cancelled: Patient did not want to assume OOP costs: Other Insurance
			if(isset($res['TC_OtherInsurance']) && !empty($res['TC_OtherInsurance'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][TC_OtherInsurance][Date]' value='".$res['TC_OtherInsurance']."' />";
            }
			//6.10 Test Cancelled: Patient did not want to assume OOP costs: Deductible
			if(isset($res['TC_Deductible']) && !empty($res['TC_Deductible'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][TC_Deductible][Date]' value='".$res['TC_Deductible']."' />";
            }
			//6.11 Test Cancelled: Patient did not want to assume OOP costs: No Coverage due to Lack of MN
			if(isset($res['TC_NoCoverage']) && !empty($res['TC_NoCoverage'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][TC_NoCoverage][Date]' value='".$res['TC_NoCoverage']."' />";
            }
			//6.12 Test Cancelled: Patient did not want to assume OOP costs: Not a Covered Benefit
			if(isset($res['TC_NotCovered']) && !empty($res['TC_NotCovered'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][TC_NotCovered][Date]' value='".$res['TC_NotCovered']."' />";
            }
			//7.1 waiting for billed ststus updated from API
			
			//8.1 Legal/AR Review: In Progress: Legal Review
			if(isset($res['Legal_InProgress_Review']) && !empty($res['Legal_InProgress_Review'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][Legal_InProgress_Review][Date]' value='".$res['Legal_InProgress_Review']."' />";
            }
			//8.2 Legal/AR Review: In Progress: Seeking additional ICD-10 codes
			if(isset($res['Legal_InProgress_AddICD10']) && !empty($res['Legal_InProgress_AddICD10'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][Legal_InProgress_AddICD10][Date]' value='".$res['Legal_InProgress_AddICD10']."' />";
            }
			//8.3 Legal/AR Review: In Progress: Obtaining Medical Records
			if(isset($res['Legal_InProgress_OR']) && !empty($res['Legal_InProgress_OR'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][Legal_InProgress_OR][Date]' value='".$res['Legal_InProgress_OR']."' />";
            }
			//8.4 Legal/AR Review: In Progress: AR Review
			if(isset($res['Legal_InProgress_ARReview']) && !empty($res['Legal_InProgress_ARReview'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][Legal_InProgress_ARReview][Date]' value='".$res['Legal_InProgress_ARReview']."' />";
            }
			//8.5 Legal/AR Review: Policy Limitation
			if(isset($res['Legal_PolicyLimitation']) && !empty($res['Legal_PolicyLimitation'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][Legal_PolicyLimitation][Date]' value='".$res['Legal_PolicyLimitation']."' />";
            }
			//8.6 Legal/AR Review: Appeal Submitted
			if(isset($res['Legal_AppealSubmitted']) && !empty($res['Legal_AppealSubmitted'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][statuses][Legal_AppealSubmitted][Date]' value='".$res['Legal_AppealSubmitted']."' />";
            }
			 
			 
			 
			 
            //Revenue section on the screen
            if(isset($res['Payor']) && !empty($res['Payor'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][revenue][Payor]' value='".$res['Payor']."' />";
            }
            if(isset($res['Patient_Responsibility']) && !empty($res['Patient_Responsibility'])){
                $content .= "<input type='hidden' name='dmdl[".$Guid_MDLNumber."][revenue][PatientAmount]' value='".$res['Patient_Responsibility']."' />";
            }
            
            $content .= "</tr>";
        }        
        //Getting page URL without query string
        $uri=strtok($_SERVER['REQUEST_URI'],"?")."?";
        
        //create a new query string without a page variable
        if (isset($_GET['page'])) unset($_GET['page']);
        if (count($_GET)) {
          foreach ($_GET as $k => $v) {
            if ($k != "page") $uri.=urlencode($k)."=".urlencode($v)."&";
          }
        }
        //getting total number of pages and filling an array with links
        $num_pages=ceil($total_rows/$per_page);
        for($i=1;$i<=$num_pages;$i++) $PAGES[$i]=$uri.'page='.$i;
    }
    $content .= "</tbody>";
    $content .= "</table>";
    $content .= "</form>";
    
    if(isset($PAGES)){
    $content .= "<div class='refreshPaging'>Pages: "; 
    foreach ($PAGES as $i => $link){
        if ($i == $CUR_PAGE){
            $content .= "<span class='current'>".$i."</span>";
        } else{ 
            $content .= "<span><a href=".$link.">".$i."</a></span>";
        }    
    }
    $content .= "</div>";
    }
    
    return $content;       
}
/**
 * Update or insert new payor
 * Used in dmdl refresh screen
 * @param type $db
 * @param type $Guid_user
 * @param type $dataPayor
 * @return type number => $Guid_payor 
 */
function updateOrInsertPayor($db,$Guid_user,$dataPayor){
    //tbl_mdl_payors
    //check is payor exists by name or PayID
    $name = $dataPayor['name'];
    $PayID = $dataPayor['PayID'];
    
    $checkPayor = $db->row("SELECT * FROM `tbl_mdl_payors` WHERE name=:name OR PayID=:PayID", array('name'=>$name, 'PayID'=>$PayID));
    $thisPayorData = array();
    if(!empty($checkPayor)){ //update empty fields
        $Guid_payor = $checkPayor['Guid_payor'];
        //check if there are empty filds then fill them
        if($checkPayor['PayID']==''){
            if(isset($dataPayor['PayID'])){
                $thisPayorData['PayID'] = $dataPayor['PayID'];
            }
        }
        if(!empty($thisPayorData)){
            updateTable($db, 'tbl_mdl_payors', $thisPayorData, array('Guid_payor'=>$Guid_payor));
        }
    } else { //insert new row
        if(isset($dataPayor['name'])){
            $thisPayorData['name'] = $dataPayor['name'];
        }
        if(isset($dataPayor['PayID'])){
            $thisPayorData['PayID'] = $dataPayor['PayID'];
        }
        if(!empty($thisPayorData)){
            $thisPayorData['Loaded'] = 'Y';
            $insertPayor = insertIntoTable($db, 'tbl_mdl_payors', $thisPayorData);
            $Guid_payor = $insertPayor['insertID'];
        }
    }       
    return $Guid_payor;
}

function updateOrInsertRevenue($db, $Guid_user, $Guid_payor, $invoiceDetails){
    
    foreach ($invoiceDetails as $k=>$invoiceDetail) {        
        if(isset($invoiceDetail['CPT'])&&$invoiceDetail['CPT']!=''){
            //check CPT
            $checkCPT = $db->row("SELECT * FROM `tbl_mdl_cpt_code` WHERE code=:code",array('code'=>$invoiceDetail['CPT']));
            if(!empty($checkCPT)){
                $Guid_cpt = $checkCPT['Guid_cpt'];
            }else{ //insert new CPT code
                $newCpt = insertIntoTable($db, 'tbl_mdl_cpt_code', array('code'=>$invoiceDetail['CPT'], 'Loaded'=>'Y'));
                $Guid_cpt = $newCpt['insertID'];
            }        
            $revenueData['Guid_cpt'] = $Guid_cpt;
        }        
        if($Guid_user && $Guid_user!=''){
            $revenueData['Guid_user'] = $Guid_user;
        }
        if($Guid_payor && $Guid_payor!=''){
            $revenueData['Guid_payor'] = $Guid_payor;
        }
        if(isset($invoiceDetail['amount'])){
            $revenueData['amount'] = $invoiceDetail['amount'];
        }
        if(isset($invoiceDetail['DatePaid'])&&$invoiceDetail['DatePaid']!=''){
            $date_paid = date('Y-m-d h:i:s', strtotime($invoiceDetail['DatePaid']));
            $revenueData['date_paid'] = $date_paid;
        }        
        if(!empty($revenueData)){
            $revenueData['Loaded'] = 'Y';
            $insertRevenue = insertIntoTable($db, 'tbl_revenue', $revenueData);
        }
    }
    
}

function insertDmdlStatuses($db,$statuses,$data, $dmdl_mdl_number,$Guid_mdl_dmdl){    
    
    //var_dump($data);
    $statusLogData = array(
        'Loaded' => 'Y',
        'Guid_user' => $data['Guid_user'],
        'Guid_patient'=> $data['Guid_patient'],
        'Guid_account' => $data['Guid_account'],
        'account' => $data['account'],
        'Guid_salesrep' => $data['Guid_salesrep'],
        'salesrep_fname' => $data['salesrep_fname'],
        'salesrep_lname' => $data['salesrep_lname'],
        'Recorded_by' => $_SESSION['user']['id'],  
        'provider_id' => $data['provider_id'],
        'deviceid' => $data['deviceid'],        
        'Date_created'=>date('Y-m-d h:i:s')
    );
    // 1. Specimen Collected
    if(isset($statuses['SpecimenCollected']['Date'])){
        $statusLogData['Date'] = $statuses['SpecimenCollected']['Date'];
        updateTable($db, 'tblpatient', array('specimen_collected'=>'Yes'), array('Guid_patient'=>$data['Guid_patient']));
        saveStatusLog($db, array('1'), $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }   
    //2 Specimen Accessioned, 2.1 SpecimenAccessioned Test Code
    if(isset($statuses['SpecimenAccessioned']['Date'])){
        $statusLogData['Date'] = $statuses['SpecimenAccessioned']['Date'];
        $statusSAccIDs[] = '2';        
        if(isset($statuses['SpecimenAccessioned']['Test_Ordered']) && $statuses['SpecimenAccessioned']['Test_Ordered']!=''){ 
            //checking for test codes
            $apiTestCodes = explode(',', $statuses['SpecimenAccessioned']['Test_Ordered']);
            $allowedTestCodes = array('1221', '1222', '1223', '1224', '1235', '1241', '1243', '1268', '1279');
            
            foreach ($apiTestCodes as $key => $value) {
                if(in_array($value, $allowedTestCodes)){
                    $getGuidStatusRow = $db->row("SELECT Guid_status FROM tbl_mdl_status WHERE `parent_id`='2' AND `status`='$value'");
                    if(!empty($getGuidStatusRow)){
                        $statusSAccIDs[] = $getGuidStatusRow['Guid_status'];
                        saveStatusLog($db, $statusSAccIDs, $statusLogData);
                        updateCurrentStatusID($db, $data['Guid_patient']);
                    }
                }
            }
        }
        
    }
    //3.1 Insurance Preauthorization: Pending: Eligibility Review
    if(isset($statuses['IPP_EligibilityReview']['Date'])){
        $statusLogData['Date'] = $statuses['IPP_EligibilityReview']['Date'];
        $statusIPP_EIDs = array('6','7','84');  
        saveStatusLog($db, $statusIPP_EIDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //3.2 Insurance Preauthorization: Pending: Preauthorization Review
    if(isset($statuses['IPP_PreauthorizationReview']['Date'])){
        $statusLogData['Date'] = $statuses['IPP_PreauthorizationReview']['Date'];
        $statusIPP_ERIDs = array('6','7','85');  
        saveStatusLog($db, $statusIPP_ERIDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //3.3 Insurance Preauthorization: Pending: Preauthorization Review
    if(isset($statuses['IPP_LegalPolicy']['Date'])){
        $statusLogData['Date'] = $statuses['IPP_LegalPolicy']['Date'];
        $statusIPP_LPIDs = array('6','7','46');  
        saveStatusLog($db, $statusIPP_LPIDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //3.4 Insurance Preauthorization: Not Required
    if(isset($statuses['IP_NotRequired']['Date'])){
        $statusLogData['Date'] = $statuses['IP_NotRequired']['Date'];
        $statusIP_NRIDs = array('6','27');  
        saveStatusLog($db, $statusIP_NRIDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //3.5 Insurance Preauthorization: Approved
    if(isset($statuses['IP_Approved']['Date'])){
        $statusLogData['Date'] = $statuses['IP_Approved']['Date'];
        $statusIP_ApprovedIDs = array('6','8');  
        saveStatusLog($db, $statusIP_ApprovedIDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //3.6 Insurance Preauthorization: Declined: Medical Necessity Not Met
    if(isset($statuses['IP_Declined_MedNecNotMet']['Date'])){
        $statusLogData['Date'] = $statuses['IP_Declined_MedNecNotMet']['Date'];
        $statusIP_DMNM_IDs = array('6','9','10');  
        saveStatusLog($db, $statusIP_DMNM_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //3.7 Insurance Preauthorization: Declined: Not a Covered Benefit
    if(isset($statuses['IP_Declined_NotCovered']['Date'])){
        $statusLogData['Date'] = $statuses['IP_Declined_NotCovered']['Date'];
        $statusIP_DNC_IDs = array('6','9','11');  
        saveStatusLog($db, $statusIP_DNC_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //3.8 Insurance Preauthorization: Declined: Experimental/Investigational
    if(isset($statuses['IP_Declined_Experimental']['Date'])){
        $statusLogData['Date'] = $statuses['IP_Declined_Experimental']['Date'];
        $statusIP_DE_IDs = array('6','9','25');  
        saveStatusLog($db, $statusIP_DE_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //3.9 Insurance Preauthorization: Declined: MDL is OON
    if(isset($statuses['IP_Declined_MDLisOON']['Date'])){
        $statusLogData['Date'] = $statuses['IP_Declined_MDLisOON']['Date'];
        $statusIP_DMDLOON_IDs = array('6','9','33');  
        saveStatusLog($db, $statusIP_DMDLOON_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //3.10 Insurance Preauthorization: Physician Responsibility: New Requisition Required
    if(isset($statuses['IP_PhysAct_NewReqRequired']['Date'])){
        $statusLogData['Date'] = $statuses['IP_PhysAct_NewReqRequired']['Date'];
        $statusIP_PANRR_IDs = array('6','99','47');   // ????????????????????
        saveStatusLog($db, $statusIP_PANRR_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //3.11 Insurance Preauthorization: Physician Responsibility: Additional ICD-10 Codes/Info Required
    if(isset($statuses['IP_PhysAct_AddICD10Required']['Date'])){
        $statusLogData['Date'] = $statuses['IP_PhysAct_AddICD10Required']['Date'];
        $statusIP_AICD10_IDs = array('6','99','48');   // ????????????????????
        saveStatusLog($db, $statusIP_AICD10_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //3.12 Insurance Preauthorization: Physician Action Required: Physician Consultation Required
    if(isset($statuses['IP_PhysAct_PhysConsultationRequired']['Date'])){
        $statusLogData['Date'] = $statuses['IP_PhysAct_PhysConsultationRequired']['Date'];
        $statusIP_PAPCR_IDs = array('6','99','49');   // ????????????????????
        saveStatusLog($db, $statusIP_PAPCR_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //3.13 Insurance Preauthorization: Physician Action Required: Precertification Required: AIMs
    if(isset($statuses['IP_PhysAct_PrecertificationRequiredAIMS']['Date'])){
        $statusLogData['Date'] = $statuses['IP_PhysAct_PrecertificationRequiredAIMS']['Date'];
        $statusIP_PAPRAIMs_IDs = array('6','99','74','75');   // ????????????????????
        saveStatusLog($db, $statusIP_PAPRAIMs_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //3.14 Insurance Preauthorization: Physician Action Required: Precertification Required: Beacon
    if(isset($statuses['IP_PhysAct_PrecertificationRequiredBeacon']['Date'])){
        $statusLogData['Date'] = $statuses['IP_PhysAct_PrecertificationRequiredBeacon']['Date'];
        $statusIP_PAPRB_IDs = array('6','99','74','76');   // ????????????????????
        saveStatusLog($db, $statusIP_PAPRB_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //4.1 Patient Responsibility: Assume financial responsibility
    if(isset($statuses['PR_AssumeFinancialResponsibility']['Date'])){
        $statusLogData['Date'] = $statuses['PR_AssumeFinancialResponsibility']['Date'];
        $statusPR_AFR_IDs = array('34','63');   // ????????????????????
        saveStatusLog($db, $statusPR_AFR_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //4.2 Patient Responsibility: New Insurance information
    if(isset($statuses['PR_NewInsurance']['Date'])){
        $statusLogData['Date'] = $statuses['PR_NewInsurance']['Date'];
        $statusPR_NI_IDs = array('34','64');   // ????????????????????
        saveStatusLog($db, $statusPR_NI_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //4.3 Patient Responsibility: Awaiting Lower Deductible
    if(isset($statuses['PR_LowerDeduct']['Date'])){
        $statusLogData['Date'] = $statuses['PR_LowerDeduct']['Date'];
        $statusPR_LD_IDs = array('34','66');   // ????????????????????
        saveStatusLog($db, $statusPR_LD_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //4.4 Patient Responsibility: Awaiting Additional Family History
    if(isset($statuses['PR_AddFamilyHistory']['Date'])){
        $statusLogData['Date'] = $statuses['PR_AddFamilyHistory']['Date'];
        $statusPR_AFH_IDs = array('34','68');   // ????????????????????
        saveStatusLog($db, $statusPR_AFH_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //4.5 Patient Responsibility: Family History Received 
    if(isset($statuses['PR_FamilyHistoryReceived']['Date'])){
        $statusLogData['Date'] = $statuses['PR_FamilyHistoryReceived']['Date'];
        $statusPR_FHR_IDs = array('34','97');   // ????????????????????
        saveStatusLog($db, $statusPR_FHR_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //4.6 & 4.7 Patient Responsibility: Genetic Counseling: Pending,Completed,Waived    
    if(isset($statuses['Genetic_Counseling']['Status'])){
        $statusGCIDs[] = '34'; //Patient Responsibility
        $statusGCIDs[] = '3'; //Patient Responsibility --> Genetic Counseling
        $statusName = $statuses['Genetic_Counseling']['Status']; //Pending->4,Completed->5,Waived?
        $getGuidStatusRow = $db->row("SELECT Guid_status FROM tbl_mdl_status WHERE `parent_id`='3' AND `status`='$statusName'");
        if(!empty($getGuidStatusRow)){
            $statusGCIDs[] = $getGuidStatusRow['Guid_status'];
            $statusLogData['Date'] = $statuses['Genetic_Counseling']['Date'];
            saveStatusLog($db, $statusGCIDs, $statusLogData);
            updateCurrentStatusID($db, $data['Guid_patient']);
        }
    }
    //5.1 Laboratory Testing Status: Pending
    if(isset($statuses['Testing_Pending']['Date'])){
        $statusLogData['Date'] = $statuses['Testing_Pending']['Date'];
        $status_LabTP_IDs = array('17','18');  
        saveStatusLog($db, $status_LabTP_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //5.2 Laboratory Testing Status: In Progress
    if(isset($statuses['Testing_InProgress']['Date'])){
        $statusLogData['Date'] = $statuses['Testing_InProgress']['Date'];
        $status_LabTInP_IDs = array('17','19');  
        saveStatusLog($db, $status_LabTInP_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //5.3 Laboratory Testing Status: Complete
    if(isset($statuses['Testing_Complete']['Date'])){
        $statusLogData['Date'] = $statuses['Testing_Complete']['Date'];
        $status_LabTC_IDs = array('17','20');  
        saveStatusLog($db, $status_LabTC_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //5.4 Laboratory Testing Status: Recollection Requested
    if(isset($statuses['Testing_RecollectionRequested']['Date'])){
        $statusLogData['Date'] = $statuses['Testing_RecollectionRequested']['Date'];
        $status_LabTC_IDs = array('17','21');  
        saveStatusLog($db, $status_LabTC_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //6.1 Test Cancelled: MDL Out-of-Network/High Patient Responsibility
    if(isset($statuses['TC_MDLOON']['Date'])){
        $statusLogData['Date'] = $statuses['TC_MDLOON']['Date'];
        $status_TC_MDLOON_IDs = array('12','13');  
        saveStatusLog($db, $status_TC_MDLOON_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //6.2 Test Cancelled: MDL In-Network/High 
    if(isset($statuses['TC_MDLIN']['Date'])){
        $statusLogData['Date'] = $statuses['TC_MDLIN']['Date'];
        $status_TC_MDLIN_IDs = array('12','14');  
        saveStatusLog($db, $status_TC_MDLIN_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //6.3 Test Cancelled: Physician Cancelled Testing
    if(isset($statuses['TC_PhysicianCancelled']['Date'])){
        $statusLogData['Date'] = $statuses['TC_PhysicianCancelled']['Date'];
        $status_TC_phC_IDs = array('12','15');  
        saveStatusLog($db, $status_TC_phC_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //6.4 Test Cancelled: Incomplete Genetic Counselling
    if(isset($statuses['TC_IncompleteGC']['Date'])){
        $statusLogData['Date'] = $statuses['TC_IncompleteGC']['Date'];
        $status_TC_IncompleteGC_IDs = array('12','16');  
        saveStatusLog($db, $status_TC_IncompleteGC_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //6.5 Test Cancelled: Patient refused to sign consent form
    if(isset($statuses['TC_PatientRefused']['Date'])){
        $statusLogData['Date'] = $statuses['TC_PatientRefused']['Date'];
        $status_TC_PatientRefused_IDs = array('12','26');  
        saveStatusLog($db, $status_TC_PatientRefused_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //6.6 Test Cancelled: Replaced by a new MDL
    if(isset($statuses['TC_NewMDL']['Date'])){
        $statusLogData['Date'] = $statuses['TC_NewMDL']['Date'];
        $status_TC_PatientRefused_IDs = array('12','51');  
        saveStatusLog($db, $status_TC_PatientRefused_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //6.7 Test Cancelled: Cancelled following Genetic Counselor Consultation
    if(isset($statuses['TC_GC']['Date'])){
        $statusLogData['Date'] = $statuses['TC_GC']['Date'];
        $status_TC_GC_IDs = array('12','65');  
        saveStatusLog($db, $status_TC_GC_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //6.8 Test Cancelled: Patient did not want to assume OOP costs: Out-Of-Network: Humana
    if(isset($statuses['TC_Humana']['Date'])){
        $statusLogData['Date'] = $statuses['TC_Humana']['Date'];
        $status_TC_Humana_IDs = array('12','67','93','95');  
        saveStatusLog($db, $status_TC_Humana_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //6.9 Test Cancelled: Patient did not want to assume OOP costs: Other Insurance
    if(isset($statuses['TC_OtherInsurance']['Date'])){
        $statusLogData['Date'] = $statuses['TC_OtherInsurance']['Date'];
        $status_TC_OtherInsurance_IDs = array('12','67','93','96');  
        saveStatusLog($db, $status_TC_OtherInsurance_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //6.10 Test Cancelled: Patient did not want to assume OOP costs: Deductible
    if(isset($statuses['TC_Deductible']['Date'])){
        $statusLogData['Date'] = $statuses['TC_Deductible']['Date'];
        $status_TC_Deductible_IDs = array('12','67','94');  
        saveStatusLog($db, $status_TC_Deductible_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //6.11 Test Cancelled: Patient did not want to assume OOP costs: No Coverage due to Lack of MN
    if(isset($statuses['TC_NoCoverage']['Date'])){
        $statusLogData['Date'] = $statuses['TC_NoCoverage']['Date'];
        $status_TC_NoCoverage_IDs = array('12','67','98');  
        saveStatusLog($db, $status_TC_NoCoverage_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //6.12 Test Cancelled: Patient did not want to assume OOP costs: Not a Covered Benefit
    if(isset($statuses['TC_NotCovered']['Date'])){
        $statusLogData['Date'] = $statuses['TC_NotCovered']['Date'];
        $status_TC_NotCovered_IDs = array('12','67','100');  
        saveStatusLog($db, $status_TC_NotCovered_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //7.1 Billed statuses don't added yet
    
    //8.1 Legal/AR Review: In Progress: Legal Review
    if(isset($statuses['Legal_InProgress_Review']['Date'])){
        $statusLogData['Date'] = $statuses['Legal_InProgress_Review']['Date'];
        $status_Legal_InProgress_Review_IDs = array('55','56','59');  
        saveStatusLog($db, $status_Legal_InProgress_Review_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //8.2 Legal/AR Review: In Progress: Seeking additional ICD-10 codes
    if(isset($statuses['Legal_InProgress_AddICD10']['Date'])){
        $statusLogData['Date'] = $statuses['Legal_InProgress_AddICD10']['Date'];
        $status_Legal_InProgress_AddICD10_IDs = array('55','56','60');  
        saveStatusLog($db, $status_Legal_InProgress_AddICD10_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //8.3 Legal/AR Review: In Progress: Obtaining Medical Records
    if(isset($statuses['Legal_InProgress_OR']['Date'])){
        $statusLogData['Date'] = $statuses['Legal_InProgress_OR']['Date'];
        $status_Legal_InProgress_OR_IDs = array('55','56','61');  
        saveStatusLog($db, $status_Legal_InProgress_OR_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //8.4 Legal/AR Review: In Progress: AR Review
    if(isset($statuses['Legal_InProgress_ARReview']['Date'])){
        $statusLogData['Date'] = $statuses['Legal_InProgress_ARReview']['Date'];
        $status_Legal_InProgress_ARReview_IDs = array('55','56','62');  
        saveStatusLog($db, $status_Legal_InProgress_ARReview_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //8.5 Legal/AR Review: Policy Limitation
    if(isset($statuses['Legal_PolicyLimitation']['Date'])){
        $statusLogData['Date'] = $statuses['Legal_PolicyLimitation']['Date'];
        $status_Legal_PolicyLimitation_IDs = array('55','57');  
        saveStatusLog($db, $status_Legal_PolicyLimitation_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    }
    //8.6 Legal/AR Review: Appeal Submitted
    if(isset($statuses['Legal_AppealSubmitted']['Date'])){
        $statusLogData['Date'] = $statuses['Legal_AppealSubmitted']['Date'];
        $status_Legal_AppealSubmitted_IDs = array('55','58');  
        saveStatusLog($db, $status_Legal_AppealSubmitted_IDs, $statusLogData);
        updateCurrentStatusID($db, $data['Guid_patient']);
    } 
       
}

function updatePatientData($db,$data,$where){   
    $updateFields = "";
    $whereStr = "";
    $executeArray = array();
    foreach ($data as $key => $val) {
        if($key=='firstname_enc'){           
            $updateFields .= "`$key`=AES_ENCRYPT('$val', 'F1rstn@m3@_%'), ";
        } elseif ($key=='lastname_enc') {            
            $updateFields .= "`$key`=AES_ENCRYPT('$val', 'L@stn@m3&%#'), ";
        } else {
            $updateFields .= "`$key`='$val', ";
        }
    }
    $updateFields = rtrim($updateFields,", ");   
    
    foreach ($where as $key => $val) {
        $whereStr = " WHERE `$key`=:$key";
        $executeArray["$key"] = $val;
    }   
    if($updateFields!=''){
        $query = "UPDATE `tblpatient` SET $updateFields $whereStr";
        $update = $db->query($query, $executeArray);

        return $update;  
    }
    return FALSE;
}