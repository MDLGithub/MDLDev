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
$accessRole = getAccessRoleByKey('salesreps');
$roleIDs = unserialize($accessRole['value']);
$dataViewAccess = isUserHasAnyAccess($roleIDs, $roleID, 'view');

$isFNameView = isset($roleIDs['first_name']['view'])?$roleIDs['first_name']['view']:"";
$isLNameView = isset($roleIDs['last_name']['view'])?$roleIDs['last_name']['view']:"";
$isEmailView = isset($roleIDs['email']['view'])?$roleIDs['email']['view']:"";
$isTitleView = isset($roleIDs['title']['view'])?$roleIDs['title']['view']:"";
$isPhotoView = isset($roleIDs['photo_filename']['view'])?$roleIDs['photo_filename']['view']:"";
$isAddressView = isset($roleIDs['address']['view'])?$roleIDs['address']['view']:"";
$isRegionView = isset($roleIDs['region']['view'])?$roleIDs['region']['view']:"";
$isCityView = isset($roleIDs['city']['view'])?$roleIDs['city']['view']:"";
$isStateView = isset($roleIDs['state']['view'])?$roleIDs['state']['view']:"";
$isZipView = isset($roleIDs['zip']['view'])?$roleIDs['zip']['view']:"";
$isColorView = isset($roleIDs['color']['view'])?$roleIDs['color']['view']:"";
$isPhoneView = isset($roleIDs['phone_number']['view'])?$roleIDs['phone_number']['view']:"";
$isActionAdd = isset($roleIDs['actions']['add'])?$roleIDs['actions']['add']:"";
$isActionEdit = isset($roleIDs['actions']['edit'])?$roleIDs['actions']['edit']:"";
$isActionDelete = isset($roleIDs['actions']['delete'])?$roleIDs['actions']['delete']:"";

$thisMessage = "";
if(isset($_GET['action']) && $_GET['action']=='edit'){
    if(!isFieldVisibleByRole($isActionEdit, $roleID)) {
	Leave(SITE_URL.'/salesreps.php');
    }
}
if(isset($_GET['action']) && $_GET['action']=='add'){
    if(!isFieldVisibleByRole($isActionAdd, $roleID)) {
	Leave(SITE_URL.'/salesreps.php');
    }
}
if (isset($_GET['delete']) && $_GET['delete'] != '') {
    if(!isFieldVisibleByRole($isActionAdd, $roleID)) {
	Leave(SITE_URL.'/salesreps.php');
    }
    if(isset($_GET['user']) && $_GET['user']!=""){
	deactivateUser($db, $_GET['user']);
	//deleteByField($db, 'tblsalesrep', 'Guid_salesrep', $_GET['delete']);
	Leave(SITE_URL.'/salesreps.php');
    }
}
if( isset($_POST['cancel_manage_salesrep'])){
    Leave(SITE_URL.'/salesreps.php');
}
if( isset($_POST['manage_salesrep'])){
    extract($_POST);
    $data = array(
	'first_name' => $first_name,
	'last_name' => $last_name,
	'email' => $email,
	'phone_number'=>cleanString($phone_number),
	'region' => $region,
	'title' => $title,
	'address'=>$address,
	'city'=>$city,
	'state'=>$state,
	'zip'=>$zip
    );
    if(isset($_POST['color'])){
	$data['color'] = $_POST['color'];
    } else {
	 $data['color'] = '';
    }
    if($_FILES["photo_filename"]["name"] != ""){
	$fileName = $_FILES["photo_filename"]["name"];
	$data['photo_filename'] = $fileName;
	$uploadMsg = uploadFile('photo_filename', 'images/users/');
    }
    if($Guid_salesrep != ''){

	//check if salesrep don't have user crate it and set Guid_user to tblsalesrep table
	$q = "SELECT Guid_user, email FROM tblsalesrep WHERE Guid_salesrep=:Guid_salesrep";
	$thisSlaesrep = $db->row($q, array('Guid_salesrep'=>$_POST['Guid_salesrep']));
	//check if user exists with this Guid_user
	if( isset($thisSlaesrep['Guid_user']) && ($thisSlaesrep['Guid_user']=='0' || $thisSlaesrep['Guid_user']=='') ){ //we should add new user for this salesrep
	     $userData = array(
		'email' => $email,
		'password'=>'',
		'user_type' => 'salesrep',
		'Guid_role'=>'4',
		'Date_created'=> date('Y-m-d H:i:s')
	    );
	    $inserUser = insertIntoTable($db, 'tbluser', $userData);

	    if($inserUser['insertID']){
		$data['Guid_user'] = $inserUser['insertID'];
		//insert sales rep
		$update = updateTable($db, 'tblsalesrep', $data, array("Guid_salesrep"=>$Guid_salesrep));
		Leave(SITE_URL.'/salesreps.php?insert' );
	    }
	} else {
	    //update Salesrep info
	    $update = updateTable($db, 'tblsalesrep', $data, array("Guid_salesrep"=>$Guid_salesrep));
	    Leave(SITE_URL.'/salesreps.php?update' );
	}
    } else {
	//insert sales rep user
	$userData = array(
	    'email' => $email,
	    'password'=>'',
	    'user_type' => 'salesrep',
	    'Guid_role'=>'4',
	    'Date_created'=> date('Y-m-d H:i:s')
	);
	$inserUser = insertIntoTable($db, 'tbluser', $userData);

	if($inserUser['insertID']){
	    $data['Guid_user'] = $inserUser['insertID'];
	    //insert sales rep
	    $insert = insertIntoTable($db, 'tblsalesrep', $data);
	    Leave(SITE_URL.'/salesreps.php?insert' );
	}
    }
}
//$salesreps = $db->selectAll('tblsalesrep');
$accounts = $db->selectAll('tblaccount');
$tblproviders = $db->selectAll('tblprovider');
$states = $db->selectAll('tblstates');
$salesreps = $db->query("SELECT srep.*, u.`status` FROM tblsalesrep srep LEFT JOIN `tbluser` u ON srep.`Guid_user`=u.`Guid_user` WHERE u.`status`='1'");

require_once ('navbar.php');
?>

<main class="full-width">
    <?php if($dataViewAccess) {?>
    <?php
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
		    <li><a href="<?php echo SITE_URL; ?>/salesreps.php">Genetic Consultant</a></li>
		    <li class="active">
			Edit Genetic Consultant
		    </li>
		</ol>
	    <?php } elseif(isset($_GET['action']) && $_GET['action']=='add') { ?>
		<ol class="breadcrumb">
		    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
		    <li><a href="<?php echo SITE_URL; ?>/salesreps.php">Genetic Consultant</a></li>
		    <li class="active">
			Add New Genetic Consultant
		    </li>
		</ol>
	    <?php    } else { ?>
		<ol class="breadcrumb">
		    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
		    <li>Genetic Consultant</li>
		</ol>
	    <?php  } ?>
	    </h4>
	    <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
	    <a href="<?php echo SITE_URL; ?>/dashboard2.php" class="button homeIcon"></a>
	    <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
	</section>

	<div id="app_data" class="scroller">
	    <?php
	    if( isset($_GET['action']) && $_GET['action'] !='' ){
		if($_GET['action'] =='edit'){
		    $where = array('Guid_salesrep'=>$_GET['id']);
		    $salesrepRow = getTableRow($db, 'tblsalesrep', $where);
		    if(empty($salesrepRow)){
			Leave(SITE_URL."/salesreps.php");
		    }
		    extract($salesrepRow);
		    $photo = $photo_filename;
		    $labelClass="";
		}else{
		    $Guid_salesrep='';
		    $first_name = '';
		    $last_name = '';
		    $email = '';
		    $photo = '';
		    $photo_filename = "";
		    $phone_number = "";
		    $title = '';
		    $region = '';
		    $address = '';
		    $city ='';
		    $state='';
		    $zip='';
		    $color = '';
		}
	    ?>

	    <div class="row">
		<div class="col-md-8 col-md-offset-2">
		    <form method="POST" enctype="multipart/form-data">
			<div class="row pB-30">
			    <div class="col-md-8">
				<button name="manage_salesrep" type="submit" class="btn-inline">Save</button>
				<button type="button" class="btn-inline btn-cancel" onclick="goBack()">Cancel</button>
			    </div>
			    <div class="col-md-4 padd-0">
				<div class="status_chart">
				    <div class="row">
					<div class="col-md-6">
					    Registered
					    <span class="pull-right"><?php echo getSalesrepStatusCount($db, $Guid_salesrep, '28' ); //28->Registered ?></span>
					</div>
					<div class="col-md-6">
					    Completed
					    <span class="pull-right"><?php echo getSalesrepStatusCount($db, $Guid_salesrep, '36'); //36->Questionnaire Completed ?></span>
					</div>
				    </div>
				    <div class="row">
					<div class="col-md-6">
					    Qualified
					    <span class="pull-right"><?php echo getSalesrepStatusCount($db, $Guid_salesrep, '29'); //29->Questionnaire Completed->Qualified ?></span>
					</div>
					<div class="col-md-6">
					    Submitted
					    <span class="pull-right"><?php echo getSalesrepStatusCount($db, $Guid_salesrep, '1' ); //28->Submitted (Specimen Collected) ?></span>
					</div>
				    </div>
				</div>
			    </div>
			    <div class="col-md-12 text-center">
				<span class="error" id="message"></span>
			    </div>
			</div>
			<div class="row">
			    <div class="col-md-6">
				<input type="hidden" name="Guid_salesrep" value="<?php echo $Guid_salesrep; ?>" />
				<?php if(isFieldVisibleByRole($isFNameView, $roleID)) {?>
				<div class="f2 required <?php echo ($first_name!="") ? "valid show-label" : ""; ?>">
				    <label class="dynamic" for="first_name"><span>First Name</span></label>
				    <div class="group">
					<input id="first_name" name="first_name" type="text" value="<?php echo $first_name; ?>" placeholder="First Name" required="">
					<p class="f_status">
					    <span class="status_icons"><strong>*</strong></span>
					</p>
				    </div>
				</div>
				<?php } ?>
				<?php if(isFieldVisibleByRole($isLNameView, $roleID)) {?>
				<div class="f2 required <?php echo ($last_name!="") ? "valid show-label" : ""; ?>">
				    <label class="dynamic" for="last_name"><span>Last Name</span></label>
				    <div class="group">
					<input id="last_name" name="last_name" type="text" value="<?php echo $last_name; ?>" placeholder="Last Name" required="">
					<p class="f_status">
					    <span class="status_icons"><strong>*</strong></span>
					</p>
				    </div>
				</div>
				<?php } ?>
				<?php if(isFieldVisibleByRole($isEmailView, $roleID)) {?>
				<div class="f2 required <?php echo ($email!="") ? "valid show-label" : ""; ?>">
				    <label class="dynamic" for="email"><span>Email</span></label>
				    <div class="group">
					<input id="email" name="email" type="email" value="<?php echo $email; ?>" placeholder="Email" required="">
					<p class="f_status">
					    <span class="status_icons"><strong>*</strong></span>
					</p>
				    </div>
				</div>
				<?php } ?>
				<?php if(isFieldVisibleByRole($isTitleView, $roleID)) {?>
				<div class="f2 <?php echo ($title!="") ? "valid show-label" : ""; ?>">
				    <label class="dynamic" for="title"><span>Title</span></label>
				    <div class="group">
					<input id="title" name="title" type="text" value="<?php echo $title; ?>" placeholder="Title">
					<p class="f_status">
					    <span class="status_icons"><strong></strong></span>
					</p>
				    </div>
				</div>
				<?php } ?>
				<?php if(isFieldVisibleByRole($isPhotoView, $roleID)) {?>
				<div class="form-group">
				    <div class="row">
					<div class="col-md-9">
					    <div class="f2 <?php echo ($photo_filename!="") ? "valid show-label" : ""; ?>">
						<label class="dynamic" for="address"><span>Photo</span></label>
						<div class="group">
						    <input id="file" class="form-control pT-5" type="file" name="photo_filename" value="<?php echo $photo_filename; ?>" />
						    <p class="f_status">
							<span class="status_icons"><strong></strong></span>
						    </p>
						</div>
					    </div>
					</div>
					<div class="col-md-3 text-center pT-20">
					<?php

					    if($photo != ""){
						$photo = SITE_URL."/images/users/".$photo;
					    } else {
						$photo =  SITE_URL."/assets/images/default.png";
					    }

					?>
					    <img id="image" width="40" src="<?php echo $photo; ?>" >
					</div>
				    </div>
				</div>
				<?php } ?>
				<?php if(isFieldVisibleByRole($isColorView, $roleID)) {?>
				<div class="form-group">
				    <div class="row">
					<div class="col-md-12 padd-0">
					    <label>Select Event Color</label>
					    <div class="colorBox">
						<?php
						    $colorsArr = array('f99d1b','16a44a','3869b3', 'd4c038', 'c13f95', '3ec5cd', '9dbd1d','FF8170','9490FF','f599ff','d8d8d8','FFDAB1','9cffb1','D9BB93','C2E7F2','ea7898' );
						    $getSelectedColors = $db->query("SELECT color FROM tblsalesrep WHERE color<>'' ");
						    $disableColors = array();
						    foreach ($getSelectedColors as $k=>$v){
							$disableColors[] = $v['color'];
						    }
						    $disabled = '';
						    foreach ($colorsArr as $k=>$v){
							$selected = ($color=='#'.$v)?' checked':'';
							$disabled = (in_array('#'.$v, $disableColors)&&($color!='#'.$v)) ? ' disabled' : '';
						?>
						    <div class="item <?php echo $disabled; ?>">
							<input <?php echo $selected.' '.$disabled; ?> id="<?php echo $v; ?>" type="radio" name="color" value="#<?php echo $v; ?>" />
							<label style="background:#<?php echo $v; ?>;" for="<?php echo $v; ?>">#<?php echo $v; ?></label>
						    </div>
						<?php } ?>
					    </div>
					</div>
				    </div>
				</div>
				<?php } ?>


			    </div>
			    <div class="col-md-6">
				<?php if(isFieldVisibleByRole($isAddressView, $roleID)) {?>
				 <div class="f2 <?php echo ($address!="") ? "valid show-label" : ""; ?>">
				    <label class="dynamic" for="address"><span>Address</span></label>
				    <div class="group">
					<input id="address" name="address" type="text" value="<?php echo $address; ?>" placeholder="Address">
					<p class="f_status">
					    <span class="status_icons"><strong></strong></span>
					</p>
				    </div>
				</div>
				<?php } ?>
				<?php if(isFieldVisibleByRole($isRegionView, $roleID)) {?>
				 <div class="f2 <?php echo ($region!="") ? "valid show-label" : ""; ?>">
				    <label class="dynamic" for="region"><span>Region</span></label>
				    <div class="group">
					<input id="region" name="region" type="text" value="<?php echo $region; ?>" placeholder="Region">
					<p class="f_status">
					    <span class="status_icons"><strong></strong></span>
					</p>
				    </div>
				</div>
				<?php } ?>
				<?php if(isFieldVisibleByRole($isCityView, $roleID)) {?>
				<div class="f2 <?php echo ($city!="") ? "valid show-label" : ""; ?>">
				    <label class="dynamic" for="city"><span>City</span></label>
				    <div class="group">
					<input id="city" name="city" type="text" value="<?php echo $city; ?>" placeholder="City">
					<p class="f_status">
					    <span class="status_icons"><strong></strong></span>
					</p>
				    </div>
				</div>
				<?php } ?>
				<div class="row">
				    <?php if(isFieldVisibleByRole($isStateView, $roleID)) {?>
				    <div class="col-md-6">
					<div class="f2 <?php echo ($state!="") ? "valid show-label" : ""; ?>">
					    <label class="dynamic" for="state"><span>State</span></label>
					    <div class="group">
						<select name="state" class="no-selection">
						    <option value="">State</option>
						    <?php foreach ($states as $k => $v) { ?>
						    <?php $selected = ($state==$v['stateCode'])? " selected" : ""; ?>
							<option <?php echo $selected; ?> value="<?php echo $v['stateCode']; ?>"><?php echo $v['stateName']; ?></option>
						    <?php  } ?>
						</select>
						<p class="f_status">
						    <span class="status_icons"><strong></strong></span>
						</p>
					    </div>
					</div>
				    </div>
				    <?php } ?>
				    <?php if(isFieldVisibleByRole($isZipView, $roleID)) {?>
				    <div class="col-md-6">
				       <div class="f2 <?php echo ($zip!="") ? "valid show-label" : ""; ?>">
					    <label class="dynamic" for="zip"><span>Zip</span></label>
					    <div class="group">
						<input class="zip" id="zip" name="zip" type="text" value="<?php echo $zip; ?>" placeholder="Zip">
						<p class="f_status">
						    <span class="status_icons"><strong></strong></span>
						</p>
					    </div>
					</div>
				    </div>
				    <?php } ?>
				</div>
				<?php if(isFieldVisibleByRole($isPhoneView, $roleID)) {?>
				<div class="f2 required <?php echo ($phone_number!="") ? "valid show-label" : ""; ?>">
				    <label class="dynamic" for="phone_number"><span>Phone</span></label>
				    <div class="group">
					<input class="phone_us" id="phone_number" name="phone_number" type="text" value="<?php echo $phone_number; ?>" placeholder="Phone" required="">
					<p class="f_status">
					    <span class="status_icons"><strong>*</strong></span>
					</p>
				    </div>
				</div>
				<?php } ?>
			    </div>
			</div>

		   </form>
		</div>
	    </div>

	    <?php } else { ?>
	    <div class="row">
		<?php if(isFieldVisibleByRole($isActionAdd, $roleID)) {?>
		<div class="col-md-12">
		    <a class="add-new-device" href="<?php echo SITE_URL; ?>/salesreps.php?action=add">
			<span class="fas fa-plus-circle"></span> Add
		    </a>
		</div>
		<?php } ?>
	    </div>
	    <div class="row">
		<div class="col-md-12">
		    <table id="dataTable" class="table">
			<thead>
			    <tr>
				<?php if(isFieldVisibleByRole($isFNameView, $roleID) || isFieldVisibleByRole($isLNameView, $roleID)) {?>
				    <th>Name</th>
				<?php } ?>
				<?php if(isFieldVisibleByRole($isEmailView, $roleID)) {?>
				    <th class="">Email</th>
				<?php } ?>
				<?php if(isFieldVisibleByRole($isTitleView, $roleID)) {?>
				    <th class="">Title</th>
				<?php } ?>
				<?php if(isFieldVisibleByRole($isCityView, $roleID)) {?>
				    <th class="">City</th>
				<?php } ?>
				<?php if(isFieldVisibleByRole($isStateView, $roleID)) {?>
				    <th class="">State</th>
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
			    //$accountsInfo = getAccountAndSalesrep($db);
			    foreach ($salesreps as $k => $v) {
			    ?>
				<tr>
				    <?php if(isFieldVisibleByRole($isFNameView, $roleID) || isFieldVisibleByRole($isLNameView, $roleID)) {?>
				    <td class="clickable">
					<a class="details"><?php echo $v['first_name']." ".$v['last_name']; ?></a>
					 <div class="moreInfo">
					    <div class="content">
						<span class="close">X</span>

						<?php if(isFieldVisibleByRole($isPhotoView, $roleID)) {?>
						    <p class="">
							<?php $photo = ($v['photo_filename'] != "") ? "/images/users/".$v['photo_filename'] : "/assets/images/default.png";  ?>
							<img width="40" src="<?php echo SITE_URL.$photo; ?>" />
						    </p>
						<?php } ?>
						<?php if(isFieldVisibleByRole($isAddressView, $roleID)) {?>
						    <?php if($v['address']!=""){ ?>
							<p><label>Address: </label><?php echo $v['address']; ?></p>
						    <?php } ?>
						<?php } ?>
						<?php if(isFieldVisibleByRole($isRegionView, $roleID)) {?>
						    <?php if($v['region']!=""){ ?>
							<p><label>Region: </label><?php echo $v['region']; ?></p>
						    <?php } ?>
						<?php } ?>
						<?php if(isFieldVisibleByRole($isZipView, $roleID)) {?>
						    <?php if($v['zip']!=""){ ?>
							<p><label>Zip: </label><?php echo $v['zip']; ?></p>
						    <?php } ?>
						<?php } ?>
						<?php if(isFieldVisibleByRole($isPhoneView, $roleID)) {?>
						    <?php if($v['phone_number']!=""){ ?>
							<p><label>Phone: </label><span class="phone_us"><?php echo $v['phone_number']; ?></span></p>
						    <?php } ?>
						<?php } ?>

						<?php if( isFieldVisibleByRole($isActionEdit, $roleID) || isFieldVisibleByRole($isActionDelete, $roleID)) {?>
						    <div class="text-right pT-15 pB-10">
							<?php if( isFieldVisibleByRole($isActionEdit, $roleID) ) {?>
							<a href="<?php echo SITE_URL; ?>/salesreps.php?action=edit&id=<?php echo $v['Guid_salesrep']; ?>">
							    <span class="fas fa-pencil-alt"></span>
							</a>
							<?php } ?>
							<?php if(isFieldVisibleByRole($isActionDelete, $roleID)) {?>
							<a onclick="javascript:confirmationDeleteSalesReps($(this));return false;" href="<?php echo SITE_URL; ?>/salesreps.php?delete=<?php echo $v['Guid_salesrep'] ?>&id=<?php echo $v['Guid_salesrep']; ?>&user=<?php echo $v['Guid_user']; ?>">
							    <span class="far fa-trash-alt"></span>
							</a>
							<?php } ?>
						    </div>
						<?php } ?>
					    </div>
					 </div>

				    </td>
				    <?php } ?>
				    <?php if(isFieldVisibleByRole($isEmailView, $roleID)) {?>
					<td><?php echo $v['email']; ?></td>
				    <?php } ?>
				    <?php if(isFieldVisibleByRole($isTitleView, $roleID)) {?>
					<td><?php echo $v['title']; ?></td>
				    <?php } ?>
				    <?php if(isFieldVisibleByRole($isCityView, $roleID)) {?>
					<td><?php echo $v['city']; ?></td>
				    <?php } ?>
				    <?php if(isFieldVisibleByRole($isStateView, $roleID)) {?>
					<td><?php echo $v['state']; ?></td>
				    <?php } ?>
				    <td><?php echo getSalesrepStatusCount($db, $v['Guid_salesrep'], '28' ); //28->Registered ?></td>
				    <td><?php echo getSalesrepStatusCount($db, $v['Guid_salesrep'], '36'); //36->Questionnaire Completed ?></td>
				    <td><?php echo getSalesrepStatusCount($db, $v['Guid_salesrep'], '29'); //29->Questionnaire Completed->Qualified ?></td>
				    <td><?php echo getSalesrepStatusCount($db, $v['Guid_salesrep'], '1' ); //28->Submitted (Specimen Collected) ?></td>
				</tr>
			    <?php
				$i++;
			    }
			    ?>
			</tbody>
		    </table>
		</div>
	    </div>
	    <?php } ?>
	</div>
    </div>
    <?php } else { ?>
	<div class="box full visible ">
	    <h4> Sorry, You Don't have Access to this page content. </h4>
	</div>
    <?php }  ?>
</main>

<?php require_once('scripts.php');?>


<script type="text/javascript">

    var table = $('#dataTable');
    if(table){
	table.DataTable({
	    orderCellsTop: true,
	    fixedHeader: true,
	    //searching: false,
	    lengthChange: false
	});
    }

</script>

<?php require_once('footer.php');?>