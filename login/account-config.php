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
                                    <th class="noFilter text-center">Actions</th>
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
<?php require_once('scripts.php');?>
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
    
     
</script>
<?php require_once('footer.php');?>