<?php
require_once('settings.php'); 
require_once('config.php'); 
    
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


