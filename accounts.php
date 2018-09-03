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
$accessRole = getAccessRoleByKey('account');
$roleIDs = unserialize($accessRole['role_ids']);
$dataViewAccess = isUserHasAnyAccess($roleIDs, $roleID, 'view');


$accounts = $db->selectAll('tblaccount', ' ORDER BY `account` ASC');
$tblproviders = $db->selectAll('tblprovider');

if(isset($_GET['account_id'])){
    $thisAccountID = $_GET['account_id'];
}else{
   $thisAccountID = $accounts[0]['Guid_account'];
}
$accountInfo = getAccountAndSalesrep($db, $thisAccountID);
$accountActive = $accountInfo['0'];
extract($accountActive);



if (isset($_GET['delete']) && $_GET['delete'] != '') {
   deleteRowByField($db, 'tblprovider', array('Guid_provider'=>$_GET['delete']));
   Leave(SITE_URL.'/accounts.php?account_id='.$_GET['account-id']);
}
if( isset($_POST['cancel_manage_provider'])){
    Leave(SITE_URL.'/accounts.php?account_id='.$_GET['account_id']);
}
if( isset($_POST['manage_provider'])){
    extract($_POST);
    $data = array(
	'title' => $title,
	'first_name' => $first_name,
	'last_name' => $last_name
    );
    if($provider_id!=""){
	$data['provider_id']=$provider_id;
    }
    if($_FILES["photo_filename"]["name"] != ""){
	$fileName = $_FILES["photo_filename"]["name"];
	$data['photo_filename'] = $fileName;
	$uploadMsg = uploadFile('photo_filename', 'images/users/');
    }

    if($Guid_provider != '' && $Guid_provider != 'add'){
	//update provider data
	$where = array("Guid_provider"=>$Guid_provider);
	$msg['success'] = "Account Provider updated!";
	$msg['error'] = "Account Provider update Issue.";
	$providerDataArray = array(
			'action'=>'update',
			'account_id'=>$_POST['account_id'],
			'Guid_provider'=>$_POST['Guid_provider']
	    );

	$isProviderValid = validateProviderId($db, $providerDataArray);

	if($isProviderValid['status']==1){
	    if($Guid_user == ""){ //insert User
		$userData['user_type'] = 'provider';
		$userData['Date_created'] = date('Y-m-d H:i:s');
		$inserUser = insertIntoTable($db, 'tbluser', $userData);
		$data['Guid_user']= $inserUser['insertID'];
	    }else{ //update
		$userData['Date_modified'] = date('Y-m-d H:i:s');
		$whereUser = array('Guid_user'=>$Guid_user);
		$updateUser = updateTable($db, 'tbluser', $userData, $whereUser);
	    }
	    $update = updateTable($db, 'tblprovider', $data, $where, $msg );
	    Leave(SITE_URL.'/accounts.php?account_id='.$_GET['account_id']);
	} else {
	    $message = $isProviderValid['msg'];
	}
    } else {
	//insert new user in tblusers table
	//insert sales rep user
	$userData['user_type'] = 'provider';
	$userData['Date_created'] = date('Y-m-d H:i:s');
	$inserUser = insertIntoTable($db, 'tbluser', $userData);

	if($inserUser['insertID']){
	    $data['Guid_user']= $inserUser['insertID'];
	    $inserRole = insertIntoTable($db, 'tbluserrole', array('Guid_user'=>$inserUser['insertID'], 'Guid_role'=>'2'));
	}
	if($inserRole['insertID']){
	    //insert new provider for this account
	    $msg['success'] = "Account New Provider inserted!";
	    $msg['error'] = "Account New Provider insert Issue.";
	    $data['account_id'] = $accountActive['account'];

	    $providerDataArray['action'] = 'insert';
	    $providerDataArray['provider_id']=$_POST['provider_id'];

	    $isProviderValid = validateProviderId($db, $providerDataArray);
	    if($isProviderValid['status']==1){
		$insert = insertIntoTable($db, 'tblprovider', $data, $msg);
		Leave(SITE_URL.'/accounts.php?account_id='.$_GET['account_id']);
	    } else {
		$message = $isProviderValid['msg'];
	    }
	}
    }
}

$providerBoxClass = "hide";
if(isset($_GET['provider_guid']) && $_GET['provider_guid']!="" && $_GET['provider_guid']!="add"){
    $providerBoxClass = "show";
    $provider_guid =  $_GET['provider_guid'];
    $providerInfo = get_provider_user_info($db, $provider_guid);
    $Guid_provider = $providerInfo["Guid_provider"];
    $Guid_user = $providerInfo["Guid_user"];
    $provider_id = $providerInfo["provider_id"];
    $provider_account_id = $providerInfo["account_id"];
    $provider_first_name = $providerInfo["first_name"];
    $provider_last_name = $providerInfo["last_name"];
    $provider_title = $providerInfo["title"];
    $provider_photo_filename = $providerInfo["photo_filename"];
    $providerTitleTxt = "Update Provider";
    $labelClass="";
} elseif (!isset($_GET['provider_guid']) || $_GET['provider_guid']=="") {
    $labelClass = "";
    $providerBoxClass = "hide";
    $Guid_provider = "";
    $Guid_user = "";
    $provider_id = "";
    $provider_account_id = "";
    $provider_first_name = "";
    $provider_last_name = "";
    $provider_title = "";
    $provider_photo_filename = "";
    $providerTitleTxt = "Add Provider";
} else {
    $labelClass = "";
    $providerBoxClass = "show";
    $Guid_provider = "";
    $Guid_user = "";
    $provider_id = "";
    $provider_account_id = "";
    $provider_first_name = "";
    $provider_last_name = "";
    $provider_title = "";
    $provider_photo_filename = "";
    $providerTitleTxt = "Add Provider";
}
?>
<?php require_once 'navbar.php'; ?>

<main class="full-width">
    <?php if($dataViewAccess) { ?>
	<div class="box full visible">
	<section id="palette_top">
	    <h4>
	    <ol class="breadcrumb">
		<li><a href="<?php echo SITE_URL; ?>">Home</a></li>
		<li><a href="<?php echo SITE_URL; ?>/account-config.php">Accounts</a></li>
		<li class="active">
		    Edit Account
		</li>
	    </ol>
	    </h4>
	    <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
	    <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
	</section>
	<div id="app_data" class="scroller">
	    <div id="accounts">
		<div class="row">
		    <div class="col-md-8">
		    <div class="selectAccountBlock">
			<label >Select Account</label><br/>
			<select class="form-control" id="selectAccount">
			    <?php
			    $accountInfo = "";
			    $i=0;
			    foreach ($accounts as $k=>$v){
				$selected = (isset($_GET['account_id'])&&$_GET['account_id']==$v['Guid_account']) ? " selected='selected'" : "";
			    $i++;
			    ?>
			    <option <?php echo $selected; ?> data-guid="<?php echo $v['Guid_account']; ?>" value="<?php echo $v['account']; ?>"><?php echo $v['account']." - ".ucwords(strtolower($v['name'])); ?></option>
			    <?php  } ?>
			</select>

			<a href="<?php echo SITE_URL;?>/account-config.php?action=edit&id=<?php echo $accountActive['Guid_account']; ?>" id="edit-selected-account" class="add-new-account">
			    <span class="fas fa-pencil-alt" aria-hidden="true"></span>
			</a>
		    </div>

		    <div class="providersTable">
			<h4 id="physiciansListLabel" class="accounts">
			    Physicians
			    <a href="<?php echo SITE_URL;?>/accounts.php?account_id=<?php echo $thisAccountID;?>&provider_guid=add" class="pull-right" id="add-account-provider">
				<span class="fas fa-plus-circle" aria-hidden="true"></span>  Add
			    </a>
			</h4>

			<table class="table providersTable">
			    <thead>
				<tr>
				    <th>UPIN</th>
				    <th>Title</th>
				    <th class="">First Name</th>
				    <th class="">Last Name</th>
				    <th class="">Image</th>
				    <th class="noFilter actions text-center">Actions</th>
				</tr>
			    </thead>
			    <tbody>
			    <?php
				$accountProviders = get_active_providers($db, 'account_id', $account);
				if($accountProviders !=''){
				    foreach ($accountProviders as $k=>$v){
					$providerGuid=$v['Guid_provider'];
				?>
				<tr>
				    <td><?php echo $v['provider_id']; ?></td>
				    <td><?php echo $v['title']; ?></td>
				    <td><?php echo $v['first_name']; ?></td>
				    <td><?php echo $v['last_name']; ?></td>
				    <td>
					<?php $photoImg = ($v['photo_filename']=="")? "/assets/images/default.png" : "/images/users/".$v['photo_filename'];   ?>
					<img width="40" src="<?php echo SITE_URL.$photoImg; ?>" />
				    </td>
				    <td class="text-center">
					<!--<a class="edit-provider" data-provider-guid="<?php echo $v['Guid_provider']; ?>">-->
					<a href="<?php echo SITE_URL."/accounts.php?account_id=$Guid_account&provider_guid=$providerGuid"; ?>">
					    <span class="fas fa-pencil-alt" aria-hidden="true"></span>
					</a>
					<a onclick="javascript:confirmationDeleteProvider($(this));return false;" href="?delete=<?php echo $providerGuid ?>&provider-id=<?php echo $v['provider_id']; ?>&account-id=<?php echo $Guid_account; ?>">
					    <span class="far fa-trash-alt" aria-hidden="true"></span>
					</a>
				    </td>
				</tr>
				<?php } ?>
			    <?php } ?>
			    </tbody>
			</table>
		    </div>  <!-- /.providersTable -->
		</div>
		    <div class="col-md-4 pL-50">
		    <div id="officeLogo">
			<?php $logo = $logo ? "/../images/practice/".$logo : "/assets/images/default.png"; ?>
			<img class="salesrepLogo" src="<?php echo SITE_URL.$logo; ?>" />
		    </div>
		    <div class="addressInfoBlock">
			<label >Address</label>
			<div id="officeAddress">
			    <div>
				<?php
				if($address){
				    echo $address."<br/>";
				    if($city !=""){ echo $city.", "; }
				    if($state !=""){ echo $state." "; }
				    if($zip !="" ){ echo $zip ."<br/>"; }
				}
				?>
			    </div>
			    <?php if($phone_number) { ?>
				<div><i class="fas fa-phone"></i> <a class="phone_us" href="tel:<?php echo $phone_number; ?>"><?php echo $phone_number; ?></a></div>
			    <?php } ?>
			    <?php if($fax) { ?>
				<div><i class="fas fa-fax"></i> <a class="phone_us" href="tel:<?php echo $fax; ?>"><?php echo $fax; ?></a></div>
			    <?php } ?>
			    <?php if($website) { ?>
				<div><i class="fas fa-globe"></i> <a target="_blank" href="<?php echo $website; ?>"><?php echo $website; ?></a></div>
			    <?php } ?>
			</div>
		    </div>
		    <div class="salesrepInfoBlock">
			<label >Genetic Consultant</label>
			<div class="imageBox">
			    <div class="pic">
				<?php $salesrepPhoto = isset($salesrepPhoto) ? "/images/users/".$salesrepPhoto : "/assets/images/default.png"?>
				<img width="50" class="salesrepProfilePic" src="<?php echo SITE_URL.$salesrepPhoto; ?>" />
			    </div>
			    <div class="name text-center">
				<?php echo $salesrepFName." ".$salesrepLName; ?>
			    </div>
			</div>

			<div id="salesrepInfo1">
			    <?php
			    if($salesrepAddress){
				echo $salesrepRegion."<br/>".$salesrepAddress.", <br/>".$salesrepCity.", ".$salesrepState." ".$salesrepZip."<br/>";
			    }
			    ?>
			    <?php if($salesrepEmail) { ?>
				<div><i class="fas fa-envelope"></i> <a href="mailto:<?php echo $salesrepEmail; ?>"><?php echo $salesrepEmail; ?></a></div>
			    <?php } ?>
			    <?php if($salesrepPhone) { ?>
				<div><i class="fas fa-phone"></i> <a class="phone_us" href="tel:<?php echo $salesrepPhone; ?>"><?php echo $salesrepPhone; ?></a></div>
			    <?php } ?>
			</div>
		    </div>
		</div>
		</div> <!-- /.row -->
	    </div>
	</div><!-- /. mainContent-->
    </div> <!-- /. full box visible-->
    <?php } else { ?>
	<div class="box full visible ">
	    <h4> Sorry, You Don't have Access to this page content. </h4>
	</div>
    <?php } ?>
</main>


<div id="add-account-provider-box" class="modalBlock <?php echo $providerBoxClass; ?>">
    <div class="contentBlock">
	<?php if(isset($message)){ ?>
	<div class="error-text"><?php echo $message; ?></div>
	<?php } ?>
	<h5 class="providersTitle"><?php echo $providerTitleTxt; ?></h5>

	<?php
	    if( isset($uploadMsg) && !empty($uploadMsg)){
		if($uploadMsg['status'] == 0){
		    echo "<div class='error-text'>".$uploadMsg['msg']."</div>";
		}
	    }
	?>
	<form method="POST" enctype="multipart/form-data">

	    <input type="hidden" value="<?php echo $Guid_provider; ?>" name="Guid_provider" class="form-control" value=""/>
	    <input type="hidden" value="<?php echo $provider_id; ?>"  name="provider_id" class="form-control" value=""/>
	    <input type="hidden" value="<?php echo $provider_account_id; ?>" name="account_id" class="form-control" value=""/>
	    <input type="hidden" value="<?php echo $Guid_user; ?>" name="Guid_user" class="form-control" value=""/>

	    <div class="f2 <?php echo ($provider_id!="")?"valid":"";?>">
		<label class="dynamic" for="provider_id"><span>UPIN</span></label>
		<div class="group">
		    <input autocomplete="off" id="provider_id" name="provider_id" type="text" value="<?php echo $provider_id; ?>" placeholder="UPIN">
		    <p class="f_status">
			<span class="status_icons"><strong></strong></span>
		    </p>
		</div>
	    </div>
	    <div class="f2 <?php echo ($provider_title!="")?"valid":"";?>">
		<label class="dynamic" for="title"><span>Title</span></label>
		<div class="group">
		    <input autocomplete="off" id="title" name="title" type="text" value="<?php echo $provider_title; ?>" placeholder="Title">
		    <p class="f_status">
			<span class="status_icons"><strong></strong></span>
		    </p>
		</div>
	    </div>
	    <div class="f2 required <?php echo ($provider_first_name!="")?"valid":"";?>">
		<label class="dynamic" for="name"><span>First Name</span></label>

		<div class="group">
		    <input autocomplete="off" id="first_name" name="first_name" type="text" value="<?php echo $provider_first_name; ?>" placeholder="First Name" required="">
		    <p class="f_status">
			<span class="status_icons"><strong>*</strong></span>
		    </p>
		</div>
	    </div>
	    <div class="f2 required <?php echo ($provider_last_name!="")?"valid":"";?>">
		<label class="dynamic" for="name"><span>Last Name</span></label>
		<div class="group">
		    <input autocomplete="off" id="last_name" name="last_name" type="text" value="<?php echo $provider_last_name; ?>" placeholder="Last Name" required="">
		    <p class="f_status">
			<span class="status_icons"><strong>*</strong></span>
		    </p>
		</div>
	    </div>

	    <div class="form-group">
		<div class="row">
		    <div class="col-md-10">
			<div class="f2 <?php echo ($provider_photo_filename!="")?"valid":"";?>">
			    <label class="dynamic" for="photo"><span>Photo</span></label>
			    <div class="group">
				<input id="file" value="<?php echo $provider_photo_filename; ?>" name="photo_filename" class="form-control pT-5" type="file" placeholder="Photo"/>
				<p class="f_status">
				    <span class="status_icons"><strong>*</strong></span>
				</p>
			    </div>
			</div>
		    </div>
		    <?php $providerImg = ($provider_photo_filename=="")?"/assets/images/default.png":"/images/users/".$provider_photo_filename; ?>
		    <div id="profile-pic" class="col-md-2 pT-30">
			<img id="image" width="40" src="<?php echo SITE_URL.$providerImg; ?>" />
		    </div>
		</div>
	    </div>


	    <div class="">
		<button class="btn-inline" name="manage_provider" type="submit" >Save</button>
		<button onclick="goBack();" type="button" class="btn-inline btn-cancel">Cancel</button>

		<!--<a class="btn-inline btn-cancel" href="<?php echo SITE_URL."/accounts.php?account_id=".$_GET['account_id'];?>">Cancel</a>-->
	    </div>
	</form>

    </div>
</div>

<?php require_once('scripts.php');?>
<?php require_once('footer.php');?>