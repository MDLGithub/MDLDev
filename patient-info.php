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

$error = array();
$userID = $_SESSION['user']["id"];
$roleInfo = getRole($db, $userID);
$roleID = $roleInfo['Guid_role'];
$role = $roleInfo['role'];
$default_account = "";

$accessRole = getAccessRoleByKey('home');
$roleIDs = unserialize($accessRole['role_ids']);
$dataViewAccess = isUserHasAnyAccess($roleIDs, $roleID, 'view');

$isValid = TRUE;
if(isset($_GET['patient']) && $_GET['patient'] !="" ){
    $Guid_user = $_GET['patient'];

    $sqlQualify = "SELECT q.Guid_qualify,q.Guid_user,q.insurance,q.other_insurance,q.Date_created,
		    p.*, u.email
		    FROM tblqualify q
		    LEFT JOIN tblpatient p ON q.Guid_user = p.Guid_user
		    LEFT JOIN tbluser u ON q.Guid_user = u.Guid_user
		    WHERE q.Guid_user=:Guid_user";
    $qualifyResult = $db->row($sqlQualify, array('Guid_user'=>$Guid_user));

    $mdlInfoQ = "SELECT * FROM tbl_mdl_stats stats "
		. "LEFT JOIN tbl_mdl_status status ON stats.Guid_status = status.Guid_status "
		. "WHERE stats.Guid_user=:Guid_user";
    $mdlInfo = $db->row($mdlInfoQ, array('Guid_user'=>$Guid_user));

    $Guid_qualify = $qualifyResult['Guid_qualify'];

    $sqlSSQualify = "SELECT ssq.* FROM tbl_ss_qualify ssq WHERE ssq.Guid_qualify=:Guid_qualify  ORDER BY Date_created DESC";
    $ssQualifyResult = $db->query($sqlSSQualify, array('Guid_qualify'=>$Guid_qualify));

    $errorMsgMdlStats = "";
    if(isset($_POST['save'])){

	$numSize = strlen($_POST['mdl_number']);
	if(isset($_POST['mdl_number'])&&$_POST['mdl_number']!=""){
	    if(isset($_POST['mdl_number']) && $numSize != 7){
		$isValid = false;
		$errorMsgMdlStats .= "MDL# must contain 7 digits only <br/>";
	    }
	}
	if($isValid){
	    $userData = array();
	    if(isset($_POST['email']) && $_POST['email']!=''){
		$userData['email'] = $_POST['email'];
	    }

	    if(!empty($userData)){
		$userData['Date_modified'] = date('Y-m-d H:i:s');
		$whereUser = array('Guid_user'=>$_GET['patient']);
		//check if user exists
		$isUserExists=$db->row("SELECT * FROM tbluser WHERE Guid_user=:Guid_user", $whereUser);
		if($isUserExists){//update user
		    $updateUser = updateTable($db, 'tbluser', $userData, $whereUser);
		    saveUserRole($db, $_GET['patient'], '3');
		} else { //insert user
		    $userData['user_type'] = 'patient';
		    $userData['Date_created'] = date('Y-m-d H:i:s');
		    $inserUser = insertIntoTable($db, 'tbluser', $userData);
		    if($inserUser['insertID']){
			$inserRole = insertIntoTable($db, 'tbluserrole', array('Guid_user'=>$inserUser['insertID'], 'Guid_role'=>'3'));
		    }
		}
	    }

	    if(isset($_POST['dob']) && $_POST['dob']!=""){
		$dob= date('Y-m-d h:i:s', strtotime($_POST['dob']));
		updateTable($db, 'tblpatient', array('dob'=>$dob), array('Guid_user'=>$_GET['patient']));
	    }

	    //update mdl stats info
	    if(isset($_POST['mdl_number'])){
		$mdlStatsData['mdl_number']=$_POST['mdl_number'];

	    }
	    if(isset($_POST['notes'])){
		$mdlStatsData['notes']=$_POST['notes'];
	    }

	    if($mdlInfo){ //update existing
		$whereMdlSats = array('Guid_user'=>$_GET['patient']);
		if($mdlStatsData){
		    $updatePatient = updateTable($db, 'tbl_mdl_stats', $mdlStatsData, $whereMdlSats);
		}
	    }else{ //insert new row
		$mdlStatsData['Guid_user'] = $_GET['patient'];
		if($mdlStatsData){
		    $inserMdlStats = insertIntoTable($db, 'tbl_mdl_stats', $mdlStatsData);
		}
	    }

	    //add revenue data if exists
	    if(isset($_POST['revenueAdd']) && !empty($_POST['revenueAdd'])){
		$revData = $_POST['revenueAdd'];
		$size = count($revData['date_paid']);
		for($i=0; $i<$size; $i++){
		    $date_paid = ($revData['date_paid'][$i] != "")?date('Y-m-d h:i:s', strtotime($revData['date_paid'][$i])):"";
		    $dataRevenue = array(
			'Guid_user'=>$_GET['patient'],
			'date_paid'=>$date_paid,
			'payor'=>$revData['payor'][$i],
			'insurance'=>$revData['insurance'][$i],
			'patient'=>$revData['patient'][$i]
		    );
		    insertIntoTable($db, 'tbl_revenue', $dataRevenue);
		}
	    }
	    //update
	    if(isset($_POST['revenueEdit']) && !empty($_POST['revenueEdit'])){
		$revenues = $_POST['revenueEdit'];
		foreach ($revenues as $revenueKey => $revenueData){
		    $whereRevenue = array('Guid_revenue'=>$revenueKey);
		    $date_paid = ($revenueData['date_paid'] != "")?date('Y-m-d h:i:s', strtotime($revenueData['date_paid'])):"";
		    $dataRevenue = array(
			'date_paid'=>$date_paid,
			'payor'=>$revenueData['payor'],
			'insurance'=>$revenueData['insurance'],
			'patient'=>$revenueData['patient']
		    );
		    $updateReveue = updateTable($db, 'tbl_revenue', $dataRevenue, $whereRevenue);
		}
	    }

	    //add deductable log
	    if(isset($_POST['deductableAdd']) && !empty($_POST['deductableAdd'])){
		$dedData = $_POST['deductableAdd'];
		$size = count($dedData['date_checked']);
		for($i=0; $i<$size; $i++){
		    $date_checked = ($dedData['date_checked'][$i] != "")?date('Y-m-d h:i:s', strtotime($dedData['date_checked'][$i])):"";
		    $dataDeductable = array(
			'Guid_user'=>$_GET['patient'],
			'date_checked'=>$date_checked,
			'checked_by'=>$dedData['checked_by'][$i],
			'deductable'=>$dedData['deductable'][$i]
		    );
		    insertIntoTable($db, 'tbl_deductable_log', $dataDeductable);
		}
	    }
	    //update deductable log
	    if(isset($_POST['deductableEdit']) && !empty($_POST['deductableEdit'])){
		$deductables = $_POST['deductableEdit'];
		foreach ($deductables as $key => $val){
		    $whereDeductable = array('Guid_deductable'=>$key);
		    $date_checked = ($val['date_checked'] != "")?date('Y-m-d h:i:s', strtotime($val['date_checked'])):"";
		    $dataDeductable = array(
			'date_checked'=>$date_checked,
			'checked_by'=>$val['checked_by'],
			'deductable'=>$val['deductable']
		    );
		    $updateReveue = updateTable($db, 'tbl_deductable_log', $dataDeductable, $whereDeductable);
		}
	    }

	    $url=SITE_URL."/patient-info.php?patient=$Guid_user&u";
	    Leave($url);
	}
    }
    //delete deductible log row
    if(isset($_GET['delete-deductible']) && $_GET['delete-deductible']!=""){
	deleteByField($db,'tbl_deductable_log', 'Guid_deductable', $_GET['delete-deductible']);
	$url=SITE_URL."/patient-info.php?patient=$Guid_user";
	Leave($url);
    }
    //delete revenue row
    if(isset($_GET['delete-revenue']) && $_GET['delete-revenue']!=""){
	deleteByField($db,'tbl_revenue', 'Guid_revenue', $_GET['delete-revenue']);
	$url=SITE_URL."/patient-info.php?patient=$Guid_user";
	Leave($url);
    }

 } ?>
<?php require_once 'navbar.php'; ?>
<main class="full-width">
	<?php
	$thisMessage = "";
	if(isset($_GET['u']) || isset($_GET['i']) ){
		$thisMessage = "Changes have been saved";
	}
	?>
	<?php if($thisMessage != ""){ ?>
	<section id="msg_display" class="show success">
	    <h4><?php echo $thisMessage;?></h4>
	</section>
	<?php } ?>
    <div class="box full visible">
	<?php if($dataViewAccess){ ?>

	<section id="palette_top">
	    <h4>
		<ol class="breadcrumb">
		    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
		    <li class="active">Patient Info</li>
		</ol>
	    </h4>
	    <a href="<?php echo SITE_URL; ?>/patient-info.php?logout=1" name="log_out" class="button red back logout"></a>
	    <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit smaller_button"><strong>View Questionnaire</strong></a>
	</section>

	<div id="app_data" class="scroller">
	    <div class="row" id="patient-info-box">
		<div class="col-md-12">
		<?php if(isset($message)){ ?>
		<div class="error-text"><?php echo $message; ?></div>
		<?php } ?>
		<h2 class="text-center"><?php echo ucfirst($qualifyResult['firstname'])." ".ucfirst($qualifyResult['lastname']);?></h2>

		<form id="mdlInfoForm" action="" method="POST" >
			<input type="hidden" name="save" value="1"/>
			<div class="row">
			    <div class="col-md-6 pInfo">
				<p><label>Date of Birth:</label><input type="text" name="dob" class="datepicker" value="<?php echo ($qualifyResult['dob']!="")?date("n/j/Y", strtotime($qualifyResult['dob'])):""; ?>" autocomplete="off" /></p>
				<p><label>Email:</label> <input type="email" name="email" value="<?php echo $qualifyResult['email']; ?>" autocomplete="off"/> </p>
				<p><label>Registration Date:</label> <?php echo date("n/j/Y h:m A", strtotime($qualifyResult['Date_created'])); ?></p>
				<p><label>Insurance:</label> <?php echo $qualifyResult['insurance']; ?></p>
				<p><label>Other Insurance:</label> <?php echo $qualifyResult['other_insurance']; ?></p>
			    </div>
			    <div class="col-md-6 pB-30">
				<div class="row">
				    <div id="message" class="error-text">
				    <?php if($errorMsgMdlStats){ ?>
					<!--Form Error messages go here-->
					<?php echo $errorMsgMdlStats; ?>
				    <?php } ?>
				    </div>
				    <div id="specimenRadioBox" class="<?php echo ($qualifyResult['specimen_collected']=='Yes')?'hidden':"";?>" >
					<h5>Specimen collected?</h5>
					<div class="col-md-4 pL-0">
					    <div id="specimen">
						<input <?php echo ($qualifyResult['specimen_collected']=='Yes')?"checked":"";?> type="radio" name="specimen_collected" value="Yes" /> Yes &nbsp;&nbsp;
						<?php if($qualifyResult['specimen_collected'] !== 'Yes'){ ?>
						<input <?php echo ($qualifyResult['specimen_collected']=='No')?"checked":"";?> type="radio" name="specimen_collected" value="No" /> No
						<?php } ?>
					    </div>
					</div>
				    </div>
				    <div id="select-reson" class="col-md-8 <?php echo ( is_null($qualifyResult['specimen_collected']) || $qualifyResult['specimen_collected']=='Yes')?"hidden":"";?>">
					<div class="f2">
					    <!--<label class="dynamic" for="reason_not"><span>Reasons for not taking the test</span></label>-->
					    <?php $reasons = $db->selectAll('tbl_reasons');?>
					    <div class="group">
						<select id="reason" name="Guid_reason" class="no-selection">
						    <option value="">Reasons for not taking the test</option>
						    <?php foreach ($reasons as $k=>$v){?>
							<option <?php echo ($qualifyResult['Guid_reason']==$v['Guid_reason'])?"selected":""; ?> value="<?php echo $v['Guid_reason']; ?>"><?php echo $v['reason']; ?></option>
						    <?php } ?>
						</select>
						<p class="f_status">
						    <span class="status_icons"><strong></strong></span>
						</p>
					    </div>
					</div>
				    </div>

				    <div id="mdlInfoBox" class="pInfo <?php echo ($qualifyResult['specimen_collected']!='Yes')?'hidden':"";?>">
					<p>
					    <label>MDL#:</label>
					    <?php
					    $mdlNumber = isset($_POST['mdl_number'])?$_POST['mdl_number']:$mdlInfo['mdl_number'];
					    $mdlClass = (strlen($mdlNumber)!=0 && strlen($mdlNumber)<7)?' error error-border' : '';
					    ?>
					    <input type="number" autocomplete="off" class="mdlnumber <?php echo $mdlClass; ?>" name="mdl_number" value="<?php echo $mdlNumber; ?>" />
					</p>
					<p>
					    <label>Comment:</label>
					    <textarea autocomplete="off" name="notes"><?php echo isset($mdlInfo['notes'])?$mdlInfo['notes']:""; ?></textarea>
					</p>
				    </div>
				</div>
			    </div>
			</div>

		    <div class="row pT-30">
			<div id="questionaryInfo"  class="col-md-6">
			    <h5>
				Submission History:
				<!-- <a class="pull-right" id="add-deductable-log">
				    <span class="fas fa-plus-circle" aria-hidden="true"></span>  Add
				</a>-->
			    </h5>
			    <table class="table">
			    <thead>
				<th>Print</th>
				<th>Qualified</th>
				<th>Date Completed</th>
				<th>Clinical History</th>
			    </thead>
			    <tbody>
				<?php
				    foreach ($ssQualifyResult as $k=>$v){
					$Guid_qualify = $v['Guid_qualify'];
					$Date_created = $v['Date_created'];
					//$qFam = $db->query("SELECT * FROM `tblqualifyfam` WHERE `Guid_qualify`=:Guid_qualify AND `Date_created`=:Date_created", array('Guid_qualify'=>$Guid_qualify, 'Date_created'=>$Date_created));
					//$queryPers = "SELECT * FROM `tbl_ss_qualifypers` WHERE `Guid_qualify`=:Guid_qualify AND `Date_created`=:Date_created";
					//$qPers = $db->query($queryPers, array('Guid_qualify'=>$Guid_qualify, 'Date_created'=>$Date_created));
					$qAns = $db->query("SELECT * FROM `tbl_ss_qualifyans` WHERE `Guid_qualify`=:Guid_qualify AND `Date_created`=:Date_created", array('Guid_qualify'=>$Guid_qualify, 'Date_created'=>$Date_created));
					$qualifyedClass = "";
					if($v['qualified'] == 'No'){
					    $qualifyedClass = "mn no";
					} elseif ($v['qualified'] == 'Yes') {
					    $qualifyedClass = "mn yes";
					}

				    ?>
				    <tr>
					<td>
					    <?php if($v['qualified'] == 'Unknown'){ ?>
						<span class="not-printable" ></span>
					    <?php } else { ?>
						<button class="print report" data-selected_date="<?php echo $v['Date_created']; ?>" data-selected_questionnaire="<?php echo $v['Guid_qualify']; ?>" ></button>
					    <?php } ?>
					</td>
					<td class="<?php echo $qualifyedClass;?>"><?php echo $v['qualified']; ?></td>
					<td><?php echo date("n/j/Y h:m:s A", strtotime($v['Date_created'])); ?></td>
					<td>
					    <p>
						<?php
						$personal = "<label>Personal: </label> ";
						$family = "<label>Family: </label> ";
						if(!empty($qAns)){
						    $ansPersonal = "";
						    $ansFam = "";
						    foreach ($qAns as $k=>$v) {
							$ansPersonalType =  $v['cancer_personal'];
							if(strpos(trim($ansPersonalType), ' ') == false){
							    $ansPersonalType .=  " Cancer";
							}
							$ansPersonal .= $ansPersonalType;
							if($v['age_personal'] && $v['age_personal']!=""){
							    $ansPersonal .= " (Age ". $v['age_personal']."); ";
							}
							if($v['age_personal']==""&&$v['age_personal']==""){
							    $ansPersonal = "No Cancer History";
							}


							$ansFamType =  $v['cancer_type'];
							if(strpos(trim($ansFamType), ' ') == false){
							    $ansFamType .=  " Cancer";
							}
							$ansFam .= $v['relative'].", ".$ansFamType;
							if($v['age_relative'] && $v['age_relative']!=""){
							    $ansFam .= " (Age ". $v['age_relative']."); ";
							}
							if($v['cancer_type']==""&&$v['relative']==""){
							    $ansFam = "No Cancer History";
							}

						    }
						    $ansPersonal = rtrim($ansPersonal,'; ');
						    $ansFam = rtrim($ansFam,'; ');
						    echo "<p>".$personal.$ansPersonal."</p>";
						    echo "<p>".$family.$ansFam."</p>";
						} else {
						    echo "<p>".$personal." No Cancer History</p>";
						    echo "<p>".$family." No Cancer History</p>";
						}
						?>

					    </p>

					</td>
				    </tr>
				<?php } ?>
			    </tbody>
			</table>
			</div>
			<div id="statusLogs"  class="col-md-6">
			    <h5>
				Test Status Change Log:
				<a title="Add New Test Status Log" class="pull-right" href="<?php echo SITE_URL."/patient-info.php?patient=".$Guid_user."&status_log=1";?>">
				    <span class="fas fa-plus-circle" aria-hidden="true"></span>  Add
				</a>
			    </h5>
			    <table class="table">
			    <thead>
				<th>Date</th>
				<th>Status
				    <a title="Add New Status"  href="<?php echo SITE_URL .'/patient-info.php?patient='.$Guid_user.'&manage_status=add'; ?>" >
					<span class="fas fa-plus-circle" aria-hidden="true"></span>
				    </a>
				</th>
			    </thead>
			    <tbody>
				<?php
				$patientID=$_GET['patient'];
				$qStatusLog = 'SELECT sl.status_ids, sl.date '
					    . 'FROM tbl_mdl_status_log sl '
					    . 'LEFT JOIN tblpatient p ON sl.Guid_patient=p.Guid_user '
					    . 'WHERE sl.Guid_patient='.$patientID.' '
					    . 'Order BY date DESC';
				$ststusLogs = $db->query($qStatusLog);
				foreach ($ststusLogs as $k=>$v){
				?>
				    <tr>
					<td><?php echo date("n/j/Y", strtotime($v['date'])); ?></td>
					<td><?php echo get_status_names( $db, unserialize($v['status_ids']) ); ?></td>
				    </tr>
				<?php } ?>
			    </tbody>
			</table>
			</div>
		    </div>
		    <div id="pLogs" class="row <?php echo (!$qualifyResult['specimen_collected'] || $qualifyResult['specimen_collected']=='No')?"hidden":"";?>">
			<div id="deductable-log" class="col-md-6">
			    <?php
				$whereUser = array('Guid_user'=>$_GET['patient']);
				$deductableLogs = $db->query('SELECT * FROM tbl_deductable_log WHERE Guid_user=:Guid_user', $whereUser);
				$revenues = $db->query('SELECT * FROM tbl_revenue WHERE Guid_user=:Guid_user', $whereUser);
			    ?>
			    <h5>
				Deductible Log:
				<a class="pull-right" id="add-deductable-log">
				    <span class="fas fa-plus-circle" aria-hidden="true"></span>  Add
				</a>
			    </h5>
			    <div class="deductable-form">

			    </div>
			    <div id="deductable-table-form" >
				<table id="deductable-table" class="table">
				    <thead>
					<tr>
					    <th>Date Checked</th>
					    <th>Checked By</th>
					    <th>Deductible $</th>
					    <th class="text-center actions">Action</th>
					</tr>
				    </thead>
				    <tbody>
					<?php
					$dedSum = 0;
					foreach ($deductableLogs as $k=>$v) {
					    if($v['deductable']!=""){
						$dedSum += $v['deductable'];
					    }
					?>
					<tr id="<?php echo $v['Guid_deductable']; ?>">
					    <td><span class="editable_date_checked"><?php echo (!preg_match("/0{4}/" , $v['date_checked'])) ? date('n/j/Y', strtotime($v['date_checked'])) : ""; ?></span></td>
					    <td><span class="editable_checked_by"><?php echo $v['checked_by']; ?></span></td>
					    <td>$<span class="editable_deductable"><?php echo formatMoney($v['deductable']); ?></span></td>
					    <td class="text-center">
						<div class="action-btns">
						<a data-id="<?php echo $v['Guid_deductable']; ?>" class="edit_deductable">
						    <span class="fas fa-pencil-alt"></span>
						</a>
						<a href="<?php echo SITE_URL."/patient-info.php?patient=".$_GET['patient'].'&delete-deductible='.$v['Guid_deductable']; ?>" onclick="javascript:confirmationDeleteDeductible($(this));return false;" class="color-red">
						    <span class="far fa-trash-alt"></span>
						</a>
						</div>
					    </td>
					</tr>
					<?php } ?>
					<tr class="priceSum">
					<?php if(count($deductableLogs) > 1){ ?>
					    <td>&nbsp;&nbsp;</td>
					    <td class="text-right">Total: &nbsp;&nbsp;</td>
					    <td class="strong">$<span><?php echo formatMoney($dedSum); ?></span></td>
					    <td>&nbsp;&nbsp;</td>
					<?php } ?>
					</tr>
				    </tbody>
				</table>
			    </div>
			</div>
			<div id="revenue" class="col-md-6">
			    <h5>
				Revenue:
				<a class="pull-right" id="add-revenue">
				    <span class="fas fa-plus-circle" aria-hidden="true"></span>  Add
				</a>
			    </h5>
			    <div class="revenue-form"></div>

			    <table id="revenue-table" class="table">
				<thead>
				    <tr>
					<th>Date Paid</th>
					<th>Payor</th>
					<th>Insurance $</th>
					<th>Patient $</th>
					<th class="text-center actions">Action</th>
				    </tr>
				</thead>
				<tbody>
				    <?php
				    $revSum = 0;
				    foreach ($revenues as $k=>$v) {
					$revSum += $v['insurance'];
					$revSum += $v['patient'];
				    ?>
				    <tr id="<?php echo $v['Guid_revenue']; ?>">
					<td><span class="editable_date_payd"><?php echo (!preg_match("/0{4}/" , $v['date_paid'])) ? date('n/j/Y', strtotime($v['date_paid'])) : ""; ?></span></td>
					<td><span class="editable_payor"><?php echo $v['payor']; ?></span></td>
					<td>$<span class="editable_insurance"><?php echo formatMoney($v['insurance']); ?></span></td>
					<td>$<span class="editable_patient"><?php echo formatMoney($v['patient']); ?></span></td>
					<td class="text-center">
					    <div class="action-btns">
					    <a data-id="<?php echo $v['Guid_revenue']; ?>" class="edit_reveue">
						<span class="fas fa-pencil-alt"></span>
					    </a>
					    <a href="<?php echo SITE_URL."/patient-info.php?patient=".$_GET['patient'].'&delete-revenue='.$v['Guid_revenue']; ?>" onclick="javascript:confirmationDeleteRevenue($(this));return false;" class="color-red">
						<span class="far fa-trash-alt"></span>
					    </a>
					    </div>
					</td>
				    </tr>

				    <?php } ?>
				    <tr class="priceSum">
				    <?php if(count($revenues) > 1){ ?>
					<td>&nbsp;&nbsp;</td>
					<td>&nbsp;&nbsp;</td>
					<td class="text-right">Total: &nbsp;&nbsp;</td>
					<td class="strong">$<span class=""><?php echo formatMoney($revSum); ?></span></td>
					<td>&nbsp;&nbsp;</td>
				    <?php } ?>
				    </tr>
				</tbody>
			    </table>
			</div>
		    </div>
		    <div class="row actionButtons pB-30">
			<div class="col-md-12">
			    <button id="save-patient-info" name="save" type="submit" class="button btn-inline">Save</button>
			    <button name="print" type="submit" class="button btn-inline">Print</button>
			    <button name="email" type="submit" class="button btn-inline">Email</button>
			 </div>
		    </div>
		</form>
	    </div>
	</div>

	</div>
	<?php } else { ?>
	    <p>Sorry! You don't have access to this page content. </p>
	<?php } ?>
    </div>
    <div id="admin_print"></div>
</main>



<?php
    if(isset($_POST['manage_status'])){
	$statusData=array('parent_id'=>$_POST['parent_id'], 'status'=>$_POST['status'], 'order_by'=>$_POST['order_by']);
	$insertStatus = insertIntoTable($db, 'tbl_mdl_status', $statusData);
	if($insertStatus['insertID']!=""){
	    $message = "New Status Inserted.";
	    //Leave(SITE_URL."/patient-info.php?patient=".$_GET['patient']);
	}
    }
?>
<?php if(isset($_GET['manage_status'])){ ?>
<div id="manage-status-modal" class="modalBlock ">
    <div class="contentBlock">
	<a class="close" href="<?php echo SITE_URL."/patient-info.php?patient=".$Guid_user; ?>">X</a>

	<h5 class="title">
<!--            <a class="" id="open-new-status-form">
		<span class="fas fa-plus-circle" aria-hidden="true"></span>  Add New
	    </a>&nbsp;&nbsp;
	    <a class="" id="opent-status-list">
		<span class="fas fa-list " aria-hidden="true"></span> List Statuses
	    </a>-->
	    Add New Status
	</h5>
	<div class="content">
	    <!--<div class="status-list">list here...</div>-->
	    <div class="add-status-form">
		<form action="" method="POST">
		<h4 class="text-center"></h4>
		<?php if(isset($message)){ ?>
		    <div class="text-center success-text"><?php echo $message; ?></div>
		<?php } ?>
		<div class="f2 ">
		    <label class="dynamic" for="status"><span>Status Name</span></label>
		    <div class="group">
			<input required autocomplete="off" id="status" name="status" type="text" value="" placeholder="Status Name">
			<p class="f_status">
			    <span class="status_icons"><strong></strong></span>
			</p>
		    </div>
		</div>
		<div class="f2  ">
		    <label class="dynamic" for="parent"><span>Status Parent</span></label>
		    <div class="group">

			    <?php
				echo get_nested_status_dropdown($db);
			    ?>
			<p class="f_status">
			    <span class="status_icons"><strong></strong></span>
			</p>
		    </div>
		</div>
		<div class="f2 ">
		    <label class="dynamic" for="order_by"><span>Order By</span></label>
		    <div class="group">
			<input type="number" min="0" step="1" autocomplete="off" id="order_by" name="order_by"  value="" placeholder="Order By">
			<p class="f_status">
			    <span class="status_icons"><strong></strong></span>
			</p>
		    </div>
		</div>

		 <div class="text-right pT-10">
		    <button class="button btn-inline" name="manage_status" type="submit" >Save</button>
		    <!--<button onclick="goBack();" type="button" class="btn-inline btn-cancel">Cancel</button>-->
		</div>
		</form>
	    </div>
	</div>
    </div>
</div>
<?php } ?>

<?php

    if(isset($_POST['add_status_log'])){
	$statusIDs = serialize($_POST['status']);
	$statusLogData=array(
		'status_ids'=>$statusIDs,
		'Guid_patient'=>$Guid_user,

	    );

	 $statusLogData = array(
			'status_ids' => $statusIDs,
			'Guid_patient' => $_GET['patient'],
			'recorded_by' => $_SESSION['user']['id'],
			//'mdl_number' => $_POST['mdl_number'],
			//'comment'=>$_POST['comment'],
			'date' => date('Y-m-d h:i:s', strtotime($_POST['date']))
		    );

	$insertStatusLog = insertIntoTable($db, 'tbl_mdl_status_log', $statusLogData);
	if($insertStatusLog['insertID']!=""){
	    $message = "New Status Inserted.";
	    Leave(SITE_URL."/patient-info.php?patient=".$_GET['patient']);
	}
    }
?>
<?php if(isset($_GET['status_log'])){ ?>
<div id="manage-status-modal" class="modalBlock ">
    <div class="contentBlock">
	<a class="close" href="<?php echo SITE_URL."/patient-info.php?patient=".$Guid_user; ?>">X</a>

	<h5 class="title">
	    Add Status Log
	</h5>
	<div class="content">
	    <!--<div class="status-list">list here...</div>-->
	    <div class="add-status-form">
		<form action="" method="POST">
		<h4 class="text-center"></h4>
		<?php if(isset($message)){ ?>
		    <div class="text-center success-text"><?php echo $message; ?></div>
		<?php } ?>
		<div class="">
			<input required class="datepicker" autocomplete="off" id="status" name="date" type="text" value="" placeholder="Date">
		</div>
		<div id="status-dropdowns-box">
		    <?php echo get_status_dropdown($db, $parent_id='0'); ?>
		</div>


		 <div class="text-right pT-10">
		    <button class="button btn-inline" name="add_status_log" type="submit" >Save</button>
		    <!--<button onclick="goBack();" type="button" class="btn-inline btn-cancel">Cancel</button>-->
		</div>
		</form>
	    </div>
	</div>
    </div>
</div>
<?php } ?>


<?php require_once 'scripts.php'; ?>
<?php require_once 'footer.php'; ?>