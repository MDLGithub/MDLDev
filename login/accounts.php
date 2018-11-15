<?php
ob_start();
require_once('config.php');
require_once('settings.php');
require_once('header.php');
if (!login_check($db)) {
    Leave(SITE_URL);
}
if (isset($_GET['logout'])) {
    logout();
    Leave(SITE_URL);
}


$userID = $_SESSION['user']["id"];
$roleInfo = getRole($db, $userID);
$roleID = $roleInfo['Guid_role'];
$accessRole = getAccessRoleByKey('account');
$roleIDs = unserialize($accessRole['value']);
$dataViewAccess = isUserHasAnyAccess($roleIDs, $roleID, 'view');

$roleInfo = getRole($db, $userID);
$role = $roleInfo['role'];

$accounts = $db->selectAll('tblaccount', ' ORDER BY `account` ASC');
$tblproviders = $db->selectAll('tblprovider');



if(isset($_GET['account_id']) && $role!='Physician'){
    $thisAccountID = $_GET['account_id'];
}else{
    if($role=='Physician'){ 
        //get the Guid_account for that Physician   
        $thisProvider = $db->row("SELECT a.Guid_account, a.account, a.name FROM `tblprovider` p
                                LEFT JOIN `tblaccount` a ON a.account=p.`account_id`
                                WHERE p.Guid_user=$userID");
        $thisAccountID = $thisProvider['Guid_account'];
    } else {
        $thisAccountID = $accounts[0]['Guid_account'];
    }
}
$accountInfo = getAccountAndSalesrep($db, $thisAccountID);
$accountActive = $accountInfo['0'];
extract($accountActive);

if (isset($_GET['delete']) && $_GET['delete'] != '') {
   //deleteRowByField($db, 'tblprovider', array('Guid_provider'=>$_GET['delete'])); 
    if(isset($_GET['user-id'])&&$_GET['user-id']!=""){
        deactivateUser($db, $_GET['user-id']);
        Leave(SITE_URL.'/accounts.php?account_id='.$_GET['account-id']);
    }
}
if( isset($_POST['cancel_manage_provider'])){
    Leave(SITE_URL.'/accounts.php?account_id='.$_GET['account_id']);
}
if( isset($_POST['manage_provider'])){  
    extract($_POST);
    $data = array(
        'title' => $title,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'npi'=>$npi,
        'Guid_account'=>$Guid_account
    );
    
    if($_POST['email']!=""){
        $userData['email']=$email;
    }
    if($_POST['password']!=""){
        $userData['password']=encode_password($password);
    }
    
    if($_FILES["photo_filename"]["name"] != ""){
        $fileName = $_FILES["photo_filename"]["name"];        
        $data['photo_filename'] = $fileName;
        $uploadMsg = uploadFile('photo_filename', 'images/users/');
    }
    
    if($Guid_provider != '' && $Guid_provider != 'add'){
        //update provider data
        $where = array("Guid_provider"=>$Guid_provider);        
        $msg['success'] = "Account Provider updated!";
        $msg['error'] = "Account Provider update Issue.";
        $providerDataArray = array(
                        'action'=>'update', 
                        'account_id'=>$_POST['account_id'], 
                        'Guid_provider'=>$_POST['Guid_provider']
            );
        $npi=$_POST['npi'];
        $isProviderValid = array();
        if(isset($npi) && $npi!=""){
            $isProviderValid = validateProviderId($db, $providerDataArray, $npi);
        } else{
            $isProviderValid['status']=1;
            $isProviderValid['msg'] = "";
        }
       
        if($isProviderValid['status']==1){ 
            if($Guid_user == ""){ //insert User
                $userData['user_type'] = 'provider';
                $userData['Date_created'] = date('Y-m-d H:i:s');        
                $inserUser = insertIntoTable($db, 'tbluser', $userData);
                $data['Guid_user']= $inserUser['insertID'];
            }else{ //update
                $userData['Date_modified'] = date('Y-m-d H:i:s');
                $whereUser = array('Guid_user'=>$Guid_user);
                $updateUser = updateTable($db, 'tbluser', $userData, $whereUser);
            }
            
            $update = updateTable($db, 'tblprovider', $data, $where, $msg );            
            Leave(SITE_URL.'/accounts.php?account_id='.$_GET['account_id']);
        } else {
            $message = $isProviderValid['msg'];            
        }                
    } else {
        //insert new user in tblusers table
        //insert provider user
        $userData['user_type'] = 'provider';
        $userData['Date_created'] = date('Y-m-d H:i:s');        
        $userData['Guid_role'] = '2';      
        $inserUser = insertIntoTable($db, 'tbluser', $userData);
        
        if($inserUser['insertID']){        
            //insert new provider for this account
            $msg['success'] = "Account New Provider inserted!";
            $msg['error'] = "Account New Provider insert Issue.";        
            $data['account_id'] = $accountActive['account'];
            $data['Guid_user'] = $inserUser['insertID'];

            $providerDataArray['action'] = 'insert';
            $providerDataArray['npi']=$_POST['npi'];
            $npi= $_POST['npi'];
           
            $isProviderValid = validateProviderId($db, $providerDataArray, $npi);        
            if($isProviderValid['status']==1){
                $insert = insertIntoTable($db, 'tblprovider', $data, $msg); 
                Leave(SITE_URL.'/accounts.php?account_id='.$_GET['account_id']);
            } else {
                $message = $isProviderValid['msg'];            
            }
        }
    }
}

$providerBoxClass = "hide";
if(isset($_GET['provider_guid']) && $_GET['provider_guid']!="" && $_GET['provider_guid']!="add"){
    $providerBoxClass = "show";
    $provider_guid =  $_GET['provider_guid'];
    $providerInfo = get_provider_user_info($db, $provider_guid); 
    
    $Guid_provider = $providerInfo["Guid_provider"];
    $Guid_user = $providerInfo["Guid_user"];
    $npi = $providerInfo["npi"];
    $provider_account_id = $providerInfo["account_id"];
    $provider_first_name = $providerInfo["first_name"];
    $provider_last_name = $providerInfo["last_name"];    
    $provider_email = $providerInfo["provider_email"];    
       
    $provider_title = $providerInfo["title"];    
    $provider_photo_filename = $providerInfo["photo_filename"];  
    $providerTitleTxt = "Update Provider";
    $labelClass="";  
} elseif (!isset($_GET['provider_guid']) || $_GET['provider_guid']=="") {
    $labelClass = "";
    $providerBoxClass = "hide";
    $Guid_provider = "";
    $Guid_user = "";
    $npi = "";
    $provider_account_id = "";
    $provider_first_name = "";
    $provider_last_name = "";
    $provider_email = "";
    $provider_password = "";
    $provider_title = "";
    $provider_photo_filename = "";
    $providerTitleTxt = "Add Provider";
} else {
    $labelClass = "";
    $providerBoxClass = "show";
    $Guid_provider = "";
    $Guid_user = "";
    $npi = "";
    $provider_account_id = "";
    $provider_first_name = "";
    $provider_last_name = "";
    $provider_email = "";
    $provider_password = "";
    $provider_title = "";
    $provider_photo_filename = "";
    $providerTitleTxt = "Add Provider";
}


$labels = array(
    'mdl_number'=>'MDL#', 
    'first_name'=>'Patient First Name', 
    'last_name'=>'Patient Last Name', 
    'account'=>'Account#', 
    'account_name'=>'Account Name', 
    'salesrep'=>'Sales Rep', 
    'date'=>'Date of the most recent status', 
    'date_accessioned'=>'Date Accessioned',
    'date_reported'=>'Date Reported', 
    'insurance_paid'=>'Insurance Paid', 
    'patient_paid'=>'Patient Paid', 
    'total_paid'=>'Total Paid', 
    'insurance_name'=>'Insurance Name',
    'test_ordered'=>'Test Ordered', 
    'location'=>'Location'
);

$configOptions = getOption($db, 'stat_details_config');
$optionVal = unserialize($configOptions['value']);


//exclude test users from mdl stats
$testUserIds = getTestUserIDs($db);
$markedTestUserIds = getMarkedTestUserIDs($db);
$initQ = 'SELECT s.Guid_status, s.Guid_user, s.Date, s.Date_created, p.Guid_patient, 
        AES_DECRYPT(p.firstname_enc, "F1rstn@m3@_%") as firstname, 
        AES_DECRYPT(p.lastname_enc, "L@stn@m3&%#") as lastname, 
        a.Guid_account, a.account AS account_number, a.name AS account_name, a.address AS location,  
        num.mdl_number,
        CONCAT(srep.`first_name`, " " ,srep.`last_name`) AS salesrep  
        FROM `tbl_mdl_status_log` s 
        LEFT JOIN `tblpatient` p ON s.Guid_patient=p.Guid_patient
        LEFT JOIN `tblaccount` a ON s.Guid_account=a.Guid_account
        LEFT JOIN `tblsalesrep` srep ON s.Guid_salesrep=srep.Guid_salesrep
        Left JOIN `tbl_mdl_number` num ON s.Guid_user=num.Guid_user
        WHERE s.Guid_status=:Guid_status 
        AND s.currentstatus="Y" ';

if($markedTestUserIds!=""){
    $initQ.='AND s.Guid_user NOT IN('.$markedTestUserIds.') ';
}
if($testUserIds!=""){
    $initQ.='AND s.Guid_user NOT IN('.$testUserIds.') '; 
}   
if(isset($_GET['account_id'])&&$_GET['account_id']!=""){
    $initQ .= 'AND a.Guid_account='.$_GET['account_id'].' ';
}  
    
$initQ.='AND s.Guid_patient<>"0"';
    
if(isset($_GET['status_id'])&& $_GET['status_id']!=""){
    $initData=$db->query($initQ, array('Guid_status'=>$_GET['status_id']));
} else {
    $initData = array();
}

?>


<?php require_once 'navbar.php'; ?> 

<!-- jQuery Modal -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />

<main class="full-width">
    <?php if($dataViewAccess) { ?>
        <?php if(isset($_GET['update'])){ ?>
            <section id="msg_display" class="show success">
                <h4>Changes have been saved</h4>
            </section>
        <?php } ?> 
    
        <div class="box full visible">
        <section id="palette_top">
            <h4>
            <ol class="breadcrumb">
                <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                <li><a href="<?php echo SITE_URL; ?>/account-config.php">Accounts</a></li>
                <li class="active">
                    Account Information	
                </li>
            </ol>
            </h4>
            <?php echo topNavLinks($role); ?>
        </section>        
        <div id="app_data" class="scroller">            
            <div id="accounts">
                <div class="row">
                    <div class="col-md-8">
                       
                        <div id = "physician-header">
                            <h2><?php echo $accountActive['account']." - ". strtoupper($accountActive['name']); ?></h2>
                        </div>
                        
                        <div class="status_chart">
                            <div class="row">
                                <div class="col-md-12">
                                    <span class="registred">
                                        Registered
                                        <img src="assets/eventschedule/icons/silhouette_icon.png">
                                        <?php 
                                            $Registered = getAccountStatusCount($db, $accountActive['account'], '28' ); //28->Registered 
                                            echo ($Registered>0)?$Registered:'-';
                                        ?>
                                    </span>
                                    <span class="completed">
                                        Completed
                                        <img src="assets/eventschedule/icons/checkmark_icon.png">
                                        <?php 
                                            $Completed = getAccountStatusCount($db, $accountActive['account'], '36'); //36->Questionnaire Completed 
                                            echo ($Completed>0)?$Completed:'-';
                                        ?>
                                    </span>
                                    <span class="qualified">
                                        Qualified
                                        <img src="assets/eventschedule/icons/dna_icon.png">
                                        <?php 
                                            $Qualified = getAccountStatusCount($db, $accountActive['account'], '29'); //29->Questionnaire Completed->Qualified 
                                            echo ($Qualified>0)?$Qualified:'-';
                                        ?>
                                    </span>
                                    <span class="submitted">
                                        Submitted
                                        <img src="assets/eventschedule/icons/flask_icon.png">
                                        <?php 
                                            $Submitted = getAccountStatusCount($db, $accountActive['account'], '1' ); //28->Submitted (Specimen Collected) 
                                            echo ($Submitted>0)?$Submitted:'-';
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php if($role!='Physician') { ?>
                    <div class="selectAccountBlock row ">
                        
                        <div class="col-md-8 padd-0">
                            
                            <label >Select Account</label><br/>
                            <select class="form-control" id="selectAccount">
                                <?php 
                                $accountInfo = "";
                                $i=0;
                                foreach ($accounts as $k=>$v){
                                    $selected = (isset($_GET['account_id'])&&$_GET['account_id']==$v['Guid_account']) ? " selected='selected'" : "";
                                $i++;
                                ?>
                                <option <?php echo $selected; ?> data-guid="<?php echo $v['Guid_account']; ?>" value="<?php echo $v['account']; ?>"><?php echo $v['account']." - ".formatAccountName($v['name']); ?></option>
                                <?php  } ?>
                            </select>
                            
                            <a href="<?php echo SITE_URL;?>/account-config.php?action=edit&id=<?php echo $accountActive['Guid_account']; ?>" id="edit-selected-account" class="add-new-account" title="Edit">
                                <span class="fas fa-pencil-alt" aria-hidden="true"></span>
                            </a>
                            <a class="followup" id="followup" href="#ex1" title="Follow">
                                <img src="assets/images/icon_forms.png">
                            </a>
                            
                        </div>
                        <!-- <div class="col-md-6 padd-0 pT-20">
                            
                        </div> -->
                    </div>
                    <?php }  ?>
                    
                    <div class = "address-container">

                    <div id="accountLogo">
                        <?php $logo = $logo ? "/../images/practice/".$logo : "/assets/images/default.png"; ?>
                        <img class="" src="<?php echo SITE_URL.$logo; ?>" />
                    </div>
                    <div class="addressInfoBlock">
                        <!-- <label >Account Address</label>-->
                        <div id="officeAddress">
                            <div>
                                <?php 
                                if($address){
                                    echo $address."<br/>";
                                    if($city !=""){ echo $city.", "; }
                                    if($state !=""){ echo $state." "; }
                                    if($zip !="" ){ echo $zip ."<br/>"; } 
                                }
                                ?>
                            </div>
                            
                            <div class = "addressContact">
                            <?php if($phone_number) { ?>
                                <div><i class="fas fa-phone"></i> <a class="phone_us" href="tel:<?php echo $phone_number; ?>"><?php echo $phone_number; ?></a></div>
                            <?php } ?>
                            <?php if($fax) { ?>
                                <div><i class="fas fa-fax"></i> <a class="phone_us" href="tel:<?php echo $fax; ?>"><?php echo $fax; ?></a></div>
                            <?php } ?>
                            <?php if($website) { ?>
                                <div><i class="fas fa-globe"></i> <a target="_blank" href="<?php echo $website; ?>"><?php echo $website; ?></a></div>                   
                            <?php } ?>
                            </div>
                        </div>
                    </div>
                    </div>

                    <div class="providersTable">
                        <?php if($role!='Physician'){ ?>                        
                        <h4 id="physiciansListLabel" class="accounts">
                            Physicians       
                            <a href="<?php echo SITE_URL;?>/accounts.php?account_id=<?php echo $thisAccountID;?>&provider_guid=add" class="pull-right" id="add-account-provider">
                                <span class="fas fa-plus-circle" aria-hidden="true"></span>  Add
                            </a>
                        </h4>
                        <?php } ?>
                        <table class="table providersTable">
                            <thead>
                                <tr>                        
                                    <th>NPI</th>
                                    <th>Title</th>
                                    <th>Name</th>
                                    <th class="">Registered</th>           
                                    <th class="">Completed</th>           
                                    <th class="">Qualified</th>           
                                    <th class="">Submitted</th>
                                    <?php if($role!='Physician'){ ?>
                                    <th class="text-center">Actions</th>
                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                                //$accountProviders = get_active_providers($db, 'account_id', $account);
                                $prQuery = "SELECT pr.*, u.status FROM tblprovider pr LEFT JOIN tbluser u ON u.`Guid_user`=pr.`Guid_user` WHERE account_id=$account AND u.status='1'";
                                $accountProviders = $db->query($prQuery);
                                
                                if($accountProviders !=''){
                                    foreach ($accountProviders as $k=>$v){
                                        $providerGuid=$v['Guid_provider'];
                                ?>
                                <tr>                            
                                    <td><?php echo $v['npi']; ?></td>
                                    <td><?php echo $v['title']; ?></td>
                                    <td><?php echo $v['first_name']." ". $v['last_name']; ?></td>                                    
                                    <td>
                                        <?php 
                                            $incomplete = getProviderStatusCount($db, 'Incomplete', $v['Guid_provider'] );
                                            $completed = getProviderStatusCount($db, 'Completed', $v['Guid_provider'] );
                                            $registred = $incomplete+$completed;
                                            echo ($registred>0)? $registred : '-'; 
                                        ?>
                                    </td>
                                    <td><?php 
                                        $completed = getProviderStatusCount($db, 'Completed', $v['Guid_provider'] );
                                        echo ($completed>0)? $completed : '-'; ?>
                                    </td>
                                    <td><?php 
                                        $qualified = getProviderStatusCount($db, 'Yes', $v['Guid_provider'] );
                                        echo ($qualified>0)? $qualified : '-'; ?>
                                    </td>
                                    <td><?php 
                                        $submitted = getProviderSubmitedCount($db, $v['Guid_provider'] );
                                        echo ($submitted>0)? $submitted : '-'; ?>
                                    </td>
                                    <?php if($role!='Physician'){ ?>
                                    <td class="text-center">
                                        <!--<a class="edit-provider" data-provider-guid="<?php echo $v['Guid_provider']; ?>">-->
                                        <a href="<?php echo SITE_URL."/accounts.php?account_id=$Guid_account&provider_guid=$providerGuid"; ?>">
                                            <span class="fas fa-pencil-alt" aria-hidden="true"></span>
                                        </a>
                                        <a class="color-red" onclick="javascript:confirmationDeleteProvider($(this));return false;" href="?delete=<?php echo $providerGuid ?>&npi=<?php echo $v['npi']; ?>&account-id=<?php echo $Guid_account; ?>&user-id=<?php echo $v['Guid_user']; ?>">
                                            <span class="far fa-trash-alt" aria-hidden="true"></span> 
                                        </a>
                                    </td>
                                    <?php } ?>
                                </tr>
                                <?php } ?>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>  <!-- /.providersTable -->
                    <div class="accountStats">
                        <table class="table stats-table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th class="wh-100">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php echo get_status_table_rows($db, '0', array('Guid_account'=>$accountActive['Guid_account']), array('account_id'=>$accountActive['Guid_account'],'status_table'=>'1'));?>
                            </tbody>
                        </table>
                    </div>
                  
                </div>
                <div class="col-md-4">
                    <div class="salesrepInfoBlock">
                      <div id = "physician-gc" class="row">
                        <label class = "col-md-12 col-sm-4"><?php echo $salesrepTitle; ?></label>
                        <div class="imageBox col-lg-6 col-md-12 col-sm-4">
                            <div class="pic">
                                <?php $salesrepPhoto = (isset($salesrepPhoto)&&$salesrepPhoto!="") ? "/images/users/".$salesrepPhoto : "/assets/images/default.png"?>
                                <img width="50" class="salesrepProfilePic" src="<?php echo SITE_URL.$salesrepPhoto; ?>" />
                            </div>
                            <div class="name text-center">
                                <?php echo $salesrepFName." ".$salesrepLName; ?>
                            </div>
                        </div>
                        
                        <div id="salesrepInfo1" class = "col-lg-6 col-md-12 col-sm-4">
                            <?php 
                            if($role!="Physician") {
                                if($salesrepAddress){
                                    echo $salesrepRegion."<br/>".$salesrepAddress.", <br/>".$salesrepCity.", ".$salesrepState." ".$salesrepZip."<br/>"; 
                                } 
                            }
                            ?>
                            <?php if($salesrepEmail) { ?>
                                <div><i class="fas fa-envelope"></i> <a href="mailto:<?php echo $salesrepEmail; ?>"><?php echo $salesrepEmail; ?></a></div>
                            <?php } ?>
                            <?php if($salesrepPhone) { ?>
                                <div><i class="fas fa-phone"></i> <a class="phone_us" href="tel:<?php echo $salesrepPhone; ?>"><?php echo $salesrepPhone; ?></a></div>
                            <?php } ?>
                        </div>
                    </div>
                    </div>
                </div>
                </div> <!-- /.row -->  
                
                <div class="row">
                    <div class="col-md-12">
                          <?php if(isset($_GET['status_table'])){ ?>
                                <div class="statusTable">
                                    <?php $parent = isset($_GET['parent'])?$_GET['parent']:""; ?>
                                    <h2>Status: <?php echo getStatusName($db, $_GET['status_id'], $parent); ?></h2>
                                    <form id="patient_information" action="" method="post" class="<?php echo $role."_table";?>">

                                        <div class="actions">
                                            <button class="btn-styled btn-home" id="bulkPrint"><i class="fas fa-print"></i> Print Selected</button>
                                        </div>

                                        <div class="">
                                            <table id="dataTableFixed" class="pseudo_t table">
                                                <thead class="">
                                                   <tr>
                                                        <th class="text-center no-bg">
                                                            <label class="switch">
                                                                <input id="selectAllPrintOptions" type="checkbox">
                                                                <span class="slider round">
                                                                    <span id="switchLabel">Select All</span>
                                                                </span>
                                                            </label>
                                                        </th>
                                                        <th>Medical Necessity</th>
                                                        <?php foreach ($labels as $k=>$v){ ?>                                
                                                        <?php 
                                                            $isVisibleForStatus = isFieldVisibleForStatus($db, $k, $_GET['status_id']);
                                                            $isVisibleForRole = isFieldVisibleForRole($db, $k, $roleID);
                                                            if($isVisibleForStatus&&$isVisibleForRole){
                                                                echo '<th>';
                                                                if(isset($optionVal[$k]['label'])){
                                                                    echo $optionVal[$k]['label']; 
                                                                } else {
                                                                    echo $v;
                                                                } 
                                                                echo '</th>';
                                                            }
                                                            ?>
                                                        <?php } ?>
                                                   </tr>
                                                </thead>
                                                <tbody> 
                                                    <?php foreach ($initData as $k=>$v){ ?>
                                                    <?php 
                                                        $Guid_user = $v['Guid_user'];
                                                        $revenue = getRevenueStat($db, $v['Guid_user']);
                                                        $patientInfoUrl = SITE_URL.'/patient-info.php?patient='.$v['Guid_user']; 
                                                        if($v['account_number'] && $v['account_number']!=''){
                                                            $patientInfoUrl .= '&account='.$v['account_number'];
                                                        }
                                                        $incomplateStr = "";
                                                        $incomplateQ = "SELECT q.Guid_qualify,q.Guid_user, q.Date_created, '1' AS incomplete FROM tblqualify q  
                                                                        WHERE NOT EXISTS(SELECT qs.Guid_qualify FROM tbl_ss_qualify qs WHERE q.Guid_qualify=qs.Guid_qualify) 
                                                                        AND q.Guid_user=$Guid_user";
                                                        $questionaryR = $db->row($incomplateQ);
                                                        if($questionaryR){
                                                            $incomplateStr = "&incomplete=1";
                                                        } else {
                                                            $complatedQ = "SELECT q.Guid_qualify, q.Guid_user, q.qualified, q.Date_created  FROM tbl_ss_qualify q   
                                                                        WHERE q.`Date_created` = (SELECT MAX(Date_created) FROM tbl_ss_qualify AS m2 WHERE q.Guid_qualify = m2.Guid_qualify)
                                                                        AND q.Guid_user=$Guid_user";
                                                            $questionaryR = $db->row($complatedQ);
                                                        }
                                                    ?>
                                                    <tr class="t_row"> 
                                                        <td class="printSelectBlock text-center sorting_1">
                                                            <?php if(!isset($questionaryR['incomplete'])){ ?>
                                                                <input name="markedRow[user][<?php echo $Guid_user; ?>]" type="checkbox" class="print1 report1" data-prinatble="1" data-selected_questionnaire="<?php echo $questionaryR['Guid_qualify']; ?>" data-selected_date="<?php echo $questionaryR['Date_created']; ?>">
                                                            <?php }  else { ?>
                                                                <input name="markedRow[user][<?php echo $Guid_user; ?>]" type="checkbox" class="print1 report1" data-prinatble="2" data-selected_questionnaire="<?php echo $questionaryR['Guid_qualify']; ?>" data-selected_date="<?php echo $questionaryR['date']; ?>" />
                                                            <?php } ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            if(isset($questionaryR['incomplete'])){
                                                                echo '<span class="mn no">Incomplete</span>';
                                                            } else {
                                                                echo '<span class="mn '.strtolower($questionaryR['qualified']).'">'.$questionaryR['qualified'].'</span>';
                                                            }

                                                            ?>
                                                        </td>
                                                        <?php if(isFieldVisibleForStatus($db, 'mdl_number', $_GET['status_id']) && isFieldVisibleForRole($db, 'mdl_number', $roleID)){ ?>
                                                        <td><?php echo $v['mdl_number'];?></td> 
                                                        <?php } ?>

                                                        <?php if(isFieldVisibleForStatus($db, 'first_name', $_GET['status_id']) && isFieldVisibleForRole($db, 'first_name', $roleID)){ ?>
                                                        <td><a href="<?php echo $patientInfoUrl.$incomplateStr; ?>"><?php echo ucfirst(strtolower($v['firstname']));?></a></td>                              
                                                        <?php } ?>

                                                        <?php if(isFieldVisibleForStatus($db, 'last_name', $_GET['status_id']) && isFieldVisibleForRole($db, 'last_name', $roleID)){ ?>
                                                        <td><a href="<?php echo $patientInfoUrl.$incomplateStr; ?>"><?php echo ucfirst(strtolower($v['lastname'])); ?></a></td>                              
                                                        <?php } ?>

                                                        <?php if(isFieldVisibleForStatus($db, 'account', $_GET['status_id']) && isFieldVisibleForRole($db, 'account', $roleID)){ ?>
                                                        <td><a href="<?php echo SITE_URL.'/accounts.php?account_id='.$v['Guid_account']; ?>"><?php echo $v['account_number'];?></a></td>                              
                                                        <?php } ?>

                                                        <?php if(isFieldVisibleForStatus($db, 'account_name', $_GET['status_id']) && isFieldVisibleForRole($db, 'account_name', $roleID)){ ?>
                                                        <td><?php echo ucwords(strtolower($v['account_name'])); ?></td>                              
                                                        <?php } ?>

                                                        <?php if(isFieldVisibleForStatus($db, 'salesrep', $_GET['status_id']) && isFieldVisibleForRole($db, 'salesrep', $roleID)){ ?>
                                                        <td><?php echo $v['salesrep'];?></td>                              
                                                        <?php } ?>

                                                        <?php if(isFieldVisibleForStatus($db, 'date', $_GET['status_id']) && isFieldVisibleForRole($db, 'date', $roleID)){ ?>
                                                        <td><?php echo date("n/j/Y", strtotime($v['Date'])); ?></td>                              
                                                        <?php } ?>

                                                        <?php if(isFieldVisibleForStatus($db, 'date_accessioned', $_GET['status_id']) && isFieldVisibleForRole($db, 'date_accessioned', $roleID)){ ?>
                                                        <td>???</td>   
                                                        <?php } ?>

                                                        <?php if(isFieldVisibleForStatus($db, 'date_reported', $_GET['status_id']) && isFieldVisibleForRole($db, 'date_reported', $roleID)){ ?>
                                                        <td><?php echo date("n/j/Y", strtotime($v['Date_created'])); ?></td>                              
                                                        <?php } ?>

                                                        <?php if(isFieldVisibleForStatus($db, 'insurance_paid', $_GET['status_id']) && isFieldVisibleForRole($db, 'insurance_paid', $roleID)){ ?>
                                                        <td><?php echo "$".formatMoney($revenue['insurance_paid']); ?></td>  
                                                        <?php } ?>

                                                        <?php if(isFieldVisibleForStatus($db, 'patient_paid', $_GET['status_id']) && isFieldVisibleForRole($db, 'patient_paid', $roleID)){ ?>
                                                        <td><?php echo "$".formatMoney($revenue['patient_paid']); ?></td>
                                                        <?php } ?>

                                                        <?php if(isFieldVisibleForStatus($db, 'total_paid', $_GET['status_id']) && isFieldVisibleForRole($db, 'total_paid', $roleID)){ ?>
                                                        <td><?php echo "$".formatMoney($revenue['total']); ?></td> 
                                                        <?php } ?>

                                                        <?php if(isFieldVisibleForStatus($db, 'insurance_name', $_GET['status_id']) && isFieldVisibleForRole($db, 'insurance_name', $roleID)){ ?>
                                                        <td><?php echo $revenue['insurance_name']; ?></td> 
                                                        <?php } ?>

                                                        <?php if(isFieldVisibleForStatus($db, 'test_ordered', $_GET['status_id']) && isFieldVisibleForRole($db, 'test_ordered', $roleID)){ ?>
                                                        <td>??</td> 
                                                        <?php } ?>

                                                        <?php if(isFieldVisibleForStatus($db, 'location', $_GET['status_id']) && isFieldVisibleForRole($db, 'location', $roleID)){ ?>
                                                        <td><?php echo $v['location']; ?></td> 
                                                        <?php } ?>
                                                    </tr>
                                                    <?php } ?>


                                                </tbody>

                                            </table>
                                        </div>


                                    </form>
                                </div>
                                <?php } ?>
                    </div>
                </div>
                


            </div>

            <div id="setDate" class="modal" style="position: absolute; top: 5%; left: 35%;">
                <a id="modal_close" href="#" rel="modal:close">x</a>
                <div class="f2">
                    <label class="" for="account"><span>Start Date</span></label>
                    <div class="group">
                        <input readonly="" class="datepicker" type="text" id="from_date" name="from_date" value="" placeholder="From Date">
                        <!-- <input type="date" name="start_date" value="<?php echo date('Y-m-d') ?>" id="start_date" > -->
                    </div>
                </div>
                <div class="f2">
                    <label class="" for="account"><span>End Date</span></label>
                    <div class="group">
                        <input readonly="" class="datepicker" type="text" id="to_date" name="to_date" value="" placeholder="To Date" max="2018-11-13">
                        <!-- <input type="date" value="<?php echo date('Y-m-d') ?>" name="end_date" id="end_date"> -->
                    </div>
                </div>
                <div class="col-md-6">
                    <button id="reset_date" value="2018-08-01" name="submit_account" type="button" class="btn-inline">Reset</button>
                </div>
                <div class="col-md-6">
                    <button id="print" name="submit_account" type="submit" class="btn-inline">Print</button>
                </div>
                
                <input type="hidden" name="account" value="<?php echo $accountActive['account']; ?>">
                <input type="hidden" name="guid_account" value="<?php echo $accountActive['Guid_account'] ?>">
            </div>

            
        </div><!-- /. mainContent-->
    </div> <!-- /. full box visible-->       
    <?php } else { ?>
        <div class="box full visible ">  
            <h4> Sorry, You Don't have Access to this page content. </h4>
        </div>
    <?php } ?>

</main>


<div id="add-account-provider-box" class="modalBlock <?php echo $providerBoxClass; ?>">
    <div class="contentBlock">
        <?php if(isset($message)){ ?>
        <div class="error-text"><?php echo $message; ?></div>
        <?php } ?>
        <h5 class="providersTitle"><?php echo $providerTitleTxt; ?></h5>

        <?php 
            if( isset($uploadMsg) && !empty($uploadMsg)){
                if($uploadMsg['status'] == 0){
                    echo "<div class='error-text'>".$uploadMsg['msg']."</div>";
                }
            } 
        ?>
        <form method="POST" enctype="multipart/form-data">                            

            <input type="hidden" value="<?php echo $accountActive['Guid_account']; ?>" name="Guid_account" />
            <input type="hidden" value="<?php echo $Guid_provider; ?>" name="Guid_provider" />
            <input type="hidden" value="<?php echo $npi; ?>"  name="npi" />
            <input type="hidden" value="<?php echo $provider_account_id; ?>" name="account_id" />
            <input type="hidden" value="<?php echo $Guid_user; ?>" name="Guid_user" />

            <div class="f2 <?php echo ($npi!="")?"valid":"";?>">
                <label class="dynamic" for="npi"><span>NPI</span></label>
                <div class="group">
                    <input autocomplete="off" id="npi" name="npi" type="text" value="<?php echo $npi; ?>" placeholder="NPI">
                    <p class="f_status">
                        <span class="status_icons"><strong></strong></span>
                    </p>
                </div>
            </div>
            <div class="f2 <?php echo ($provider_title!="")?"valid":"";?>">
                <label class="dynamic" for="title"><span>Title</span></label>
                <div class="group">
                    <input autocomplete="off" id="title" name="title" type="text" value="<?php echo $provider_title; ?>" placeholder="Title">
                    <p class="f_status">
                        <span class="status_icons"><strong></strong></span>
                    </p>
                </div>
            </div>
            <div class="f2 required <?php echo ($provider_first_name!="")?"valid":"";?>">
                <label class="dynamic" for="name"><span>First Name</span></label>

                <div class="group">
                    <input autocomplete="off" id="first_name" name="first_name" type="text" value="<?php echo $provider_first_name; ?>" placeholder="First Name" required="">
                    <p class="f_status">
                        <span class="status_icons"><strong>*</strong></span>
                    </p>
                </div>
            </div>
            <div class="f2 required <?php echo ($provider_last_name!="")?"valid":"";?>">
                <label class="dynamic" for="name"><span>Last Name</span></label>
                <div class="group">
                    <input autocomplete="off" id="last_name" name="last_name" type="text" value="<?php echo $provider_last_name; ?>" placeholder="Last Name" required="">
                    <p class="f_status">
                        <span class="status_icons"><strong>*</strong></span>
                    </p>
                </div>
            </div>
            <div class="f2 <?php echo ($provider_emial!="")?"valid":"";?>">
                <label class="dynamic" for="email"><span>Email</span></label>
                <div class="group">
                    <input autocomplete="off" id="email" name="email" type="email" value="<?php echo $provider_email; ?>" placeholder="Email" >
                    <p class="f_status">
                        <span class="status_icons"><strong>*</strong></span>
                    </p>
                </div>
            </div>
            <div class="f2 ">
                <label class="dynamic" for="password"><span>Password</span></label>
                <div class="group">
                    <input autocomplete="off" id="password" name="password" type="password" value="" placeholder="Password" >
                    <p class="f_status">
                        <span class="status_icons"><strong>*</strong></span>
                    </p>
                </div>
            </div>
            
            <div class="form-group">
                <div class="row">
                    <div class="col-md-10">
                        <div class="f2 <?php echo ($provider_photo_filename!="")?"valid":"";?>">
                            <label class="dynamic" for="photo"><span>Photo</span></label>
                            <div class="group">
                                <input id="file" value="<?php echo $provider_photo_filename; ?>" name="photo_filename" class="form-control pT-5" type="file" placeholder="Photo"/>
                                <p class="f_status">
                                    <span class="status_icons"><strong>*</strong></span>
                                </p>
                            </div>
                        </div>                    
                    </div>
                    <?php $providerImg = ($provider_photo_filename=="")?"/assets/images/default.png":"/images/users/".$provider_photo_filename; ?>
                    <div id="profile-pic" class="col-md-2 pT-10">
                        <img id="image" width="40" src="<?php echo SITE_URL.$providerImg; ?>" />
                    </div>
                </div>
            </div>
            
            
            <div class="">
                <button class="btn-inline" name="manage_provider" type="submit" >Save</button>
                <button onclick="goBack();" type="button" class="btn-inline btn-cancel">Cancel</button>                   

                <!--<a class="btn-inline btn-cancel" href="<?php echo SITE_URL."/accounts.php?account_id=".$_GET['account_id'];?>">Cancel</a>-->
            </div>
        </form>
          
    </div> 
</div>

<?php require_once('scripts.php');?>

<style type="text/css">
    #setDate input{    
        border-radius: .438em;
        border-right-width: 2px;
        width: 100%;
    }
    #setDate .group{ padding-left: 0; }
    #setDate #modal_close{
        font-size: 21px;
        position: absolute;
        right: -5px;
        top: -6px;
        background: #e0e0e0;
        color: #fff;
        padding: 2px 6px;
    }
</style>

<script type="text/javascript">
    if ($('#dataTableFixed').length ) { 
        var table = $('#dataTableFixed').DataTable({
                        dom: '<"top"i>rt<"bottom"flp><"wider-bottom"><"clear">',
                        orderCellsTop: true,
                        fixedHeader: true,
                        lengthMenu: [[10, 20, 30, 50, 100,-1], [10, 20, 30, 50, 100, "All"]],
                        //lengthChange: false,
                        searching: false,
                        "pageLength": 30,
                        "aoColumnDefs": [
                          { 
                              "bSortable": false, 
                              "aTargets": [ 0 ] 
                          } 
                        ]      
                    });  
    }
    </script>
    <script type="text/javascript" src="assets/js/custom-script.js"></script>
<?php require_once('footer.php');?>