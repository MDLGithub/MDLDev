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
$role = $roleInfo['role'];

if($role!="Admin"){
    Leave(SITE_URL."/no-permission.php");
}
$users = getUsersAndRoles($db);

$initLabels = array(
    'first_name'=>'Patient First Name',
    'last_name'=>'Patient Last Name',
    'account'=>'Account#',
    'salesrep'=>'Sales Rep',
);
//$initQ = 'SELECT s.Guid_status, p.Guid_patient, p.firstname, p.lastname,
//	a.account AS account_number, a.name AS account_name,
//	CONCAT(srep.`first_name`, " " ,srep.`last_name`) AS salesrep
//        FROM `tbl_mdl_stats` s
//        LEFT JOIN `tblpatient` p ON s.Guid_patient=p.Guid_patient
//        LEFT JOIN `tblaccount` a ON s.Guid_account=a.Guid_account
//        LEFT JOIN `tblsalesrep` srep ON s.Guid_salesrep=srep.Guid_salesrep
//        WHERE s.Guid_status=:Guid_status ';
//$initData=$db->query($initQ, array('Guid_status'=>$_GET['status_id']));

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
$statsConfigUrl= SITE_URL.'/mdl-stat-details-config.php';


$configOptions = getOption($db, 'stat_details_config');
$optionVal = unserialize($configOptions['value']);

require_once ('navbar.php');
?>

<main class="full-width">
    <div class="box full visible ">

	<section id="palette_top" class="shorter_palette_top">
	    <h4>
		<ol class="breadcrumb">
		    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
		    <li class="active">MDL Status Details Configuration</li>
		</ol>
	    </h4>
	    <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
	    <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
	</section>

	<div id="app_data" class="scroller ">
	    <h1 class="title-st1 fs-20 ">MDL Status Details Configuration</h1>

	    <div class="row ">
		<div class="col-md-12">
		    <table class="table">
			<thead>
			    <tr>
				<th>Fields/Custom Name</th>
				<th>Assigned Status</th>
				<th>Assigned Roles</th>
				<th class="wh-100">Actions</th>
			    </tr>
			</thead>
			<tbody>
			    <?php foreach ($labels as $k=>$v){ ?>
			    <tr>
				<td>
				    <?php
				    echo $v;
				    if(isset($optionVal[$k]['label'])){
					echo " / ".$optionVal[$k]['label'];
				    }

				    ?>
				</td>
				<td>
				    <?php
					if(isset($optionVal[$k])){
					    if(isset($optionVal[$k]['statuses'])){
						$assignedStatuses = $optionVal[$k]['statuses'];
						$statusNames = "";
						foreach ($assignedStatuses as $key=>$stausID){
						    $getStatusParent = $db->row("SELECT parent_id FROM `tbl_mdl_status` WHERE Guid_status=:Guid_status", array('Guid_status'=>$stausID));
						    $parent=$getStatusParent['parent_id'];
						    $parent = $parent!="0" ? $parent : "";
						    $statusNames .= getStatusParentNames($db, $stausID)."; ";
						}
						echo rtrim($statusNames, '; ');
					    }
					}
				    ?>
				</td>
				<td>
				    <?php
					if(isset($optionVal[$k])){
					    if(isset($optionVal[$k]['roles'])){
						$assignedRoles = $optionVal[$k]['roles'];
						$roleNames = "";
						foreach ($assignedRoles as $key=>$roleID){
						    $roleNames .= getRoleName($db, $roleID)."; ";
						}
						echo rtrim($roleNames, '; ');
					    }
					}
				    ?>
				</td>
				<td class="text-center">
				    <a href="<?php echo $statsConfigUrl.'?field_id='.$k; ?>" class="">
					<span class="fas fa-pencil-alt"></span>
				    </a>
				</td>
			    </tr>
			    <?php } ?>
			</tbody>
		    </table>
		</div>
	    </div>


	</div>

    </div>
</main>


<?php

$stConfigInfo = array();
$stConfigInfo['label'] = "";

$mdlConfigData = array();
$getOption = getOption($db, 'stat_details_config');
$optionValue = unserialize($getOption['value']);

if(isset($_POST['add_stat_config'])){
    $mdlConfigData = array();
    $rolesData = array();
    $statusData = array();
    if(isset($_POST['roles']) && !empty($_POST['roles'])){
	foreach ($_POST['roles'] as $k=>$v){
	    $rolesData[] = $k;
	}
    }
    if(isset($_POST['stauses']) && !empty($_POST['stauses'])){
	$statusData = $_POST['stauses'];
    }

    foreach ($_POST['label'] as $k=>$v){
	if($v != ""){
	    $mdlConfigData['label'] = $v;
	}
	if(!empty($statusData)){
	    $mdlConfigData['statuses'] = $statusData;
	}
	if(!empty($rolesData)){
	    $mdlConfigData['roles'] = $rolesData;
	}
    }
    if(!empty($mdlConfigData)){
	$key = 'stat_details_config';
	$fieldID = $_POST['field_id'];
	$fieldID = $_POST['field_id'];


	//if(isset($optionValue) && !empty($optionValue)){
	    $optionValue[$fieldID] = $mdlConfigData;
	    setOption($db, $key, serialize($optionValue), 'columns' );
//        }else {
//            setOption($db, $key, serialize($mdlConfigData), 'columns' );
//        }

	Leave(SITE_URL.'/mdl-stat-details-config.php');
    }
}

?>

<?php
if(isset($_GET['field_id'])){
    $fieldId = $_GET['field_id'];
    $fieldOptions = $optionVal[$fieldId];
    $fieldConfigTitle = (isset($fieldOptions['label'])&&$fieldOptions['label']!="")?$fieldOptions['label'] : $labels[$fieldId];
?>
<div id="manage-status-modal" class="modalBlock ">
    <div class="contentBlock manageStatsModal">
	<a class="close" href="<?php echo $statsConfigUrl; ?>">X</a>

	<h5 class="title">
	    Config field - <?php echo $fieldConfigTitle; ?>
	</h5>
	<div class="content">
	    <!--<div class="status-list">list here...</div>-->
	    <div class="add-status-form">
		<form action="" method="POST">
		    <h4 class="text-center"></h4>
		    <?php if(isset($message)){ ?>
			<div class="text-center success-text"><?php echo $message; ?></div>
		    <?php } ?>
		    <div class="row">
			<input type="hidden" name="field_id" value="<?php echo isset($_GET['field_id'])?$_GET['field_id']:""; ?>" />
			<div class="col-md-12 clearfix">
			    <div class="f2 ">
				<label class="dynamic" for="custom_field_name"><span>Custom Field Name</span></label>
				<div class="group">
				    <input value="<?php echo isset($_POST['custom_field_name'])?$_POST['custom_field_name']:$fieldConfigTitle; ?>" type="text" autocomplete="off" id="custom_field_name" name="label[<?php echo isset($_GET['field_id'])?$_GET['field_id']:""; ?>]" placeholder="Custom Field Name">
				    <p class="f_status">
					<span class="status_icons"><strong></strong></span>
				    </p>
				</div>
			    </div>
			</div>
			<div class="col-md-6">
			    <div class="stat-config-title">
				<label>Select Statuses</label>
				<span class="pull-right"><input data-id="statuses-dropdowns-box" class="checkAll" type="checkbox" /> All</span>
			    </div>
			    <div id="statuses-dropdowns-box">
				<?php echo get_option_of_nested_status($db,0,'',TRUE); ?>
			    </div>
			</div>
			<div class="col-md-6">
			    <div class="stat-config-title">
				<label>Select Roles</label>
				<span class="pull-right"><input class="checkAll" data-id="roles-dropdown-box" type="checkbox" /> All </span>
			    </div>
			    <div id="roles-dropdown-box">
				<?php
				    $roles = $db->selectAll('tblrole');
				?>
				<?php foreach ($roles as $key => $role) { ?>
				<?php
				    $isSelected = "";
				    if(isset($fieldOptions['roles'])){
				    $isSelected = in_array($role['Guid_role'], $fieldOptions['roles'])? " checked": "";
				    }
				?>
				    <p><input <?php echo $isSelected; ?> type="checkbox" name="roles[<?php echo $role['Guid_role']; ?>]" > <?php echo $role['role']; ?></p>
				<?php }?>
			    </div>
			</div>
			<div class="col-md-12 clearfix">
			    <div class="text-right pT-10">
				<button class="button btn-inline" name="add_stat_config" type="submit" >Save</button>
				<a href="<?php echo $statsConfigUrl; ?>" class="btn-inline btn-cancel">Cancel</a>
			    </div>
			</div>
		    </div>
		</form>
	    </div>
	</div>
    </div>
</div>
<?php } ?>



<?php require_once('scripts.php');?>

<?php require_once('footer.php');?>