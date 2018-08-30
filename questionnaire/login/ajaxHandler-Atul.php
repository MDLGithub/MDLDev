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
if(isset($_POST['get_salesrep']) && $_POST['get_salesrep']=='1'){
    get_salesrep($db, $_POST['account_id']);
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
