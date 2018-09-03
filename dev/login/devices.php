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

$userID = $_SESSION['user']["id"];
$roleInfo = getRole($db, $userID);
$roleID = $roleInfo['Guid_role'];
$accessRole = getAccessRoleByKey('devices');
$roleIDs = unserialize($accessRole['role_ids']);
$dataViewAccess = isUserHasAnyAccess($roleIDs, $roleID, 'view');


if (isset($_GET['delete']) && $_GET['delete'] != '') {
    deleteRowByField($db, 'tbldevice', array('deviceid'=>$_GET['delete']));
    Leave(SITE_URL.'/devices.php');
}
if(isset($_POST['cancel'])){
    Leave(SITE_URL.'/devices.php');
}
if(isset($_POST['insert'])){
    $data = $_POST;
    unset($data['insert']);
    unset($data['deviceid']);
    $insert = insertIntoTable($db,'tbldevice', $data);
    if($insert['status']=='1'){
	Leave(SITE_URL.'/devices.php');
    }
}
if(isset($_POST['update'])){
    $data = $_POST;
    unset($data['update']);
    unset($data['deviceid']);
    $update = updateTable($db,'tbldevice', $data, array('deviceid'=>$_POST['deviceid']));
    if($update['status']=='1'){
	 Leave(SITE_URL.'/devices.php');
    }
}
$devices = $db->selectAll('tbldevice');
$salesreps = $db->selectAll('tblsalesrep');
?>
<?php require_once 'navbar.php'; ?>
<main class="full-width">
    <?php if($dataViewAccess) { ?>
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
		<?php } else { ?>
		    <ol class="breadcrumb">
			<li><a href="<?php echo SITE_URL; ?>">Home</a></li>
			<li>Devices</li>
		    </ol>
		<?php } ?>
	    </h4>
	    <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
	    <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
	</section>

	<div id="app_data" class="scroller">
	    <div class="row">
		<?php
		if( isset($_GET['action']) && $_GET['action'] !='' ){

		if($_GET['action'] =='edit'){
		    $deviceInfo = get_row($db, 'tbldevice', ' WHERE deviceid='.$_GET['id']);
		    $deviceInfo = $deviceInfo['0'];
		    extract($deviceInfo);
		    $actionName = 'update';
		}else{
		    $deviceid='';
		    $device_name = '';
		    $url_flag = '';
		    $actionName = 'insert';
		}


	    ?>

	    <div class="col-md-5">
		<form action="" method="POST">
		    <input type="hidden" name="deviceid" value="<?php echo $deviceid; ?>" />
		    <div class="form-group">
		      <label for="device_name">Device Name </label>
		      <input name="device_name" value="<?php echo $device_name; ?>" type="text" class="form-control" id="device_name" placeholder="Device Name">
		    </div>
		    <div class="form-group">
		      <label for="url_flag">URL Flag</label><br/>
		      <div class="radio-inline">
			<label><input <?php echo ($url_flag==1) ? ' checked' : ""; ?> type="radio"  name="url_flag" value="1">Yes</label>
		      </div>
		      <div class="radio-inline">
			<label><input <?php echo ($url_flag==0) ? ' checked' : ""; ?> type="radio"  name="url_flag" value="0">No</label>
		      </div>

		    </div>

		    <button name="<?php echo $actionName;?>" type="submit" class="btn-inline">Save</button>
		    <button name="cancel" type="submit" class="btn-inline">Cancel</button>

		  </form>
	    </div>


	    <?php } else { ?>
		<div class="col-md-12">
		    <div class="row">
			<div class="col-md-12">
			    <a class="add-new-device" href="?action=add">
				<span class="fas fa-plus-circle"></span> Add
			    </a>
			</div>
		    </div>
		    <table id="dataTable" class="table">
		    <thead>
			<tr>
			    <th>Device Name</th>
			    <th>URL Flag</th>
			    <!--<th class="noFilter">Ship Date</th>-->
			    <th class="noFilter actions text-center">Actions</th>
			</tr>
		    </thead>
		    <tbody>
			<?php
			$i = 1;
			foreach ($devices as $k => $v) {
				?>
			    <tr>
				<td><?php echo $v['device_name']; ?></td>
				<td><?php echo ($v['url_flag']==1)? "Yes" : "No"; ?></td>
				<!--<td><?php //echo $v['ship_date']; ?></td>-->
				<td class="text-center">
				    <a href="?action=edit&id=<?php echo $v['deviceid']; ?>">
					<span class="fas fa-pencil-alt"></span>
				    </a>&nbsp;&nbsp;
				    <a onclick="javascript:confirmationDeleteDevice($(this));return false;" href="?delete=<?php echo $v['deviceid'] ?>&id=<?php echo $v['deviceid']; ?>">
					<span class="far fa-trash-alt"></span>
				    </a>
				</td>
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

<?php require_once('scripts.php');?>

<script type="text/javascript">
    if ($('#dataTable').length ) {
	var table = $('#dataTable').DataTable({
			orderCellsTop: true,
			fixedHeader: true,
			lengthChange: false,
			"aoColumnDefs": [
			  {
			      "bSortable": false,
			      "aTargets": [ 2 ]
			  }
			]
		    });
    }
</script>
<?php require_once('footer.php');?>
