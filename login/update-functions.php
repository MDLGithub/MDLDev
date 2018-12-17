<?php
/**
 * add new updates in this array 
 * and create handle function with the same name in update-functions.php
 */
$updateData = array(
    '1' => array(
        'description' => 'Change account_number to accountNumber in patients table',
        'function' => 'update_v1'
    ),
    '2' => array(
        'description' => 'Update MDL Stat Logs for missing accounts',
        'function' => 'update_v2'
    ),
    '3' => array(
        'description' => 'Rename Column PayID to fullName in Payors Table',
        'function' => 'update_v3'
    ),
    '4' => array(
        'description' => 'Add Column dMDL_mdl_number to patients screen in order to have all api params for xml page',
        'function' => 'update_v4'
    ),
    '5' => array(
        'description' => 'Add Column PolicyID in patients table',
        'function' => 'update_v5'
    ),
    '6' => array(
        'description' => 'Add dMDL CPT Test Code Mapping Table and insert provided data',
        'function' => 'update_v6'
    ),
);

/**
 *  Create updates log table if not exists 
 *  and insert new rows from $updateData
 */
$logTable = create_log_table($db);
if($logTable['staus']===TRUE){
    foreach ($updateData as $k=>$v){
        $insertUpdatesData = array(
                'function_name'=>$v['function'],
                'description'=>$v['description'],
                'isUpdated'=>'N',
                'Date'=> date("Y-m-d H:i:s")
            );
        $checkUpdate = $db->row("SELECT `Guid_updates_log` FROM `tbl_mdl_updates_log` WHERE function_name=:function_name", array('function_name'=>$v['function']));
        if(empty($checkUpdate)){
            $insertUpdates = insertIntoTable($db, 'tbl_mdl_updates_log', $insertUpdatesData);
        }
        
    } 
}

/*
 * Add Column dMDL_mdl_number to patients screen in order to have all api params for xml page
 */
function update_v6($db, $function){
    $message = '';
    $sql = "CREATE TABLE IF NOT EXISTS `tbl_mdl_dmdl_cpt_mapping` (
                    `Guid_cpt_mapping` INT(11) NOT NULL AUTO_INCREMENT,
                    `cpt_pattern` TEXT DEFAULT NULL,
                    `test_code` VARCHAR(60) DEFAULT NULL,
                    PRIMARY KEY (`Guid_cpt_mapping`)
                );";
    $db->query($sql);
    //check if table crated
    $updatesMappingTable = $db->query('select 1 from `tbl_mdl_dmdl_cpt_mapping` LIMIT 1');
    if(!$updatesMappingTable) {
        $message = 'Could not create table.';
        $staus = FALSE;
    }
    $message = "Table created successfully!";
    $staus = TRUE;
    //mapping array paterns provided by Martin
    $mappingArr = array(
                        '0' => array('pattern' => '81211,81213', 'test_code'=>'1221'),
                        '1' => array('pattern' => '81215', 'test_code'=>'1224'),
                        '2' => array('pattern' => '81213,81408', 'test_code'=>'1235'),
                        '3' => array('pattern' => '81211,81213', 'test_code'=>'1221'),
                    );
    if($staus){
        //insert provided patternt to table
        foreach ($mappingArr as $k=>$v){
            $data = array('cpt_pattern'=>$v['pattern'], 'test_code'=>$v['test_code']);
            insertIntoTable($db, 'tbl_mdl_dmdl_cpt_mapping', $data);
        }
        $message = "Table created and patern data inserted successfully!";
        
        updateTable($db, 'tbl_mdl_updates_log', array('isUpdated'=>'Y'), array('function_name'=>$function));
    }
   
    $returnArr = array(
        'staus' => $staus,
        'message' => $message
    );
    return $returnArr;
}
/*
 * Add Column PolicyID in patients table
 */
function update_v5($db, $function){
    $query = "ALTER TABLE `tblpatient` ADD COLUMN `policyID` VARCHAR(60) AFTER source";
    $result = $db->query($query); 
    updateTable($db, 'tbl_mdl_updates_log', array('isUpdated'=>'Y'), array('function_name'=>$function));
    $returnArr = array(
        'staus' => TRUE,
        'message' => 'Patiens Table Updated.'
    );
    return $returnArr;
}
/*
 * Add Column dMDL_mdl_number to patients screen in order to have all api params for xml page
 */
function update_v4($db, $function){
    $query = "ALTER TABLE `tblpatient` ADD COLUMN `dMDL_mdl_number` VARCHAR(25) AFTER Guid_dmdl_physician";
    $result = $db->query($query); 
    updateTable($db, 'tbl_mdl_updates_log', array('isUpdated'=>'Y'), array('function_name'=>$function));
    $returnArr = array(
        'staus' => TRUE,
        'message' => 'Patiens Table Updated.'
    );
    return $returnArr;
}
/*
 * Rename Column PayID to fullName in Payors Table
 */
function update_v3($db, $function){
    $query = "ALTER TABLE `tbl_mdl_payors` CHANGE `PayID` `fullName` VARCHAR(256)";
    $renamePayID = $db->query($query);    
    updateTable($db, 'tbl_mdl_updates_log', array('isUpdated'=>'Y'), array('function_name'=>$function));
  
    $returnArr = array(
        'staus' => TRUE,
        'message' => 'Payors Table Updated.'
    );
    return $returnArr;
}
/**
 * Update MDL Stat Logs for missing accounts 
 */
function update_v2($db, $function){
    $logsData = $db->query("SELECT * FROM `tbl_mdl_status_log` WHERE Guid_account='0' OR `account`='0'");
    $staus = FALSE;
    if(!empty($logsData)){
        foreach ($logsData as $k=>$v){
            $ssTable = $db->row("SELECT * FROM `tbl_ss_qualify` 
                                WHERE Guid_user=:Guid_user
                                ORDER BY Date_created DESC LIMIT 1",
                                array('Guid_user'=>$v['Guid_user'])
                        );
            if(empty($ssTable)){
                $ssTable = $db->row("SELECT * FROM `tblqualify` 
                                WHERE Guid_user=:Guid_user
                                ORDER BY Date_created DESC LIMIT 1",
                                array('Guid_user'=>$v['Guid_user'])
                        );
            }
            if(!empty($ssTable)){
                $data = array();
                if($ssTable['account_number']!=''){
                    $data['account'] = $ssTable['account_number'];
                    $data['deviceid'] = $ssTable['deviceid'];
                    $accountQ = "SELECT a.Guid_account, a.account, provider.Guid_provider, "
                                . "sr.Guid_salesrep, sr.first_name AS salesrep_fname, sr.last_name AS salesrep_lname "
                                . "FROM tblaccount a "
                                . "LEFT JOIN tblaccountrep ar ON a.Guid_account=ar.Guid_account "
                                . "LEFT JOIN tblsalesrep sr ON ar.Guid_salesrep = sr.Guid_salesrep "
                                . "LEFT JOIN tblprovider provider ON provider.account_id = a.account "
                                . "WHERE a.account = '" . $ssTable['account_number'] . "'";
                    $accountInfo = $db->row($accountQ);                     
                    if(!empty($accountInfo)){
                        $data['Guid_account'] = $accountInfo['Guid_account'];
                        $data['Guid_salesrep'] = $accountInfo['Guid_salesrep'];
                        $data['salesrep_fname'] = $accountInfo['salesrep_fname'];
                        $data['salesrep_lname'] = $accountInfo['salesrep_lname'];
                        $data['provider_id'] = $accountInfo['Guid_provider'];
                    }                    
                }
                if(!empty($data)){
                    $where = array('Guid_user'=>$v['Guid_user']);
                    updateTable($db, 'tbl_mdl_status_log', $data, $where);
                }
            }
        } /** foreach $logData END*/
        
        updateTable($db, 'tbl_mdl_updates_log', array('isUpdated'=>'Y'), array('function_name'=>$function));
        $message = "Table updated successfully!";
        $staus = TRUE;
    }
    
     $returnArr = array(
        'staus' => $staus,
        'message' => 'Status Logs Updated.'
    );
    return $returnArr;
}
/**
 *  Change account_number to accountNumber in patients table
 */
function update_v1($db, $function){
    $check_account_number = $db->row("SELECT * FROM `tblpatient`");
    if(array_key_exists('account_number', $check_account_number)){
        $sql = "ALTER TABLE `tblpatient` CHANGE `account_number` `accountNumber` VARCHAR(32);";
        $db->query($sql);    
    }
    $check_accountNumber = $db->row("SELECT * FROM `tblpatient`");    
    if(array_key_exists('accountNumber', $check_accountNumber)){
        updateTable($db, 'tbl_mdl_updates_log', array('isUpdated'=>'Y'), array('function_name'=>$function));
        $message = "Table updated successfully!";
        $staus = TRUE;
    } else {
        $message = 'Could not update table.';
        $staus = FALSE;
    }    
    $returnArr = array(
        'staus' => $staus,
        'message' => $message
    );
    return $returnArr;
}


/**
 * Run given function
 * @param type $db
 * @param type $data
 * @return type
 */
function doUpdate($db,$data){
    $function = $data['runFunction'];
    return $function($db, $function);
}
/**
 * Create Update Log Table If Not Exists
 * @return string
 */
function create_log_table($db){
    $message = '';
    $sql = "CREATE TABLE IF NOT EXISTS `tbl_mdl_updates_log` (
                    `Guid_updates_log` INT(11) NOT NULL AUTO_INCREMENT,
                    `function_name` VARCHAR(128) DEFAULT NULL,
                    `description` VARCHAR(255) DEFAULT NULL,
                    `isUpdated` ENUM('Y','N') DEFAULT 'N',
                    `Date` DATETIME NOT NULL,
                    PRIMARY KEY (`Guid_updates_log`)
                );";
    $db->query($sql);
    //check if table crated
    $updatesLogTable = $db->query('select 1 from `tbl_mdl_updates_log` LIMIT 1');
    if(!$updatesLogTable) {
        $message = 'Could not create table.';
        $staus = FALSE;
    }
    $message = "Table created successfully!";
    $staus = TRUE;
   
    $returnArr = array(
        'staus' => $staus,
        'message' => $message
    );
    return $returnArr;
}