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
$roleIDs = unserialize($accessRole['value']);
$dataViewAccess = isUserHasAnyAccess($roleIDs, $roleID, 'view');

if (isset($_POST['search']) && (strlen($_POST['from_date']) || strlen($_POST['to_date']))) {
    verify_input($error);
}
?>
<?php require_once 'navbar.php'; ?>
<!--SEARCH FORM BLOCK Start-->
<aside id="action_palette" class="" >
    <div class="box full">
	<h4 class="box_top">Filters</h4>
	<?php if($dataViewAccess) { ?>
	<div class="boxtent scroller ">
	    <form id="filter_form" action="" method="post">
		<?php
		$date_error = "";

		if (isset($_POST['search'])) {
		    if (isset($error['from_date'])) {
			$date_error = " error";
		    } elseif (strlen($_POST['from_date'])) {
			$date_error = " valid";
		    }
		}
		?>
		<?php if(isFieldVisibleByRole($roleIDs['from_date']['view'], $roleID)) {?>
		<div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['from_date'])) && (strlen($_POST['from_date']))) ? " show-label" : ""; ?><?php echo $date_error; ?>">
		    <label class="dynamic" for="from_date"><span>From Date</span></label>

		    <div class="group">
			<input readonly class="datepicker" type="text" id="from_date" name="from_date" value="<?php echo ((!isset($_POST['clear'])) && isset($_POST['from_date']) && strlen($_POST['from_date'])) ? $_POST['from_date'] : ""; ?>" placeholder="From Date">

			<p class="f_status">
			    <span class="status_icons"><strong></strong></span>
			</p>
		    </div>
		</div>
		<?php } ?>
		<?php
		$date_error = "";

		if (isset($_POST['search'])) {
		    if (isset($error['to_date'])) {
			$date_error = " error";
		    } elseif (strlen($_POST['to_date'])) {
			$date_error = " valid";
		    }
		}
		?>
		<?php if(isFieldVisibleByRole($roleIDs['to_date']['view'], $roleID)) {?>
		    <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['to_date'])) && (strlen($_POST['to_date']))) ? " show-label" : ""; ?><?php echo $date_error; ?>">
			<label class="dynamic" for="to_date"><span>To Date</span></label>

			<div class="group">
			    <input readonly class="datepicker" type="text" id="to_date" name="to_date" value="<?php echo ((!isset($_POST['clear'])) && isset($_POST['to_date']) && strlen($_POST['to_date'])) ? $_POST['to_date'] : ""; ?>" placeholder="To Date" max="<?php echo date('Y-m-d'); ?>">

			    <p class="f_status">
				<span class="status_icons"><strong></strong></span>
			    </p>
			</div>
		    </div>
		<?php } ?>
		<?php if(isFieldVisibleByRole($roleIDs['meets_mn']['view'], $roleID)) {?>
		    <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['meets_mn'])) && (strlen($_POST['meets_mn']))) ? " show-label valid" : ""; ?>">
			<label class="dynamic" for="meets_mn"><span>Medical Necessity</span></label>

			<div class="group">
			    <select id="meets_mn" name="meets_mn" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['meets_mn'])) && (strlen($_POST['meets_mn']))) ? "" : "no-selection"; ?>">
				<option value="">Medical Necessity</option>
				<option value="yes"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['meets_mn']) && ($_POST['meets_mn'] == "yes")) ? " selected" : ""); ?>>Yes</option>
				<option value="no"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['meets_mn']) && ($_POST['meets_mn'] == "no")) ? " selected" : ""); ?>>No</option>
				<option value="unknown"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['meets_mn']) && ($_POST['meets_mn'] == "unknown")) ? " selected" : ""); ?>>Unknown</option>
				<option value="incomplete"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['meets_mn']) && ($_POST['meets_mn'] == "incomplete")) ? " selected" : ""); ?>>Incomplete</option>
			    </select>

			    <p class="f_status">
				<span class="status_icons"><strong></strong></span>
			    </p>
			</div>
		    </div>
		<?php } ?>
		<?php if(isFieldVisibleByRole($roleIDs['first_name']['view'], $roleID)) {?>
		    <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['first_name'])) && (strlen(trim($_POST['first_name'])))) ? " show-label valid" : ""; ?>">
			<label class="dynamic" for="first_name"><span>First Name</span></label>

			<div class="group">
			    <input id="first_name" name="first_name" type="text" value="<?php echo ((!isset($_POST['clear'])) && isset($_POST['first_name']) && strlen(trim($_POST['first_name']))) ? trim($_POST['first_name']) : ""; ?>" placeholder="First Name">

			    <p class="f_status">
				<span class="status_icons"><strong></strong></span>
			    </p>
			</div>
		    </div>
		<?php } ?>
		<?php if(isFieldVisibleByRole($roleIDs['last_name']['view'], $roleID)) {?>
		<div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['last_name'])) && (strlen(trim($_POST['last_name'])))) ? " show-label valid" : ""; ?>">
		    <label class="dynamic" for="last_name"><span>Last Name</span></label>

		    <div class="group">
			<input id="last_name" name="last_name" type="text" value="<?php echo ((!isset($_POST['clear'])) && isset($_POST['last_name']) && strlen(trim($_POST['last_name']))) ? trim($_POST['last_name']) : ""; ?>" placeholder="Last Name">

			<p class="f_status">
			    <span class="status_icons"><strong></strong></span>
			</p>
		    </div>
		</div>
		<?php } ?>
		<?php if(isFieldVisibleByRole($roleIDs['insurance']['view'], $roleID)) {?>
		    <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['insurance'])) && (strlen($_POST['insurance']))) ? " show-label valid" : ""; ?>">
			<label class="dynamic" for="insurance"><span>Insurance</span></label>

			<div class="group">
			    <select id="insurance" name="insurance" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['insurance'])) && (strlen($_POST['insurance']))) ? "" : "no-selection"; ?>">
				<option value="">Insurance</option>
				<option value="aetna"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['insurance']) && ($_POST['insurance'] == "aetna")) ? " selected" : ""); ?>>Aetna</option>
				<option value="medicare"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['insurance']) && ($_POST['insurance'] == "medicare")) ? " selected" : ""); ?>>Medicare</option>
				<option value="other"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['insurance']) && ($_POST['insurance'] == "other")) ? " selected" : ""); ?>>Other</option>
				<option value="none"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['insurance']) && ($_POST['insurance'] == "none")) ? " selected" : ""); ?>>None</option>
			    </select>

			    <p class="f_status">
				<span class="status_icons"><strong></strong></span>
			    </p>
			</div>
		    </div>
		<?php }?>
		<?php
		if (($role == "Sales Rep") || ((isset($_POST['salesrep']) && strlen($_POST['salesrep']) && (!isset($_POST['clear']))))) {
		    $query = "SELECT
		    tblaccount.*
		    FROM tblsalesrep
		    LEFT JOIN `tblaccountrep` ON  tblsalesrep.Guid_salesrep = tblaccountrep.Guid_salesrep
		    LEFT JOIN `tblaccount` ON tblaccountrep.Guid_account = tblaccount.Guid_account
		    WHERE tblsalesrep.Guid_user=";

		    if (isset($_POST['salesrep']) && strlen($_POST['salesrep'])) {
			$query .= $_POST['salesrep'];
		    } else {
			$query .= $_SESSION['user']['id'];
		    }
		} else {
		    $query = "SELECT * FROM tblaccount";
		}

		$query .= " ORDER BY account";

		$accounts = $db->query($query);
		?>

		<?php if(isFieldVisibleByRole($roleIDs['account']['view'], $roleID)) {?>
		    <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['account'])) && (strlen(trim($_POST['account'])))) ? " show-label valid" : ""; ?>">
			<label class="dynamic" for="account"><span>Account</span></label>

			<div class="group">
			    <select id="account" name="account" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['account'])) && (strlen($_POST['account']))) ? "" : "no-selection"; ?>">
				<option value="">Account</option>
				<?php
				foreach ($accounts as $account) {
				    $default_account .= $account['account'] . ",";
				    ?>
				    <option value="<?php echo $account['account']; ?>"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['account']) && ($_POST['account'] == $account['account'])) ? " selected" : ""); ?>><?php echo $account['account'] . " - " . ucwords(strtolower($account['name'])); ?></option>
				    <?php
				}

				$default_account = rtrim($default_account, ',');
				?>
			    </select>

			    <p class="f_status">
				<span class="status_icons"><strong></strong></span>
			    </p>
			</div>
		    </div>
		<?php } ?>

		<?php if(isFieldVisibleByRole($roleIDs['provider']['view'], $roleID)) {?>
		    <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['provider'])) && (strlen($_POST['provider']))) ? " show-label valid" : ""; ?>">
			<label class="dynamic" for="provider"><span>Provider</span></label>

			<div class="group">
			    <select id="provider" name="provider" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['provider'])) && (strlen($_POST['provider']))) ? "" : "no-selection"; ?>">
				<option value="">Provider</option>
				<?php
				$default_account = ltrim($default_account, ',');
				if($default_account){
				$query = "SELECT * FROM tblprovider WHERE account_id IN (" . $default_account . ") GROUP BY first_name";

				$providers = $db->query($query);
				foreach ($providers as $provider) {
				    ?>
				    <option value="<?php echo $provider['Guid_provider']; ?>"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['provider']) && ($_POST['provider'] == $provider['Guid_provider'])) ? " selected" : ""); ?>><?php echo $provider['first_name']." ".$provider['last_name']; ?></option>
				    <?php
				}
				}
				?>
			    </select>

			    <p class="f_status">
				<span class="status_icons"><strong></strong></span>
			    </p>
			</div>
		    </div>
		<?php } ?>
		<?php if(isFieldVisibleByRole($roleIDs['location']['view'], $roleID)) {?>
		    <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['location'])) && (strlen($_POST['location']))) ? " show-label valid" : ""; ?>">
			<label class="dynamic" for="location"><span>Location</span></label>

			<div class="group">
			    <select id="location" name="location" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['location'])) && (strlen($_POST['location']))) ? "" : "no-selection"; ?>">
				<option value="">Location</option>
				<?php
				$locations = $db->query("SELECT description FROM tblsource ORDER BY description ASC");

				foreach ($locations as $location) {
				    ?>
				    <option value="<?php echo $location['description']; ?>"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['location']) && ($_POST['location'] == $location['description'])) ? " selected" : ""); ?>><?php echo $location['description']; ?></option>
				    <?php
				}
				?>
			    </select>

			    <p class="f_status">
				<span class="status_icons"><strong></strong></span>
			    </p>
			</div>
		    </div>
		<?php  } ?>
		<?php if(isFieldVisibleByRole($roleIDs['salesrep']['view'], $roleID)) {?>
		    <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['salesrep'])) && (strlen($_POST['salesrep']))) ? " show-label valid" : ""; ?>">
			<label class="dynamic" for="salesrep"><span>Genetic Consultant</span></label>

			<div class="group">
			    <select id="salesrep" name="Guid_salesrep" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['Guid_salesrep'])) && (strlen($_POST['Guid_salesrep']))) ? "" : "no-selection"; ?>">
				<option value="">Genetic Consultant</option>
				<?php
				$salesreps = $db->query("SELECT * FROM tblsalesrep GROUP BY first_name");

				foreach ($salesreps as $salesrep) {
				    ?>
				    <option value="<?php echo $salesrep['Guid_salesrep']; ?>"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['Guid_salesrep']) && ($_POST['Guid_salesrep'] == $salesrep['Guid_salesrep'])) ? " selected" : ""); ?>><?php echo $salesrep['first_name']." ".$salesrep['last_name']; ?></option>
				    <?php
				}
				?>
			    </select>

			    <p class="f_status">
				<span class="status_icons"><strong></strong></span>
			    </p>
			</div>
		    </div>
		<?php }   ?>
		<?php if($role != 'Physician') { ?>
		<div>
		    <input id="show-tests" name="mark_test" value="1" type="checkbox" <?php echo ((!isset($_POST['clear'])) && (isset($_POST['mark_test']) && ($_POST['mark_test'] == 1)) ? " checked" : ""); ?> />
		    <label for="show-tests">Show Tests</label>
		</div>
		<?php } ?>



		<button id="filter" value="1" name="search" type="submit" class="button filter half"><strong>Search</strong></button>
		<a href="<?php ?>" class="button cancel half"><strong>Clear</strong></a>
	    </form>
	    <!--********************   SEARCH BY PALETTE END    ******************** -->

	</div>
	<?php } ?>
    </div>
</aside>
<!--SEARCH FORM BLOCK END-->

<?php

//updating mark as test users
if(isset($_POST['mark_as_test'])){
    $markedUsers =$_POST['markedRow']['user'];
    if($markedUsers){
	foreach ($markedUsers as $userID=>$v){
	    updateTable($db,'tbluser', array('marked_test'=>'1'), array('Guid_user'=>$userID));
	}
    }
}

$sqlTbl = "SELECT q.*, p.*, "
	. "a.name as account_name, "
	. "CONCAT (srep.first_name, ' ', srep.last_name) AS salesrep_name, srep.Guid_salesrep, "
	. "u.email, u.marked_test,  u.Guid_role, "
	. "q.Date_created AS date FROM tbl_ss_qualify q "
	. "LEFT JOIN tblaccount a ON q.account_number = a.account "
	. "LEFT JOIN tblaccountrep arep ON arep.Guid_account = a.Guid_account "
	. "LEFT JOIN tblsalesrep srep ON srep.Guid_salesrep = arep.Guid_salesrep "
	. "LEFT JOIN tblpatient p ON q.Guid_user = p.Guid_user "
	. "LEFT JOIN tbluser u ON q.Guid_user = u.Guid_user";
$where = "";
$whereTest = (strlen($where)) ? " AND " : " WHERE ";
$whereTest .= " u.marked_test='0' ";
$whereIncomplete  = "";


//if ((!count($error)) && (!isset($_POST['clear'])) && (!empty($_POST))) {
if ((!isset($_POST['clear'])) && (!empty($_POST['search']))) {

    $where = "";  $whereTest = "";  $whereIncomplete  = "";
    //Marked as test
    if (isset($_POST['mark_test']) && strlen($_POST['mark_test'])) {
	$whereTest = (strlen($where)) ? " AND " : " WHERE ";
	$whereTest .= " u.marked_test = '1'";
    } else {
	$whereTest = (strlen($where)) ? " AND " : " WHERE ";
	$whereTest .= " u.marked_test = '0'";
    }

    //From date - To Date range
    if (strlen($_POST['from_date']) && strlen($_POST['to_date'])) {
	if ($_POST['from_date'] == $_POST['to_date']) {
	    $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
	    $where .= " q.Date_created LIKE '%" . date("Y-m-d", strtotime($_POST['from_date'])) . "%'";
	} else {
	    $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
	    $where .= " q.Date_created BETWEEN '" . date("Y-m-d", strtotime($_POST['from_date'])) . "' AND '" . date("Y-m-d", strtotime($_POST['to_date'])) . "'";
	}
    }
    //Medical Necessity
    if (isset($_POST['meets_mn']) && strlen($_POST['meets_mn'])) {
	$whereTest = "";
	if($_POST['meets_mn']=='incomplete'){
	    $sqlTbl  = "SELECT q.*,p.*, a.name as account_name,
			CONCAT (srep.first_name, ' ', srep.last_name) AS salesrep_name, srep.Guid_salesrep,
			u.email, u.marked_test, u.Guid_role, q.Date_created AS `date`,
			'1' AS incomplete FROM tblqualify q
			LEFT JOIN tblaccount a ON q.account_number = a.account
			LEFT JOIN tblaccountrep arep ON arep.Guid_account = a.Guid_account
			LEFT JOIN tblsalesrep srep ON srep.Guid_salesrep = arep.Guid_salesrep
			LEFT JOIN tblpatient p ON q.Guid_user = p.Guid_user
			LEFT JOIN tbluser u ON p.Guid_user = u.Guid_user";
	    $where = " WHERE NOT EXISTS(SELECT * FROM tbl_ss_qualify qs WHERE q.Guid_qualify=qs.Guid_qualify) AND u.marked_test='0'";

	}else{
	    $where = (strlen($where)) ? " AND " : " WHERE ";
	    $where .= " q.qualified = '" . $_POST['meets_mn'] . "'";
	}
    }
    //First Name
    if (isset($_POST['first_name']) && strlen(trim($_POST['first_name']))) {
	$where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
	$where .= " p.firstname = '" . $_POST['first_name'] . "'";
    }
    //Last Name
    if (isset($_POST['last_name']) && strlen(trim($_POST['last_name']))) {
	$where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
	$where .= " p.lastname = '" . $_POST['last_name'] . "'";
    }
    //Insurance
    if (isset($_POST['insurance']) && strlen($_POST['insurance'])) {
	$where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
	$where .= " q.insurance = '" . $_POST['insurance'] . "'";
    }
    //Account
    if (isset($_POST['account']) && strlen($_POST['account'])) {
	$where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
	$where .= " q.account_number = '" . $_POST['account'] . "'";
    } elseif (isset($_POST['salesrep']) && strlen($_POST['salesrep'])) {
	$where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
	$where .= " q.account_number IN (" . $default_account . ")";
    }
    //Provider
    if (isset($_POST['provider']) && strlen($_POST['provider'])) {
	$where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
	$where .= " q.provider_id = '" . $_POST['provider'] . "'";
    }
    //Location
    if (isset($_POST['location']) && strlen($_POST['location'])) {
	$where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
	$where .= " q.source = '" . $_POST['location'] . "'";
    }
    //Genetic Consultant
    if (isset($_POST['Guid_salesrep']) && strlen($_POST['Guid_salesrep'])) {
	$where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
	$where .= " srep.Guid_salesrep = '" . $_POST['Guid_salesrep'] . "'";
    }


    $postAccount = isset($_POST['account']) ? $_POST['account'] : "";
    if ((isset($role) && $role == "Sales Rep") && (!strlen($postAccount))) {
	$where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
	$where .= " q.account_number IN (" . $default_account . ")";
    }
}

if($role == 'Physician'){
    $physicianInfo = $db->row('SELECT Guid_provider FROM tblprovider WHERE Guid_user='.$userID);
    $physicianID = $physicianInfo['Guid_provider'];
    $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
    $where .= " q.provider_id='".$physicianID."'";
}

$where  .= " AND CONCAT(p.firstname, ' ', p.lastname) NOT LIKE '%test%' "
	. "AND CONCAT(p.firstname, ' ', p.lastname) NOT LIKE '%John Smith%' "
	. "AND CONCAT(p.firstname, ' ', p.lastname) NOT LIKE '%John Doe%' "
	. "AND CONCAT(p.firstname, ' ', p.lastname) NOT LIKE '%Jane Doe%'";
if( !(isset($_POST['meets_mn']) && $_POST['meets_mn']=='incomplete')){
    $where .= "AND q.`Date_created` = (SELECT MAX(Date_created) FROM tbl_ss_qualify AS m2 WHERE q.Guid_qualify = m2.Guid_qualify)";
}


if($role == "Sales Rep"){


}

$sqlTbl .= $whereTest;
$sqlTbl .= $whereIncomplete;
$sqlTbl .= $where;

//$sqlTbl .= " GROUP BY p.Guid_user";
$sqlTbl .= " ORDER BY date DESC";


$qualify_requests = $db->query($sqlTbl);

$num_estimates = $qualify_requests;


?>

<main class="">
    <div class="box full visible">
	<?php if($dataViewAccess){ ?>
	<section id="palette_top" class="shorter_palette_top">
	    <h4><?php echo count($num_estimates) . " Results"; ?></h4>
	    <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
	    <a href="<?php echo SITE_URL; ?>/dashboard2.php" class="button homeIcon"></a>
	    <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit smaller_button"><strong>View Questionnaire</strong></a>
	</section>

	<div id="app_data" class="home_scroller">

	    <div class="row">
		<?php
		    if($role=='Physician'){
		      $salesRep = getProviderSalesRep($db, $_SESSION['user']['id']);
		    ?>
		<div class="col-md-6 pull-right">
		    <?php
			$img = ($salesRep['photo_filename']!="")? $salesRep['photo_filename']: "";
			$image = ($img!="") ? SITE_URL.'/images/users/'.$img : "assets/images/default.png";
			$address = "";
		    ?>
		    <div class="row" id="salesrepInfo1">
			<div class="col-md-2 text-center">
			    <img width="40" src="<?php echo $image; ?>" />
			</div>
			<div class="col-md-5">
			    <p>
				<?php if($salesRep['title']) { echo " ".$salesRep['title']; } ?>
			    </p>
			    <p>
				<?php if($salesRep['first_name']) { echo $salesRep['first_name']; } ?>
				<?php if($salesRep['last_name']) { echo " ".$salesRep['last_name']; } ?>
			    </p>
			</div>
			<div  class="col-md-5">
			    <p>
			    <?php if($salesRep['email']) { ?>
				<i class="fas fa-envelope"></i>
				<a href="mailto:<?php echo $salesRep['email'];?>"><?php echo $salesRep['email'];?></a>
				<?php }?>
			    </p>
			    <p>
				<?php if($salesRep['phone_number']) { ?>
				<i class="fas fa-phone"></i>
				<a class="phone_us" href="tel:<?php echo $salesRep['phone_number']; ?>">><?php echo $salesRep['phone_number']; ?></a>
				<?php } ?>
			    </p>
			</div>
		    </div>
		</div>
		<?php } ?>
	    </div>

	    <form id="patient_information" action="" method="post">

		<div class="actions">
		    <button class="btn-styled btn-home" id="bulkPrint"><i class="fas fa-print"></i> Print Selected</button>
		    <?php if($role != 'Physician') { ?>
			<button name="mark_as_test" class="btn-styled btn-home"><i class=""></i> Mark as Test</button>
		    <?php } ?>
		</div>
		<div class="formContent">

		    <input name="detail_request" type="hidden" value="1">
		    <input name="date_rng" type="hidden" value="<?php echo isset($_POST['date_rng'])?$_POST['date_rng']:''; ?>">
		    <input name="meets_mn" type="hidden" value="<?php echo isset($_POST['meets_mn'])?$_POST['meets_mn']:''; ?>">
		    <input name="first_name" type="hidden" value="<?php echo isset($_POST['first_name'])?$_POST['first_name']:''; ?>">
		    <input name="last_name" type="hidden" value="<?php echo isset($_POST['last_name'])?$_POST['last_name']:''; ?>">
		    <input name="insurance" type="hidden" value="<?php echo isset($_POST['insurance'])?$_POST['insurance']:''; ?>">
		    <input name="selected_questionnaire" id="selected_questionnaire" type="hidden" value="">
		    <input name="selected_date" id="selected_date" type="hidden"  value="">

		    <table id="dataTableHome" class="pseudo_t table">

		    <?php if ($num_estimates) { ?>

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
		       <?php if(isFieldVisibleByRole($roleIDs['meets_mn']['view'], $roleID)) {?>
			   <th>Medical Necessity</th>
		       <?php } ?>
		       <?php if(isFieldVisibleByRole($roleIDs['from_date']['view'], $roleID)) {?>
			   <th>Date</th>
		       <?php } ?>
		       <?php if(isFieldVisibleByRole($roleIDs['first_name']['view'], $roleID)) {?>
			   <th>First Name</th>
		       <?php } ?>
		       <?php if(isFieldVisibleByRole($roleIDs['last_name']['view'], $roleID)) {?>
			   <th>Last Name</th>
		       <?php } ?>

		       <?php if(isFieldVisibleByRole($roleIDs['account']['view'], $roleID)) {?>
			   <th>Account</th>
		       <?php } ?>

		       <?php if(isFieldVisibleByRole($roleIDs['location']['view'], $roleID)) {?>
			   <th>Location</th>
		       <?php } ?>
		       <?php if(isFieldVisibleByRole($roleIDs['salesrep']['view'], $roleID)) {?>
			   <th>Genetic Consultant</th>
		       <?php } ?>
		       <?php if(isFieldVisibleByRole($roleIDs['salesrep']['view'], $roleID)) {?>
			   <th>Email</th>
		       <?php } ?>
			</tr>
		   </thead>
		   <tbody>
		    <?php
			foreach ($qualify_requests as $qualify_request) {
			    $provider_name = "";
			    if (strlen($qualify_request['provider_id'])) {
				$provider_name = $db->row("SELECT CONCAT(p.first_name, ' ', p.last_name) AS name FROM tblprovider p WHERE Guid_provider = '" . $qualify_request['provider_id'] . "'");
				if($provider_name){
				    $provider_name = $provider_name['name'];
				} else {
				    $provider_name = "";
				}
			    }
			    $isIncomplete=FALSE;
			    $dataPrintable = "1";
			    if(isset($qualify_request['incomplete'])){
				$isIncomplete=TRUE;
				$dataPrintable = '2';
			    }
			    $trClass='';
			    $trClass = ($qualify_request['marked_test']=='1')?' marked_test':'';
			    if($qualify_request['Guid_role']=='6'){
				$trClass = ' mdl_patient';
			    }
		    ?>
			    <tr class="t_row <?php echo $trClass; ?>">

				<td class="printSelectBlock text-center">
					<?php if(isset($qualify_request['qualified']) && $qualify_request['qualified']=='Unknown'){ ?>
					    <input name="markedRow[user][<?php echo $qualify_request['Guid_user']; ?>]" type="checkbox" class="print1 report1" data-prinatble="0" data-selected_questionnaire="<?php echo $qualify_request['Guid_qualify']; ?>" data-selected_date="<?php echo $qualify_request['date']; ?>" />
					<?php } else { ?>
					    <?php if($isIncomplete){ ?>
					    <input type="hidden" name="q_incomplete" value="1" />
					    <?php } ?>
					    <input name="markedRow[user][<?php echo $qualify_request['Guid_user']; ?>]" type="checkbox" class="print1 report1" data-prinatble="<?php echo $dataPrintable; ?>" data-selected_questionnaire="<?php echo $qualify_request['Guid_qualify']; ?>" data-selected_date="<?php echo $qualify_request['date']; ?>" />
					<?php } ?>
				    </td>
				    <?php if(isset($qualify_request['qualified']) && isFieldVisibleByRole($roleIDs['meets_mn']['view'], $roleID)) {?>
					<td class="mn <?php echo strtolower($qualify_request['qualified']); ?>"><?php echo $qualify_request['qualified']; ?></td>
				    <?php } else { ?>
					<td class="mn no">Incomplete</td>
				    <?php } ?>
				    <?php if(isFieldVisibleByRole($roleIDs['from_date']['view'], $roleID)) {?>
					<td><?php echo date("n/j/Y", strtotime($qualify_request['date'])); ?></td>
				    <?php } ?>
				    <?php
					$accountStr = $qualify_request['account_number'] ? "&account=".$qualify_request['account_number']:"";
					$incompleteStr = $isIncomplete ? '&incomplete=1' : '';
				    ?>
				    <?php if(isFieldVisibleByRole($roleIDs['first_name']['view'], $roleID)) {?>
					<td>
					    <a target="_blank" href="<?php echo SITE_URL."/patient-info.php?patient=".$qualify_request['Guid_user'].$accountStr.$incompleteStr; ?>">
					    <?php echo ucfirst(strtolower($qualify_request['firstname'])); ?>
					    </a>
					</td>
				    <?php } ?>
				    <?php if(isFieldVisibleByRole($roleIDs['last_name']['view'], $roleID)) {?>
					<td>
					    <a target="_blank" href="<?php echo SITE_URL."/patient-info.php?patient=". $qualify_request['Guid_user'].$accountStr.$incompleteStr; ?>">
						<?php echo ucfirst(strtolower($qualify_request['lastname'])); ?>
					    </a>
					</td>
				    <?php } ?>

				    <?php if(isFieldVisibleByRole($roleIDs['account']['view'], $roleID)) {?>
					<td class="tdAccount"><?php
					    if( $qualify_request['account_number']!="" && !is_null($qualify_request['account_number']) && $qualify_request['account_number']!="NULL"){
						echo $qualify_request['account_number'];
						if($qualify_request['account_name']!=""){
						echo "<span class='account_name'>".ucwords(strtolower($qualify_request['account_name']))."</span>";
						}
					    }
					    ?>
					</td>
				    <?php } ?>

				    <?php if(isFieldVisibleByRole($roleIDs['location']['view'], $roleID)) {?>
					<td><?php echo $qualify_request['source']; ?></td>
				    <?php } ?>
				    <?php if(isFieldVisibleByRole($roleIDs['salesrep']['view'], $roleID)) {?>
					<td><?php echo $qualify_request['salesrep_name']; ?></td>
				    <?php } ?>
				    <?php if(isFieldVisibleByRole($roleIDs['salesrep']['view'], $roleID)) {?>
					<td class="mn">
					    <?php if($qualify_request['email']==""){ ?>
						<img src = "<?php echo SITE_URL; ?>/assets/images/no_email_icon_30.png" />
					    <?php } ?>
					</td>
				    <?php } ?>

			    </tr>
			<?php }
			    }
			?>
		    </tbody>
		    </table>
		</div>
	    </form>
	</div>
	<?php } else { ?>
	    <p>Sorry! You don't have access to this page content. </p>
	<?php } ?>
    </div>
    <div id="admin_print"></div>
</main>

<button id="action_palette_toggle" class=""><i class="fa fa-2x fa-angle-left"></i></button>



<?php require_once 'scripts.php'; ?>
<script type="text/javascript">
    if ($('#dataTableHome').length ) {
	var table = $('#dataTableHome').DataTable({
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

    jQuery.fn.dataTableExt.oSort['uk_date-pre']  = function(a) {
	a = a.slice(0, -2) + ' ' + a.slice(-2);
	var date = Date.parse(a);
	return typeof date === 'number' ? date : -1;
    }
    jQuery.fn.dataTableExt.oSort['uk_date-asc']  = function(a,b) {
	return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    }
    jQuery.fn.dataTableExt.oSort['uk_date-desc'] = function(a,b) {
	return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    }

    var table = $('#example').DataTable({
	aoColumns: [
	  { sType: 'uk_date' }
	]
    });
</script>
<?php require_once 'footer.php'; ?>