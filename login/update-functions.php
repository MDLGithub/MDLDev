<?php
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
                        $data['deviceid'] = $accountInfo['Guid_salesrep'];
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