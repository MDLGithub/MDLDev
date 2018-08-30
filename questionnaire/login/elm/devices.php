<?php
ob_start();
require_once('settings.php');
require_once('config.php');
require_once('header.php');

if (!isUserLogin()) {
    Leave(SITE_URL);
}

if (isset($_GET['logout'])) {
    doLogout();
    Leave(SITE_URL);
}

if (isset($_GET['delete']) && $_GET['delete'] != '') {
    deleteById($db, 'tbldevice', $_GET['delete']);
}

if(isset($_POST['insert'])){
    $insert = insertDevice($db, $_POST);
    if($insert['status']=='1'){
        Leave(SITE_URL.'/devices.php');
    }
}
if(isset($_POST['update'])){
    $update = updateDevice($db, $_POST);
    if($update['status']=='1'){
         Leave(SITE_URL.'/devices.php');
    }
}
$devices = $db->selectAll('tbldevice');
$salesreps = $db->selectAll('tblsalesrep');
?>
<div class="container">
    <!-- Static navbar -->
    <?php require_once 'navbar.php'; ?> 

    <div class="url_config_box">
        <div class="row">
            <div class="col-md-12 text-center">
                <h2 class="devicesTitle">
                    <?php 
                        if(isset($_GET['action']) && $_GET['action']=='edit'){
                           echo "Edit Device";
                        }
                        elseif(isset($_GET['action']) && $_GET['action']=='add') {
                            echo "Add New Device";
                        } else {
                            echo "Device Inventory Manager";
                            echo '<a class="add-new-device" href="?action=add">
                                    <span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
                                </a>';
                        }                    
                    ?>
                    
                </h2>
                <br/>
            </div>

            <?php 
            
            if( isset($_GET['action']) && $_GET['action'] !='' ){ 
                
                if($_GET['action'] =='edit'){
                    $deviceInfo = get_row($db, 'tbldevice', ' WHERE id='.$_GET['id']); 
                    $deviceInfo = $deviceInfo['0'];
                    extract($deviceInfo);                    
                    $actionName = 'update';
                    
                }else{
                    $serial_number = '';
                    $id='';
                    $Guid_salesrep = '';
                    $device_type = '';
                    $device_name = '';
                    $url_flag = '';
                    $actionName = 'insert';
                }
               
           
            ?>
           
            <div class="col-md-5">
                <form action="" method="POST">
                    <input type="hidden" name="id" value="<?php echo $id; ?>" />
                    <div class="form-group">
                      <label for="Serial">Serial </label>
                      <input name="serial_number" value="<?php echo $serial_number; ?>" type="text" class="form-control" id="Serial" placeholder="Serial Number">
                    </div>
                    <div class="form-group">
                      <label for="salesRep">Sales Rep</label>
                      <select class="form-control" name="Guid_salesrep">
                          <?php 
                          foreach ($salesreps as $key => $v) { 
                              //var_dump($v);
                              $selected = ($Guid_salesrep == $v['Guid_salesrep']) ? ' selected' : '';
                              ?>
                          <option <?php echo $selected; ?> value="<?php echo $v['Guid_salesrep']; ?>"><?php echo $v['name']; ?></option>     
                          <?php }?>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="deviceType">Device Name</label>
                      <input value="<?php echo $device_type; ?>" name="device_type" type="text" class="form-control" id="deviceType" placeholder="Device Type">
                    </div>
                   
                    <div class="form-group">
                      <label for="deviceName">Comment</label>
                      <textarea name="device_name" class="form-control" id="deviceName" placeholder="Device Name"><?php echo $device_name; ?></textarea>
                    </div>
                    
                   
                    
                    <button name="<?php echo $actionName;?>" type="submit" class="btn btn-primary">Save</button>
                    <a href="<?php echo SITE_URL; ?>/devices.php" name="cancel" class="btn btn-danger">Cancel</a>
                  </form>
            </div>
            
                
            <?php } else { ?>

            <table class="table">
                <thead>
                    <tr>
                        <!--<th></th>-->
                        <th>Serial</th>
                        <th>Sales Rep</th>
                        <th>Device Name</th>
                        <th>Comment</th>
                        <th>Ship Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    foreach ($devices as $k => $v) {
                        $selesrepId = $v['Guid_salesrep'];
                        $salesrep = get_field_value($db, 'tblsalesrep', 'name', 'WHERE Guid_salesrep=' . $selesrepId);
                        ?>
                        <tr>
                            <!--<td><?php echo $v['id']; ?></td>-->
                            <td><?php echo $v['serial_number']; ?></td>
                            <td><?php echo $salesrep['name']; ?></td>
                            <td><?php echo $v['device_type']; ?></td>
                            <td><?php echo substr($v['device_name'], 0, 50); ?></td>
                            <td><?php //echo ($v['url_flag'] == '1') ? 'Yes' : 'No'; ?></td>
                            <td class="text-center">
                                <a href="?action=edit&id=<?php echo $v['id']; ?>">
                                    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                </a>&nbsp;&nbsp;
                                <a onclick="javascript:confirmationDeleteDevice($(this));return false;" href="?delete=<?php echo $v['id'] ?>&id=<?php echo $v['id']; ?>">
                                    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span> 
                                </a>
                            </td>
                        </tr>
                    <?php
                        $i++;
                    }
                    ?>


                </tbody>
            </table>
            
            <?php } ?>
        </div>
    </div>
</div>

<?php require_once('footer.php');?>