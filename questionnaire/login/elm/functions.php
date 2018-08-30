<?php
/**
*  Redirect url
*/
function Leave($url) {
    header("Location: $url");exit;
}
/**
*  Encode given text to hash using whirlpool algo
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
	unset($_SESSION['id']);
}
/**
*	Check if user logged in
*/
function isUserLogin(){
    if( isset($_SESSION['user']['email']) && $_SESSION['user']['email'] != ''){
        return true;
    }
    return false;
}
/**
*	Return page url
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
* Return Base url
*/
function baseUrl(){
    if(isset($_SERVER['HTTPS'])){
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    }
    else{
        $protocol = 'http';
    }
    return $protocol . "://" . $_SERVER['HTTP_HOST'];
}

function getThisUserInfo(){
    if(isset($_SESSION['user'])){
        return $_SESSION['user'];
    } else {
        return FALSE;
    }
}
function getThisUserID(){
    if(isset($_SESSION['user']['id']) && $_SESSION['user']['id'] != ''){
        return $_SESSION['user']['id'];
    } else {
        return FALSE;
    }
}

function getUrlConfigurations($db, $userID){ 
    $query = "SELECT 
                    tblurlconfig.id, tblurlconfig.Guid_user, tblurlconfig.geneveda, tblurlconfig.pin, 
                    tblaccount.*,  
                    tblsource.description, tblsource.Guid_source, tblsource.code, 
                    tbldevice.`serial_number`, tbldevice.`id` AS device_id                     
                    FROM tblurlconfig 
                    LEFT JOIN `tblaccount` ON tblurlconfig.account = tblaccount.Guid_account
                    LEFT JOIN `tblsource` ON tblurlconfig.location = tblsource.code
                    LEFT JOIN `tbldevice` ON tblurlconfig.device_id = tbldevice.id
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
function get_row($db, $table, $where=1){
    $query = "SELECT * FROM $table $where";
    $row = $db->query($query);
    return $row;
}


function getAcount($db, $accountId){
    $query = "SELECT * FROM tblaccount WHERE account=:id";
    $row = $db->query($query, array("id"=>$accountId));
    return $row;
}

function get_field_value($db, $table, $extractFieldValue, $where=1){
    //var_dump("SELECT $extractFieldValue FROM `".$table."` $where");
    $field = $db->row("SELECT $extractFieldValue FROM `".$table."` $where");
    return $field;
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


/**
* Save Table View Function
*/
function saveTableView($db, $action, $data){   
    
    $selectTables = $data['tables']['selected'];
    $joinTables = isset($data['tables']['joined']) ? $data['tables']['joined']  : false;
    $conditions = $data['condition'];
    $name =$data['name'];
    $slug=$data['slug'];

    $sql = '';
    $collumnsString = '';
    $selectTableNames = '';
    $joinTableNames = '';
    $joinStr = '';

    $tableViewObj = $data;
    unset($tableViewObj['insert_table_view']);   

    if(isset($selectTables) ){      
      foreach ($selectTables as $tableName => $v) {
        $selectTableNames .= "`".$tableName."`, ";
        //var_dump($v);die();
        foreach ($v as $key => $val ) {
           $collumnsString .= $tableName .'.'. $key.', ';
        }            
      }
      $selectTableNames = rtrim($selectTableNames,", ");        
      if($joinTables){
        foreach ($joinTables as $tableName => $v) {
          $joinOn = $v['joinOn'];
          $joinType = $v['joinType'];
          unset($v['joinOn']);
          unset($v['joinType']);
          $joinStr .= $joinType.' `'.$tableName.'` ON '. $joinOn.' ';
          foreach ($v as $key => $val ) {
             $collumnsString .= $tableName .'.'. $key.', ';
          }            
        }
      }

      $collumnsString = rtrim($collumnsString,", ");

      $sql .= "SELECT ".$collumnsString." FROM ".$selectTableNames .' ';
      $sql .= $joinStr . ' ';

      if($conditions['where'] != ''){
        $sql .= "WHERE ".$conditions['where'].' ';
      }
      if($conditions['groupBy'] != ''){
        $sql .= "GROUP BY ".$conditions['groupBy'].' ';
      }
      if($conditions['orderBy'] != ''){
        $sql .= "ORDER BY ".$conditions['orderBy'].' ';
      }
    }

  	$viewObject = serialize($tableViewObj); //serializing array for save it in db

    //Insert Data to table view
    if($action=='insert'){    	
    	//validate for slug; slug must be unique
  		$getSlug = $db->row("SELECT `view_slug` FROM `".DB_PREFIX."table_views` WHERE view_slug = :view_slug", array("view_slug"=>$data['slug']));
  		if($getSlug['view_slug'] === $slug ){
  			return array(
  						'message'=>'Slug <span>`'.$slug.'`</span> already exists.',
  						'status'=>'error'
  					);
  		}
  		$insert   =  $db->query("INSERT INTO `".DB_PREFIX."table_views`(view_name, view_slug, view_object, view_sql) VALUES(:view_name,:view_slug, :view_object, :view_sql)", array("view_name"=>"$name","view_slug"=>"$slug", "view_object"=>"$viewObject", "view_sql"=>"$sql"));
  		if($insert > 0 ) {
  		  return array(
    						'message'=>'Table View Succesfully created!',
    						'status'=>'success'
    					);
  		} else {
  			return array(
    						'message'=>'Insert Issue',
    						'status'=>'error'
    					);
  		} 

    }


    //Update data into table view    
    if($action=='update'){
        $pageSlug = $_GET['edit'];
        $update = $db->query("UPDATE  `".DB_PREFIX."table_views` SET view_name = :view_name, view_object = :view_object, view_sql = :view_sql WHERE view_slug = :view_slug", array("view_name"=>"$name", "view_object"=>"$viewObject", "view_sql"=>"$sql", "view_slug"=>"$pageSlug"));

        if($update) {
          return array(
                  'message'=>'Table View Succesfully updated!',
                  'status'=>'success'
                );
        } else {
          return array(
                  'message'=>'Update Issue',
                  'status'=>'error'
                );
        }
    } 

}