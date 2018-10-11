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

$roles=  array(
    array('Guid_role'=>'2', 'role' => 'Physician'),
    array('Guid_role'=>'4', 'role' => 'Sales Rep'),
    array('Guid_role'=>'5', 'role' => 'Sales Manager')
);
$tables = array(
    'home' => array(
	'tableName' => 'Home',
	'tableActions' => array('add', 'edit', 'delete'),
	'from_date'=>'From Date',
	'to_date'=>'To Date',
	'meets_mn'=>'Medical Necessity',
	'first_name'=>'First Name',
	'last_name'=>'Last Name',
	'insurance'=>'Insurance',
	'account'=>'Account',
	'provider'=>'Provider',
	'location'=>'Location',
	'salesrep'=>'Genetic Consultants'
    ),
    'account' => array(
	'tableName' => 'Account',
	'tableActions' => array('add', 'edit', 'delete'),
	'account' => 'Account',
	'name' => 'Name',
	'Guid_salesrep'=>'Genetic Consultant',
	'logo'=>'Logo',
	'address'=>'Address',
	'city'=>'City',
	'state'=>'State',
	'zip'=>'Zip',
	'phone_number'=>'Phone',
	'fax'=>'Fax',
	'website'=>'Website'
    ),
    'devices' => array(
	'tableName' => 'Devices',
	'tableActions' => array('add', 'edit', 'delete'),
	'serial_number'=>'Serial',
	'Guid_salesrep'=>'Genetic Consultant',
	'deviceid'=>'Device Name',
	'inservice_date'=>'In-Service Date',
	'outservice_date'=>'Out-Of-Service Date',
	'comment'=>'Comment'
    ),
    'salesreps' => array(
	'tableName' => 'Genetic Consultants',
	'tableActions' => array('add', 'edit', 'delete'),
	'first_name' => 'First Name',
	'last_name' => 'Last Name',
	'email' => 'Email',
	'title' => 'Title',
	'photo_filename' => 'Photo',
	'address' => 'Address',
	'region' => 'Region',
	'city' => 'City',
	'state' => 'State',
	'zip' => 'Zip',
	'color' => 'Color',
	'phone_number' => 'Phone'
    ),
);
$userID = $_SESSION['user']["id"];
$roleInfo = getRole($db, $userID);
$role = $roleInfo['role'];

if($role!="Admin"){
    Leave(SITE_URL."/no-permission.php");
}

$thisMessage = "";
if(isset($_POST['submit_config_tables'])){
    extract($_POST);
    $data = $_POST;
    $data['table_id']=$_GET['tableid'];
    unset($data['submit_config_tables']);

    foreach ($tables as $tableKey=>$tableVal){
	if($tableKey==$data['table_id']){
	    unset($tableVal['tableName']);
	    foreach ($tableVal as $fieldKey=>$fieldVal ) {
		if(isset($data['role'])){
		    if(!array_key_exists($tableKey, $data['role']) ){
			$data['role'][$tableKey] = array();
		    }
		}else{
		    $data['role'][$tableKey] = array();
		}
	    }
	}
    }

    if(saveTableAccessRole($db, $data['role'])){
	$thisMessage = "Changes have been saved";
    }

}


require_once ('navbar.php');
?>

<main class="full-width">
    <?php if($thisMessage != ""){ ?>
    <section id="msg_display" class="show success">
	<h4><?php echo $thisMessage;?></h4>
    </section>
    <?php } ?>
    <div class="box full visible ">
	<section id="palette_top">
	    <h4>
		<ol class="breadcrumb">
		    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
		     <?php if( !isset($_GET['tableid']) ){?>
			<li class="active">Access Roles</li>
		     <?php } else { ?>
			<li><a href="<?php echo SITE_URL; ?>/access-roles.php">Access Roles</a></li>
			<li class="active"><?php echo $tables[$_GET['tableid']]['tableName']; ?></li>
		     <?php } ?>

		</ol>
	    </h4>
	    <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
	    <a href="<?php echo SITE_URL; ?>/dashboard2.php" class="button homeIcon"></a>
	    <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
	</section>
	<div class="scroller access-roles">

	<?php if(!isset($_GET['config'])) { ?>
	<div class="role_tables">
	    <p><a href="<?php echo SITE_URL; ?>/access-roles.php?config=tables&tableid=home"><i class="fas fa-arrow-circle-right"></i> Home</a></p>
	    <p><a href="<?php echo SITE_URL; ?>/access-roles.php?config=tables&tableid=account"><i class="fas fa-arrow-circle-right"></i> Accounts</a></p>
	    <p><a href="<?php echo SITE_URL; ?>/access-roles.php?config=tables&tableid=devices"><i class="fas fa-arrow-circle-right"></i> Devices</a></p>
	    <p><a href="<?php echo SITE_URL; ?>/access-roles.php?config=tables&tableid=salesreps"><i class="fas fa-arrow-circle-right"></i> Genetic Consultants</a></p>
	</div>
	<?php } ?>
	<!-- ./Config Tables -->
	<?php if(isset($_GET['config']) && $_GET['config']=='tables'){ ?>


		<?php if( isset($_GET['tableid']) ){?>
		    <form method="POST" >
			<?php
			    $tableID = $_GET['tableid'];
			    $thisTable = $tables[$tableID];
			    $tableName = $thisTable['tableName'];
			    $tableActions = $thisTable['tableActions'];
			    unset($thisTable['tableName']);
			    unset($thisTable['tableActions']);
			?>
			<div class="actions">
			    <button name="submit_config_tables" type="submit" class="btn-inline">Save</button>
			    <a href="<?php echo SITE_URL."/access-roles.php";?>" class="btn-inline btn-cancel">Cancel</a>
			</div>
			<table class="table table-striped">
			    <thead>
				<tr>
				    <th><?php echo $tableName; ?> Table Fields</th>
				    <?php foreach ($roles as $k=>$v){ ?>
				    <th class='text-center '><?php echo $v['role']; ?></th>
				    <?php } ?>
				</tr>
			    </thead>
			    <tbody>
				<?php
				foreach($thisTable as $tableKey => $val ){ ?>
				<tr>
				    <td>
					<label><?php echo $val; ?></label>
				    </td>
				    <?php foreach ($roles as $roleKey=>$roleVal){?>
				    <td class="text-center">
					<?php //echo isCheckedRoleTableCheckbox($tableID, $tableKey, $roleVal['Guid_role'], 'view');?>
					    <span>
						<input <?php echo isCheckedRoleTableCheckbox($tableID, $tableKey,  $roleVal['Guid_role'], 'view'); ?> name="role[<?php echo $tableID; ?>][<?php echo $tableKey;?>][view][<?php echo $roleVal['Guid_role']; ?>]" type="checkbox" value="1" />
						View |
					    </span>
					    <span>
						<input <?php echo isCheckedRoleTableCheckbox($tableID, $tableKey, $roleVal['Guid_role'], 'add'); ?> name="role[<?php echo $tableID; ?>][<?php echo $tableKey;?>][add][<?php echo $roleVal['Guid_role']; ?>]" type="checkbox" value="1" />
						Add |
					    </span>
					    <span>
						<input <?php echo isCheckedRoleTableCheckbox($tableID, $tableKey, $roleVal['Guid_role'], 'edit'); ?> name="role[<?php echo $tableID; ?>][<?php echo $tableKey;?>][edit][<?php echo $roleVal['Guid_role']; ?>]" type="checkbox" value="1" />
						Edit |
					    </span>
					    <span>
						<input <?php echo isCheckedRoleTableCheckbox($tableID, $tableKey, $roleVal['Guid_role'], 'delete'); ?> name="role[<?php echo $tableID; ?>][<?php echo $tableKey;?>][delete][<?php echo $roleVal['Guid_role']; ?>]" type="checkbox" value="1" />
						Delete
					    </span>
					</td>
				    <?php } ?>
				</tr>
				<?php } ?>

				<?php foreach($tableActions as $actionKey => $actionVal ){ ?>
				<tr>
				    <td>
					<label><?php echo ucfirst($actionVal)." ".$tableName; ?></label>
				    </td>
				    <?php foreach ($roles as $roleKey=>$roleVal){?>
					<td class="text-center">
					    <span>
						<input <?php echo isCheckedRoleTableCheckbox($tableID, 'actions',  $roleVal['Guid_role'], $actionVal); ?> name="role[<?php echo $tableID; ?>][actions][<?php echo $actionVal; ?>][<?php echo $roleVal['Guid_role']; ?>]" type="checkbox" value="1" />
					    </span>
					</td>
				    <?php } ?>
				</tr>
				<?php } ?>
			    </tbody>
			</table>

		    </form>
		<?php } ?>

	    <?php } ?>
	</div>
    </div>
</main>

<?php require_once('scripts.php');?>
<?php require_once('footer.php');?>