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


if(isset($_GET['delete-config']) && $_GET['delete-config'] != '' ){
    deleteUrlConfig($db, $_GET['delete-config']);
    Leave(SITE_URL.'/url-configuration.php');
}

$userID = getThisUserID();
$urlConfgs = getUrlConfigurations($db, $userID);

if ($role == "Sales Rep"){
    $query = "SELECT
    tblaccount.*
    FROM tblsalesrep
    LEFT JOIN `tblaccountrep` ON  tblsalesrep.Guid_salesrep = tblaccountrep.Guid_salesrep
    LEFT JOIN `tblaccount` ON tblaccountrep.Guid_account = tblaccount.Guid_account
    WHERE tblsalesrep.Guid_user=".$_SESSION['user']['id'];

} else {
    $query = "SELECT * FROM tblaccount";
}
$query .= " ORDER BY account";
$accounts = $db->query($query);

$sources = $db->selectAll('tblsource', ' ORDER BY description ASC');


$lastConfigData = getLastUrlConfig($db);
$urlData = $lastConfigData;
$currentUserId = $_SESSION['user']['id'];
$urlMain = "https://www.mdlab.com/questionnaire";
$urlPrev = "https://www.mdlab.com/previous";
$urlStr = "";
$generateUrlLink = $urlMain;
$isValid = TRUE;
$accountMessage=""; $dvMessage="";
if (isset($_POST['generate_url_config']) && $_POST['generate_url_config']=='1'){

    extract($_POST);

    //$reqLocations = array('D', 'F', 'L', 'DE');
    $reqLocations = array('D', 'F', 'L', 'DE');
    if($role=='Sales Rep' && $dv=='' && in_array($lc, $reqLocations)){
	$isValid = FALSE;
	$generateUrlLink = "";
	$dvMessage = "<div class='error-text'>Please select device.</div>";
    }
    $accountNumber = $_POST['an'];
    $hasAccountProviders = $db->query("SELECT Guid_provider FROM tblprovider WHERE account_id=:account_id", array('account_id'=>$accountNumber));
    if($lc!="F"){
	if(empty($hasAccountProviders)){
	    $isValid = FALSE;
	    $generateUrlLink = "";
	    $accountMessage = "<div class='error-text'>Selected account doesn't have providers.</div>";
	}
    }
    if(isset($_POST['previous']) && $_POST['previous'] != ""){
	$generateUrlLink = $urlPrev;
    }

    if( $isValid ){
	$urlStr .= ($logo=='gen') ? 'co=gen&' : '';
	if($ln =='pin'){
	   $urlStr .= 'ln=pin&';
	}elseif ($ln =='np') {
	    $urlStr .= 'ln=np&';
	}
       //$urlStr .= ($ln =='pin') ? 'ln=pin&' : '';
       $urlStr .= ($an!=''&&$an!=0) ? 'an='.$an.'&' : '';
       $urlStr .= ($lc!=''&&$lc!='0') ? 'lc='.$lc.'&' : '';
       $urlStr .= ($dv!=''&&$dv!=0) ? 'dv='.$dv.'&' : '';

       $urlStr =  rtrim($urlStr,"&");

       $getAccountId = get_field_value($db, 'tblaccount', 'Guid_account', " WHERE account=$an");

       if ($urlStr != ''){

	    if($ln =='pin'){
	       $lnVal = 1; //for pin
	    } elseif ($ln =='np') {
		$lnVal = 2; //for no email and pass
	    } else{
		$lnVal = 0; // for pass
	    }
	    $data = array(
		'currentUserId'=>$currentUserId,
		'geneveda' => ($logo=='gen') ? 1 : 0,
		'account' => ($an!=''&&$an!=0) ? $getAccountId['Guid_account'] : '',
		'location' => ($lc!=''&&$lc!='0') ? $lc : 'W',
		'pin' => $lnVal,
		'device_id' => ($dv!=''&&$dv!=0) ? $dv : ''
	    );

	    $checkSettings = validateSettings($db, $data);

	    if($checkSettings=='1'){
		$saveSettings = saveUrlSettings($db, $data);
		if($saveSettings && $saveSettings['status']=='1'){
		    $generateUrlLink .= '/?'.$urlStr;
		}
	    }else{
		$generateUrlLink .= '/?'.$urlStr;
	    }
	}
    } /** isValid */
}
$accountProviders = '';
?>


<?php require_once 'navbar.php'; ?>
<main class="full-width">
    <?php if($role != 'Physician' &&  $role != 'Patient'){ ?>

    <div class="box full visible">
	<section id="palette_top">
	    <h4>URL Configuration</h4>
	    <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
	    <a href="<?php echo SITE_URL; ?>/dashboard2.php" class="button homeIcon"></a>
	    <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
	</section>

	<div id="app_data" class="scroller">

	    <div class="url_config_box ">

	<div class="row">
	  <div class="col-md-5">
	    <h4>Use Previous Settings</h4>

	    <table class="table prev_settings">

		<thead class="thead-dark">
		    <tr>
			<th></th>
			<th>#</th>
			<th>Logo</th>
			<th>Account</th>
			<th>Location</th>
			<th>Login</th>
			<th>Action</th>
		    </tr>
		</thead>

	    <?php
	    if (isset($_POST['generate_url_config']) && $_POST['generate_url_config']=='1'){
		$userID = getThisUserID();
		$urlConfgs = getUrlConfigurations($db, $userID);
	    }
		$i = 1;
		foreach ($urlConfgs as $k=>$v){
		    $cntlogo = ($v['geneveda']==0) ? 'MDL': 'Geneveda';
		    $cntAccount = $v['account']." - ".ucwords(strtolower($v['name']));
		    $cntDesc = $v['description'];
		    if($v['pin'] == '1'){
			$cntPin = "Pin";
		    } elseif ($v['pin'] == '2') {
			$cntPin = "None";
		    } else {
			$cntPin = "Pass";
		    }
		    //$cntPin = $v['pin'] ? "Pin" : 'Pass';
	    ?>
		<tr>
		    <td>
			<input type="radio" id="<?php echo $v['id']; ?>" class="url_config" name="url_config" >
		    </td>
		    <td ><?php echo $i; ?></td>
		    <td><?php echo $cntlogo;?></td>
		    <td><?php echo ($cntAccount && $cntAccount !='-') ? $cntAccount : "N/A";?></td>
		    <td><?php echo $cntDesc ? $cntDesc : "";?></td>
		    <td><?php echo $cntPin; ?> </td>

		    <td class="text-center">
			<a onclick="javascript:confirmationDelete($(this));return false;" href="?delete-config=<?php echo $v['id']?>&id=<?php echo $i; ?>">
			    <span class="far fa-trash-alt"></span>
			</a>
		    </td>
		</tr>

	    <?php
		$i++;
		}
	    ?>


	    </table>
	  </div>
	  <div class="col-md-7">

	    <div class="row">
	      <div class="col-md-6">
		  <div class="row">
		      <div class="col-md-12">
			  <h4>Select Settings</h4>
		      </div>
		  </div>

		<form method="POST" id="url-config-settings">
		  <fieldset>
		    <div class="row">
			<div class="col-md-6">
			    <div class="form-group">
			      <label class="genevedaLogo">
				  <input id="geneveda-logo" name="logo" value="gen" type="radio" checked="" >
				  <span class="image" ></span>
			      </label>
			    </div>
			    <div class="form-group">
				<label class="mdlLogo">
				    <input id="mdl-logo" name="logo" value="" type="radio" <?php echo (isset($_POST['logo']) && $_POST['logo'] != 'gen') ? 'checked' : '';?>  >
				    <span class="image"></span>
				</label>
			    </div>
			</div>
			<div class="col-md-6">
			    <p class="pB-10">
				<label>Version</label>
			    </p>
			    <p>
				<label class="current">
				<?php
				    $isCurrentSelected = "";
				    $isPrevSelected = "";
				       if( !isset($_POST['previous']) || (isset($_POST['previous']) && $_POST['previous'] == '') ) {
					   $isCurrentSelected = "checked";
					   $isPrevSelected = "";
				       } else {
					   $isPrevSelected = "checked";
					   $isCurrentSelected ="";
				       }
				       ?>

				    <input id="current" name="previous" value="" type="radio" <?php echo $isCurrentSelected; ?>  >
				    Current
				</label>
			    </p>
			    <p>
				<label class="previous">
				    <input id="previous" name="previous" value="1" type="radio" <?php echo $isPrevSelected;?>  >
				    Previous
				</label>
			    </p>

			</div>
		    </div>
		      <div class="row">
			  <div class="col-md-12">
				<div class="form-group ">
				    <select name="an" id="account" class="form-control <?php if(!$isValid && $accountMessage!="") { echo 'error-border'; }?>">
					<option value="0">Account</option>
				     <?php
					 foreach ($accounts as $k=>$v){
					     $isAccount = ( isset($an) && ($an==$v['account']) ) ? ' selected' : '';  ?>
					 <option <?php echo $isAccount; ?> value="<?php echo $v['account'];?>" ><?php echo $v['account']." - ".ucwords(strtolower($v['name']));?></option>
				     <?php } ?>
				   </select>
				 </div>
				 <div class="form-group">
				    <select name="lc" id="location" class="form-control">
				       <option value="0">Originating Location</option>
				     <?php
					 foreach ($sources as $k=>$v){
					 $isLocation = ( isset($lc) && $lc==$v['code']) ? ' selected' : '';

				     ?>
					 <option <?php echo $isLocation; ?> value="<?php echo $v['code'];?>" ><?php echo $v['description']; ?></option>
				     <?php } ?>
				   </select>
				 </div>
				 <div class="form-group">
				   <label>
				       <input type="radio" name="ln" id="pin" value="pin" checked >
					 Pin
				   </label>
				 </div>
				 <div class="form-group">
				   <label>
				     <input type="radio" name="ln" id="pass" value="" <?php echo (isset($ln) && $ln=='')? " checked" : ""; ?> >
				     Password
				   </label>
				 </div>
				 <div class="form-group">
				   <label>
				     <input type="radio" name="ln" id="noemail" value="np" <?php echo (isset($ln) && $ln=='np')? " checked" : ""; ?> >
				     No Email and Password
				   </label>
				 </div>
				  <?php if($role=='Admin' || $role=='Sales Manager'){ ?>
				  <?php

				   ?>
				  <div class="form-group">
				      <select name="dv" id="deviceId" class="form-control">
					  <option value="">Device ID</option>

					    <?php
					    $devices = getDevicesWithSalesRepInfo($db, '1');
					    foreach ($devices as $k=>$v){
						$isDevice = ( isset($_POST['dv']) && $_POST['dv'] == $v['id']) ? ' selected' : '';
					    ?>
					    <option <?php echo $isDevice; ?> value="<?php echo $v['id'];?>" ><?php echo $v['first_name']." ".$v['last_name']." - ".$v['device_name']." - ".$v['serial_number'];?></option>

					<?php } ?>
				    </select>

				  </div>
				  <?php } elseif($role=='Sales Rep') {

				      ?>
				    <div class="form-group">
					<select name="dv" id="deviceId" class="form-control <?php if(!$isValid && $dvMessage!="") { echo 'error-border'; }?>">
					    <option value="">Device ID</option>
					    <?php
					    $thisSalesRep = get_field_value($db, 'tblsalesrep', 'Guid_salesrep', ' WHERE Guid_user='.$userID);
					    $devices = getDevicesBySalesrepID($db, $thisSalesRep['Guid_salesrep']);
					    foreach ($devices as $k=>$v){
						$isDevice = ( isset($_POST['dv']) && $_POST['dv'] == $v['id']) ? ' selected' : '';
					    ?>

					    <option <?php echo $isDevice; ?> value="<?php echo $v['id'];?>" ><?php echo $v['serial_number']." - ".$v['device_name'];?></option>
					   <?php } ?>

					</select>
				    </div>
				    <?php if(!$isValid && $dvMessage!="") { echo $dvMessage; }?>
				  <?php } ?>

				    <?php if(!$isValid && $accountMessage!="") { echo $accountMessage; }?>
				 <div class="text-center">
				    <button name="generate_url_config" value="1" type="submit" class="btn btn-info">Generate URL</button>
				 </div>
			  </div>
		      </div>



		     </fieldset>
	      </div>
	      <div class="col-md-6">
		  <div class="row">
		      <div class="col-md-4">
			  <p id="officeLogo">
			      <?php
				if( isset($_POST['an']) && $_POST['an'] != "0"){
				    $thisAccountId = $_POST['an'];
				    $thisAccount = get_field_value($db, 'tblaccount', 'account', " WHERE account=$thisAccountId");
				    $accountActive = getAcount($db, $thisAccount['account']);
				    $accountActive = $accountActive[0];
				    if($accountActive!=''){
					$activeAccountLogo = ($accountActive['logo']!="") ? "/../images/practice/".$accountActive['logo']:"/assets/images/default.png";
					echo "<img src='". SITE_URL.$activeAccountLogo."' />";
				    }
				}
			      ?>
			  </p>
		      </div>
		      <div class="col-md-8">
			      <?php
				$addressInfo = "";
				$accountInfo = "";
				if(isset($accountActive) && $accountActive!=''){
				    extract($accountActive);
				    $accountInfo = $account." ".ucwords(strtolower($name));

				    if($address || $city){
					$addressInfo .= "<div>";
					if($address){
					    $addressInfo .= $address."<br/>";
					}
					if($city){
					   $addressInfo .= $city;
					   if($state || $zip){
					       $addressInfo .= ", ";
					   }
					}
					if($state){
					    $addressInfo .= $state;
					}
					if($zip){
					    $addressInfo .= " " . $zip;
					}
					$addressInfo .= "</div>";
				    }
				    if($phone_number){
					$addressInfo .= "<div><i class='fas fa-phone'></i> <a class='phone_us' href='tel:".$phone_number."'>".$phone_number."</a></div>";
				    }
				    if($fax){
					$addressInfo .= "<div><i class='fas fa-fax'></i> <a class='phone_us' href='tel:".$fax."'>".$fax."</a></div>";
				    }
				    if($website){
					$addressInfo .= "<div><i class='fas fa-globe'></i> <a target='_blank' href='".$website."'>".$website."</a></div>";
				    }
				}
			      ?>
			    <h5 id="officeAddressLabel" class="addressTitle">
				<?php  echo $accountInfo; ?>
			    </h5>
			    <div id="officeAddress">
				<?php  echo $addressInfo; ?>
			    </div>
		      </div>
		  </div>
		  <h5 id="physiciansListLabel">
		      <?php  if(isset($accountActive) && $accountActive!=''){ echo '<p class="providersTitle">Health Care Providers</p>'; }?>
		  </h5>
		  <div id="physiciansList">
		    <?php
			if(isset($accountActive) && $accountActive!=''){
			    $accountProviders = get_active_providers($db, 'account_id', $thisAccount['account']);
			    if($accountProviders !=''){
				foreach ($accountProviders as $k=>$v){
				    echo "<p>".$v['first_name']." ".$v['last_name'].", ".$v['title'].'</p>';
				}
			    }
			}
		    ?>
		  </div>
	      </div>
	    </div>

	  </form>
	  </div>

	</div>

	<div class="row">
	      <div class="col-md-5"></div>
	      <div class="col-md-7">
		  <br/>
		  <?php if(isset($_POST['generate_url_config']) && $generateUrlLink!=''){ ?>
		  <a class="pL-30" id="urlLink" target="_blank" href="<?php echo $generateUrlLink; ?>">
		    Link:
		    <?php echo $generateUrlLink; ?>
		  </a>
		  <?php } ?>
	      </div>
	  </div>
    </div>


	</div>
    </div>

    <?php } else { ?>
	<div class="box full visible ">
	    <h4> Sorry, You Don't have Access to this page content. </h4>
	</div>
    <?php } ?>
</main>

<?php require_once 'scripts.php';?>
<?php require_once 'footer.php';?>