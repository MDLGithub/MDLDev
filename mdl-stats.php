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
	    <h1 class="title-st1">MDL Statuses</h1>

	    <div class="row ">
		<div class="col-md-12 text-center">
		    <form action="" method="POST">
			<div class="row ">
			    <div class="col-md-12 text-center">
				<div class="h-filters">
				    <label>From: </label> <input class="date datepicker" type="text" />
				    <label>To: </label> <input class="date datepicker" type="text" />
				    <select class="salesrep"><option value="">Genetic Consultant</option></select>
				    <select class="account"><option value="">Account</option></select>
				    <button type="submit" class="" >Filter</button>
				</div>
			    </div>
			</div>
			<div class="row pT-30">
			    <div class="col-md-6 col-md-offset-3">
				<table class="table stats-table">
				    <thead>
					<tr>
					    <th>Status</th>
					    <th class="wh-100">Quantity</th>
					</tr>
				    </thead>
				    <tbody>
					<?php echo get_status_table_rows($db);?>
				    </tbody>
				</table>
			    </div>
			</div>
		    </form>
		</div>
	    </div>


	</div>

    </div>
</main>




<?php require_once('scripts.php');?>

<?php require_once('footer.php');?>