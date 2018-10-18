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
$accessRole = getAccessRoleByKey('devices');
$roleIDs = unserialize($accessRole['value']);
$dataViewAccess = isUserHasAnyAccess($roleIDs, $roleID, 'view');

$thisMessage = "";
$fieldMsg = "";

$isSerialView = isset($roleIDs['serial_number']['view'])?$roleIDs['serial_number']['view']:"";
$isSalesRepView = isset($roleIDs['Guid_salesrep']['view'])?$roleIDs['Guid_salesrep']['view']:"";
$isDeviceNameView = isset($roleIDs['deviceid']['view'])?$roleIDs['deviceid']['view']:"";
$isInserviceDateView = isset($roleIDs['inservice_date']['view'])?$roleIDs['inservice_date']['view']:"";
$isOutserviceDateView = isset($roleIDs['outservice_date']['view'])?$roleIDs['outservice_date']['view']:"";
$isCommentView = isset($roleIDs['comment']['view'])?$roleIDs['comment']['view']:"";

$isActionAdd = isset($roleIDs['actions']['add'])?$roleIDs['actions']['add']:"";
$isActionEdit = isset($roleIDs['actions']['edit'])?$roleIDs['actions']['edit']:"";
$isActionDelete = isset($roleIDs['actions']['delete'])?$roleIDs['actions']['delete']:"";

$thisMessage = "";
if(isset($_GET['action']) && $_GET['action']=='edit'){
    if(!isFieldVisibleByRole($isActionEdit, $roleID)) {
        Leave(SITE_URL.'/devicesInventory.php');
    }
}
if(isset($_GET['action']) && $_GET['action']=='add'){
    if(!isFieldVisibleByRole($isActionAdd, $roleID)) {
        Leave(SITE_URL.'/devicesInventory.php');
    }
}
if (isset($_GET['delete']) && $_GET['delete'] != '') {
    if(!isFieldVisibleByRole($isActionAdd, $roleID)) {
        Leave(SITE_URL.'/devicesInventory.php');
    }
    deleteById($db, 'tbldeviceinv', $_GET['delete']);
    Leave(SITE_URL.'/devicesInventory.php');
}
$link = "";
if(isset($_GET['action'])&&$_GET['action']!=""){
    $link .= "?action=".$_GET['action'];
}
if(isset($_GET['id'])&&$_GET['id']!=""){
    $link .= "&id=".$_GET['id'];
}

if(isset($_POST['add_new_device'])){  
    $deviceData = array(
        'device_name'=>$_POST['device_name'],
        'url_flag'=>$_POST['url_flag']
    );
    
    $insert = insertIntoTable($db,'tbldevice', $deviceData); 
    if($insert['status']=='1'){
        Leave(SITE_URL.'/devicesInventory.php'.$link);
    }
} 
if(isset($_GET['add_device']) && $_GET['add_device']=='1'){  
     $modalBoxClass = "show";
} else {
    $modalBoxClass = "hide";
}

if(isset($_POST['save_device_inv'])){
    
    $data = $_POST;
    unset($data['save_device_inv']);
    
    if($_POST['inservice_date'] != ""){
        $data['inservice_date'] = date('Y-m-d h:i:s', strtotime($_POST['inservice_date']));
    } else {
        $data['inservice_date'] = '';
    }
    if($_POST['outservice_date'] != ""){
        $data['outservice_date'] = date('Y-m-d h:i:s', strtotime($_POST['outservice_date']));
    } else {
        $data['outservice_date'] = '';
    }
    
    if($_POST['id'] != "" ){
        //update
         if( ifDeviceSerialValid($_POST['serial_number'], $_POST['id']) ){
            $update = updateTable($db,'tbldeviceinv', $data, array('id'=>$_POST['id']));
            Leave(SITE_URL.'/devicesInventory.php?update');
        } else {
            $thisMessage = "Device ID <strong>".$_POST['serial_number']."</strong> Exists. Please choose another.";
            $accountFieldMsg = 'error';
        }        
    } else {
        //insert 
        if( ifDeviceSerialValid($_POST['serial_number']) ){
            $insert = insertIntoTable($db,'tbldeviceinv', $data);
            if($insert['status']=='1'){
                Leave(SITE_URL.'/devicesInventory.php?insert');
            }
        }else {
            $thisMessage = "Account ID <strong>".$_POST['serial_number']."</strong> Exists. Please choose another.";
            $accountFieldMsg = 'error';
        }
    }
    
}

$devices = getDeviceinves($db);
$salesreps = $db->selectAll('tblsalesrep');
$getDevices = $db->selectAll('tbldevice');

require_once ('navbar.php');
?> 

<main class="full-width">
    <?php 
    if($dataViewAccess){
        if(isset($_GET['update']) || isset($_GET['insert']) ){ 
            $thisMessage = "Changes have been saved";
        }
    ?>
    <?php if($thisMessage != ""){ ?>
    <section id="msg_display" class="show success">
        <h4><?php echo $thisMessage;?></h4>
    </section>
    <?php } ?>  
    <div class="box full visible">
        <section id="palette_top">
            <h4>
                <?php if(isset($_GET['action']) && $_GET['action']=='edit'){ ?>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/devicesInventory.php">Devices</a></li>
                        <li class="active">
                            Edit Device	
                        </li>
                    </ol>                      
                <?php } elseif(isset($_GET['action']) && $_GET['action']=='add') { ?>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/devicesInventory.php">Devices</a></li>
                        <li class="active">
                            Add New Device	
                        </li>
                    </ol>  
                <?php    } else { ?>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                        <!--<li><a href="<?php echo SITE_URL; ?>/devicesInventory.php">Device Inventory Manager</a></li>-->
                        <li class="active">
                            Devices                           
                        </li>
                    </ol>
                <?php } ?>                
            </h4>
            <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
            <a href="<?php echo SITE_URL; ?>/dashboard2.php" class="button homeIcon"></a>
            <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
        </section>
        <div id="app_data" class="scroller">
            <div class="row">
                <?php 
                if( isset($_GET['action']) && $_GET['action'] !='' ){
                if($_GET['action'] =='edit'){
                    $deviceInfo = get_row($db, 'tbldeviceinv', ' WHERE id='.$_GET['id']); 
                    if(empty($deviceInfo)){
                        Leave(SITE_URL."/devicesInventory.php");
                    }
                    $deviceInfo = $deviceInfo['0'];
                    extract($deviceInfo);                    
                    $actionName = 'update';
                    
                }else{
                    $serial_number = '';
                    $id='';
                    $Guid_salesrep = '';
                    $device_type = '';
                    $comment = '';
                    $url_flag = '';
                    $inservice_date ='';
                    $outservice_date='';
                    $actionName = 'insert';
                }
            ?>
           
            <div class="col-md-12">
                <form action="" method="POST">                
                    <div class="row">
                        <div class="col-md-2"></div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="status_chart">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <span class="registred">                                                    
                                                    Registered 
                                                    <img src="assets/eventschedule/icons/silhouette_icon.png">
                                                    <?php 
                                                        $Registered = getDeviceStatusCount($db, $Guid_salesrep, '28' ); //28->Registered 
                                                        echo ($Registered>0)?Registered:'-';
                                                    ?>
                                                </span>
                                                <span class="completed">
                                                    Completed
                                                    <img src="assets/eventschedule/icons/checkmark_icon.png">
                                                    <?php 
                                                    $Completed = getDeviceStatusCount($db, $Guid_salesrep, '36'); //36->Questionnaire Completed 
                                                    echo ($Completed>0)?$Completed:'-';
                                                    ?>
                                                </span>
                                                <span class="qualified">                                                    
                                                    Qualified
                                                    <img src="assets/eventschedule/icons/dna_icon.png">
                                                    <?php 
                                                    $Qualified = getDeviceStatusCount($db, $Guid_salesrep, '29'); //29->Questionnaire Completed->Qualified 
                                                    echo ($Qualified>0)?$Qualified:'-';
                                                    ?>
                                                </span>
                                                <span class="submitted">                                                    
                                                    Submitted
                                                    <img src="assets/eventschedule/icons/flask_icon.png">
                                                    <?php 
                                                    $Submitted = getDeviceStatusCount($db, $Guid_salesrep, '1' ); //28->Submitted (Specimen Collected) 
                                                    echo ($Submitted>0)?$Submitted:'';
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row pB-30">
                                <div class="col-md-6">
                                    <button name="save_device_inv" type="submit" class="btn-inline">Save</button>
                                    <button onclick="goBack();" type="button" class="btn-inline btn-cancel">Cancel</button>                   
                                    <!--<a href="<?php echo SITE_URL."/devicesInventory.php";?>" class="btn-inline btn-cancel">Cancel</a>-->                                   
                                </div>                                
                                <div class="col-md-6 text-center">
                                    <span class="error" id="message"></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="hidden" name="id" value="<?php echo $id; ?>" />
                                    <?php if(isFieldVisibleByRole($isSerialView, $roleID)) {?>
                                    <div class="f2 required <?php echo ($serial_number!="")?"valid show-label":"";?>">
                                        <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                        <div class="group">
                                            <input id="serial_number" name="serial_number" type="text" value="<?php echo $serial_number; ?>" placeholder="Serial" required="">
                                            <p class="f_status">
                                                <span class="status_icons"><strong>*</strong></span>
                                            </p>
                                        </div>
                                    </div>    
                                    <?php } ?>
                                    <?php if(isFieldVisibleByRole($isSalesRepView, $roleID)) {?>
                                    <div class="f2  <?php echo ($Guid_salesrep!='0' && $Guid_salesrep!="")?"valid show-label":"";?>">
                                        <label class="dynamic" for="Guid_salesrep"><span>Genetic Consultant</span></label>
                                        <div class="group">
                                            <select class="no-selection" name="Guid_salesrep" id="Guid_salesrep">
                                                <option>Genetic Consultant</option>
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
                                    <?php } ?>
                                    <?php if(isFieldVisibleByRole($isDeviceNameView, $roleID)) {?>
                                    <div class="row">
                                        <div class="col-md-10 select_device_dropdown">
                                          <div class="f2  <?php echo ($deviceid!="")?"valid show-label":"";?>">
                                            <label class="dynamic" for="deviceType"><span>Device Name</span></label>
                                            <div class="group">
                                                <select name="deviceid" class="form-control">
                                                    <option>Select Device</option>
                                                      <?php 
                                                        foreach ($getDevices as $key => $v) { 
                                                            $selected = (isset($deviceid)&&$deviceid == $v['deviceid']) ? ' selected' : '';
                                                            ?>
                                                        <option <?php echo $selected; ?> value="<?php echo $v['deviceid']; ?>"><?php echo $v['device_name']; ?></option>     
                                                        <?php }?>
                                                 </select>
                                                <p class="f_status">
                                                    <span class="status_icons"><strong></strong></span>
                                                </p>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="col-md-2 text-center addPlusIconBox">
                                          <a class="add-new-device wh-35" href="<?php echo $link; ?>&add_device=1">
                                              <span class="fas fa-plus-circle" aria-hidden="true"></span>
                                          </a>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>                            
                                <div class="col-md-6">
                                    <?php if(isFieldVisibleByRole($isInserviceDateView, $roleID)) {?>
                                    <div class="f2 <?php echo (($inservice_date !="" && !preg_match("/0{4}/" , $inservice_date)))?"valid show-label":"";?>">
                                        <label class="dynamic" for="inservice_date"><span>In-Service Date</span></label>
                                        <div class="group">
                                            <input readonly="" autocomplete="off" class="datepicker" id="inservice_date" name="inservice_date" type="text" value="<?php echo ($inservice_date !="" && !preg_match("/0{4}/" , $inservice_date)) ? date('n/j/Y', strtotime($inservice_date)) : ""; ?>" placeholder="In-Service Date">
                                            <p class="f_status">
                                                <span class="status_icons"><strong>*</strong></span>
                                            </p>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <?php if(isFieldVisibleByRole($isOutserviceDateView, $roleID)) {?>
                                    <div class="f2 <?php echo ($outservice_date !="" && (!preg_match("/0{4}/" , $outservice_date)))?"valid show-label":"";?>">
                                        <label class="dynamic" for="outservice_date"><span>Out-Of-Service Date</span></label>
                                        <div class="group">
                                            <input readonly="" autocomplete="off" class="datepicker" id="outservice_date" name="outservice_date" type="text" value="<?php echo ($outservice_date !="" && !preg_match("/0{4}/" , $outservice_date)) ? date('n/j/Y', strtotime($outservice_date)) : ""; ?>" placeholder="Out-Of-Service Date">
                                            <p class="f_status">
                                                <span class="status_icons"><strong>*</strong></span>
                                            </p>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <?php if(isFieldVisibleByRole($isCommentView, $roleID)) {?>
                                    <div class="f2 <?php echo ($comment!="")?"valid show-label":"";?>">
                                        <label class="dynamic" for="comment"><span>Comment</span></label>
                                        <div class="group fullWidth">
                                            <textarea rows="3" name="comment" class="form-control " id="comment" placeholder="Your Comment here."><?php echo $comment; ?></textarea>
                                            
                                        </div>
                                    </div>   
                                    <?php } ?>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </form>
            </div>
            
                
            <?php } else { ?>
                <div class="col-md-12">
                    <?php if(isFieldVisibleByRole($isActionAdd, $roleID)) {?>
                    <div class="row">
                        <div class="col-md-12 ">
                            <a class="add-new-device" href="<?php echo SITE_URL; ?>/devicesInventory.php?action=add">
                                <span class="fas fa-plus-circle" aria-hidden="true"></span> <span>Add</span>
                            </a>
                        </div>
                    </div>     
                    <?php } ?>
                    <table id="dataTable" class="table">
                        <thead>
                            <tr>                                
                                <?php if(isFieldVisibleByRole($isSalesRepView, $roleID)) {?>
                                    <th>Genetic Consultant</th>
                                <?php } ?>
                                <?php if(isFieldVisibleByRole($isDeviceNameView, $roleID)) {?>
                                    <th>Device Name</th>
                                <?php } ?>
                                <?php if(isFieldVisibleByRole($isSerialView, $roleID)) {?>
                                    <th>Serial Number</th>
                                <?php } ?>
                                <th class="">Registered</th>           
                                <th class="">Completed</th>           
                                <th class="">Qualified</th>           
                                <th class="">Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $i = 1;
                        $devices = getDeviceInvsWithSalesRepInfo($db);
                        foreach ($devices as $k => $v) {
                            ?>
                            <tr>                                
                                <?php if(isFieldVisibleByRole($isSalesRepView, $roleID)) {?>
                                    <td>
                                        <a href="<?php echo SITE_URL; ?>/devicesInventory.php?action=edit&id=<?php echo $v['id']; ?>">
                                        <?php echo $v['first_name']." ".$v['last_name']; ?>
                                        </a>
                                    </td>
                                <?php } ?>
                                <?php if(isFieldVisibleByRole($isDeviceNameView, $roleID)) {?>
                                    <td><?php echo $v['device_name']; ?></td>
                                <?php } ?>
                                <?php if(isFieldVisibleByRole($isSerialView, $roleID)) {?>
                                    <td><?php echo $v['serial_number']; ?></td>
                                <?php } ?>  
                                    
                                <td><?php echo getDeviceStatusCount($db, $v['Guid_salesrep'], '28' ); //28->Registered ?></td>
                                <td><?php echo getDeviceStatusCount($db, $v['Guid_salesrep'], '36'); //36->Questionnaire Completed ?></td>
                                <td><?php echo getDeviceStatusCount($db, $v['Guid_salesrep'], '29'); //29->Questionnaire Completed->Qualified ?></td>
                                <td><?php echo getDeviceStatusCount($db, $v['Guid_salesrep'], '1' ); //28->Submitted (Specimen Collected) ?></td>
                                
                            </tr>
                        <?php
                            $i++;
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php } else { ?>
    <div class="box full visible ">  
            <h4> Sorry, You Don't have Access to this page content. </h4>
            
     </div>
    <?php } ?>
</main>


<div id="add-account-provider-box" class="modalBlock <?php echo $modalBoxClass; ?>">
    <div class="contentBlock">        
        <h5 class="providersTitle">Add New Device</h5>        
        <form action="" method="POST">            
            <div class="f2">
                <label class="dynamic" for="device_name"><span>Device Name</span></label>
                <div class="group">
                    <input required="" name="device_name" value="" type="text" class="form-control" id="device_name" placeholder="Device Name">
                    <p class="f_status">
                        <span class="status_icons"><strong>*</strong></span>
                    </p>
                </div>
            </div>
                                   
            <div class="form-group text">
              <label for="url_flag">URL Flag</label><br/>                      
              <div class="radio-inline">
                    <label><input type="radio"  name="url_flag" value="1">Yes</label>
              </div>
              <div class="radio-inline">
                  <label><input type="radio" checked="" name="url_flag" value="0">No</label>
              </div>
            </div>

            <button name="add_new_device" type="submit" class="btn-inline">Save</button>
            <button onclick="goBack();" type="button" class="btn-inline btn-cancel">Cancel</button>                   
            <!--<a href="<?php echo SITE_URL."/devicesInventory.php".$link;?>" class="btn-inline btn-cancel">Cancel</a>-->
        </form>          
    </div>    
</div>



<?php require_once('scripts.php');?>


<script type="text/javascript">
    
    if ($('#dataTable').length ) {
        var table = $('#dataTable').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            //searching: false,
            lengthChange: false,
            "paging":   false,
            "info":     false,
            "aoColumnDefs": [
              { 
                  "bSortable": false, 
                  "aTargets": [ 3,6 ] } 
            ]
        });
    }
   
</script>

<?php require_once('footer.php');?>