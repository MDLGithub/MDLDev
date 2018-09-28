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
$roleID = $roleInfo['Guid_role'];

if($role!="Admin"){
    Leave(SITE_URL."/no-permission.php");
}
$users = getUsersAndRoles($db);

//this is just for test, will remove it later
if(isset($_GET['get_patient_ids'])){
    $patientEmptyIds = $db->query("SELECT * FROM `tbl_mdl_status_log` WHERE Guid_patient='0'");
    foreach ($patientEmptyIds as $k=>$v){
	$getPatient = $db->row("SELECT Guid_patient FROM `tblpatient` WHERE Guid_user=:Guid_user", array('Guid_user'=>$v['Guid_user']));
	$patientID = $getPatient['Guid_patient'];
	//updateTable($db, 'tbl_mdl_stats', array('Guid_patient'=>$patientID), array('Guid_user'=>$v['Guid_user']));
	var_dump("Guid_patient: ".$patientID."; Guid_user: ".$v['Guid_user']);
    }
}//remove it after all tests

//exclude test users from mdl stats
$testUserIds = getTestUserIDs($db);
$markedTestUserIds = getMarkedTestUserIDs($db);

$initLabels = array(
    'first_name'=>'Patient First Name',
    'last_name'=>'Patient Last Name',
    'account'=>'Account#',
    'salesrep'=>'Sales Rep',
);

    $initQ = 'SELECT s.Guid_status, s.Guid_user, s.Date, s.Date_created, p.Guid_patient,
	p.firstname, p.lastname,
	a.account AS account_number, a.name AS account_name, a.address AS location,
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
    $initQ.='AND s.Guid_patient<>"0"';
if(isset($_GET['status_id'])&& $_GET['status_id']!=""){
    $initData=$db->query($initQ, array('Guid_status'=>$_GET['status_id']));
} else {
    $initData = array();
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

require_once ('navbar.php');
?>

<main class="full-width">
    <div class="box full visible ">

	<section id="palette_top" class="shorter_palette_top">
	    <h4>
		<ol class="breadcrumb">
		    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
		    <li class="active">MDL Stats</li>
		</ol>
	    </h4>
	    <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
	    <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
	</section>

	<div id="app_data" class="scroller ">
	    <?php $parent = isset($_GET['parent'])?$_GET['parent']:""; ?>
	    <h1 class="title-st1">
		Status: <?php echo getStatusName($db, $_GET['status_id'], $parent); ?>
		<a class="pull-right" href="<?php echo SITE_URL."/mdl-stat-details-config.php"?>"  style="font-size:30px; margin-right: 30px;">
		    <i class="fas fa-cogs "></i>
		</a>
	    </h1>
	    <?php if(isset($_GET['status_id']) && $_GET['status_id']!=""){ ?>
	    <div class="row ">
		<div class="col-md-12 text-center">
		    <table id="dataTable" class="table">
			<thead>
			    <tr>
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
				$revenue = getRevenueStat($db, $v['Guid_user']);
				$patientInfoUrl = SITE_URL.'/patient-info.php?patient='.$v['Guid_user'];
				if($v['account_number'] && $v['account_number']!=''){
				    $patientInfoUrl .= '&account='.$v['account_number'];
				}
			    ?>
			    <tr class="text-left">

				<?php if(isFieldVisibleForStatus($db, 'mdl_number', $_GET['status_id']) && isFieldVisibleForRole($db, 'mdl_number', $roleID)){ ?>
				<td><?php echo $v['mdl_number'];?></td>
				<?php } ?>

				<?php if(isFieldVisibleForStatus($db, 'first_name', $_GET['status_id']) && isFieldVisibleForRole($db, 'first_name', $roleID)){ ?>
				<td><a href="<?php echo $patientInfoUrl; ?>"><?php echo ucfirst(strtolower($v['firstname']));?></a></td>
				<?php } ?>

				<?php if(isFieldVisibleForStatus($db, 'last_name', $_GET['status_id']) && isFieldVisibleForRole($db, 'last_name', $roleID)){ ?>
				<td><a href="<?php echo $patientInfoUrl; ?>"><?php echo ucfirst(strtolower($v['lastname']));?></a></td>
				<?php } ?>

				<?php if(isFieldVisibleForStatus($db, 'account', $_GET['status_id']) && isFieldVisibleForRole($db, 'account', $roleID)){ ?>
				<td><?php echo $v['account_number'];?></td>
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
			<?php $userRevenuTotals = getStatusRevenueTotals($db, $_GET['status_id']); ?>
			<tfoot class="strong">
			    <?php if(isFieldVisibleForStatus($db, 'insurance_paid', $_GET['status_id']) && isFieldVisibleForRole($db, 'insurance_paid', $roleID)){ ?>
			    <tr>
				<td class=" text-right" colspan="1">Insurance Total: </td>
				<td colspan="2"><?php echo "$".formatMoney($userRevenuTotals['insurance_total']); ?></td>
			    </tr>
			    <?php } ?>
			    <?php if(isFieldVisibleForStatus($db, 'patient_paid', $_GET['status_id']) && isFieldVisibleForRole($db, 'patient_paid', $roleID)){ ?>
			    <tr>
				<td class=" text-right" colspan="1">Patient Total: </td>
				<td colspan="2"><?php echo "$".formatMoney($userRevenuTotals['patient_total']); ?></td>
			    </tr>
			    <?php } ?>
			    <?php if(isFieldVisibleForStatus($db, 'total_paid', $_GET['status_id']) && isFieldVisibleForRole($db, 'total_paid', $roleID)){ ?>
			    <tr>
				<td class=" text-right" colspan="1">Total: </td>
				<td colspan="2"><?php echo "$".formatMoney($userRevenuTotals['total']); ?></td>
			    </tr>
			    <?php } ?>
			</tfoot>
		    </table>
		</div>
	    </div>
	    <?php } ?>


	</div>

    </div>
</main>




<?php require_once('scripts.php');?>
<script type="text/javascript">
    if ($('#dataTable').length ) {
	var table = $('#dataTable').DataTable({

		fixedHeader: true,
		lengthMenu: [[10, 20, 30, 50, 100,-1], [10, 20, 30, 50, 100, "All"]],
		searching: false,
		"pageLength": 20
	});
    }
</script>

<?php require_once('footer.php');?>