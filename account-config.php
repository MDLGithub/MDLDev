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
$role = $roleInfo['role'];
$accessRole = getAccessRoleByKey('account');
$roleIDs = unserialize($accessRole['value']);
$dataViewAccess = isUserHasAnyAccess($roleIDs, $roleID, 'view');

$isAccountView = isset($roleIDs['account']['view'])?$roleIDs['account']['view']:"";
$isNameView = isset($roleIDs['name']['view'])?$roleIDs['name']['view']:"";
$isAddressView = isset($roleIDs['address']['view'])?$roleIDs['address']['view']:"";
$isCityView = isset($roleIDs['city']['view'])?$roleIDs['city']['view']:"";
$isStateView = isset($roleIDs['state']['view'])?$roleIDs['state']['view']:"";
$isZipView = isset($roleIDs['zip']['view'])?$roleIDs['zip']['view']:"";
$isPhoneView = isset($roleIDs['phone_number']['view'])?$roleIDs['phone_number']['view']:"";
$isFaxView = isset($roleIDs['fax']['view'])?$roleIDs['fax']['view']:"";
$isSalesrepView = isset($roleIDs['Guid_salesrep']['view'])?$roleIDs['Guid_salesrep']['view']:"";
$isLogoView = isset($roleIDs['logo']['view'])?$roleIDs['logo']['view']:"";
$isWebsiteView = isset($roleIDs['website']['view'])?$roleIDs['website']['view']:"";
$isActionAdd = isset($roleIDs['actions']['add'])?$roleIDs['actions']['add']:"";
$isActionEdit = isset($roleIDs['actions']['edit'])?$roleIDs['actions']['edit']:"";
$isActionDelete = isset($roleIDs['actions']['delete'])?$roleIDs['actions']['delete']:"";


if(isset($_GET['action']) && $_GET['action']=='edit'){
    if(!isFieldVisibleByRole($isActionEdit, $roleID)) {
        Leave(SITE_URL.'/account-config.php');
    }
}
if(isset($_GET['action']) && $_GET['action']=='add'){
    if(!isFieldVisibleByRole($isActionAdd, $roleID)) {
        Leave(SITE_URL.'/account-config.php');
    }
}

if (isset($_GET['delete']) && $_GET['delete'] != '') {
    if(!isFieldVisibleByRole($isActionDelete, $roleID)) {
        Leave(SITE_URL.'/account-config.php');
    }
    deleteAccountById($db, 'tblaccount', $_GET['delete']);
    deleteByField($db, 'tblaccountrep', 'Guid_account', $_GET['delete']);
    Leave(SITE_URL.'/account-config.php');
}

$thisMessage = "";
$accountFieldMsg = "";
$errorMessage = "";
if(isset($_POST['submit_account'])){   
    extract($_POST);
    $accountData = $_POST;    
    $accountData['phone_number'] = cleanString($phone_number);
    $accountData['fax'] = cleanString($fax);    
    $accountData['name'] = remove_accent($name);    
    unset($accountData['submit_account']);
    unset($accountData['Guid_account']);
    unset($accountData['Guid_salesrep']);            
    if($_FILES["logo"]["name"] != ""){
        $fileName = $_FILES["logo"]["name"];        
        $accountData['logo'] = $fileName;
        $uploadMsg = uploadFile('logo', '../images/practice/');
    }    
    if($Guid_account!=""){ //do update  
        if( ifAccountIDValid($_POST['account'], $_POST['Guid_account']) ){
            $updateAccount = updateTable($db,'tblaccount', $accountData, array('Guid_account'=>$_POST['Guid_account']));
            $checkSalesRep = getTableRow($db, 'tblaccountrep', array('Guid_account'=>$_POST['Guid_account']));
            if($checkSalesRep){
                updateTable($db,'tblaccountrep', array('Guid_salesrep' => $_POST['Guid_salesrep']), array('Guid_account'=>$_POST['Guid_account']));
            } else {
                insertIntoTable($db, 'tblaccountrep', array('Guid_account' => $_POST['Guid_account'],'Guid_salesrep' => $_POST['Guid_salesrep']));
            }
            if($role=='Physician'){
                Leave(SITE_URL.'/accounts.php?update');
            }else{
                Leave(SITE_URL.'/account-config.php?update');
            }
        } else {
            if($_POST['account']=="0"){
                $errorMessage = "Account ID can't be <strong>".$_POST['account']."</strong>. Please Type valid ID.";
            }else{
                $errorMessage = "Account ID <strong>".$_POST['account']."</strong> Exists. Please choose another.";
            }
            $accountFieldMsg = 'error';
        }
    } else {
        //insert new Account for this account       
        if( ifAccountIDValid($_POST['account']) ){
            $insertAccount = insertIntoTable($db, 'tblaccount', $accountData);
            if(isset($insertAccount['insertID'])&& $insertAccount['insertID'] != ""){
                $accountRepData = array(
                    'Guid_account' => $insertAccount['insertID'],
                    'Guid_salesrep' => $_POST['Guid_salesrep']
                );                  
                $insertAccountRep = insertIntoTable($db, 'tblaccountrep', $accountRepData);                
            } 
            Leave(SITE_URL.'/account-config.php?insert');
        }else {
            if($_POST['account']=="0"){
                $errorMessage = "Account ID can not be <strong>".$_POST['account']."</strong>. Please Type valid ID.";
            }else{
                $errorMessage = "Account ID <strong>".$_POST['account']."</strong> Exists. Please choose another.";
            }
            $accountFieldMsg = 'error';
        }
    }
}
$salesreps = $db->selectAll('tblsalesrep');
$accounts = $db->selectAll('tblaccount', ' ORDER BY name ASC');

if($role=='Physician'){ 
    //get the Guid_account for that Physician   
    $thisProvider = $db->row("SELECT a.Guid_account, a.account, a.name FROM `tblprovider` p
                            LEFT JOIN `tblaccount` a ON a.account=p.`account_id`
                            WHERE p.Guid_user=$userID");
    if( !(isset($_GET['action']) && $_GET['action']=='edit')){
        Leave(SITE_URL.'/accounts.php');
    }
}

$tblproviders = $db->selectAll('tblprovider');
$states = $db->selectAll('tblstates');

require_once ('navbar.php');
?>

<main class="full-width">
    <?php  if($dataViewAccess) { ?>
    <?php 
        if(isset($_GET['update']) || isset($_GET['insert']) ){ 
            $thisMessage = "Changes have been saved";
        }
    ?>
    <?php if($thisMessage != ""){ ?>
    <section id="msg_display" class="show success">
        <h4><?php echo $thisMessage;?></h4>
    </section>
    <?php } ?>    
    <div class="box full visible ">  
        <section id="palette_top">
            <h4>                
                <?php 
                    if(isset($_GET['action']) && $_GET['action']=='edit'){
                ?>
                <ol class="breadcrumb">
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/account-config.php">Accounts</a></li>
                    <li class="active">
                        Edit Account
                    </li>
                </ol>
                <?php } elseif(isset($_GET['action']) && $_GET['action']=='add') { ?>
                <ol class="breadcrumb">
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/account-config.php">Accounts</a></li>
                    <li class="active">Add New Account</li>
                </ol>
                <?php    } else { ?>
                <ol class="breadcrumb">
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="active">Accounts</li>                   
                </ol>                
                <?php } ?>
            </h4>
            <?php echo topNavLinks($role); ?>
        </section>
        <div class="scroller">         
            <?php
            if( isset($_GET['action']) && $_GET['action'] !='' ){ 
                if($_GET['action'] =='edit'){
                    $thisAccountID =  $_GET['id'];
                    if($role=='Physician'){ 
                        $thisAccountID = $thisProvider['Guid_account'];
                    }
                    $accountInfo = getAccountAndSalesrep($db,$thisAccountID); 
                    if(empty($accountInfo)){
                        Leave(SITE_URL."/account-config.php");
                    }
                    $accountInfo = $accountInfo['0'];
                    extract($accountInfo); 
                    $labelClass="";   
                    $noSelection = '';
                }else{  
                    $labelClass = "";
                    $Guid_account= isset($Guid_account) ? $Guid_account: '';
                    $account = isset($account) ? $account : '';
                    $name = isset($name) ? $name : '';
                    $logo = '';
                    $address = isset($address)?$address:'';
                    $city = isset($city)?$city:'';
                    $state= isset($state)?$state:'';
                    $zip= isset($zip)?$zip:'';
                    $website= isset($website)?$website:'';
                    $phone_number = isset($phone_number)?$phone_number:"";
                    $fax = isset($fax)?$fax:"";
                    $noSelection = 'no-selection';
                }
            ?>
                <div class="row">
                    <div class="col-md-2"></div>
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-lg-12">
                                <!--Print messages here-->
                                <?php 
                                    if( isset($uploadMsg) && !empty($uploadMsg)){
                                        if($uploadMsg['status'] == 0){
                                            echo "<div class='error-text'>".$uploadMsg['msg']."</div>";
                                        }
                                    } 
                                    
                                ?>
                            </div>
                        </div>
                        <form method="POST" enctype="multipart/form-data">  
                            <div class="row pB-30">
                                <div class="col-md-6">
                                    <button id="saveForm" name="submit_account" type="submit" class="btn-inline">Save</button>
                                    <button onclick="goBack();" type="button" class="btn-inline btn-cancel">Cancel</button>                   
                                    <!--<a href="<?php echo SITE_URL."/account-config.php";?>" class="btn-inline btn-cancel">Cancel</a>-->                       
                                </div>
                                
                                <div class="col-md-6">
                                    <?php if(isset($_GET['action']) && $_GET['action']=='edit'){ ?>
                                    <div class="status_chart">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <span class="registred">
                                                    Registered
                                                    <img src="assets/eventschedule/icons/silhouette_icon.png">
                                                    <?php echo getAccountStatusCount($db, $account, '28' ); //28->Registered ?>
                                                </span>
                                                <span class="completed">
                                                    Completed
                                                    <img src="assets/eventschedule/icons/checkmark_icon.png">
                                                    <?php echo getAccountStatusCount($db, $account, '36'); //36->Questionnaire Completed ?>
                                                </span>
                                                <span class="qualified">
                                                    Qualified
                                                    <img src="assets/eventschedule/icons/dna_icon.png">
                                                    <?php echo getAccountStatusCount($db, $account, '29'); //29->Questionnaire Completed->Qualified ?>
                                                </span>
                                                <span class="submitted">
                                                    Submitted
                                                    <img src="assets/eventschedule/icons/flask_icon.png">
                                                    <?php echo getAccountStatusCount($db, $account, '1' ); //28->Submitted (Specimen Collected) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                                <div class="col-md-12">
                                    <?php 
                                        if( isset($errorMessage) && $errorMessage != ""){
                                            echo "<div class='error-text'>".$errorMessage."</div>";
                                        } 
                                    ?>
                                    <span class="error" id="message"></span>
                                </div>
                           </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="hidden" name="Guid_account" value="<?php echo $Guid_account; ?>" />
                                     <?php if(isFieldVisibleByRole($isAccountView, $roleID)) {?>
                                    <div class="f2 required <?php echo ($account!="" && $accountFieldMsg=="")?"valid show-label":"";?>">
                                        <label class="dynamic" for="account"><span>Account Number</span></label>
                                        <div class="group">
                                            <input class="numberonly" id="account" name="account" type="text" value="<?php echo $account; ?>" placeholder="Account Number" required="">
                                            <p class="f_status">
                                                <span class="status_icons"><strong>*</strong></span>
                                            </p>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <?php if(isFieldVisibleByRole($isNameView, $roleID)) {?>
                                    <div class="f2 required <?php echo ($name!="")?"valid":"";?>">
                                        <label class="dynamic" for="name"><span>Account Name</span></label>
                                        <div class="group">
                                            <input id="name" name="name" type="text" value="<?php echo $name; ?>" placeholder="Account Name" required="">
                                            <p class="f_status">
                                                <span class="status_icons"><strong>*</strong></span>
                                            </p>
                                        </div>
                                    </div>                               
                                    <?php } ?>
                                    
                                    <?php if(isFieldVisibleByRole($isSalesrepView, $roleID)) {?>
                                    <div class="row">
                                        <div class="col-md-9">
                                            <div class="f2  <?php echo ($Guid_salesrep!="")?"valid show-label":"";?>">
                                                <label class="dynamic" for="Guid_salesrep"><span>Genetic Consultant</span></label>
                                                <div class="group">
                                                    <select class="<?php echo $noSelection; ?>" name="Guid_salesrep" id="Guid_salesrep">
                                                        <option value="">Genetic Consultant</option>
                                                        <?php 
                                                        foreach ($salesreps as $key => $v) { 
                                                            $selected = ($Guid_salesrep == $v['Guid_salesrep']) ? ' selected' : '';
                                                            ?>
                                                        <option <?php echo $selected; ?> value="<?php echo $v['Guid_salesrep']; ?>"><?php echo $v['first_name']." ".$v['last_name']; ?></option>     
                                                        <?php }?>
                                                    </select>
                                                    <p class="f_status">
                                                        <span class="status_icons"><strong></strong></span>
                                                    </p>
                                                </div>
                                            </div>  
                                            
                                        </div>
                                        <div class="col-md-3 pT-20">
                                            <a href="<?php echo SITE_URL; ?>/salesreps.php?action=add" class="add-new-account fs-28">
                                                <span class="fas fa-plus-circle"></span>
                                            </a>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <?php if(isFieldVisibleByRole($isLogoView, $roleID)) {?>
                                    <div class="row">                                
                                        <div class="col-md-9">
                                            <div class="f2 <?php echo ($logo!="")?"valid show-label":"";?>">
                                                <label class="dynamic" for="photo"><span>Photo</span></label>
                                                <div class="group">
                                                    <input id="file" class="accountLogoInput form-control pT-5" type="file" name="logo" value="<?php echo $logo; ?>" />
                                                    <p class="f_status">
                                                        <span class="status_icons"><strong>*</strong></span>
                                                    </p>
                                                </div>
                                            </div>                                            
                                        </div>
                                        <div class="col-md-3 pT-5">
                                            <?php $logo = ($logo=="")? "/assets/images/default.png":"/../images/practice/".$logo; ?>
                                            <img id="image" class="accountLogo" height="40" src="<?php echo SITE_URL.$logo; ?>" >
                                        </div>
                                    </div>
                                </div>
                                <?php }?>
                                
                                <div class="col-md-6">
                                    <?php if(isFieldVisibleByRole($isAddressView, $roleID)) {?>
                                    <div class="f2 <?php echo ($address!="")?"valid show-label":"";?>">
                                        <label class="dynamic" for="address"><span>Account Address</span></label>
                                        <div class="group">
                                            <input id="address" name="address" type="text" value="<?php echo $address; ?>" placeholder="Account Address">
                                            <p class="f_status">
                                                <span class="status_icons"><strong></strong></span>
                                            </p>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    
                                    <?php if(isFieldVisibleByRole($isCityView, $roleID)) {?>
                                    <div class="f2 <?php echo ($city!="")?"valid show-label":"";?>">
                                        <label class="dynamic" for="city"><span>City</span></label>
                                        <div class="group">
                                            <input id="city" name="city" type="text" value="<?php echo $city; ?>" placeholder="City">

                                            <p class="f_status">
                                                <span class="status_icons"><strong></strong></span>
                                            </p>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    
                                    <div class="row">
                                        <?php if(isFieldVisibleByRole($isStateView, $roleID)) {?>
                                        <div class="col-md-6">
                                            <div class="f2 <?php echo ($state!="")?"valid show-label":"";?>">
                                                <label class="dynamic" for="state"><span>State</span></label>
                                                <div class="group">
                                                    <select name="state" class="<?php echo $noSelection; ?>" id="state">
                                                        <option value="">State</option>
                                                        <?php foreach ($states as $k => $v) { ?>
                                                        <?php $selected = ($state==$v['stateCode'])? " selected" : ""; ?>
                                                            <option <?php echo $selected; ?> value="<?php echo $v['stateCode']; ?>"><?php echo $v['stateName']; ?></option>                                            
                                                        <?php  } ?>
                                                    </select>                                        
                                                    <p class="f_status">
                                                        <span class="status_icons"><strong></strong></span>
                                                    </p>
                                                </div>
                                            </div>                                 
                                        </div>
                                        <?php } ?>
                                        <?php if(isFieldVisibleByRole($isZipView, $roleID)) {?>
                                        <div class="col-md-6">
                                             <div class="f2 <?php echo ($zip!="")?"valid show-label":"";?>">
                                                <label class="dynamic" for="zip"><span>Zip</span></label>
                                                <div class="group">
                                                    <input id="zip" class="zip" name="zip" type="text" value="<?php echo $zip; ?>" placeholder="Zip">
                                                    <p class="f_status">
                                                        <span class="status_icons"><strong></strong></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <?php } ?>
                                    </div>
                                    <div class="row">
                                        <?php if(isFieldVisibleByRole($isPhoneView, $roleID)) {?>
                                        <div class="col-md-6">
                                            <div class="f2 <?php echo ($phone_number!="")?"valid show-label":"";?>">
                                                <label class="dynamic" for="phone_number"><span>Phone</span></label>
                                                <div class="group">
                                                    <input class="phone_us" id="phone_number" name="phone_number" type="text" value="<?php echo $phone_number; ?>" placeholder="Phone">
                                                    <p class="f_status">
                                                        <span class="status_icons"><strong></strong></span>
                                                    </p>
                                                </div>
                                            </div> 
                                        </div>
                                        <?php } ?>
                                        <?php if(isFieldVisibleByRole($isFaxView, $roleID)) {?>
                                        <div class="col-md-6">
                                            <div class="f2 <?php echo ($fax!="")?"valid show-label":"";?>">
                                                <label class="dynamic" for="fax"><span>Fax</span></label>
                                                <div class="group">
                                                    <input class="phone_us" id="fax" name="fax" type="text" value="<?php echo $fax; ?>" placeholder="Fax">
                                                    <p class="f_status">
                                                        <span class="status_icons"><strong></strong></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div> 
                                        <?php } ?>
                                    </div>
                                    <?php if(isFieldVisibleByRole($isWebsiteView, $roleID)) {?>
                                    <div class="f2 <?php echo ($website!="")?"valid show-label":"";?>">
                                        <label class="dynamic" for="website"><span>Account Website</span></label>
                                        <div class="group">
                                            <input id="website" name="website" type="url" value="<?php echo $website; ?>" placeholder="Account Website">
                                            <p class="f_status">
                                                <span class="status_icons"><strong></strong></span>
                                            </p>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>           
                           
                    </form>
                </div>
            </div>
            <?php } else { ?>
                <div class="row">
                    <?php if(isFieldVisibleByRole($isActionAdd, $roleID)) {?>
                    <div class="col-md-12">
                        <a class="add-new-device" href="?action=add">
                            <span class="fas fa-plus-circle" aria-hidden="true"></span> Add
                        </a>
                        <a class="followup" id="followup" href="javascript:void(0)" onclick="PrintPreview();" target="_blank">
                            <span class="fas fa-print" aria-hidden="true"></span> Follow Up
                        </a>
                    </div>
                    <?php } ?>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <table id="dataTable" class="table accountsTable">
                        <thead>
                            <tr>   
                                <?php if(isFieldVisibleByRole($isAccountView, $roleID)) {?>
                                    <th>Account Number</th>
                                <?php } ?>
                                <?php if(isFieldVisibleByRole($isNameView, $roleID)) {?>
                                    <th>Account Name</th>
                                <?php } ?>    

                                <?php if(isFieldVisibleByRole($isCityView, $roleID)) {?>
                                    <th class="">City</th>
                                <?php } ?>    
                                <?php if(isFieldVisibleByRole($isStateView, $roleID)) {?>
                                    <th class="">State</th>
                                <?php } ?> 
                                <?php if(isFieldVisibleByRole($isSalesrepView, $roleID)) {?>
                                    <th class="">Genetic Consultant</th>           
                                <?php } ?>  
                                <th class="">Registered</th>           
                                <th class="">Completed</th>           
                                <th class="">Qualified</th>           
                                <th class="">Submitted</th>
                                <?php if( isFieldVisibleByRole($isActionEdit, $roleID) || isFieldVisibleByRole($isActionDelete, $roleID)) {?>
                                    <th class="noFilter actions text-center">Actions</th>
                                <?php } ?>
                            
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            $accountsInfo = getAccountAndSalesrep($db);
                            foreach ($accountsInfo as $k => $v) {
                            ?>
                                <tr> 
                                <?php if(isFieldVisibleByRole($isAccountView, $roleID)) {?>
                                    <td class="clickable">
                                       <a href="<?php echo SITE_URL; ?>/accounts.php?account_id=<?php echo $v['Guid_account']; ?>"><?php echo $v['account']; ?></a>                                                                        
                                    </td>
                                <?php } ?>
                                <?php if(isFieldVisibleByRole($isNameView, $roleID)) {?>
                                    <td><?php echo ucwords(strtolower($v['name'])); ?></td>
                                <?php } ?> 
                                <?php if(isFieldVisibleByRole($isCityView, $roleID)) {?>
                                    <td><?php echo $v['city']; ?></td>
                                <?php } ?>    
                                <?php if(isFieldVisibleByRole($isStateView, $roleID)) {?>
                                   <td><?php echo $v['state']; ?></td>
                                <?php } ?>
                                <?php if(isFieldVisibleByRole($isSalesrepView, $roleID)) {?>
                                    <td><?php echo $v['salesrepFName']." ".$v['salesrepLName']?></td>          
                                <?php } ?>    
                                <td><?php echo getAccountStatusCount($db, $v['account'], '28' ); //28->Registered ?></td>
                                <td><?php echo getAccountStatusCount($db, $v['account'], '36'); //36->Questionnaire Completed ?></td>
                                <td><?php echo getAccountStatusCount($db, $v['account'], '29'); //29->Questionnaire Completed->Qualified ?></td>
                                <td><?php echo getAccountStatusCount($db, $v['account'], '1' ); //28->Submitted (Specimen Collected) ?></td>
                                <?php if( isFieldVisibleByRole($isActionEdit, $roleID) || isFieldVisibleByRole($isActionDelete, $roleID)) {?>
                                    <td class="text-center">
                                        <?php if(isFieldVisibleByRole($isActionEdit, $roleID)) {?>
                                        <a href="<?php echo SITE_URL; ?>/accounts.php?account_id=<?php echo $v['Guid_account']; ?>">
                                            <span class="fas fa-pencil-alt" aria-hidden="true"></span>
                                        </a>
                                        <?php } ?>
                                        <?php if(isFieldVisibleByRole($isActionDelete, $roleID)) {?>
                                        <a onclick="javascript:confirmationDeleteAccount($(this));return false;" href="<?php echo SITE_URL; ?>/account-config.php?delete=<?php echo $v['Guid_account'] ?>&id=<?php echo $v['account']; ?>">
                                            <span class="far fa-trash-alt" aria-hidden="true"></span> 
                                        </a>
                                        <?php } ?>
                                    </td>
                                <?php } ?>
                                </tr>
                            <?php
                                $i++;
                            }
                            ?>
                        </tbody>
                    </table>      
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    
    <?php } else { ?>
     <div class="box full visible ">  
            <h4> Sorry, You Don't have Access to this page content. </h4>
     </div>
    <?php } ?>
</main>

<section class="form follow-up geneveda" id="printarea">
    <link rel="stylesheet" type="text/css" href="assets/css/forms.css">
    <link rel="stylesheet" type="text/css" href="assets/css/custom-styles.css">
    <style type="text/css" >
        @charset "utf-8";
        @media print {
            .exact, .form * {-webkit-print-color-adjust: exact;print-color-adjust: exact;}
            .c2 th:nth-of-type(2), .c2 td:nth-of-type(2), .c3 th:nth-of-type(3), .c3 td:nth-of-type(3),
            .c4 th:nth-of-type(4), .c4 td:nth-of-type(4), .c5 th:nth-of-type(5), .c5 td:nth-of-type(5) {
                text-align: center;}
            .sf4 td:nth-of-type(4) {font-size: 2.47mm;font-weight: normal;max-width: 50mm;}
            .columns {display: flex;}
            .columns > .col {flex-grow:1;}
            .columns.two > .col {width: 50%;}
            .dashed {border: .42 dashed #000; border-top:0; border-bottom:0;}
            .geneveda .dashed {border-color: #7a68ae;}
            .follow-up p {font-size: 3.53mm;line-height: 5.29mm;margin-top: 7.95mm;color: #242424 !important;}
            .follow-up .account_logo {max-width: 54.18mm;}
            .follow-up .lab {text-align: right;margin-bottom: 10.16mm;}
            .follow-up .lab > img {max-width: 44.36mm;display: inline-block;}
            .follow-up .header {display: flex; padding-bottom: 7.19mm;}
            .follow-up .header .col.one { width: 76.2mm;}
            .follow-up .header .col.two {flex-grow: 1;text-align: right; }
            .follow-up .main {border: .67mm solid #cbcbcb; border-left:0; border-right: 0;padding-bottom: 7.62mm;}
            .form p > .labColor, .lC5 td:nth-of-type(5) {color: #7a68ae !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .geneveda .ftable > th {
                background: #7a68ae;
            }
            
            .pBG_size {
                position: relative;
            }
            
            .pBG {
                width: 100%;
                height:100%;
                position: absolute;
                left:0;
                top:0;
                z-index:-1;
            }
            
            .ftable {
                position: relative;
                width: 100%;
                font-size: 2.82mm;
                margin-top: 4.23mm;
                font-weight: 600;
                text-align: left;
            }
            
            .ftable th, .ftable .pBG {
                height: 5.24mm;
            }
            
            .ftable th {
                color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                vertical-align: middle;
            }
            
            .ftable tr {
                min-height: 5.24mm;
            }
            
            .ftable td {
                border-bottom: .16mm solid #4c4c4c;
                padding: 1.35mm 0;
                color: #2b2b2b !important;
            }
            
            .ftable th:first-child, .ftable td:first-child {
                padding-left: 4.23mm;
            }
            
            #testing_recommended th:last-child, #testing_recommended td:last-child {
                text-align: center;
                width: 28.61mm;
            }
            
            #testing_recommended td:last-child {
                border-left: .42mm dashed #7a68ae; border-right: .42mm dashed #7a68ae;
                text-align: center;
            }
            
            .sTable {
                text-align: left;
                width: 100%;
                table-layout: fixed;
                margin-top: 3.81mm;
            }
            
            .sTable td, .sTable th  {
                border: .16mm solid #4c4c4c; border-top:0; border-left:0;
                width: 100%;
                font-size: 2.82mm;
                padding-left: 3.13mm;
            }
            
            .sTable td:last-child, .sTable th:last-child {
                border-right:0; 
            }
            
            .sTable th {
                font-weight: bold;
                padding-top: 2.03mm; padding-bottom: 2.03mm;
            }
            
            .geneveda .sTable th {
                color: #7a68ae !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .sTable td {
                font-weight: 600;
                color: #2b2b2b !important;
                max-width: 33%;
                padding-top: 1.69mm; padding-bottom: 1.69mm;
            }
            
            .sTable td:first-child, .sTable th:first-child {
                max-width: 28.36mm;
            }
            
            #not_completed {
                padding-left: 6.77mm;
            }
            
            #not_completed .sTable {
                margin-top: 9mm;
            }
            
            #not_recommended .sTable {
                width: calc(100% - 7.62mm);
            }
            
            #not_completed th:last-child, #not_recommended th:last-child {
                max-width: 21mm;
            }
            
            .form footer {
                padding-top: 6.94mm;
                font-size: 8pt;
            }
            
            .gc_title {
                padding-bottom: 5.41mm;
            }
            
            .gc_title > h4 {
                font-weight: bold;
                font-size: 10pt;
                padding-bottom: 1.27mm;
            }
            
            .gc_title > em {
                font-size: 8pt;
                color: #242424 !important;
            }
            
            .columns .foogo {
                flex-grow: 0;
                padding-right: 4.23mm;
            }
            
            .foogo > img {
                width: 13.88mm;
            }
            
            .gc_contact {
                border-top: 0.25mm solid #1c1c1c;
                flex-grow: 1;
                display: flex;
                margin-top: 2.11mm;
                position: relative;
                padding-top: 1.86mm;
            }
            
            .gc_contact > section {
                flex-grow: 1;
                padding-left: 3.81mm;
            }
            
            .gc_contact::before {
                content:"";
                position: absolute;
                left:-3.72mm; top:-.2mm;
                z-index:1;
                font-size: inherit;
                display: inline-block;
                width: 3.72mm;
                height: .25mm;
                border-top: 0.25mm solid #1c1c1c;
                transform-origin: bottom right;
                transform: rotate(45deg);
            }
            
            .gc_contact::after, .gc_contact > section::after {
                content:"";
                position: absolute;
                border-radius: 50%;
                width: 0.84mm;
                height: 0.84mm;
                border: .33mm solid #1c1c1c;
                overflow: hidden;
                right:0;
                top: -.5mm;
            }
            
            .gc_contact > section::after {
                right: auto;
                left:-2.8mm;
                top:-2.8mm;
            }
            
            .gc_contact h5 {
                text-transform: uppercase;
                font-weight: 600;
            }
            
            .gc_contact span {
                padding-left: 3.38mm;
            }
            
            .gc_contact > * {
                line-height: 11pt;
            }
            
            .gc_contact li {
                position: relative;
            }
            
            .gc_contact > ul:last-child {
                margin-left: 12.63mm;
                font-weight: 600;
            }
            
            .gc_contact img {
                position: absolute;
                max-width: 2.62mm;
                max-height: 2.62mm;
                left: -4mm;
                top: .9mm;
            }
            
            .gc_contact .gc_email {
                left:-4.8mm;
            }
            
            .sb {
                font-weight: 600;
            }
            
            .stat_table {
                text-align: left;
                position: relative;
                font-size: 8pt;
                font-weight: 600;
                color: #242424 !important;
            }
            
            .follow-up .stat_table {
                display: inline-block;
            }
            
            .stat_table th {
                border-right: .42mm solid #000;
                padding: 1.69mm 2.03mm;
            }
            
            .stat_table td {
                border-left: .42mm solid #000;
                border-bottom: .25mm solid #000;
                padding: 2.03mm 0;
            }
            
            .stat_table td:first-child {
                padding-right: 3.55mm; padding-left: 3.55mm;
            }
            
            .stat_table tr:last-child > td {
                border-bottom: 0;
            }
            
            .stat_table td:last-child {
                border-right: .42mm solid #000;
                color: #7a68ae !important;
            }
            
            .geneveda .stat_table th, .geneveda .stat_table td {
                border-color: #7a68ae;
            }
            
            .side_head {
                position:absolute !important;
                left:-12mm;
                transform: rotate(-90deg);
                bottom: 12mm;
                text-transform: uppercase;
                color: #9e9e9e !important;
                font-weight: bold;
                letter-spacing: .25mm;
            }
            
            .follow-up .stat_table .pBG {
                width: 12mm;
                height: 25.4mm;
                top: auto;
                left: auto;
                bottom:0;
                right: 11.5mm;
            }
        }
    </style>
    <input type="button" value="Print" class="btn" onclick="window.print();"/>
    <header class="header">
        <div class="col one">
            <figure class="account_logo"><img src="https://www.mdlab.com/dev/images/practice/22230-LEXINGTON%20OBGYN%20ASSOCIATES.png" alt="Lexington"></figure>

            <p>Thank you for selecting Geneveda to provide Hereditary Breast and Ovarian Cancer (HBOC) Screening for your patients. This report was prepared on <strong>September 24, 2018</strong>.</p>
        </div>
    
        <div class="col two">
            <figure class="lab"><img src="https://www.mdlab.com/dev/images/logo_geneveda.png" alt="Geneveda"></figure>
            
            <table class="stat_table c2 c3">
                <thead>
                    <tr>
                        <th><h4 class="side_head">Summary</h4></th>
                        <th><img class="pBG" src="images/swatch_gen_green.png" alt="">Today</th>
                        <th>Total</th>
                    </tr>
                </thead>
                
                <tbody>
                    <tr>
                        <td>Registered Patients</td>
                        <td>11</td>
                        <td>24</td>
                    </tr>
                    <tr>
                        <td>Completed Questionnaire</td>
                        <td>9</td>
                        <td>20</td>
                    </tr>
                    <tr>
                        <td>Insufficient Information</td>
                        <td>2</td>
                        <td>5</td>
                    </tr>
                    <tr>
                        <td>Medically Qualified</td>
                        <td>4</td>
                        <td>12</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </header>
    
    <div class="main">
        <p>Based upon the cited clinical policy testing guidelines, testing is <strong class="labColor">recommended</strong> for the following patients:</p>
        
        <table id="testing_recommended" class="ftable c4 c5 c6 lC5">
            <thead>
                <tr>
                    <th><img class="pBG" src="images/swatch_gen_purple.png" alt="">First Name</th>
                    <th>Last Name</th>
                    <th>DOB</th>
                    <th>Guideline Applied</th>
                    <th>Guideline Met</th>
                    <th>Date Collected</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>John</td>
                    <td>Doe</td>
                    <td>1/1/1980</td>
                    <td>Aetna</td>
                    <td>BRCA</td>
                    <td></td>
                </tr>
                <tr>
                    <td>John</td>
                    <td>Doe</td>
                    <td>1/1/1980</td>
                    <td>Aetna</td>
                    <td>BRCA</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        
        <p><strong>Insufficient information</strong> was available to determine if the following patients met clinical policy testing guidelines:</p>
        
        <table class="ftable sf4">
            <thead>
                <tr>
                    <th><img class="pBG" src="images/swatch_gen_grey.png" alt="">First Name</th>
                    <th>Last Name</th>
                    <th>DOB</th>
                    <th>Information Needed</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>John</td>
                    <td>Doe</td>
                    <td>1/1/1980</td>
                    <td>Genetic Mutation</td>
                </tr>
                <tr>
                    <td>John</td>
                    <td>Doe</td>
                    <td>1/1/1980</td>
                    <td>Do you have at least one first- or second-degree close blood relative in your family with breast cancer at age 45 years or younger?</td>
                </tr>
            </tbody>
        </table>
        
        <div id="not_recommended" class="columns two">
            <div class="col">
                <p>The following patients were also screened and based upon the information provided, screening for BRCA, HBOC, and/or Lynch Syndrome is <strong>not</strong> recommended:</p>
            
                <table class="sTable">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>DOB</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Jane</td>
                            <td>Smith</td>
                            <td>3/3/1985</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div id="not_completed" class="col">
                <p>The following patients initiated, but did not complete the questionnaire:</p>
            
                <table class="sTable">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>DOB</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Irma</td>
                            <td>Redulus</td>
                            <td>9/18/1965</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <p>Access your physician portal any time at <span class="labColor">https://www.mdlab.com/questionnaire/login</span>. Please contact me for any additional details or if you do not have access to your portal.</p>
    
    <footer>
        <section class="gc_title">
            <h4>Brandon Franklin, PhD</h4>
            <em>Specialty Sales Consultant, Division of Genetics and Oncology</em>
        </section>
        
        <div class="columns">
            <figure class="foogo col"><img src="images/logo_mdl_print.svg" alt=""></figure>

            <div class="gc_contact col">
                <section>
                    <h5>Medical Diagnostic Laboratories, L.L.C.</h5>
                    <span>www.<b class="sb">mdlab</b>.com</span>
                </section>

                <ul>
                    <li><img src="images/icon_address.svg" alt="">2439 Kuser Rd</li>
                    <li>Hamilton, NJ 08690</li>
                </ul>

                <ul>
                    <li><img src="images/icon_phone.svg" alt="">888.414.3237</li>
                    <li><img class="gc_email" src="images/icon_email.svg" alt="">bfranklin@mdlab.com</li>
                </ul>
            </div>
        </div>
    </footer>
</section>


<?php require_once('scripts.php');?>

<script src='assets/js/jspdf.debug.js'></script>
<script src='assets/js/html2pdf.js'></script> 

<script type="text/javascript">  
    if ($('#dataTable').length ) {
        var table = $('#dataTable').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            //searching: false,
            lengthChange: false,
            "order": [[ 1, "asc" ]]
        });   
    }
    function PrintPreview() {

        var toPrint = document.getElementById('printarea');

        var popupWin = window.open('', '_blank','width=900,height=900,location=no,left=200px');

        popupWin.document.open();

        popupWin.document.write('<html><title>::Print Preview::</title><link rel="stylesheet" type="text/css" href="assets/css/forms.css" media="screen"/></head><body">');

        popupWin.document.write(toPrint.innerHTML);

        popupWin.document.write('</html>');

        popupWin.document.close();

    }
</script>
<style type="text/css">
    .followup{
        margin-bottom: 15px;
        font-size: 17px;
        color: #173d68;
        font-weight: bold;
        height: 40px;
        min-width: 100px;
        background: linear-gradient(to bottom, rgba(255,255,255,1) 46%,rgba(224,224,224,1) 64%,rgba(243,243,243,1) 100%);
        border: 0;
        outline: 0;
        cursor: pointer;
        border: 1px solid #b7b7b7;
        box-shadow: inset 0 0 30px rgba(255,255,255,.8), 0 1px 6px rgba(0,0,0,.31);
        padding-top: 0.5em;
        padding-bottom: 0.5em;
        border-radius: 2em;
        padding-right: 0.1em;
        padding-left: 0.1em;
        text-align: center;
        float: right; width: 150px;
    }
</style>
<?php require_once('footer.php');?>