<?php
require_once('../config.php');
require ("../db/dbconnect.php");

$error = array();
session_start();

if (empty($_POST)) {
	generate_outer();
	generate_login_html($error);
	generate_footer();
} elseif (isset($_POST['account_login'])) {
	verify_login($error);

	if (count($error)) {
		generate_login_html($error);
	} else {
		generate_outer();
		generate_header($error);
		generate_html($error);
		generate_footer();
	}
} else {
	if (isset($_POST['search']) && (strlen($_POST['from_date']) || strlen($_POST['to_date']))) {
		verify_input($error);
	}

	generate_outer();
	generate_header($error);
	generate_html($error);
	generate_footer();
}

function generate_outer() {
?>
<?php $medlab = '../../wp-content/themes/medlab/';?>
<?php $questionnaire = '../../questionnaire/';?>
	<!doctype html>
	<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
		<meta name="viewport" content="width=device-width" />
		<title>MDL Administration</title>
		<link rel="stylesheet" href="<?php echo $medlab;?>style.css">
		<link rel="stylesheet" href="<?php echo $questionnaire;?>style.min.css">
		<link rel="stylesheet" href="ad.min.css">
		<script src="js/jquery.min.js"></script>
	<!--[if lt IE 9]>
		<script src="<?php echo $medlab;?>js/html5shiv.js"></script>
	<![endif]-->
	</head>
	<body>
<?php
}
function generate_header($error) {
?>
	<header id="app_top">
	<section id="modules">
	    <h5>Module</h5>
	    <ul id="mod_links">
		<li>
		    <a class="module">BRCAcare<sup>&#174;</sup> Estimate</a>
		</li>
				<li class="active">
		    <a class="module">BRCAcare<sup>&#174;</sup> Portal</a>
		</li>
	    </ul>
	</section>

	<div id="app_user">
	    <form action="" method="post">
		<button type="button" id="mdl" class="toggle" data-on="#user_window"></button>
		<div id="user_window">
		    <button type="submit" id="log_out" name="log_out" class="button red back"><strong>Log Out</strong></button>
		</div>
	    </form>
	</div>
<?php
    if ((isset($_POST['update'])) && (!$error['update']) && (!$error['qualification']) && (!$error['counseling']) && (!$error['test_1221_cost']) && (!$error['test_1241_cost']) && (!$error['insurance_provider']) && (!$error['brca_kit_sent']) && (!$error['date_kit_mailed']) && (!$error['physician_contacted'])) {
	$class = "show success";
	$message = "<h4>Changes have been saved</h4>";
    } elseif ((isset($_POST['update'])) && (($error['update']) || ($error['qualification']) || ($error['counseling']) || ($error['test_1221_cost']) || ($error['test_1241_cost']) || ($error['insurance_provider']) || ($error['brca_kit_sent']) || ($error['date_kit_mailed']) || ($error['physician_contacted']))) {
	$class = "show error";
	$message = "<h4>There were errors. Changes have not been saved.</h4>";
    }
	if (count($error)) {
?>
		<section id="msg_display" class="show error">
			<h4>Correct errors in filters</h4>
	</section>
<?php
	}
?>
    </header>
<?php
}
function generate_login_html($error) {
	require ("db/dbconnect.php");
	if (isset($error['login_invalid'])) {
?>
	<section id="msg_display" class="<?php echo "show error"; ?>">
		<h4>Invalid login information proivded.</h4>
    </section>
<?php
	}
?>
    <form method="post" action="">
    <section id="admin_login" class="center_box">
	<div class="v_center">
	    <div class="box">
		<h4 class="box_top">Please Login</h4>

		<div class="boxtent">
		    <div class="f2 required">
			<label class="dynamic" for="username"><span>Username</span></label>

			<div class="group">
			    <input id="username" name="username" type="text" value="" placeholder="Username" required>

			    <p class="f_status">
				<span class="status_icons"><strong>*</strong></span>
			    </p>
			</div>
		    </div>

		    <div class="f2 required">
			<label class="dynamic" for="password"><span>Password</span></label>

			<div class="group">
			    <input id="password" name="password" type="password" value="" placeholder="Password" required>

			    <p class="f_status">
				<span class="status_icons"><strong>*</strong></span>
			    </p>
			</div>
		    </div>

		    <div class="box_btns">
			<button type="submit" class="submit button" name="account_login">
			    <strong>Login</strong>
			</button>
		    </div>
		</div>
	    </div>
	</div>
    </section>
    </form>
<?php
}

function generate_html($error) {
	require ("../db/dbconnect.php");
?>
    <aside id="action_palette">
	<div class="box full">
	    <h4 class="box_top">Filters</h4>

	    <div class="boxtent scroller">
				<form id="filter_form" action="" method="post">
<!-- ********************   SEARCH BY PALETTE    ********************-->
			<!--<div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['date_rng'])) && (strlen(trim($_POST['date_rng'])))) ? " show-label valid" : ""; ?>">
		    <label class="dynamic" for="date_rng"><span>Date Range</span></label>

		    <div class="group">
			<select id="date_rng" name="date_rng" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['date_rng'])) && (strlen(trim($_POST['date_rng'])))) ? "" : "no-selection"; ?>">
			    <option value="">Date Range</option>
							<option value="current_week"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['date_rng']) && ($_POST['date_rng'] == "current_week")) ? " selected" : ""); ?>>Current week</option>
							<option value="past_week"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['date_rng']) && ($_POST['date_rng'] == "past_week")) ? " selected" : ""); ?>>Past week</option>
							<option value="current_month"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['date_rng']) && ($_POST['date_rng'] == "current_month")) ? " selected" : ""); ?>>Current month</option>
			</select>

			<p class="f_status">
			    <span class="status_icons"><strong></strong></span>
			</p>
		    </div>
		</div>-->
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
				<div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['from_date'])) && (strlen($_POST['from_date']))) ? " show-label" : ""; ?><?php echo $date_error; ?>">
		    <label class="dynamic" for="from_date"><span>From Date</span></label>

		    <div class="group">
						<input type="date" id="from_date" name="from_date" value="<?php echo ((!isset($_POST['clear'])) && isset($_POST['from_date']) && strlen($_POST['from_date'])) ? $_POST['from_date'] : ""; ?>" placeholder="From Date">

			<p class="f_status">
			    <span class="status_icons"><strong></strong></span>
			</p>
		    </div>
		</div>
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
				<div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['to_date'])) && (strlen($_POST['to_date']))) ? " show-label" : ""; ?><?php echo $date_error; ?>">
		    <label class="dynamic" for="to_date"><span>To Date</span></label>

		    <div class="group">
						<input type="date" id="to_date" name="to_date" value="<?php echo ((!isset($_POST['clear'])) && isset($_POST['to_date']) && strlen($_POST['to_date'])) ? $_POST['to_date'] : ""; ?>" placeholder="To Date" max="<?php echo date('Y-m-d'); ?>">

			<p class="f_status">
			    <span class="status_icons"><strong></strong></span>
			</p>
		    </div>
		</div>

			<div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['meets_mn'])) && (strlen($_POST['meets_mn']))) ? " show-label valid" : ""; ?>">
		    <label class="dynamic" for="meets_mn"><span>Medical Necessity</span></label>

		    <div class="group">
			<select id="meets_mn" name="meets_mn" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['meets_mn'])) && (strlen($_POST['meets_mn']))) ? "" : "no-selection"; ?>">
			    <option value="">Medical Necessity</option>
							<option value="yes"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['meets_mn']) && ($_POST['meets_mn'] == "yes")) ? " selected" : ""); ?>>Yes</option>
							<option value="no"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['meets_mn']) && ($_POST['meets_mn'] == "no")) ? " selected" : ""); ?>>No</option>
							<option value="unknown"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['meets_mn']) && ($_POST['meets_mn'] == "unknown")) ? " selected" : ""); ?>>Unknown</option>
			</select>

			<p class="f_status">
			    <span class="status_icons"><strong></strong></span>
			</p>
		    </div>
		</div>

		<div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['first_name'])) && (strlen(trim($_POST['first_name'])))) ? " show-label valid" : ""; ?>">
		    <label class="dynamic" for="first_name"><span>First Name</span></label>

		    <div class="group">
			<input id="first_name" name="first_name" type="text" value="<?php echo ((!isset($_POST['clear'])) && isset($_POST['first_name']) && strlen(trim($_POST['first_name']))) ? trim($_POST['first_name']) : ""; ?>" placeholder="First Name">

			<p class="f_status">
			    <span class="status_icons"><strong></strong></span>
			</p>
		    </div>
		</div>

		<div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['last_name'])) && (strlen(trim($_POST['last_name'])))) ? " show-label valid" : ""; ?>">
		    <label class="dynamic" for="last_name"><span>Last Name</span></label>

		    <div class="group">
			<input id="last_name" name="last_name" type="text" value="<?php echo ((!isset($_POST['clear'])) && isset($_POST['last_name']) && strlen(trim($_POST['last_name']))) ? trim($_POST['last_name']) : ""; ?>" placeholder="Last Name">

			<p class="f_status">
			    <span class="status_icons"><strong></strong></span>
			</p>
		    </div>
		</div>

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

				<div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['account'])) && (strlen(trim($_POST['account'])))) ? " show-label valid" : ""; ?>">
		    <label class="dynamic" for="account"><span>Account</span></label>

		    <div class="group">
			<input id="account" name="account" type="text" value="<?php echo ((!isset($_POST['clear'])) && isset($_POST['account']) && strlen(trim($_POST['account']))) ? trim($_POST['account']) : ""; ?>" placeholder="Account">

			<p class="f_status">
			    <span class="status_icons"><strong></strong></span>
			</p>
		    </div>
		</div>

				<div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['provider'])) && (strlen($_POST['provider']))) ? " show-label valid" : ""; ?>">
		    <label class="dynamic" for="provider"><span>Provider</span></label>

		    <div class="group">
			<select id="provider" name="provider" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['provider'])) && (strlen($_POST['provider']))) ? "" : "no-selection"; ?>">
			    <option value="">Provider</option>
<?php
	$providers = $conn->query("SELECT * FROM tblprovider GROUP BY name");

	foreach($providers as $provider) {
?>
							<option value="<?php echo $provider['Guid_provider']; ?>"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['provider']) && ($_POST['provider'] == $provider['Guid_provider'])) ? " selected" : ""); ?>><?php echo $provider['name']; ?></option>
<?php
	}
?>
			</select>

			<p class="f_status">
			    <span class="status_icons"><strong></strong></span>
			</p>
		    </div>
		</div>

				<div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['location'])) && (strlen($_POST['location']))) ? " show-label valid" : ""; ?>">
		    <label class="dynamic" for="location"><span>Location</span></label>

		    <div class="group">
			<select id="location" name="location" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['location'])) && (strlen($_POST['location']))) ? "" : "no-selection"; ?>">
			    <option value="">Location</option>
<?php
	$locations = $conn->query("SELECT description FROM tblsource");

	foreach($locations as $location) {
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

				<div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['salesrep'])) && (strlen($_POST['salesrep']))) ? " show-label valid" : ""; ?>">
		    <label class="dynamic" for="salesrep"><span>Sales Rep</span></label>

		    <div class="group">
			<select id="salesrep" name="salesrep" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['salesrep'])) && (strlen($_POST['salesrep']))) ? "" : "no-selection"; ?>">
			    <option value="">Sales Rep</option>
<?php
	$salesreps = $conn->query("SELECT a.account, sr.name FROM tblaccount a LEFT JOIN tblaccountrep ar ON a.Guid_account = ar.Guid_account LEFT JOIN tblsalesrep sr ON ar.Guid_salesrep = sr.Guid_salesrep GROUP BY name");

	foreach($salesreps as $salesrep) {
?>
							<option value="<?php echo $salesrep['account']; ?>"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['salesrep']) && ($_POST['salesrep'] == $salesrep['account'])) ? " selected" : ""); ?>><?php echo $salesrep['name']; ?></option>
<?php
	}
?>
			</select>

			<p class="f_status">
			    <span class="status_icons"><strong></strong></span>
			</p>
		    </div>
		</div>

	       <!-- <fieldset class="cbox">
		    <label for="auto_apply">
			<input id="auto_apply" type="checkbox" checked>
			<strong>Auto Apply</strong>
		    </label>
		</fieldset> -->

		<button id="filter" name="search" type="submit" class="button filter half"><strong>Search</strong></button>
		<button type="submit" name="clear" class="button cancel half"><strong>Clear</strong></button>
    </form>
<!--********************   SEARCH BY PALETTE END    ******************** -->

	    </div>
	</div>
    </aside>
<?php
    $sql = "SELECT *, q.Date_created AS date FROM tbl_ss_qualify q LEFT JOIN tblpatient p ON q.Guid_user = p.Guid_user";

	$where = "";

	if ((!count($error)) && (!isset($_POST['clear']))) {
		//if (isset($_POST['date_rng'])) {
		if (strlen($_POST['from_date']) && strlen($_POST['to_date'])) {
			if ($_POST['from_date'] == $_POST['to_date']) {
				$where = " WHERE q.Date_created LIKE '%" . $_POST['from_date'] . "%'";
			} else {
				$where = " WHERE q.Date_created BETWEEN '" . $_POST['from_date'] . "' AND '" . $_POST['to_date'] . "'";
			}
		}
			// elseif ($_POST['date_rng'] == "current_week") {
				// $last_sunday = date('Y-m-d', strtotime('last Sunday', strtotime(date("m/d/Y")))) . " 12:00:00";

				// $next_saturday = date('Y-m-d', strtotime('next Saturday', strtotime(date("m/d/Y")))) . " 11:59:59";

				// $where = " WHERE q.Date_created BETWEEN '" . $last_sunday . "' AND '" . $next_saturday . "'";
			// } elseif (isset($_POST['date_rng']) && ($_POST['date_rng'] == "past_week")) {
				// $last_saturday = date('Y-m-d', strtotime('last Saturday', strtotime(date("m/d/Y")))) . " 11:59:59";

				// $previous_sunday = date('Y-m-d', strtotime('last Sunday', strtotime($last_saturday))) . " 12:00:00";

				// $where = " WHERE q.Date_created BETWEEN '" . $previous_sunday . "' AND '" . $last_saturday . "'";
			// } elseif (isset($_POST['date_rng']) && ($_POST['date_rng'] == "current_month")) {
				// $first_day_of_month = date('Y-m-01') . " 12:00:00";

				// $last_day_of_month = date('Y-m-') . date('t',strtotime('today')) . " 11:59:59";

				// $where = " WHERE q.Date_created BETWEEN '" . $first_day_of_month . "' AND '" . $last_day_of_month . "'";
			// }
		// }
		if (isset($_POST['meets_mn']) && strlen($_POST['meets_mn'])) {
			$where .= (strlen($where)) ? " AND " : " WHERE ";
			$where .= " q.qualified = '" . $_POST['meets_mn'] . "'";
		}

		if (isset($_POST['first_name']) && strlen(trim($_POST['first_name']))) {
			$where .= (strlen($where)) ? " AND " : " WHERE ";
			$where .= " p.firstname = '" . $_POST['first_name'] . "'";
		}

		if (isset($_POST['last_name']) && strlen(trim($_POST['last_name']))) {
			$where .= (strlen($where)) ? " AND " : " WHERE ";
			$where .= " p.lastname = '" . $_POST['last_name'] . "'";
		}

		if (isset($_POST['insurance']) && strlen($_POST['insurance'])) {
			$where .= (strlen($where)) ? " AND " : " WHERE ";
			$where .= " q.insurance = '" . $_POST['insurance'] . "'";
		}

		if (isset($_POST['account']) && strlen(trim($_POST['account']))) {
			$where .= (strlen($where)) ? " AND " : " WHERE ";
			$where .= " q.account_number = '" . $_POST['account'] . "'";
		}

		if (isset($_POST['provider']) && strlen($_POST['provider'])) {
			$where .= (strlen($where)) ? " AND " : " WHERE ";
			$where .= " q.provider_id = '" . $_POST['provider'] . "'";
		}

		if (isset($_POST['salesrep']) && strlen($_POST['salesrep'])) {
			$where .= (strlen($where)) ? " AND " : " WHERE ";
			$where .= " q.account_number = '" . $_POST['salesrep'] . "'";
		}

		if (isset($_POST['location']) && strlen($_POST['location'])) {
			$where .= (strlen($where)) ? " AND " : " WHERE ";
			$where .= " q.source = '" . $_POST['location'] . "'";
		}
	}

    $sql .= $where;

	$qualify_requests = $conn->query($sql);

    $num_estimates = mysqli_num_rows($qualify_requests);
?>

    <main>
	<div class="box full visible">
	    <section id="palette_top">
		<h4><?php echo $num_estimates . " Results"; ?></h4>
		<a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
	    </section>

	    <div id="app_data" class="scroller">
		<form id="patient_information" action="" method="post">
		    <input name="detail_request" type="hidden" value="1">
		    <input name="date_rng" type="hidden" value="<?php echo $_POST['date_rng']; ?>">
					<input name="meets_mn" type="hidden" value="<?php echo $_POST['meets_mn']; ?>">
		    <input name="first_name" type="hidden" value="<?php echo $_POST['first_name']; ?>">
		    <input name="last_name" type="hidden" value="<?php echo $_POST['last_name']; ?>">
		    <input name="insurance" type="hidden" value="<?php echo $_POST['insurance']; ?>">
					<input name="selected_questionnaire" id="selected_questionnaire" type="hidden" value="">
					<input name="selected_date" id="selected_date" type="hidden"  value="">
		    <section class="pseudo_t">

<?php
    if ($qualify_requests) {
?>
		    <div class="col_group"></div>
		    <div class="col_group"></div>
		    <div class="col_group"></div>
		    <div class="col_group"></div>
		    <div class="col_group"></div>

		    <h2 class="t_row head">
						<p>Medical<br>Necessity</p>
						<p>Date</p>
			<p>First<br>Name</p>
						<p>Last<br>Name</p>
						<p>Insurance</p>
						<p>Account</p>
						<p>Provider</p>
						<p>Location</p>
						<p>Sales<br>Rep</p>
						<p>Report</p>
		    </h2>
<?php
	foreach($qualify_requests as $qualify_request) {
			$provider_name = "";
			if (strlen($qualify_request['provider_id'])) {
				$provider_name = $conn->query("SELECT name FROM tblprovider WHERE Guid_provider = " . $qualify_request['provider_id'])->fetch_object()->name;
			}

			$salesrep = $conn->query("SELECT sr.name FROM tblaccount a LEFT JOIN tblaccountrep ar ON a.Guid_account=ar.Guid_account LEFT JOIN tblsalesrep sr ON ar.Guid_salesrep = sr.Guid_salesrep WHERE a.account = '" . $qualify_request['account_number'] . "'")->fetch_object()->name;

?>
		    <div data-id="<?php echo $estimate_request['Guid_brcaestimate']; ?>" class="t_row">
						<p class="mn <?php echo strtolower($qualify_request['qualified']); ?>"><?php echo $qualify_request['qualified']; ?></p>
						<p><?php echo date("n/j/Y", strtotime($qualify_request['date'])); ?></p>
			<p><?php echo $qualify_request['firstname']; ?></p>
						<p><?php echo $qualify_request['lastname']; ?></p>
						<p><?php echo $qualify_request['insurance']; ?></p>
						<p><?php echo $qualify_request['account_number']; ?></p>
						<p><?php echo $provider_name; ?></p>
						<p><?php echo $qualify_request['source']; ?></p>
						<p><?php echo $salesrep; ?></p>
						<p><button class="print report" data-selected_questionnaire="<?php echo $qualify_request['Guid_qualify']; ?>" data-selected_date="<?php echo $qualify_request['date']; ?>"></button></p>
		    </div>
<?php
	}
    } else {
?>
		    <p>There are no Pending BRCAcare<sup>&reg;</sup> Estimate Requests</p>
<?php
    }
?>
		</section>
    </form>
	    </div>
	</div>
		<div id="admin_print"></div>
    </main>

<?php
}
function generate_footer() {
?>
    <!--<script src="../../wp-content/themes/medlab/js/common.min.js"></script> -->
    <script src="js/admin.js"></script>
	<script src="js/printThis.js"></script>
</body>
</html>
<?php
}
function verify_login(&$error) {
	require ("../db/dbconnect.php");

	$Guid_user = $conn->query("SELECT Guid_user FROM tbluser WHERE email = '" . $conn->real_escape_string($_POST['username']) . "' AND password = '" . md5($conn->real_escape_string($_POST['password'])) . "'")->fetch_object()->Guid_user;

	if (strlen($Guid_user)) {
		$role = $conn->query("SELECT r.role FROM tblrole r LEFT JOIN tbluserrole u ON r.Guid_role = u.Guid_role WHERE u.Guid_user = " . $Guid_user)->fetch_object()->role;

		if ($role != "Admin") {
			$error['login_invalid'] = 1;
		} else {
			$_SESSION["id"] = $Guid_user;
		}
	} else {
		$error['login_invalid'] = 1;
	}
}
function verify_input(&$error) {
	if (strlen($_POST['from_date']) && (!strlen($_POST['to_date']))) {
		$error['to_date'] = 1;
	} elseif ((!strlen($_POST['from_date'])) && strlen($_POST['to_date'])) {
		$error['from_date'] = 1;
	} elseif (strlen($_POST['from_date']) && strlen($_POST['to_date']) && (strtotime($_POST['to_date']) < strtotime($_POST['from_date']))) {
		$error['from_date'] = 1;
		$error['to_date'] = 1;
	}
}
?>