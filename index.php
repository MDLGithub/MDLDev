<?php
//require "database.php";

//$db = Database::getInstance();
//$mysqli = $db->getConnection(); 
//$sql_query = "SELECT * FROM tblqualify";
//$result = $mysqli->query($sql_query);

//if ($_SERVER["HTTPS"] != "on") {
//    header("Location: http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
 //   exit();
//}
require_once('config.php');

if ((ENV == "Live") && ($_SERVER["HTTPS"] != "on")) {
    header('Location: ' . HTTPS_SERVER);
    exit();
}

session_start();

require ("db/dbconnect.php");

$result = $conn->query("SELECT value FROM tblqsetting s WHERE s.key=\"session_timeout\"");
			
$session_timeout = $result->fetch_assoc();

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > (60 * $session_timeout['value']))) {	
    session_unset();   
    session_destroy();	
	header('Location: ' . HTTPS_SERVER);
	exit;
}

$_SESSION['LAST_ACTIVITY'] = time();

$error=array();

if ((isset($_POST['save'])) || (isset($_POST['nc_validated']) && ($_POST['nc_validated'] == "1")) || (isset($_POST['fl_validated']) && ($_POST['fl_validated'] == "1")) || (isset($_POST['exit_app']))) {	
	if (isset($_POST['fl_validated']) && ($_POST['fl_validated'] == "1") && ($_GET['ln'] != "np")) {
		$content = '
			<p>You can still finish!</p>
			<p>You have recently used our questionnaire to determine if you meet <strong>clinical guidelines for hereditary cancer genetic testing</strong> but have selected to finish it later.</p>
			<p>Your progress in the questionnaire has been saved and you can continue at any time by clicking on the link below.</p>
			<a href="' . HTTPS_SERVER . '?continue=Yes"" style="color:#973737"><strong>Complete the questionnaire</strong></a>';
		
		$title = "Should I Be Screened";
		
		send_email($content, $title);
	}
	if ((isset($_POST['save'])) || (isset($_POST['fl_validated']) && ($_POST['fl_validated'] == "1")) || (isset($_POST['exit_app']))) {
		save_input();
	}
	
	session_unset(); 

	session_destroy(); 
	
	$url = HTTPS_HOST . $_SERVER['REQUEST_URI'];
	
	header('Location: ' . $url);
	exit;
}
if (isset($_POST['get_estimate'])) {
	header('Location: https://www.mdlab.com/brca/estimate/');
	exit;
}

//generate_outer_top();

if (empty($_POST)) {
	if (isset($_GET['lc']) && ($_GET['lc'] == "DE")) {
		perform_login();
		
		$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);
			
		$qualify = $result->fetch_assoc();
	
		generate_insurance();
		
		exit;
	} else {
		session_unset(); 
	
		generate_splash();
	}
} elseif ((!isset($_POST['account_login'])) && (!isset($_SESSION['id']))) {
	generate_email($error);
} elseif (isset($_POST['back'])) {
	$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);
			
	$qualify = $result->fetch_assoc();

	delete_saved();
	
	if ($qualify['insurance'] == "Medicare") {
		$func = "generate_cancer_list_personal";
	} else {
		$func = "generate_".$_POST['prev_step'];
	}
					
	$func($error);
} elseif (isset($_POST['cancel_finish'])) {
	$func = "generate_".$_POST['current_step'];
					
	$func($error);
} elseif (isset($_POST['resubmit'])) {
	$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);
			
	$qualify = $result->fetch_assoc();
	
	delete_saved();
	
	$func = "generate_insurance";
					
	$func($error);
} elseif (isset($_POST['start'])) {
	generate_email($error);
} else {
	verify_input($error, $not_qualified, $type);

	if (isset($_POST['account_login'])) {
		 if (count($error)) {			 
			 generate_email($error);
		 } elseif (!isset($_POST['returning_user'])) {
			perform_login($error);
		 }
	} 

	$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);

	$qualify = $result->fetch_assoc();
	
	$_SESSION["id"] = $qualify['Guid_qualify'];
		
	if (isset($_POST['change_pin_to_pass'])) {
		update_password($error);
		
		if (count($error)) {
			generate_password($error);
		}  else {
			if (isset($_GET['continue']) &&(isset($_GET['lc']) && (($_GET['lc'] == "PMR") || ($_GET['lc'] == "FR")))) {
				$qualification_text=array();
		
				determine_qualification($qualification_text);
				
				display_qualification($qualification_text, $not_qualified);
			} else {
				generate_insurance($error);
			}
		}
	} elseif (count($error)) {
		$func = "generate_".$_POST['current_step'];
		$func($error);		
	} elseif ((isset($_POST['account_login'])) && (isset($_POST['returning_user'])) && (strlen($_POST['account_password']) == 4)) {
		generate_password($error);				
	} elseif (($not_qualified) && ($type == "personal")) {
		save_input();
		
		generate_cancer_list_family($error, $not_qualified);
	} elseif (((isset($_POST['family_cancer'])) && (in_array("No Cancer/None of the Above", $_POST['family_cancer']))) ||
			  (($qualify['insurance'] == "Medicare") && (isset($_POST['personal_cancer'])) && (in_array("No Cancer/None of the Above", $_POST['personal_cancer'])))) {
		save_input();
		
		$result = $conn->query("SELECT * FROM tblunknownans WHERE Guid_qualify = " . $_SESSION['id']);
		$num_unknown = mysqli_num_rows($result);
		
		if ($qualify['gene_mutation'] == "Unknown") {			
			$date = new DateTime("now", new DateTimeZone('America/New_York'));
			$sql = "INSERT INTO tblunknowngene (Guid_qualify, date_created) VALUES(" . $_SESSION['id'] . ",'" . $date->format('Y-m-d H:i:s') . "')";			
			$conn->query($sql);
			
			$num_unknown += 1;
		}
		
		$qualification_text=array();
		
		array_push($qualification_text, "BRCA-Related Breast and/or Ovarian Cancer Syndrome");
		
		if ($num_unknown) {
			generate_unknown_screen($qualification_text);
		} else {
			display_qualification($qualification_text, "1");
		}
	} elseif (isset($_POST['type']) && ($_POST['type'] == "personal") && (in_array("No Cancer/None of the Above", $_POST['personal_cancer']))) {
		save_input();
		
		generate_cancer_list_family($error, $not_qualified);
	} elseif (isset($_POST['medicare_yes'])) {
		save_input();
		
		$qualification_text=array();
		
		array_push($qualification_text, "BRCA-Related Breast and/or Ovarian Cancer Syndrome");
			
		display_qualification($qualification_text, "1");
	} elseif (($qualify['insurance'] == "Aetna") && ($qualify['gender'] == "Male") && (isset($_POST['personal_cancer'])) && (in_array("No Cancer/None of the Above", $_POST['personal_cancer']))) {	
		save_input();
		
		$qualification_text=array();
		
		array_push($qualification_text, "BRCA-Related Breast and/or Ovarian Cancer Syndrome");
			
		display_qualification($qualification_text, "1");
	} else {		
		save_input();
		
		if (isset($_POST['insurance']) && ($_POST['insurance'] == "Aetna")) {
			$result = $conn->query("SELECT dob FROM tblpatient WHERE Guid_user = " . $qualify['Guid_user']);
				 
			$patient = $result->fetch_assoc();	
			
			$diff = abs(strtotime(date("Y-m-d")) - strtotime($patient['dob']));

			$age = floor($diff / (365*60*60*24));
		
			if ($age <= 18) {
				save_input();
		
				$qualification_text=array();
			
				array_push($qualification_text, "BRCA-Related Breast and/or Ovarian Cancer Syndrome");
				
				display_qualification($qualification_text, "1");
			
				exit;
			}
		}
		
		if (isset($_POST['next_step']) && ($_POST['next_step'] == "additional_summary")) {
			$qualification_text=array();
		
			determine_qualification($qualification_text);
			
			$func = "generate_" . $_POST['next_step'];
			$func($error, $not_qualified, $qualification_text);
			exit;
		}
		 
		if (isset($_POST['additional_relatives']) && ($_POST['additional_relatives'] == "Yes")) {
			verify_additional_info($error);
			
			$func = "generate_additional_screen";
			$func($error, $_POST['not_qualified'], $_POST['qualification_text']);
			exit;			 
		}
		
		if (isset($_POST['additional_relatives'])) {			
			display_qualification($_POST['qualification_text'], $_POST['not_qualified']);
			exit;
		}
		
		$qualification_text=array();
		
		determine_qualification($qualification_text);
	
		if (count($qualification_text)) {
			$result = $conn->query("SELECT * FROM tblunknownans WHERE Guid_qualify = " . $_SESSION['id']);
			$num_unknown = mysqli_num_rows($result);
		
			if (($qualify['gene_mutation'] == "Unknown") && ($not_qualified)) {
				$date = new DateTime("now", new DateTimeZone('America/New_York'));
				$sql = "INSERT INTO tblunknowngene (Guid_qualify, date_created) VALUES(" . $_SESSION['id'] . ",'" . $date->format('Y-m-d H:i:s') . "')";
				$conn->query($sql);
				$num_unknown += 1;
			}
			
			if (($not_qualified) && ($num_unknown)) {
				generate_unknown_screen($qualification_text);
			} else {
				if ($num_unknown) {
					$conn->query("DELETE FROM tblunknownans WHERE Guid_qualify=" . $_SESSION['id']);	
					$conn->query("DELETE FROM tblunknowngene WHERE Guid_qualify=" . $_SESSION['id']);	
				}
				display_qualification($qualification_text, $not_qualified);
			}
		} else {
			$sql = "UPDATE tblqualify SET finish_later = \"0\" WHERE Guid_qualify = " . $_SESSION['id'];

			$conn->query($sql);		

			if (isset($_GET['continue'])) {
				$func = get_function();
			} elseif (strlen($_POST['next_step'])) {				
				$result = $conn->query("SELECT * FROM tblunknownans WHERE Guid_qualify =" . $_SESSION['id']);			
				$num_unknownans = mysqli_num_rows($result);

				$result = $conn->query("SELECT * FROM tblunknowngene WHERE Guid_qualify = " . $_SESSION['id']);
				$num_unknowngene = mysqli_num_rows($result);

				if ($num_unknowngene) {
					generate_gene_mutation($error, $not_qualified);				
				} elseif ($num_unknownans) {
					generate_cancer_detail($error, $not_qualified);				
				} else {
					$func = "generate_" . $_POST['next_step'];
				}
			} else {				
				$func = "generate_splash";
			}
			
			$func($error);
		} 
	}
	require ("db/dbdisconnect.php");
}

function get_CBR_box() {
    $CBR_box = '
	    <div class="more_info cbr">
		    <strong class="ico_info">Close Blood Relatives</strong>
			<section>
				<h4><strong>Relatives chosen must be on the same side of the family and can include those that are living, deceased and/or had cancer in the past</strong>.</h4>
			</section>
			
		    <p><strong>First-degree relatives:</strong>father, mother, brother, sister, son, daughter</p>
			<p><strong>Second-degree relatives:</strong>grandfather, grandmother, aunts, uncles, nieces, nephews, grandson, granddaughter, half-brothers, half-sisters</p>
			<p><strong>Third-degree relatives:</strong>great-grandfather, great-grandmother, great-aunts, great-uncles, great-grandson, great-granddaughter, first cousins</p>
						
			<section>
				<h4>If a relative does not appear on the drop-down list, they are not considered to increase your cancer risk.</h4>
			</section>
	    </div>
	';
	return $CBR_box;
}

function get_profile(){
	require_once ("db/dbconnect.php");
	global $conn;
	global $qualify;
	
	$patientTable = $conn->query("SELECT AES_DECRYPT(firstname_enc, 'F1rstn@m3@_%') as firstname FROM tblpatient WHERE Guid_user = " . $qualify['Guid_user']);

    $profile = $patientTable->fetch_assoc();
	
	return $profile['firstname'];
}


function generate_outer_top($result_unknown=0) {	
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
        <meta name="viewport" content="width=device-width" />
		<title>BRCAcare | Should I Be Screened?</title>
		<link href="https://fonts.googleapis.com/css?family=Questrial" rel="stylesheet">
        <link href="style.min.css" rel="stylesheet" type="text/css">
<!--[if lt IE 9]>
        <script src="js/html5shiv.js"></script>
<![endif]-->
    </head>
    <body>
		<form action="" method="post" id="app_wrap"<?php echo ($result_unknown ? "class=\"unknown\"" : ""); ?>>
<?php
}
function generate_header($title, $result_unknown=0) {
	generate_outer_top($result_unknown);
?>
			<header id="app_head">
				<section id="app_title">
<?php
	if ($_GET['co'] == "gen") {
?>
					<figure class="app_logo_geneveda"><img src="images/logo_geneveda.png" alt="Geneveda"></figure>					
<?php
	} else {
?>
					<figure id="app_logo_mdl"><img src="../dev2/wp-content/themes/oceanreef/images/logo.png" alt="MDL"></figure>
<?php
	}
	
	if (isset($_GET['an'])) {
		require ("db/dbconnect.php");
			
		$result = $conn->query("SELECT * FROM tblaccount WHERE account  = '" . $conn->real_escape_string(intval($_GET['an'])) . "'");
		
		if (mysqli_num_rows($result)) {
			$account = $result->fetch_assoc();		
			if (strlen($account['logo'])) {
?>
					<span class="divide"></span>
					<figure id="practice_logo" class="preload"><img data-src="images/practice/<?php echo $account['logo']; ?>" alt=""></figure>
<?php
			} else {
?>                  
 					<span class="divide"></span>
					<figure id="app_logo_brccare"><img class="brccare" src="../framework/images/logo_brcacare.png" alt="BRCAcare"></figure>
				    <h2 class="sfont">Should I Be Screened?</h2>	
				                 
					<!--<h2 id="practice"><?php //echo $account['name']; ?></h2>-->
<?php
			}
		} else {
?>                  
 					<span class="divide"></span>
					<figure id="app_logo_brccare"><img class="brccare" src="../framework/images/logo_brcacare.png" alt="BRCAcare"></figure>
				    <h2 class="sfont">Should I Be Screened?</h2>	
				                 
					<!--<h2 id="practice"><?php //echo $account['name']; ?></h2>-->
<?php
		}
	} else {
?>                  
 					<span class="divide"></span>
					<figure id="app_logo_brccare"><img class="brccare" src="../framework/images/logo_brcacare.png" alt="BRCAcare"></figure>
				    <h2 class="sfont">Should I Be Screened?</h2>	
				                 
					<!--<h2 id="practice"><?php //echo $account['name']; ?></h2>-->
<?php
	}
?>	
                </section>
				
				<section class="unknown_req">
				    <h4><span>Please fill in all unknown information</span></h4>
				</section>
				
				<button type="button" id="exit_app" name="exit_app" class="toggle" data-on="#logout_app_modal"><strong>Exit</strong></button>

<?php if (isset($_SESSION['id'])) { ?>
	                
				<section id="profile">
					<h4>Hi, <span class="blue"><?php echo get_profile(); ?></span></h4>
			        <p>Not you? Please exit.</p>
		        </section>
<?php } ?>
				
			    <h1><span><?php echo $title; ?></span></h1>
			</header>
			
			<main>
<?php
}
function generate_login() {	
	generate_header("Please login");
	
	if (isset($error['login'])) {
?>
				<div class="display_msg error">
					<span class="msg_type"></span>
					<p>login/password invalid</p>
				</div>
<?php
	}
?>
				<div class="field med<?php echo (isset($error['account_login']) ? " error" : ""); ?>">
					<div class="iwrap">
						<label for="account_login">Login</label>
						<input type="text" id="account_login" name="account_login" class="if" value="" required>
						<icon class="icon istatus"><span class="required"></span></icon>
					</div>
				</div>
				
				<div class="field med<?php echo (isset($error['login_password']) ? " error" : ""); ?>">
					<div class="iwrap">
						<label for="login_password">Password</label>
						<input type="password" id="login_password" name="login_password" class="if" value="" required>
						<icon class="icon istatus"><span class="required"></span></icon>
					</div>
				</div>
<?php
	$buttons = array("Login"=>"login");
	
	generate_outer_bottom($error, $buttons);
}
function generate_splash() {
	generate_outer_top();
?>
			<input type="hidden" name="next_step" value="email">
			<div id="splash">
			    <div id="splash_corner">
				    <strong>Created by</strong>
<?php
	if ($_GET['co'] == "gen") {
?>
					<figure class="app_logo_geneveda preload"><img data-src="images/logo_geneveda.png" alt="Geneveda"></figure>					
<?php
	} else {
?>
					<figure class="app_logo_mdl preload"><img data-src="../dev2/wp-content/themes/oceanreef/images/logo.png" alt="MDL"></figure>
<?php
	}
?>
				</div>
	
				<section id="start">
<?php	
	if (isset($_GET['an'])) {
		require ("db/dbconnect.php");
			
		$result = $conn->query("SELECT * FROM tblaccount WHERE account  = '" . $conn->real_escape_string(intval($_GET['an'])) . "'");
		
		if (mysqli_num_rows($result)) {
			$account = $result->fetch_assoc();		
			if (strlen($account['logo'])) {				
?>				
					<figure id="practice_logo" class="preload"><img data-src="images/practice/<?php echo $account['logo']; ?>" alt="The Lexington Fertility Center"></figure>
<?php
			} else {				
?>                  
					<figure class="app_logo_brccare preload"><img class="brccare" data-src="../framework/images/logo_brcacare.png" alt="BRCAcare"></figure>				    
				    <h1 class="sfont">Should I Be Screened?</h1>                    
<?php
			}
		} else {				
?>                  
					<figure class="app_logo_brccare preload"><img class="brccare" data-src="../framework/images/logo_brcacare.png" alt="BRCAcare"></figure>				    
				    <h1 class="sfont">Should I Be Screened?</h1>                    
<?php
		}
	} else {
?>                  
					<figure class="app_logo_brccare preload"><img class="brccare" data-src="../framework/images/logo_brcacare.png" alt="BRCAcare"></figure>				    
				    <h1 class="sfont">Should I Be Screened?</h1>                    
<?php
	}
?>					
					<div class="info_box">
					    <p>Use this questionnaire to determine if you meet clinical guidelines for hereditary cancer genetic testing.</p>
						
						<p>You and your healthcare provider may use this information to further evaluate your hereditary cancer risk and determine which test is right for you.</p>
					</div>
					
					<button type="submit" id="start_btn" name="start"><strong>Start</strong></button>
				</section>
				
				<figure id="brca_woman" class="preload"><img data-src="../framework/images/banner_brcacare_fg.png" alt=""></figure>
			</div>
<?php
	$buttons = array("Start"=>"start");

	generate_outer_bottom($error, $buttons);
}
function generate_email($error) {
	if (isset($_GET['continue'])) {	
	   generate_header("Please login to complete your questionnaire.");
	} else {
	   generate_header("Welcome! Please create an account.");
	}
	if (count($error)) {
?>
				<div class="display_msg error">
					<span class="msg_type"></span>
					<div class="emsg_box">
<?php
		if (isset($error['dob'])) {
?>
						<p>Date of Birth is not valid</p>
<?php
		} elseif (isset($error['account_email_exists'])) {
?>
						<p>An account with this email exists</p>
<?php
		} else {
?>
						<p>Login credentials are not valid</p>
<?php
		}
?>
					</div>
				</div>
<?php
	}
?>
				<input type="hidden" name="next_step" value="insurance">
				<input type="hidden" name="current_step" value="email">
				<div class="toggle_group">
<?php
	if (isset($_GET['lc']) && ($_GET['lc'] == "PM")) {
?>
					<div class="field med<?php echo ((!isset($_POST['salutation'])) ? "" : (isset($error['salutation']) ? " error" : " valid")); ?> tg1<?php echo (isset($_POST['returning_user']) ? " off" : ""); ?>">
						<div class="iwrap">
							<label for="salutation">Salutation</label>
							<select id="salutation" name="salutation" class="if selectF" required>
								<option value="Ms.">Ms.</option>
								<option value="Mr.">Mr.</option>
							</select>
							<icon class="icon istatus"><span class="required"></span></icon>
						</div>
					</div>
<?php
	}
	if (!isset($_GET['continue'])) { 
?>
					<div class="field med<?php echo ((!isset($_POST['first_name'])) ? "" : (isset($error['first_name']) ? " error" : " valid")); ?> tg1<?php echo (isset($_POST['returning_user']) ? " off" : ""); ?>">
						<div class="iwrap">
							<label for="first_name">First Name</label>
							<input type="text" id="first_name" name="first_name" class="if" value="<?php echo (isset($_POST['first_name']) ? $_POST['first_name'] : ""); ?>" required>
							<icon class="icon istatus"><span class="required"></span></icon>
						</div>
					</div>
					
					<div class="field med<?php echo ((!isset($_POST['last_name'])) ? "" : (isset($error['last_name']) ? " error" : " valid")); ?> tg1<?php echo (isset($_POST['returning_user']) ? " off" : ""); ?>">
						<div class="iwrap">
							<label for="last_name">Last Name</label>
							<input type="text" id="last_name" name="last_name" class="if" value="<?php echo (isset($_POST['last_name']) ? $_POST['last_name'] : ""); ?>" required>
							<icon class="icon istatus"><span class="required"></span></icon>
						</div>
					</div>
<?php
	}
	if ((isset($_GET['ln']) && ($_GET['ln'] == "np")) || (isset($_GET['lc']) && ($_GET['lc'] == "O"))) {
	} else {
?>
					<div class="field med<?php echo ((isset($error['account_email']) || isset($error['account_email_exists'])) ? " error" : ""); ?>">
					    <div class="iwrap">
						    <label for="account_email">Email</label>
						    <input type="email" id="account_email" name="account_email" class="sameP if" value="<?php echo (isset($_POST['account_email']) ? $_POST['account_email'] : ""); ?>" required>
						    <icon class="icon istatus"><span class="required"></span></icon>
					    </div>
				    </div>
<?php
		if (!isset($_GET['continue'])) {
?>	
					<div class="field med<?php echo (isset($error['account_email_confirm']) ? " error" : ""); ?> tg1<?php echo (isset($_POST['returning_user']) ? " off" : ""); ?>">
					    <div class="iwrap">
						    <label for="account_email_confirm">Confirm Email</label>
						    <input type="email" id="account_email_confirm" name="account_email_confirm" class="same if" value="" required>
						    <icon class="icon istatus"><span class="required"></span></icon>
					    </div>
				    </div>
<?php
		}
		
		if (isset($_GET['ln']) && ($_GET['ln'] == "pin")) {
			$text = "Pin";
			$pattern = "^[0-9]{4}$";
			$data_reqs = "be 4 digits";
		} else {		
			$text = "Password";
			$pattern = "(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$";
			$data_reqs = "Be at least 8 characters,Include an uppercase letter,Include a number";
		}
		if (isset($_GET['lc']) && ($_GET['lc'] == "PM")) {
		} else {
?>
					<div class="field med<?php echo (isset($error['account_password']) ? " error" : ""); ?>">
					    <div class="iwrap">
						    <label for="account_password"><?php echo $text; ?></label>
						    <input type="password" id="account_password" name="account_password" class="sameP if" value="" required data-reqs="<?php echo $data_reqs; ?>" pattern="<?php echo $pattern; ?>">
						    <icon class="icon istatus"><span class="required"></span></icon>
					    </div>
				    </div>
<?php
		}
		if (isset($_GET['lc']) && ($_GET['lc'] == "PM")) {
		} elseif (!isset($_GET['continue'])) {
?>					
					<div class="field med<?php echo (isset($error['confirm_password']) ? " error" : ""); ?> tg1<?php echo (isset($_POST['returning_user']) ? " off" : ""); ?>">
					    <div class="iwrap">
						    <label for="confirm_password">Confirm <?php echo $text; ?></label>
						    <input type="password" id="confirm_password" name="confirm_password" class="same if" value="" required data-reqs="<?php echo $data_reqs; ?>" pattern="<?php echo $pattern; ?>">
						    <icon class="icon istatus"><span class="required"></span></icon>
					    </div>
				    </div>
<?php
		}
	}
	if (isset($_GET['continue']) &&(isset($_GET['lc']) && (($_GET['lc'] == "PMR") || ($_GET['lc'] == "FR")))) {
	} elseif (isset($_GET['an']) && (strlen($_GET['an']))) {
		require ("db/dbconnect.php");
				
		$result = $conn->query("SELECT * FROM tblaccount WHERE account  = '" . $conn->real_escape_string(intval($_GET['an'])) . "'");
			
		if (mysqli_num_rows($result)) {
			$account = $result->fetch_assoc();
				
			$providers = $conn->query("SELECT Guid_provider, first_name, last_name, title FROM tblprovider WHERE account_id=\"" . $account['account'] . "\"");
		
			if (mysqli_num_rows($providers)) {
?>
					<div class="field med<?php echo ((!isset($_POST['provider'])) ? "" : (isset($error['provider']) ? " error" : " valid")); ?> tg1 EX">
						<div class="iwrap">
							<label for="provider">Select Health Care Provider</label>
							
							<select id="provider" name="provider" class="if selectF" required>
								<option value=""></option>
<?php
				foreach($providers as $provider) {			
?>
								<option value="<?php echo $provider['Guid_provider']; ?>"<?php echo (($provider['Guid_provider'] == $_POST['provider']) ? " selected" : "");?>><?php echo $provider['first_name'] .  " " . $provider['last_name'] . ", " . $provider['title']; ?></option> 
<?php
				}
?>
								<option value="Other">Other</option>
							</select>
							<input type="text" class="efield sel_other" placeholder="Please Provide Name" name="other_provider">
							<icon class="icon istatus"><span class="required"></span></icon>
						</div>
					</div>
<?php
			}
		}
	}
	if (isset($_GET['continue']) &&(isset($_GET['lc']) && (($_GET['lc'] == "PMR") || ($_GET['lc'] == "FR")))) {
	} else {
?>
					<div class="field med<?php echo ((!isset($_POST['dob'])) ? "" : (isset($error['dob']) ? " error" : " valid")); ?> tg1<?php echo (isset($_POST['returning_user']) ? " off" : ""); ?>">
						<div class="iwrap">
							<label for="dob">Date Of Birth</label>
							<input type="date" id="dob" name="dob" class="if" value="<?php echo (isset($_POST['dob']) ? $_POST['dob'] : ""); ?>" required>
							<icon class="icon istatus"><span class="required"></span></icon>
						</div>
					</div>
<?php
	}
	if (isset($_GET['continue']) && isset($_GET['lc']) && (($_GET['lc'] == "PMR") || ($_GET['lc'] == "FR"))) {
?>
		<input type="hidden" name="returning_user" value="Yes">
<?php
	} elseif (((!isset($_GET['ln'])) && (!isset($_GET['lc']))) || (isset($_GET['ln']) && ($_GET['ln'] != "np")) || (isset($_GET['lc']) && ($_GET['lc'] != "O") && ($_GET['lc'] != "PM") && ($_GET['lc'] != "F") && ($_GET['lc'] != "D"))) {	
?>
					<div class="input toggle_box">	
							<input type="checkbox" name="returning_user" value="Yes" id="no_email"<?php echo (isset($_POST['returning_user']) ? " checked" : ""); ?>>
						    <label for="no_email"><strong>I already have an account</strong></label>
					    </div>
					</div>
				</div>
<?php
	}
?>
				<div class="more_info">
					<strong class="ico_info"></strong>
				    <p>Do not use your browser's back or forward buttons to navigate this questionnaire.  Only use the back and next buttons provided.</p>
				</div>
<?php
	$class = array("Forgot Password"=>" forgot", "Finish Later"=>" gold save");

	$buttons = array("Forgot Password"=>"forgot_password", "Login"=>"account_login");
	
	generate_outer_bottom($error, $buttons);
}
function generate_password($error) {	
	generate_header("Please change your pin to a password");	
	
	if (isset($error['account_password'])) {
?>
		<div class="display_msg error">
			<span class="msg_type"></span>
			<div class="emsg_box">
				<p>Passwords do not match</p>
			</div>
		</div>
<?php
	}
?>
	<input type="hidden" name="next_step" value="insurance">
	<input type="hidden" name="current_step" value="password">
	<div class="toggle_group">
		<div class="field med<?php echo (isset($error['account_password']) ? " error" : ""); ?>">
			<div class="iwrap">
				<label for="account_password">Password</label>
				<input type="password" id="account_password" name="account_password" class="sameP if" value="" required data-reqs="Be at least 8 characters,Include an uppercase letter,Include a number" pattern="(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$">
				<icon class="icon istatus"><span class="required"></span></icon>
			</div>
		</div>
	
		<div class="field med<?php echo (isset($error['confirm_password']) ? " error" : ""); ?>">
			<div class="iwrap">
				<label for="confirm_password">Confirm Password</label>
				<input type="password" id="confirm_password" name="confirm_password" class="same if" value="" required data-reqs="Be at least 8 characters,Include an uppercase letter,Include a number" pattern="(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$">
				<icon class="icon istatus"><span class="required"></span></icon>
			</div>
		</div>
	</div>
<?php
	$buttons = array("Create Password"=>"change_pin_to_pass");
	
	generate_outer_bottom($error, $buttons);
}
function generate_insurance($error) {
	require ("db/dbconnect.php");
	
	$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);
	
	$qualify = $result->fetch_assoc();
		
	$insurance = "";
	
	if ((isset($_POST['insurance'])) && (strlen($_POST['insurance']))) {
		$insurance = $_POST['insurance'];
	} elseif (strlen($qualify['insurance'])) {		
		$insurance = $qualify['insurance'];
	}
	
	generate_header("What medical insurance do you have?");
	
	if (isset($error['insurance']) || isset($error['other_insurance'])) {
?>
				<div class="display_msg error">					
					<span class="msg_type"></span>
					<div class="emsg_box">
<?php
		if (isset($error['insurance'])) {
?>
						<p>Choose Insurance</p>
<?php
		} else {
?>
						<p>Enter Name of Insurer</p>
<?php
		}
?>
					</div>
				</div>
<?php
	}
?>
				<input type="hidden" name="prev_step" value="email">
				<input type="hidden" name="next_step" value="gender_aj">
				<input type="hidden" name="current_step" id="current_step" value="insurance">
				<fieldset class="answers">
				    <legend>Insurance</legend>

					<div class="input<?php echo (($insurance == "Aetna") ? " checked" : ""); ?>">
						<span class="radio">
					        <input type="radio" name="insurance" value="Aetna" id="aetna">
					    </span>
                        <label for="aetna">Aetna</label>
					</div>
					
					<div class="input<?php echo (($insurance == "Medicare") ? " checked" : ""); ?>">
						<span class="radio">
					        <input type="radio" name="insurance" value="Medicare" id="medicare">
				        </span>
                        <label for="medicare">Medicare</label>
					</div>
					
					<div class="input toggle<?php echo (($insurance == "Other") ? " checked" : ""); ?>" data-on=".show_input">
						<span class="radio">
					        <input type="radio" name="insurance" value="Other" id="other_ins">
				        </span>
                        <label for="other_ins">Other</label>
						<ul class="extra_input single">
							<li>
								<div class="field sm<?php echo (($insurance == "Other") ? " show" : ""); ?>">
									<div class="iwrap">
										<label for="other_insurance">Provide Insurer</label>
										<input id="other_insurance" type="text" name="other_insurance" class="if" value="<?php echo $qualify['other_insurance']; ?>">
										<icon class="icon istatus"></icon>
									</div>
								</div>
							</li>
						</ul>						
					</div>
				
					<div class="input<?php echo (($insurance == "None") ? " checked" : ""); ?>">
						<span class="radio">
					        <input type="radio" name="insurance" value="None" id="no_ins">
				        </span>
                        <label for="no_ins">None</label>
					</div>				
				</fieldset>
				
				<div class="more_info">
					<strong class="ico_info"></strong>
				    <p>Knowing your medical insurance helps us determine the specific medical policy guidelines that will cover the costs for testing.</p>
				</div>
<?php
	require ("db/dbdisconnect.php");
	if (isset($_GET['lc']) && ($_GET['lc'] == "O")) {
		$buttons = array("Next"=>"next");
		$class = array("Next"=>"");
	} else {
		$buttons = array("Finish Later"=>"save", "Next"=>"next");
		$class = array("Finish Later"=>" gold save", "Next"=>"");
	}
	
	generate_outer_bottom($error, $buttons, $class);
}
function generate_gender_aj($error) {
	require ("db/dbconnect.php");
	
	$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);
	
	$qualify = $result->fetch_assoc();
		
	$gender = "";
	$ashkenazi = "";
	
	if ((isset($_POST['gender'])) && (strlen($_POST['gender']))) {
		$gender = $_POST['gender'];
	} elseif (strlen($qualify['gender'])) {
		$gender = $qualify['gender'];
	}
	
	if ((isset($_POST['ashkenazi'])) && (strlen($_POST['ashkenazi']))) {
		$ashkenazi = $_POST['ashkenazi'];
	} elseif (strlen($qualify['ashkenazi'])) {
		$ashkenazi = $qualify['ashkenazi'];
	}
	
	generate_header("What is your gender?");
	
	if (($qualify['insurance'] == "Other") || ($qualify['insurance'] == "None") || ($qualify['insurance'] == "Aetna")) {
?>
				<input type="hidden" name="next_step" value="gene_mutation">
<?php
	} else {
?>
				<input type="hidden" name="next_step" value="cancer_list_personal">
<?php
	}
?>
				<input type="hidden" name="prev_step" value="insurance">
				<input type="hidden" name="current_step" value="gender_aj">
<?php
	if (isset($error['gender']) || (isset($error['ashkenazi'])) ) {
?>
				<div class="display_msg error">
					<span class="msg_type"></span>
					<div class="emsg_box">
<?php	if (isset($error['gender']) && (isset($error['ashkenazi'])) ) { ?>
						<p>Please answer both questions</p>
<?php
	    } else if (isset($error['gender'])) {
?>
						<p>What is your gender?</p>
<?php
	    } else if (isset($error['ashkenazi'])) {
?>
						<p>Please choose if you are Ashkenazi Jewish</p>
<?php
	    }
?>
					</div>
				</div>
<?php
	}
?>
				<fieldset class="answers alignFix">
				    <legend>Gender</legend>
					
					<div class="input<?php echo (($gender == "Male") ? " checked" : ""); ?>">
						<span class="radio">
					        <input type="radio" name="gender" value="Male" id="male">
					    </span>
                        <label for="male">Male</label>
					</div>
					
					<div class="input<?php echo (($gender == "Female") ? " checked" : ""); ?>">
						<span class="radio">
					        <input type="radio" name="gender" value="Female" id="female">
					    </span>
                        <label for="female">Female</label>
					</div>
				</fieldset>
			
				<section class="sub_input lg">
					<h2>Are you Ashkenazi Jewish?</h2>
					
				    <fieldset class="answers">
				        <legend>Ashkenazi Jewish</legend>
					
					    <div class="input<?php echo (($ashkenazi == "Yes") ? " checked" : ""); ?>">
						    <span class="radio">
					            <input type="radio" name="ashkenazi" value="Yes" id="ashkenazi_yes">
				            </span>
                            <label for="ashkenazi_yes">Yes</label>
					    </div>

					    <div class="input<?php echo (($ashkenazi == "No") ? " checked" : ""); ?>">
						    <span class="radio">
					            <input type="radio" name="ashkenazi" value="No" id="ashkenazi_no">
					        </span>
                            <label for="ashkenazi_no">No/Unknown</label>
					    </div>
					</fieldset>
				</section>
				
				<div class="more_info">
					<strong class="ico_info"></strong>
				    <p>People of Ashkenazi Jewish Heritage (Eastern European descent) have a higher risk of having gene mutations that causes hereditary breast and ovarian cancer.</p>
				</div>
<?php
	require ("db/dbdisconnect.php");
	
	if (isset($_GET['lc']) && ($_GET['lc'] == "O")) {
		$buttons = array("Back"=>"back", "Next"=>"next");	
		$class = array("Back"=>" back neutral sm", "Next"=>"");
	} else {
		$buttons = array("Back"=>"back", "Finish Later"=>"save", "Next"=>"next");	
		$class = array("Back"=>" back neutral sm", "Finish Later"=>" gold save", "Next"=>"");
	}		
	generate_outer_bottom($error, $buttons, $class);
}
function generate_gene_mutation($error) {
	require ("db/dbconnect.php");
	
	$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);
	
	$qualify = $result->fetch_assoc();
	
	$result = $conn->query("SELECT * FROM tblunknowngene WHERE Guid_qualify = " . $_SESSION['id']);
	$num_unknowngene = mysqli_num_rows($result);
	
	if ($num_unknowngene) {
		generate_header("Please provide Gene Mutation details:", $num_unknowngene);
	} elseif (($qualify['insurance'] == "Aetna") || ($qualify['insurance'] == "Medicare")){
		generate_header("Do you have any close blood relatives that tested positive for a mutation in the BRCA1 or BRCA2 genes?");
	} else {
		generate_header("Do you have any close blood relatives that tested positive for a pathogenic/likely pathogenic mutation in BRCA1, BRCA2 and/or another cancer related gene?");
	}
	
	if (isset($error['gene_mutation'])) {
?>
				<div class="display_msg error">
					<span class="msg_type"></span>
					<div class="emsg_box">
						<p>Choose one of the options</p>
					</div>
				</div>
<?php
	}
	if (isset($error['select_gene'])) {
?>
				<div class="display_msg error">
					<span class="msg_type"></span>
					<div class="emsg_box">
						<p>Choose atleast one gene mutation</p>
					</div>
				</div>
<?php
	}
	
	$gm = "";
	
	if ((isset($_POST['gene_mutation'])) && (strlen($_POST['gene_mutation']))) {
		$gm = $_POST['gene_mutation'];
	} elseif (strlen($qualify['gene_mutation'])) {
		$gm = $qualify['gene_mutation'];
	} 
?> 
				<input type="hidden" name="prev_step" value="gender_aJ">
				<input type="hidden" name="next_step" value="cancer_list_personal">
				<input type="hidden" name="current_step" value="gene_mutation">
				<fieldset class="answers full">
				    <legend>BRCA1 or BRCA2 Deleterious Gene Mutation</legend>
					
					<div class="input<?php echo (($gm == "Yes") ? " checked" : ""); ?>">
						<span class="radio">
					        <input type="radio" name="gene_mutation" value="Yes" id="gene_mutation_yes">
					    </span>
                        <label for="gene_mutation_yes">Yes</label>
						
						<ul class="extra_input">
<?php
	$count = 1;
	
	$gene = array();
	$gene_relation = array();
	
	if ((isset($_POST['gene_mutation'])) && ($_POST['gene_mutation'] == "Yes")) {
		$gene_relation = $_POST['gene_relation'];
		$gene = $_POST['gene'];
		$count = count($gene);
	} else {
		$family_genes = $conn->query("SELECT * FROM tblqualifygene WHERE Guid_qualify=" . $_SESSION['id']);
		
		if (mysqli_num_rows($family_genes)) {
			foreach($family_genes as $family_gene) {
				array_push($gene_relation, $family_gene['gene_relation']);
				array_push($gene, $family_gene['gene']);			
				$count = count($gene);
			}
		} else { 
			$count = 1;
		}
	}
	
	$relations = $conn->query("SELECT value FROM (SELECT * FROM tblfirstdegrel UNION ALL SELECT * FROM tblseconddegrel UNION ALL SELECT * FROM tblthirddegrel) raw");
	
	$gene_mutations = $conn->query("SELECT description FROM tblgenemutation ORDER BY description");
	
	for ($i=0; $i < $count; $i++) {		
?>
						    <li>
							    <div class="field sm<?php echo ((!isset($gene_relation[$i])) ? "" : (strlen($gene_relation[$i]) ? " valid" : " error")); ?>">
					                <div class="iwrap">
						                <label for="gene_relation<?php echo ($i+1);?>">Select Relationship</label>
						                <select id="gene_relation<?php echo ($i+1);?>" name="gene_relation[]" class="if">
											<option value=""></option>
<?php

	foreach($relations as $relation) {
?>
											<option value="<?php echo $relation['value']; ?>"<?php echo (($gene_relation[$i] == $relation['value']) ? " selected" : "");?>><?php echo $relation['value']; ?></option>
<?php 
	}
?>
										</select>
						                <icon class="icon istatus"><span class="required"></span></icon>
					                </div>
				                </div>
							
							    <div class="field sm<?php echo ((!isset($gene[$i])) ? "" : (strlen($gene[$i]) ? " valid" : " error")); ?>">
					                <div class="iwrap">
						                <label for="gene<?php echo ($i+1);?>">Select Gene</label>
						                <select id="gene<?php echo ($i+1);?>" name="gene[]" class="if" >
										    <option value=""></option>
<?php
	if ($qualify['insurance'] == "Aetna") {
?>
											<option value="BRCA1"<? echo ((isset($gene[$i]) && ($gene[$i] == "BRCA1")) ? " selected" : ""); ?>>BRCA1</option>
											<option value="BRCA2"<? echo ((isset($gene[$i]) && ($gene[$i] == "BRCA2")) ? " selected" : ""); ?>>BRCA2</option>
											<option value="Both"<? echo ((isset($gene[$i]) && ($gene[$i] == "Both")) ? " selected" : ""); ?>>Both</option>
											<option value="unknown"<? echo ((isset($gene[$i]) && ($gene[$i] == "unknown")) ? " selected" : ""); ?>>Unknown</option>
<?php
	} else {				
		foreach($gene_mutations as $gene_mutation) {
?>
											<option value="<?php echo $gene_mutation['description']; ?>"<? echo ((isset($gene[$i]) && ($gene[$i] == $gene_mutation['description'])) ? " selected" : ""); ?>><?php echo $gene_mutation['description']; ?></option>
<?php
		}
	}
?>
										</select>
						                <icon class="icon istatus"><span class="required"></span></icon>
					                </div>
				                </div>
								
								<button type="button" class="ask_help toggle" data-on=".overlay.help">
								    <span>Ask for Help</span>
								</button>
<?php
	if ($i) {
?>
								<button type="button" class="remove_field iconP">X</button>								
<?php
	}
?>
							</li>
<?php
	}
?>
						</ul>
						<button type="button" class="add_field">
						    <strong>Add Relative</strong>
						</button>
					</div> 
					
					<div class="input<?php echo (($gm == "No") ? " checked" : ""); ?>">
						<span class="radio">
					        <input type="radio" name="gene_mutation" value="No" id="gene_mutation_no">
				        </span>
                        <label for="gene_mutation_no">No</label>
					</div>
					
					<div class="input<?php echo (($gm == "Unknown") ? " checked" : ""); ?>">
						<span class="radio">
					        <input type="radio" name="gene_mutation" value="Unknown" id="gene_mutation_unknown">
				        </span>
                        <label for="gene_mutation_unknown">Unknown</label>
					</div>
				</fieldset>
				<aside class="side_col">
					<?php echo get_CBR_box(); ?>
				</aside>				
<?php
	if (isset($_GET['lc']) && ($_GET['lc'] == "O")) {
		$buttons = array("Back"=>"back", "Next"=>"next");
		$class = array("Back"=>" back neutral sm", "Next"=>"");
	} else {
		$buttons = array("Back"=>"back", "Finish Later"=>"save", "Next"=>"next");
		$class = array("Back"=>" back neutral sm", "Finish Later"=>" gold save", "Next"=>"");
	}
	
	generate_outer_bottom($error, $buttons, $class);
}
function generate_cancer_list_personal($error) {
	require ("db/dbconnect.php");
	
	$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);
	
	$qualify = $result->fetch_assoc();
	
	$conn->query("DELETE FROM tblqualifyfam WHERE Guid_qualify=" . $_SESSION['id']);	
	
	$conn->query("DELETE FROM tblqualifyans WHERE Guid_qualify=" . $_SESSION['id'] . " AND relative IS NOT NULL");	
	
	$selected_cancer = array();
	
	$results = $conn->query("SELECT cancer_type FROM tblqualifypers WHERE Guid_qualify = " . $_SESSION['id']);
	
	foreach($results as $result) {
		array_push($selected_cancer, $result['cancer_type']);
	}
	
	$type = "personal";
	generate_header("Do you have or had any of the following cancers?<em>(Check all that apply)</em>");

	if (($qualify['insurance'] == "Other") || ($qualify['insurance'] == "None") || ($qualify['insurance'] == "Aetna")) {
?>
				<input type="hidden" name="prev_step" value="gene_mutation">
<?php
	} else {
?>
				<input type="hidden" name="prev_step" value="gender_aj">
<?php
	}
?>
				<input type="hidden" name="next_step" value="cancer_detail">
				<input type="hidden" name="current_step" id="current_step" value="cancer_list_personal">
				<input type="hidden" name="type" value="<?php echo $type; ?>">
<?php
	generate_cancer_list($qualify, $type, $selected_cancer);
	if (isset($_GET['lc']) && ($_GET['lc'] == "O")) {
		$buttons = array("Back"=>"back", "Next"=>"next");	
		$class = array("Back"=>" back neutral sm", "Next"=>"");
	} else {
		$buttons = array("Back"=>"back", "Finish Later"=>"save", "Next"=>"next");	
		$class = array("Back"=>" back neutral sm", "Finish Later"=>" gold save", "Next"=>"");
	}
	
	generate_outer_bottom($error, $buttons, $class);
}
function generate_cancer_list_family($error, $not_qualified=0) {
	require ("db/dbconnect.php");	
		
	$result = $conn->query("SELECT * FROM tblqualifypers WHERE Guid_qualify = " . $_SESSION['id'] . "  AND cancer_type =\"No Cancer/None of the Above\"");
	
	//if ((!$not_qualified) && (!mysqli_num_rows($result))) {
	////	generate_cancer_detail();
	//	exit;
	//}
	$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);
	
	$qualify = $result->fetch_assoc();
	
	$selected_cancer = array();
	
	$results = $conn->query("SELECT cancer_type FROM tblqualifyfam WHERE Guid_qualify = " . $_SESSION['id']);
	
	foreach($results as $result) {
		array_push($selected_cancer, $result['cancer_type']);
	}
	
	$type = "family";
	
	generate_header("Do you have a close blood relative(s) with any of the following cancers?<em>(Check all that apply)</em>");		
?>
				<input type="hidden" name="prev_step" value="cancer_list_personal">
				<input type="hidden" name="next_step" value="cancer_detail">
				<input type="hidden" name="current_step" id="current_step" value="cancer_list_family">
				<input type="hidden" name="type" value="<?php echo $type; ?>">
<?php
	generate_cancer_list($qualify, $type, $selected_cancer);
	if (isset($_GET['lc']) && ($_GET['lc'] == "O")) {
		$buttons = array("Back"=>"back", "Next"=>"next");	
		$class = array("Back"=>" back neutral sm", "Next"=>"");
	} else {
		$buttons = array("Back"=>"back", "Finish Later"=>"save", "Next"=>"next");	
		$class = array("Back"=>" back neutral sm", "Finish Later"=>" gold save", "Next"=>"");
	}		
	generate_outer_bottom($error, $buttons, $class);
}
function generate_cancer_list($qualify, $type, $selected_cancer) {
?>
	<div class="display_msg hide">	
		<span class="msg_type"></span>
		<div class="emsg_box">
			<p>Please choose an option</p>
		</div>
	</div>
			
	<div class="check_list three<?php echo (($type == "family") ? " main_col" : ""); ?>">
		<div class="input<?php echo (in_array("Breast", $selected_cancer) ? " checked" : ""); ?>">
			<span class="cbox">
				<input type="checkbox" name="<?php echo $type . "_cancer[]"; ?>" value="Breast" id="cancer_breast"<?php echo (in_array("Breast", $selected_cancer) ? " checked" : ""); ?>>
			</span>
			<label for="cancer_breast">Breast</label>
		</div>
<?php
	if (($qualify['gender'] == "Female") || ($type == "family")) {
?>
		<div class="input<?php echo (in_array("Ovarian", $selected_cancer) ? " checked" : ""); ?>">
			<span class="cbox">
				<input type="checkbox" name="<?php echo $type . "_cancer[]"; ?>" value="Ovarian" id="cancer_ovarian"<?php echo (in_array("Ovarian", $selected_cancer) ? " checked" : ""); ?>>
			</span>
			<label for="cancer_ovarian">Ovarian <em>(includes fallopian tube and primary peritoneal carcinoma)</em></label>
		</div>
<?php
	}
	if (($qualify["insurance"] != "Aetna") && (($type == "family") || (($type == "personal") && ($qualify['gender'] == "Male")))) {
?>			
		<div class="input<?php echo (in_array("Prostate", $selected_cancer) ? " checked" : ""); ?>">
			<span class="cbox">
				<input type="checkbox" name="<?php echo $type . "_cancer[]"; ?>" value="Prostate" id="cancer_prostate"<?php echo (in_array("Prostate", $selected_cancer) ? " checked" : ""); ?>>
			</span>
			<label for="cancer_prostate">Prostate</label>
		</div>
<?php
	}
	if (($qualify["insurance"] != "Aetna") || (($qualify["insurance"] == "Aetna") && ($qualify['gender'] == "Female") && ($type == "personal"))) {
?>
		<div class="input<?php echo (in_array("Pancreatic", $selected_cancer) ? " checked" : ""); ?>">
			<span class="cbox">
				<input type="checkbox" name="<?php echo $type . "_cancer[]"; ?>" value="Pancreatic" id="cancer_pancreatic"<?php echo (in_array("Pancreatic", $selected_cancer) ? " checked" : ""); ?>>
			</span>
			<label for="cancer_pancreatic">Pancreatic</label>
		</div>
<?php
    }
	if (($qualify["insurance"] != "Aetna") && ($qualify["insurance"] != "Medicare")) {
?>
		<div class="input<?php echo (in_array("Colorectal/Endometrial (Uterine)", $selected_cancer) ? " checked" : ""); ?>">
			<span class="cbox">
				<input type="checkbox" name="<?php echo $type . "_cancer[]"; ?>" value="Colorectal/Endometrial (Uterine)" id="cancer_colo_endo_uterine"<?php echo (in_array("Colorectal/Endometrial (Uterine)", $selected_cancer) ? " checked" : ""); ?>>
			</span>
			<label for="cancer_endo_uterine">Colorectal/Endometrial (Uterine)</label>
		</div>
		
		<div class="input<?php echo (in_array("Gastric", $selected_cancer) ? " checked" : ""); ?>">
			<span class="cbox">
				<input type="checkbox" name="<?php echo $type . "_cancer[]"; ?>" value="Gastric" id="cancer_gastric"<?php echo (in_array("Gastric", $selected_cancer) ? " checked" : ""); ?>>
			</span>
			<label for="cancer_gastric">Gastric</label>
		</div>
		
		<div class="input<?php echo (in_array("Ureter", $selected_cancer) ? " checked" : ""); ?>">
			<span class="cbox">
				<input type="checkbox" name="<?php echo $type . "_cancer[]"; ?>" value="Ureter" id="cancer_ureter"<?php echo (in_array("Ureter", $selected_cancer) ? " checked" : ""); ?>>
			</span>
			<label for="cancer_ureter">Ureter</label>
		</div>
		
		<div class="input<?php echo (in_array("Renal", $selected_cancer) ? " checked" : ""); ?>">
			<span class="cbox">
				<input type="checkbox" name="<?php echo $type . "_cancer[]"; ?>" value="Renal" id="cancer_renal"<?php echo (in_array("Renal", $selected_cancer) ? " checked" : ""); ?>>
			</span>
			<label for="cancer_renal">Renal</label>
		</div>
		<div class="input<?php echo (in_array("Pelvic", $selected_cancer) ? " checked" : ""); ?>">
			<span class="cbox">
				<input type="checkbox" name="<?php echo $type . "_cancer[]"; ?>" value="Pelvic" id="cancer_pelvic"<?php echo (in_array("Pelvic", $selected_cancer) ? " checked" : ""); ?>>
			</span>
			<label for="cancer_pelvic">Pelvic</label>
		</div>
		<div class="input<?php echo (in_array("Small Intestinal", $selected_cancer) ? " checked" : ""); ?>">
			<span class="cbox">
				<input type="checkbox" name="<?php echo $type . "_cancer[]"; ?>" value="Small Intestinal" id="cancer_intestinal"<?php echo (in_array("Small Intestinal", $selected_cancer) ? " checked" : ""); ?>>
			</span>
			<label for="cancer_intestinal">Small Intestinal</label>
		</div>
		
		<div class="input<?php echo (in_array("Sebaceous Adenoma", $selected_cancer) ? " checked" : ""); ?>">
			<span class="cbox">
				<input type="checkbox" name="<?php echo $type . "_cancer[]"; ?>" value="Sebaceous Adenoma" id="cancer_adenoma"<?php echo (in_array("Sebaceous Adenoma", $selected_cancer) ? " checked" : ""); ?>>
			</span>
			<label for="cancer_adenoma">Sebaceous Adenoma</label>
		</div>
		
		<div class="input<?php echo (in_array("Sebaceous Carcinomas", $selected_cancer) ? " checked" : ""); ?>">
			<span class="cbox">
				<input type="checkbox" name="<?php echo $type . "_cancer[]"; ?>" value="Sebaceous Carcinomas" id="cancer_carcinomas"<?php echo (in_array("Sebaceous Carcinomas", $selected_cancer) ? " checked" : ""); ?>>
			</span>
			<label for="cancer_carcinomas">Sebaceous Carcinomas</label>
		</div>
		
		<div class="input<?php echo (in_array("Brain", $selected_cancer) ? " checked" : ""); ?>">
			<span class="cbox">
				<input type="checkbox" name="<?php echo $type . "_cancer[]"; ?>" value="Brain" id="cancer_brain"<?php echo (in_array("Brain", $selected_cancer) ? " checked" : ""); ?>>
			</span>
			<label for="cancer_brain">Brain</label>
		</div>
		
		<div class="input<?php echo (in_array("Keratoacanthomas", $selected_cancer) ? " checked" : ""); ?>">
			<span class="cbox">
				<input type="checkbox" name="<?php echo $type . "_cancer[]"; ?>" value="Keratoacanthomas" id="cancer_keratoacanthomas"<?php echo (in_array("Keratoacanthomas", $selected_cancer) ? " checked" : ""); ?>>
			</span>
			<label for="cancer_keratoacanthomas">Keratoacanthomas</label>
		</div>
<?php
	}
?>
		<div class="input<?php echo (in_array("No Cancer/None of the Above", $selected_cancer) ? " checked" : ""); ?>">
			<span class="cbox">
				<input type="checkbox" name="<?php echo $type . "_cancer[]"; ?>" value="No Cancer/None of the Above" id="cancer_none"<?php echo (in_array("No Cancer/None of the Above", $selected_cancer) ? " checked" : ""); ?>>
			</span>
			<label for="cancer_none">No Cancer/None of the Above</label>
		</div>
	</div>	
<?php
	if (($qualify["insurance"] == "Medicare") && ($type == "personal")) {
?>
		<div class="more_info">
			<strong class="ico_info"></strong>
			<p>Based on Medicare's medical policy guidelines, members must have a personal history of breast, ovarian, pancreatic or prostate cancer for testing to medically qualify for testing and be covered. The cancer selections above reflect coverage for BRCA1 and BRCA2 testing only. Multi-gene panels are covered by Medicare, but a more extensive criteria must be met.</p>
		</div>
<?php
	}
	if (($qualify["insurance"] == "Aetna") && ($type == "personal")) {				
?>
		<div class="more_info">
			<strong class="ico_info"></strong>
			<p>Most Aetna insurance products only cover testing for sequencing of the BRCA1 and BRCA2 genes, reflecting the cancer selections indicated above. Multi-gene testing and large genomic re-arrangements testing is not covered by Aetna and is considered experimental and investigational.</p>
		</div>
<?php
	}
	if ($type == "family") {		
?>
	<aside class="side_col">
		<?php echo get_CBR_box(); ?>		
	</aside>
<?php
	}
}
function generate_cancer_detail($error=array(), $field_name=array()) {
	require ("db/dbconnect.php");
	
	$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);
	
	$qualify = $result->fetch_assoc();
	
	$type = "personal";
	
	$table = tblqualifypers;
	
	if (isset($_POST['type']) && ($_POST['type'] == "family")) {			
		$type = "family";
		$table = tblqualifyfam;
	} else {
		$result = $conn->query("SELECT * FROM tblqualifypers WHERE Guid_qualify = " . $_SESSION['id'] . "  AND cancer_type =\"No Cancer/None of the Above\"");
	
		if (mysqli_num_rows($result)) {	
			$type = "family";
			$table = tblqualifyfam;
		}
	}
	
	$result_unknown = 0;
	
	$result_pers_unknown = $conn->query("SELECT * FROM tblunknownans LEFT JOIN tblcancerquestion ON tblunknownans.Guid_question = tblcancerquestion.Guid_question WHERE question_type='personal' AND Guid_qualify =" . $_SESSION['id']);
	
	$result_fam_unknown = $conn->query("SELECT * FROM tblunknownans LEFT JOIN tblcancerquestion ON tblunknownans.Guid_question = tblcancerquestion.Guid_question WHERE question_type='family' AND Guid_qualify =" . $_SESSION['id']);
	
	if ((!mysqli_num_rows($result_pers_unknown)) && (mysqli_num_rows($result_fam_unknown))) {
		$type = "family";
		$table = tblqualifyfam;
	}
	
	if (mysqli_num_rows($result_pers_unknown)) {
		$result_unknown = 1;
	}
	
	if (($type == "family")  && (mysqli_num_rows($result_fam_unknown))) {	
		$result_unknown = 1;
	}	
		
	generate_header("Please give more detail on the following:", $result_unknown);
	
	if ($type == "family") {
		$conn->query("DELETE FROM tblqualifyans WHERE Guid_qualify=" . $_SESSION['id'] . " AND relative IS NOT NULL");	
	} else {		
		$conn->query("DELETE FROM tblqualifyans WHERE Guid_qualify=" . $_SESSION['id'] . " AND cancer_personal IS NOT NULL");
	}
	
	if (count($error)) {
?>
				<div class="display_msg error">
					<span class="msg_type"></span>
					<div class="emsg_box">
						<p>Please Correct all errors</p>
						<ul>
						
<?php
		foreach ($error as $key => $value) {
			$field_name = substr($key, 0, strpos($key, "_relation"));
			if (strlen($field_name)) {
				$result = $conn->query("SELECT * FROM tblcancerquestion WHERE field_name = '" . $field_name . "'");
				$row = mysqli_fetch_assoc($result);
				
				if (mysqli_num_rows($result)) {
?>

								<li><?php echo $row['cancer_type'] . " Cancer section"; ?></li>
				
<?php
				}
			}
			if ($value == "none_selected") {
?>
				
							<li>Please complete remaining questions under <?php echo $key; ?> Cancer</li>
				
<?php
			}
		}		
?>
					
					</ul>			
					
				</div>
			</div
<?php
	}
	if (isset($error['min_num_relative'])) {
?>
				
<?php
	}
	$cancer_type = array();
		
	$insurance = $qualify['insurance'];
	
	if ($insurance != "Aetna") {
		$insurance = "NCCN";
	}
	
	// if ((mysqli_num_rows($result_pers_unknown)) || (mysqli_num_rows($result_fam_unknown))) {
		// if (mysqli_num_rows($result_fam_unknown)) {
			// $type = "family";
			// $table = tblqualifyfam;
		// }
	// } else {
		// if (isset($_POST['type']) && ($_POST['type'] == "family")) {			
			// $type = "family";
			// $table = tblqualifyfam;
		// } //else {
			//$result = $conn->query("SELECT * FROM tblqualifypers WHERE Guid_qualify = " . $_SESSION['id'] . "  AND cancer_type =\"No Cancer/None of the Above\"");
	
			//if (isset($_POST['type']) && ($_POST['type'] == "family")) {	
			//	$type = "family";
			//	$table = tblqualifyfam;
			//}
		//}
	//}

	$selected_cancers = $conn->query("SELECT * FROM " . $table . " WHERE Guid_qualify = " . $_SESSION['id'] . " ORDER BY cancer_type");

	$lynch_syndrome_cancer = "";

	foreach($selected_cancers as $selected_cancer) {
		if (in_array($selected_cancer['cancer_type'], array("Breast", "Ovarian", "Prostate", "Pancreatic", "Colorectal/Endometrial (Uterine)"))) {
			array_push($cancer_type, $selected_cancer['cancer_type']);		
		} elseif (in_array($selected_cancer['cancer_type'], array("Gastric", "Ureter", "Renal", "Pelvic", "Small Intestinal", "Sebaceous Adenoma", "Sebaceous Carcinomas", "Brain", "Keratoacanthomas"))) {
            if (!in_array("Lynch syndrome", $cancer_type)) {
				array_push($cancer_type, "Lynch syndrome");
            }
            $lynch_syndrome_cancer .= $selected_cancer['cancer_type'] . ", ";
		}
	}

    $lynch_syndrome_cancer = rtrim($lynch_syndrome_cancer, ', ');
	
	$option = "";
	
	$popup = array();
	
	$results = $conn->query("SELECT * FROM tblpopup");
	
	foreach($results as $result) {
		$popup[$result['key']] = $result['definition'];
	}
	
	if ($type == "family") {
?>
				<input type="hidden" name="prev_step" value="cancer_list_family">
				<input type="hidden" name="next_step" value="additional_summary">
<?php
	} else {
?>		
				<input type="hidden" name="prev_step" value="cancer_list_personal">
<?php
	}
	$num_relative = 0;
		
	if ($type == "personal") {
		for ($i=0; $i < count($cancer_type); $i++) {
			$result = $conn->query("SELECT * FROM tblcancerquestion WHERE insurance = \"" . $insurance . "\" AND question_type=\"" . $type . "\" AND cancer_type = \"" . $cancer_type[$i] . "\" AND relation_needed=\"1\"");
			$num_relative += mysqli_num_rows($result);
		}		
	}
	
	$num_lynch = 0;
	
	if (($qualify['insurance'] == "Other") || ($qualify['insurance'] == "None")) {
		$result = $conn->query("SELECT * FROM tblqualifypers WHERE Guid_qualify = " . $_SESSION['id'] . " AND cancer_type in (\"Colorectal/Endometrial (Uterine)\", \"Gastric\", \"Ureter\", \"Renal\", \"Pelvic\", \"Small Intestinal\", \"Sebaceous Adenoma\", \"Sebaceous Carcinomas\", \"Brain\", \"Keratoacanthomas\")");
				
		$num_lynch += mysqli_num_rows($result);
					
		$result = $conn->query("SELECT * FROM tblqualifyfam WHERE Guid_qualify = " . $_SESSION['id'] . " AND cancer_type in (\"Endometrial (Uterine)\", \"Colorectal\", \"Gastric\", \"Ureter\", \"Renal\", \"Pelvic\", \"Small Intestinal\", \"Sebaceous Adenoma\", \"Sebaceous Carcinomas\", \"Brain\", \"Keratoacanthomas\")");
		
		$num_lynch += mysqli_num_rows($result);
	}
	$result = $conn->query("SELECT * FROM tblunknownans WHERE Guid_qualify =" . $_SESSION['id']);
	
	$result_unknown = mysqli_num_rows($result);
?>
				<input type="hidden" name="current_step" value="cancer_detail">
				<input type="hidden" name="type" value="<?php echo $type; ?>">
				<div class="accordion<?php echo ((($type == "family") || $num_relative || $num_lynch) ? " main_col" : ""); ?>">	
<?php
	$first_not_displayed = 1;
		
	for ($i=0; $i < count($cancer_type); $i++) {		
		if ($first_not_displayed) {
			$first = " first";
			$display =  "";
			$first_not_displayed = 0;
		}
			
		$outlier_generated = 0;
		
		if ($type == "personal") {
			$result = $conn->query("SELECT * FROM tbloutlier WHERE cancer_type= \"" . $cancer_type[$i] . "\"");
			
			if (mysqli_num_rows($result)) {				
				$outliers = $conn->query("SELECT * FROM tbloutlier");				
				foreach($outliers as $outlier) {
					if ($cancer_type[$i] == $outlier['cancer_type']) {
						$cancer_detected = 0;
						if ($outlier['cancer_type'] == "N/A") {
							$cancer_detected = 1;
						} else {
							$result = $conn->query("SELECT * FROM tblqualifypers WHERE Guid_qualify = " . $_SESSION['id'] . " AND cancer_type = \"" . $outlier['cancer_type'] . "\"");
							$cancer_detected = mysqli_num_rows($result);
						}					
						$ashkenazi = 0;
						if (($outlier['ashkenazi'] == "N/A") || (($outlier['ashkenazi'] != "N/A") && ($qualify['ashkenazi'] == $outlier['ashkenazi']))) {
							$ashkenazi = 1;
						}
						$insurance = "";
						if ($qualify['insurance'] == "Aetna") {
							$insurance = "Aetna";
						} elseif (in_array($qualify['insurance'], array("Medicare", "Other", "None"))) {
							$insurance = "NCCN";
						}
						$gender=0;
						if ($outlier['gender'] == "N/A") {
							$gender = 1;
						} elseif ($qualify['gender'] == $outlier['gender']) {
							$gender = 1;
						}
						
						if (($insurance == $outlier['insurance']) &&			
							$gender &&
							$ashkenazi &&
							($qualify['gene_mutation'] == $outlier['gene_mutation']) &&
							($cancer_detected)) {
								$outlier_generated = 1;
?>
					<button type="button" class="acc_btn"><strong><?php echo $outlier['cancer_type']; ?> Cancer</strong></button>
					
					<ul class="qTree acc_con<?php echo $first;?>">
						<li class=" show">
							<fieldset class="acc_sub">
								<legend>What age were you diagnosed with <?php echo strtolower($outlier['cancer_type']); ?> cancer?</legend>
								<div class="as_fields">
									<input type="hidden" name="apo1" value="">
								</div>
							</fieldset>
							<ul class="fixed_f toggled show<?php echo ((!count($error)) ? "" : (isset($error[$outlier['field_name'] . "_age"]) ? " error" : " valid")); ?>">
								<li class="field_group">			
									<div class="field sm age show">
										<div class="iwrap">
											<label for="<?php echo $outlier['field_name']; ?>">Age</label>
											<input type="text" id="<?php echo $outlier['field_name']; ?>" name="<?php echo $outlier['field_name'] . "_age"; ?>" class="if" value="<?php echo (isset($_POST[$outlier['field_name'] . "_age"]) ? $_POST[$outlier['field_name'] . "_age"] : ""); ?>">
											<icon class="icon istatus"><span class="required"></span></icon>
										</div>
									</div>
								</li>
							</ul>
						</li>
					</ul>
<?php						
						}
					}
				}
			}
		}
						
		if (!$outlier_generated) {
			$questions = $conn->query("SELECT * FROM tblcancerquestion WHERE insurance = \"" . $insurance . "\" AND question_type=\"" . $type . "\" AND cancer_type = \"" . $cancer_type[$i] . "\" ORDER BY sort_order");
?>
					<button type="button" class="acc_btn<?php echo ((count($error) && ($error[$cancer_type[$i]] == "none_selected")) ? " show" : ""); ?>"><strong><?php echo (($cancer_type[$i] == "Lynch syndrome") ? $lynch_syndrome_cancer : $cancer_type[$i]); ?> Cancer</strong></button>
				    
					<ul class="qTree acc_con<?php echo $first;?>" style="<?php echo $display; ?>">
<?php
		$first_question = 1;
		
		foreach($questions as $question) {
			$fn = $question['field_name'];
						
			if (($qualify['insurance'] == "Medicare") && ($question['exclude_medicare'])) {
			} else {
				$class = "";
				if ($question['indent']) {
					$class .= "child ";
				}
				if ($question['yes_continue']) {					
					 $class .= "parent ";
				}
				if ($question['hide_yes_no']) {					
					 $class .= "no-yn ";
				}
				if ($first_question) {
					$first_question = 0;
					$class .= "show ";
				} elseif (isset($_POST[$fn])) {
					$class .= "show ";
					$show_next = 1;
				} else if ((($type == "personal") && (mysqli_num_rows($result_pers_unknown))) ||
						   (($type == "family") && (mysqli_num_rows($result_fam_unknown))) ) {
					$class .= "show ";
					$show_next = 1;
				}
				
				foreach ($error as $key => $value) {
					if (($question['field_name'] . "_relation") == $key) {						
						$class .= "flag ";
					}
				}
				
				if (strlen($class)) {
					$class = " class=\"" . $class . "\"";
				}
				$personal_cancer = rtrim($personal_cancer, ', ');
				
				$ques = $question['question'];
				
				foreach ($popup as $key => $definition) {
					if (strpos($ques, $key) !== false) {						
						$ques = preg_replace("/\b" . $key . "\b/" , "<span class=\"define\" alt=\"" . $definition . "\">" . $key . "</span>", $ques);
					}	
				}
?>
						<li<?php echo $class; ?>>
							<fieldset class="acc_sub">
								<legend><?php echo $ques; ?></legend>
								<div class="as_fields">
<?php
				if ($question['yes_no_required']) {
					$result = $conn->query("SELECT * FROM tblunknownans WHERE Guid_question = ". $question['Guid_question'] . " AND Guid_qualify =" . $_SESSION['id']);
					$result_ques_unknown = mysqli_num_rows($result);
?>
									<div class="input yes<?php echo (($question['yes_continue']) ? " cont" : ""); ?>">
										<span class="radio">
											<input type="radio" name="<?php echo $fn; ?>" value="Yes" id="<?php echo $fn . "_yes"; ?>"<?php echo (((isset($_POST[$fn]) && ($_POST[$fn] == "Yes"))) ? " checked" : "" ); ?>>
										</span>
										<label for="<?php echo $fn . "_yes"; ?>">Yes</label>
									</div>
								
									<div class="input no<?php echo (($result_unknown && (!$result_ques_unknown)) ? " checked" : ""); ?>">
										<span class="radio">
											<input type="radio" name="<?php echo $fn; ?>" value="No" id="<?php echo $fn . "_no"; ?>"<?php echo ((isset($_POST[$fn]) && ($_POST[$fn] == "No")) ? " checked" : "" ); ?>>
										</span>
										<label for="<?php echo $fn . "_no"; ?>">No</label>
									</div>
									
									<div class="input unknown<?php echo (($result_ques_unknown) ? " checked" : ""); ?>">
										<span class="radio">
											<input type="radio" name="<?php echo $fn; ?>" value="Unknown" id="<?php echo $fn . "_unknown"; ?>"<?php echo ((isset($_POST[$fn]) && ($_POST[$fn] == "Unknown")) ? " checked" : "" ); ?>>
										</span>
										<label for="<?php echo $fn . "_unknown"; ?>">Unknown</label>
									</div>
<?php
				} else {
?>
									<input type="hidden" name="<?php echo $fn; ?>" value="Yes">
<?php
				}
?>
								</div>
							</fieldset>
<?php
				$count_personal = 0;
				
				$age_personal = $question['field_name'] . "_age_personal";
				$personal_cancer_fn = $question['field_name'] . "_cancer_personal";
				$relation = $question['field_name'] . "_relation";				
				$age_relative = $question['field_name'] . "_age_relative";
				$cancer_fn = $question['field_name'] . "_cancer";
				$cancer_fn_2 = $question['field_name'] . "_additional_cancer";
				$gene_mutation_fn = $question['field_name'] . "_gene_mutation";
				$deceased_relative = $question['field_name'] . "_deceased_relative";
				$personal_cancer_options = array();
				
				if ($question['personal_age_needed']) {
					if (isset($_POST[$age_personal])) {
						$count_personal = count($_POST[$age_personal]);
					} elseif ($question['min_num_personal']) {
						$count_personal = $question['min_num_personal'];
					}
				}
				if ($question['personal_cancer_needed']) {
					if (isset($_POST[$personal_cancer_fn])) {
						$count_personal = count($_POST[$personal_cancer_fn]);
					} elseif ($question['special_rule'] == 7) {																
						$count_personal = count(explode(",", $lynch_syndrome_cancer));
					} elseif ($question['min_num_personal']) {
						$count_personal = $question['min_num_personal'];
					}
					
					$sql = "SELECT value FROM tblqcancertype";
					
					if ((strlen($question['pesonal_cancer_list'])) || ($question['special_rule'] == 7)) {
						if ($question['special_rule'] == 7) {
							$cancer_list = "\"" . str_replace(", ", "\",\"", $lynch_syndrome_cancer) . "\"";					
							$sql .= " WHERE value IN (" . $cancer_list . ")";
						} else {
							$sql .= " WHERE value IN (" . $question['pesonal_cancer_list'] . ")";
						}
					}
					
					$sql.= " ORDER BY value";
					
					$cancers = $conn->query($sql);
						
					foreach($cancers as $cancer) {
						array_push($personal_cancer_options, $cancer['value']);
					}		
				}
				
				if ($count_personal) {
?>
							<ul class="fixed_f toggled<?php echo ((!$question['yes_no_required']) || ((isset($_POST[$fn]) && ($_POST[$fn] == "Yes"))) ? " show" : "" ); ?>">	
<?php
					if ($question['personal_age_needed'] && $question['relation_age_needed']) {
						if (($question['field_name'] == "pne3") || ($question['field_name'] == "pne4")) {
?>
									<section class="fgroup">
										<h5><span>PERSONAL DIAGNOSIS</span></h5>
<?php
						} else {
?>
									<section class="fgroup">
										<h5><span>Age Of Occurrences</span></h5>
<?php
						}
					}
					for ($num_dropdowns=0; $num_dropdowns < $count_personal; $num_dropdowns++) {
?>
								<li class="field_group<?php echo ((isset($_POST[$fn]) && ($_POST[$fn] == "Yes")) ? " show" : "" ); ?>">
<?php
						if ($question['personal_cancer_needed']) {
							if ($question['display_cancer_p']) {
?>			
									<div class="field sm<?php echo ((!count($error)) ? "" : (isset($error[$personal_cancer_fn][$num_dropdowns]) ? " error" : " valid")); ?><?php echo (((isset($_POST[$fn]) && ($_POST[$fn] == "Yes")) || (!$question['yes_no_required'])) ? " show" : "" ); ?>">
										<div class="iwrap">
											<label for="<?php echo $personal_cancer_fn . ($question['Guid_question'] + $num_dropdowns); ?>">Select Cancer</label>
											<select id="<?php echo $personal_cancer_fn . ($question['Guid_question'] + $num_dropdowns); ?>" name="<?php echo $personal_cancer_fn; ?>[]" class="if">
												<option></option>
<?php
								for ($j=0; $j < count($personal_cancer_options); $j++) {			
?>
												<option value="<?php echo $personal_cancer_options[$j]; ?>"<?php echo ((isset($_POST[$personal_cancer_fn]) && ($personal_cancer_options[$j] == $_POST[$personal_cancer_fn][$num_dropdowns])) ? " selected" : ""); ?>><?php echo $personal_cancer_options[$j]; ?></option>
<?php
								}
?>
											</select>
											<icon class="icon istatus"><span class="required"></span></icon>
										</div>
									</div>
<?php
							} else {
								
?>							
									<input type="hidden" id="<?php echo $personal_cancer_fn . $question['Guid_question']; ?>" name="<?php echo $personal_cancer_fn; ?>[]" value="<?php echo str_replace('"', '', $question['pesonal_cancer_list']); ?>">
<?php
							}
						}
						if ($question['personal_age_needed']) {
							$min_age = "";
							
							if (strlen($question['min_personal_age'])) {
								$min_age = " min=\"" . $question['min_personal_age'] . "\" ";
							}
?>													
									<div class="field sm age<?php echo ((!count($error)) ? "" : (isset($error[$age_personal][$num_dropdowns]) ? " error" : " valid")); ?>">
											<div class="iwrap">
												<label for="<?php echo $age_personal . ($question['Guid_question'] + $num_dropdowns); ?>">Age Diag.</label>
												<input type="number"<?php echo $min_age; ?> max="<?php $question['max_personal_age']?>" id="<?php echo $age_personal . ($question['Guid_question'] + $num_dropdowns); ?>" name="<?php echo $age_personal; ?>[]" class="if" value="<?php echo ((isset($_POST[$age_personal][$num_dropdowns])) ? $_POST[$age_personal][$num_dropdowns] : ""); ?>"<?php echo (strlen($question['max_personal_age']) ? " data-reqs=\"\"" : ""); ?>"" required>
												<icon class="icon istatus"><span class="required"></span></icon>
											</div>
									</div>
<?php
						}					
?>
								</li>
<?php
					}
					if ($question['personal_age_needed'] && $question['relation_age_needed']) {
?>
									</section>
<?php
					}
?>
							</ul>
<?php
					if ($question['multiple_personal']) {
?>
						<ul class="dynamic_f toggled"></ul>
						<div class="dynamic_btns">
							<button type="button" class="add_field cancer_detail" id="<?php echo $question['field_name'] . "_add"; ?>">
								<strong>Add More</strong>
							</button>
						</div>
<?php
					}
				}
				
				$count_relative = 0;
				
				$relation_options = array();
				$cancer_options = array();
				$gene_mutation_options = array();
				
				if ($question['relation_age_needed']) {
					if (isset($_POST[$age_relative])) {
						$count_relative = count($_POST[$age_relative]);
					} elseif ($question['min_num_relative']) {
						$count_relative = $question['min_num_relative'];
					}
				}
				if ($question['relation_needed']) {	
					if (isset($_POST[$relation])) {
						$count_relative = count($_POST[$relation]);
					} elseif ($question['min_num_relative']) {
						$count_relative = $question['min_num_relative'];
					}
					
					$tables = explode(",", $question['relation_table']);					
					
					for ($j=0; $j < count($tables); $j++) {								
						$relation_ships = $conn->query("SELECT value FROM " . $tables[$j]);
							
						foreach($relation_ships as $relation_ship) {
							array_push($relation_options, $relation_ship['value']);
						}
					}
				}
				if ($question['cancer_needed']) {
					if (isset($_POST[$cancer_fn])) {						
						$count_relative = count($_POST[$cancer_fn]);
					} elseif ($question['special_rule'] == 7) {																
						$count_relative = count(explode(",", $lynch_syndrome_cancer));
					} elseif ($question['min_num_relative']) {						
						$count_relative = $question['min_num_relative'];
					}
					
					$sql = "SELECT value FROM tblqcancertype";
					
					if ((strlen($question['cancer_list'])) || ($question['special_rule'] == 7)) {		
						if ($question['special_rule'] == 7) {
							$cancer_list = "\"" . str_replace(", ", "\",\"", $lynch_syndrome_cancer) . "\"";					
							$sql .= " WHERE value IN (" . $cancer_list . ")";
						} else {
							$sql .= " WHERE value IN (" . $question['cancer_list'] . ")";
						}
					}
					
					$sql.= " ORDER BY value";
					
					$cancers = $conn->query($sql);
						
					foreach($cancers as $cancer) {
						array_push($cancer_options, $cancer['value']);
					}						
				}
				if ($question['gene_mutation_needed']) {
					if (isset($_POST[$gene_mutation_fn])) {
						$count_relative = count($_POST[$gene_mutation_fn]);
					} elseif ($question['min_num_relative']) {
						$count_relative = $question['min_num_relative'];
					}					
					
					$gene_mutations = $conn->query("SELECT value FROM tbl_gene_mutation");
					
					foreach($gene_mutations as $gene_mutation) {
						array_push($gene_mutation_options, $gene_mutation['value']);
					}						
				}
			
				if ($count_relative) {
?>
						<ul class="fixed_f toggled<?php echo (((isset($_POST[$fn]) && ($_POST[$fn] == "Yes")) || (!$question['yes_no_required'])) ? " show" : "" ); ?>">			
<?php
					for ($num_dropdowns=0; $num_dropdowns < $count_relative; $num_dropdowns++) {
?>
							<li class="field_group<?php echo ((isset($_POST[$fn]) && ($_POST[$fn] == "Yes")) ? " show" : "" ); ?>">								
<?php
						if ($question['relation_needed']) {
?>
								<div class="field sm<?php echo ((!count($error)) ? "" : (isset($error[$relation][$num_dropdowns]) ? " error" : " valid"))	; ?><?php echo ((isset($_POST[$fn]) && ($_POST[$fn] == "Yes")) ? " show" : "" ); ?>">
									<div class="iwrap">
										<label for="<?php echo $relation . ($question['Guid_question'] + $num_dropdowns); ?>">Select Relative</label>
										<select id="<?php echo $relation . ($question['Guid_question'] + $num_dropdowns); ?>" name="<?php echo $relation; ?>[]" class="if">
											<option value=""></option>
<?php
							for ($j=0; $j < count($relation_options); $j++) {				
?>
											<option value="<?php echo $relation_options[$j]; ?>"<?php echo ((isset($_POST[$relation]) && ($relation_options[$j] == $_POST[$relation][$num_dropdowns])) ? " selected" : ""); ?>><?php echo $relation_options[$j]; ?></option>
<?php
							}
?>
										</select>
										<icon class="icon istatus"><span class="required"></span></icon>
									</div>
								</div>	
<?php			
						}
						if ($question['cancer_needed']) {
							if ($question['display_cancer_r']) {
?>
								<div class="field sm<?php echo ((!count($error)) ? "" : (isset($error[$cancer_fn][$num_dropdowns]) ? " error" : " valid")); ?><?php echo ((isset($_POST[$fn]) && ($_POST[$fn] == "Yes")) ? " show" : "" ); ?>">
									<div class="iwrap">
										<label for="<?php echo $cancer_fn . ($question['Guid_question'] + $num_dropdowns); ?>">Select Cancer</label>
										<select id="<?php echo $cancer_fn . ($question['Guid_question'] + $num_dropdowns); ?>" name="<?php echo $cancer_fn; ?>[]" class="if">
											<option></option>
<?php
								for ($j=0; $j < count($cancer_options); $j++) {			
?>
											<option value="<?php echo $cancer_options[$j]; ?>"<?php echo ((isset($_POST[$cancer_fn]) && ($cancer_options[$j] == $_POST[$cancer_fn][$num_dropdowns])) ? " selected" : ""); ?>><?php echo $cancer_options[$j]; ?></option>
<?php
								}
?>
										</select>
										<icon class="icon istatus"><span class="required"></span></icon>
									</div>
								</div>
<?php
							} else {
?>
								<input type="hidden" id="<?php echo $cancer_fn . ($question['Guid_question'] + $num_dropdowns); ?>" name="<?php echo $cancer_fn; ?>[]" value="<?php echo str_replace('"', '', $question['cancer_list']); ?>">
<?php
							}
						}
						if ($question['gene_mutation_needed']) {
?>
								<div class="field sm<?php echo ((!count($error)) ? "" : (isset($error[$gene_mutation_fn][$num_dropdowns]) ? " error" : " valid")); ?><?php echo ((isset($_POST[$fn]) && ($_POST[$fn] == "Yes")) ? " show" : "" ); ?>">
									<div class="iwrap">
										<label for="<?php echo $gene_mutation_fn . ($question['Guid_question'] + $num_dropdowns); ?>">Select Gene</label>
										<select id="<?php echo $gene_mutation_fn . ($question['Guid_question'] + $num_dropdowns); ?>" name="<?php echo $gene_mutation_fn; ?>[]" class="if">
											<option></option>
<?php
							for ($j=0; $j < count($gene_mutation_options); $j++) {			
?>
											<option value="<?php echo $gene_mutation_options[$j]; ?>"><?php echo $gene_mutation_options[$j]; ?></option>
<?php
							}
?>
										</select>
										<icon class="icon istatus"><span class="required"></span></icon>
									</div>
								</div>
<?php
						}
						if (($question['cancer_needed']) && ($question['additinal_cancer_needed'])) {
							
?>
								<div class="field sm<?php echo ((!count($error)) ? "" : (isset($error[$cancer_fn_2][$num_dropdowns]) ? " error" : " valid")); ?><?php echo ((isset($_POST[$fn]) && ($_POST[$fn] == "Yes")) ? " show" : "" ); ?>">
									<div class="iwrap">
										<label for="<?php echo $cancer_fn_2 . ($question['Guid_question'] + $num_dropdowns); ?>">Select Cancer</label>
										<select id="<?php echo $cancer_fn_2 . ($question['Guid_question'] + $num_dropdowns); ?>" name="<?php echo $cancer_fn_2; ?>[]" class="if">
											<option></option>
<?php
								$cancer_list = explode(",", $question['additionall_cancer_list']);
								for ($j=0; $j < count($cancer_list); $j++) {			
?>
											<option value="<?php echo $cancer_list[$j]; ?>"<?php echo ((isset($_POST[$cancer_fn_2]) && ($cancer_list[$j] == $_POST[$cancer_fn_2][$num_dropdowns])) ? " selected" : ""); ?>><?php echo $cancer_list[$j]; ?></option>
<?php
								}
?>
										</select>
										<icon class="icon istatus"><span class="required"></span></icon>
									</div>
								</div>
<?php							
						}
						if ($question['relation_age_needed']) {						
?>
								<div class="field sm age<?php echo ((!count($error)) ? "" : (isset($error[$age_relative][$num_dropdowns]) ? " error" : " valid")); ?><?php echo (((isset($_POST[$fn]) && ($_POST[$fn] == "Yes")) || (!$question['yes_no_required'])) ? " show" : "" ); ?>">
										<div class="iwrap">
											<label for="<?php echo $age_relative . ($question['Guid_question'] + $num_dropdowns); ?>">Age Diag.</label>
											<input type="number" max="<?php echo $question['max_relative_age']; ?>" id="<?php echo $age_relative . ($question['Guid_question'] + $num_dropdowns); ?>" name="<?php echo $age_relative; ?>[]" class="if" value="<?php echo ((isset($_POST[$age_relative][$num_dropdowns])) ? $_POST[$age_relative][$num_dropdowns] : ""); ?>"<?php echo (strlen($question['max_relative_age']) ? " data-reqs=\"\"" : ""); ?>>
											<icon class="icon istatus"><span class="required"></span></icon>
										</div>
								</div>
<?php
						}
                        if ($question['relation_needed']) {
?>
								<div class="dec_box">
									<input type="checkbox" name="<?php echo $deceased_relative . $num_dropdowns; ?>" id="<?php echo $deceased_relative . ($question['Guid_question'] + $num_dropdowns); ?>" value="1">
									<label for="<?php echo $deceased_relative . ($question['Guid_question'] + $num_dropdowns); ?>">Deceased</label>
								</div>
<?php
                        }
?>
							</li>
<?php
					}				
?>
						</ul>					
<?php
					if ($question['multiple_relative']) {
?>
						<ul class="dynamic_f toggled"></ul>
						<div class="dynamic_btns">
							<button type="button" class="add_field cancer_detail" id="<?php echo $question['field_name'] . "_add"; ?>">
								<strong>Add Relative</strong>
							</button>
						</div>
<?php
					}
				}				
?>
						</li>
<?php
			}
		}
?>
					</ul>
<?php
	}
	$first = "";
	}
?>
				</div>
<?php	
	if (($type == "family") || $num_relative || $num_lynch) {
?>
			<aside class="side_col">
				<div id="scroller-anchor"></div>
<?php
		if ($num_lynch) {
?>
				<div class="more_info">
					<strong class="ico_info">Lynch Syndrome Related Cancers</strong>
					<p>Colorectal, Endometrial (Uterine), Gastric, Ovarian, Pancreatic, Ureter, Renal, Brain, Small Intestinal, Sebaceous Adenoma, Sebaceous Carcinomas, and Keratoacanthomas.</p>
				</div>
<?php
		}
?>
<?php
		if (($type == "family") || $num_relative) {
?>
				<?php echo get_CBR_box(); ?>
<?php
		}
?>
			</aside>
<?php
	}
	if (isset($_GET['lc']) && ($_GET['lc'] == "O")) {
		$buttons = array("Back"=>"back", "Next"=>"qualify");	
		$class = array("Back"=>" back neutral sm", "Next"=>"");
	} else {
		$buttons = array("Back"=>"back", "Finish Later"=>"save", "Next"=>"qualify");	
		$class = array("Back"=>" back neutral sm", "Finish Later"=>" gold save", "Next"=>"");
	}
	
	generate_outer_bottom($error, $buttons, $class);
}
function generate_outlier_html($fieldname, $first) {
	require ("db/dbconnect.php");	
		
	if (isset($_POST['fieldname'])) {
		$fieldname = $_POST['fieldname'];
	}
	$result = $conn->query("SELECT * FROM tbloutlier WHERE field_name = '" . $fieldname . "'");
				
	$outlier = $result->fetch_assoc();
?>
		<button type="button" class="acc_btn"><strong><?php echo $outlier['cancer_type']; ?> Cancer</strong></button>
		
		<ul class="qTree acc_con<?php echo $first;?>">
			<li class=" show">
				<fieldset class="acc_sub">
					<legend>What age were you diagnosed with <?php echo strtolower($outlier['cancer_type']); ?> cancer?</legend>
					<div class="as_fields">
						<input type="hidden" name="apo1" value="">
					</div>
				</fieldset>
				<ul class="fixed_f toggled show">
					<li class="field_group">			
						<div class="field sm age show">
							<div class="iwrap">
								<label for="<?php echo $fieldname; ?>">Age</label>
								<input type="text" id="<?php echo $fieldname; ?>" name="<?php echo $fieldname . "_age"; ?>" class="if" value="">
								<icon class="icon istatus"><span class="required"></span></icon>
							</div>
						</div>
					</li>
				</ul>
			</li>
		</ul>
    
<?php	
	require ("db/dbdisconnect.php");
}
function generate_outer_bottom($error, $buttons=array(), $class=array()) {		
	if (count($buttons)) {	
?>
				<div class="btns">
<?php
		foreach($buttons as $key => $value) {
			if ($value == "forgot_password") {
?>
				<a href="<?php echo HTTPS_SERVER . "forgot-password.php?" . $_SERVER['QUERY_STRING']; ?>" class="btn" id="<?php echo $value; ?>">
					<icon class="icon"></icon>
					<strong>Forgot Password</strong>
				</a>
<?php
			} else {
?>
					<button class="btn<? echo $class[$key]; ?>" name="<?php echo $value; ?>" id="<?php echo $value; ?>"<?php echo ((($value == "back") || ($value == ""))? " formnovalidate=\"formnovalidate\"" : ""); ?> tabindex="<?php echo ((($value == "next") || ($value == "account_login"))? "1" : "2"); ?>">
					    <icon class="icon"></icon>
					    <strong><?php echo $key; ?></strong>
				    </button>
<?php
			}
		}
?>
			    </div>
<?php
	}
?>
			</main>
<?php	
	generate_overlays($error);
?>
		</form>
        <script src="../framework/js/common.js"></script>
        <script src="../framework/js/defer.js" defer></script>
		<script src="js/should_I_test.js" defer></script>
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-18558117-27"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());

          gtag('config', 'UA-18558117-27');
        </script>
    </body>
</html>
<?php
}
function determine_qualification(&$qualification_text) {
	require ("db/dbconnect.php");
	
	$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);
			
	$qualify = $result->fetch_assoc();
	
	$outliers = $conn->query("SELECT * FROM tbloutlier");
		
	foreach($outliers as $outlier) {
		$cancer_type = 0;
		if ($outlier['cancer_type'] == "N/A") {
			$cancer_type = 1;
		} else {
			$result = $conn->query("SELECT * FROM tblqualifypers WHERE Guid_qualify = " . $_SESSION['id'] . " AND cancer_type = \"" . $outlier['cancer_type'] . "\"");
			$cancer_type = mysqli_num_rows($result);
		}
		
		$ashkenazi = 0;
		if (($outlier['ashkenazi'] == "N/A") || (($outlier['ashkenazi'] != "N/A") && ($qualify['ashkenazi'] == $outlier['ashkenazi']))) {
			$ashkenazi = 1;
		}
		$insurance = "";
		if ($qualify['insurance'] == "Aetna") {
			$insurance = "Aetna";
		} elseif (in_array($qualify['insurance'], array("Medicare", "Other", "None"))) {
			$insurance = "NCCN";
		}
		$gender=0;
		if ($outlier['gender'] == "N/A") {
			$gender = 1;
		} elseif ($qualify['gender'] == $outlier['gender']) {
			$gender = 1;;
		}
		
		if (($insurance == $outlier['insurance']) &&			
			$gender &&
			$ashkenazi &&
			($qualify['gene_mutation'] == $outlier['gene_mutation']) &&			
			$cancer_type) {
				if (($outlier['age_required']) && (!isset($_POST[$outlier['field_name'] . "_age"]))) {
				} else {
					$conn->query("DELETE FROM tblqualifyans WHERE Guid_qualify=" . $_SESSION['id'] . " AND Guid_outlier IS NULL");						
					
					$result = $conn->query("SELECT * FROM tblqualifygene WHERE Guid_qualify = " . $_SESSION['id'] . " AND gene IN (\"EPCAM\",\"MLH1\",\"MSH2\",\"MSH6\",\"PMS2\")");
					if (mysqli_num_rows($result)) {
						$cancer_found_lynch = 1;
					} 
					$result = $conn->query("SELECT * FROM tblqualifygene WHERE Guid_qualify = " . $_SESSION['id'] . " AND gene IN (\"ATM\",\"BARD1\",\"BRCA1\",\"BRCA2\",\"BRIP1\",\"CDH1\",\"CHEK2\",\"MUTYH\",\"PALB2\",\"PTEN\",\"RAD51C\",\"RAD51D\",\"STK11\",\"TP53\")");						
					if (mysqli_num_rows($result)) {
						$cancer_found_brca = 1;
					} else {
						if (($qualify["insurance"] == "Aetna") || ($qualify["insurance"] == "Medicare") || ($outlier['cancer_type'] == "Breast") || ($outlier['cancer_type'] == "Ovarian") || ($outlier['cancer_type'] == "Pancreatic") || ($outlier['cancer_type'] == "Prostate")) {
						$cancer_found_brca = 1;
						} else {					
							$cancer_found_brca = 1;		
						}
					}
				}
		}
	}
	if ((isset($_POST['yes_continue_same_rel'])) || (isset($_POST['qualify'])) || ($_POST['current_step'] == "additional_screen")) {
		if (($qualify["insurance"] == "Aetna") || ($qualify["insurance"] == "Medicare")) {
			$cancer_found_brca = 1;
		} else {
			$answers = $conn->query("SELECT * FROM tblqualifyans WHERE Guid_qualify = " . $_SESSION['id'] . " AND Guid_outlier IS NULL");
			
			if (mysqli_num_rows($answers)) {
				foreach($answers as $answer) {
					$result = $conn->query("SELECT * FROM tblcancerquestion WHERE Guid_question = " . $answer['Guid_question']);
			
					$question = $result->fetch_assoc();
					
					$child_answer_yes = 0;
					
					if ($question['hide_yes_no']) {
						$questions_childrens = $conn->query("SELECT * FROM tblcancerquestion WHERE parent_field_name=\"" . $question['field_name'] . "\"");
						
						foreach($questions_childrens as $questions_children) {
							if ($_POST[$questions_children['field_name']] == "Yes") {	
								$child_answer_yes = 1;
							}
						}
						if ($child_answer_yes) {
							if (in_array($question['cancer_type'], array("Breast", "Ovarian", "Prostate", "Pancreatic"))) {					
								$cancer_found_brca = 1;
							} else {
								$cancer_found_lynch = 1;
							}
						} else {
							if (in_array($question['cancer_type'], array("Breast", "Ovarian", "Prostate", "Pancreatic"))) {					
								$cancer_found_brca = 1;
							} else {
								$cancer_found_lynch = 1;
							}
						}
					} else {
						if (in_array($question['cancer_type'], array("Breast", "Ovarian", "Prostate", "Pancreatic"))) {					
							$cancer_found_brca = 1;
						} else {
							$cancer_found_lynch = 1;
						}
					}										
				}			
			} else {				
				$cancer_found_brca = 1;
			}				
		}		
	}
	
	if ($cancer_found_brca) {
		array_push($qualification_text, "BRCA-Related Breast and/or Ovarian Cancer Syndrome");
	}
	
	if ($cancer_found_lynch) {
		array_push($qualification_text, "High Risk Colon Cancer Syndromes (Lynch syndrome)");
	}
	
	require ("db/dbdisconnect.php");
}
function verify_input(&$error, &$not_qualified, &$type) {
	require ("db/dbconnect.php");
	
	if ($_POST['current_step'] == "email") {		
		if ((!isset($_POST['returning_user'])) && (!strlen(trim($_POST['first_name'])))) {			
			$error['first_name'] = 1;
		}
		if ((!isset($_POST['returning_user'])) && (!strlen(trim($_POST['last_name'])))) {			
			$error['last_name'] = 1;
		}
		
		if (isset($_POST['dob']) && strlen($_POST['dob'])) {		
			if (strtotime($_POST['dob']) < (strtotime('-125 years'))) {
				$error['dob'] = 1;
			} elseif(strtotime($_POST['dob']) > strtotime(date('Y-m-d'))){
				$error['dob'] = 1;
			}
		}
		if (isset($_GET['continue']) &&(isset($_GET['lc']) && (($_GET['lc'] == "PMR") || ($_GET['lc'] == "FR")))) {
		} elseif ((isset($_GET['an'])) && (!strlen($_POST['provider']))) {
			$error['provider'] = 1;
		}
		if ((isset($_GET['ln']) && ($_GET['ln'] == "np")) || (isset($_GET['lc']) && ($_GET['lc'] == "O"))) {
		} else {
			if (strlen($_POST['account_email'])) {
				if (filter_var($_POST['account_email'], FILTER_VALIDATE_EMAIL) === false) {				
					$error['account_email'] = 1;
				}			
				if ((!isset($_POST['returning_user'])) && ($_POST['account_email'] != $_POST['account_email_confirm'])) {				
					$error['account_email'] = 1;
					$error['account_email_confirm'] = 1;
				}				
			} else {
				$error['account_email'] = 1;
			}			
			if (strlen($_POST['account_password'])) {
				$uppercase = preg_match('@[A-Z]@', $_POST['account_password']);
				$lowercase = preg_match('@[a-z]@', $_POST['account_password']);
				$number = preg_match('@[0-9]@', $_POST['account_password']);
				$invalid_entry = strpos($_POST['account_password'], $_POST['account_email']);
				$invalid_entry = strpos(strtolower($_POST['account_password']), "password");
			
				if (strlen($_POST['account_password']) == 4) {
					$pin = preg_match('/^[0-9]{4}$/', $_POST['account_password']);
				
					if (!pin) {
						$error['account_password'] = 1;
					}
				} elseif ((strlen($_POST['account_password']) != 4) && ((!$uppercase) || (!$lowercase) || (!$number) || strlen($_POST['account_password']) < 8 || $invalid_entry)) {
					$error['account_password'] = 1;
				}
				if ((!isset($_POST['returning_user'])) && ($_POST['account_password'] != $_POST['confirm_password'])) {
					$error['account_password'] = 1;
					$error['confirm_password'] = 1;
				}
				
			} else {
				if ((!isset($_GET['lc'])) || (isset($_GET['lc']) && ($_GET['lc'] != "PM"))) {
					$error['account_password'] = 1;
				}
			}		
			if ((!isset($error['account_email'])) && (!isset($error['account_password']))) {			
				if (isset($_POST['returning_user'])) {
					$result_user = $conn->query("SELECT Guid_user FROM tbluser WHERE email = '" . $conn->real_escape_string($_POST['account_email']) . "' AND password = '" . md5($conn->real_escape_string($_POST['account_password'])) . "'");				
					
					if ($result_user->num_rows) {
						$account = $result_user->fetch_assoc();
						
						$result = $conn->query("SELECT Guid_qualify FROM tblqualify WHERE Guid_user = '" . $account['Guid_user'] . "'");
					
						if ($result->num_rows) {
							$qualify = $result->fetch_assoc();
							
							$_SESSION["id"] = $qualify['Guid_qualify'];
						}
					} else {
						$error[login] = 1;
					}		
				} else {				
					$result_user = $conn->query("SELECT * FROM tbluser WHERE email = '" . $conn->real_escape_string($_POST['account_email']) . "'");
					if ($result_user->num_rows) {
						$error['account_email_exists'] = 1;
						$error[login] = 1;
					}
				}			
			}
		}
	} elseif (($_POST['current_step'] == "insurance") && (!isset($_POST['insurance']))) {
		$error['insurance'] = 1;
	} elseif (($_POST['current_step'] == "insurance") && (isset($_POST['insurance'])) && ($_POST['insurance'] == "Other") && (!strlen(trim($_POST['other_insurance'])))) {
		$error['other_insurance'] = 1;
	} elseif ($_POST['current_step'] == "gender_aj") {
		if (!isset($_POST['gender'])) {
			$error['gender'] = 1;
		}
		if (!isset($_POST['ashkenazi'])) {
			$error['ashkenazi'] = 1;
		}
	} elseif (($_POST['current_step'] == "gene_mutation") && (!isset($_POST['gene_mutation']))) {
		$error['gene_mutation'] = 1;
	} elseif (($_POST['current_step'] == "gene_mutation") && (isset($_POST['gene_mutation'])) && ($_POST['gene_mutation'] == "Yes")) {
		$num_not_entered = 0;
		for ($i=0; $i < count($_POST['gene']); $i++) {
			if ((strlen($_POST['gene_relation'][$i])) && (!strlen($_POST['gene'][$i]))) {
				$error['gene'][$i] = 1;
			}
			if ((!strlen($_POST['gene_relation'][$i])) && (strlen($_POST['gene'][$i]))) {
				$error['gene_relation'][$i] = 1;
			}
			if ((!strlen($_POST['gene_relation'][$i])) && (!strlen($_POST['gene'][$i]))) {
				$num_not_entered++;
			}
		}
		for ($i=0; $i < count($_POST['gm_cancer']); $i++) {
			if (strlen($_POST['gm_cancer'][$i]) && (!strlen(trim($_POST['gm_cancer_age'][$i])))) {
				$error['gm_cancer_age'][$i] = 1;
			} elseif ((!strlen($_POST['gm_cancer'][$i])) && strlen(trim($_POST['gm_cancer_age'][$i]))) {
				$error['gm_cancer'][$i] = 1;
			} elseif ((!strlen($_POST['gm_cancer'][$i])) && (!strlen(trim($_POST['gm_cancer_age'][$i])))) {
				$error['gm_cancer'][$i] = 1;
				$error['gm_cancer_age'][$i] = 1;
			} elseif (strlen($_POST['gm_cancer'][$i]) && strlen($_POST['gm_cancer_age'][$i]) && (!ctype_digit($_POST['gm_cancer_age'][$i]))) {
				$error['gm_cancer_age'][$i] = 1;
			}
		}
		if (count($_POST['gene']) == $num_not_entered) {
			$error['select_gene'] = 1;
		}
	} elseif (($_POST['current_step'] == "cancer_list") && ($_POST['type'] == "personal") && ((!isset($_POST['personal_cancer'])))) {
		$error['cancer_list'] = 1;
	} elseif (($_POST['current_step'] == "cancer_list") && ($_POST['type'] == "family") && ((!isset($_POST['family_cancer'])))) {
		$error['cancer_list'] = 1;
	} 
if ((isset($_POST['yes_continue_same_rel'])) || (isset($_POST['qualify'])) || (isset($_POST['additional_relatives']))) {	
		$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);
			
		$qualify = $result->fetch_assoc();	
		
		$outliers = $conn->query("SELECT * FROM tbloutlier");
		
		$outlier_qualified = 0;
		
		foreach($outliers as $outlier) {							
			if (($outlier['age_required']) && (isset($_POST[$outlier['field_name'] . "_age"]))) {
				$qualification["outlier"] = "not qualified";
				if ((!strlen($_POST[$outlier['field_name'] . "_age"])) || (!ctype_digit($_POST[$outlier['field_name'] . "_age"]))) {
					$error[$outlier['field_name'] . "_age"] = 1;
				} else {
					$outlier_qualified = 1;
					$qualification["outlier"] = "qualified";
				}					
			}			
		}
		
		$sql = array();
		
		$insurance = $qualify['insurance'];
		
		if ($insurance != "Aetna") {
			$insurance = "NCCN";
		}
		
		$type = "personal";
		$table = tblqualifypers;
		
		$result = $conn->query("SELECT * FROM tblqualifypers WHERE Guid_qualify = " . $_SESSION['id'] . "  AND cancer_type =\"No Cancer/None of the Above\"");
		
		if ($_POST['type'] == "family") {
			$type = "family";
			$table = tblqualifyfam;
		}
		
		$cancer_type = array();
		
		$selected_cancers = $conn->query("SELECT * FROM " . $table . " WHERE Guid_qualify = " . $_SESSION['id'] . " ORDER BY cancer_type");
		
		foreach($selected_cancers as $selected_cancer) {
			if (in_array($selected_cancer['cancer_type'], array("Breast", "Ovarian", "Prostate", "Pancreatic", "Colorectal/Endometrial (Uterine)"))) {				
				array_push($cancer_type, $selected_cancer['cancer_type']);
			} elseif ((in_array($selected_cancer['cancer_type'], array("Gastric", "Ureter", "Renal", "Pelvic", "Small Intestinal", "Sebaceous Adenoma", "Sebaceous Carcinomas", "Brain", "Keratoacanthomas"))) && (!in_array("Lynch Syndrome", $cancer_type))) {
				array_unshift($cancer_type, "Lynch Syndrome"); 
			}
		}
		 
		$option = "";
		$same_rel = array();
		
		for ($i=0; $i < count($cancer_type); $i++) {			
			$questions = $conn->query("SELECT * FROM tblcancerquestion WHERE  insurance = \"" . $insurance . "\" AND question_type=\"" . $type . "\" AND cancer_type = \"" . $cancer_type[$i] . "\" ORDER BY sort_order");
		
			$num_questions = 0;			
			$num_yes = 0;
			$num_no = 0;
			
			$yes_continue = 0;
			$total_children = 0;
			$num_child_yes = 0;
			$num_child_no = 0;
			$num_set = 0;
			$qualification[$cancer_type[$i]] = "qualified";
			
			$num_total_maternal = 0;
			$num_total_maternal_mainq = 0;		
			$num_total_paternal = 0;
			$num_total_paternal_mainq = 0;
			
			foreach($questions as $question) {
				if (!$question['indent']) {
					$num_questions++;
				}
				
				if (isset($_POST[$question['field_name']])) {
					$num_set++;					
					if ($_POST[$question['field_name']] == "Yes") {
						if (!$question['indent']) {
							$num_yes++;
						}
						if ($question['personal_age_needed']) {
							$num_meeting_criteria = 0;
							
							$age_personal = $question['field_name'] . "_age_personal";
							
							for ($j=0; $j < count($_POST[$age_personal]); $j++) {
								if (strlen($_POST[$age_personal][$j])) {
									if (!ctype_digit($_POST[$age_personal][$j])) {
										$error[$age_personal][$j] = 1;			
									} elseif (strlen($question['max_personal_age']) && ($_POST[$age_personal][$j] <= $question['max_personal_age'])) {
										$num_meeting_criteria++;
									} elseif (!strlen($question['max_personal_age'])) {
										$num_meeting_criteria++;
									}
								} else {
									$error[$age_personal][$j] = 1;									
								}
							}

							if ($num_meeting_criteria < $question['num_to_meet_criteria_p']) {
								for ($j=0; $j < count($_POST[$age_personal]); $j++) {									
									$error[$age_personal][$j] = 1;
								}
							}							
						}
						if ($question['personal_cancer_needed']) {
							$count_col_endo = 0;							
							$selected_cancer = array();
							$cancer = $question['field_name'] . "_cancer_personal";							
							
							for ($j=0; $j < count($_POST[$cancer]); $j++) {
								if (!strlen($_POST[$cancer][$j])) {
									$error[$cancer][$j] = 1;
								} else {
									$selected_cancer[$_POST[$cancer][$j]] = "1";
								}
								if (($question['special_rule'] == 4) && (($_POST[$cancer][$j] == "Colorectal") || ($_POST[$cancer][$j] == "Endometrial (Uterine)"))) {
									$count_col_endo++;
								}								
							}							
							if (($question['special_rule'] == 4) && (!$count_col_endo)) {
								for ($j=0; $j < count($_POST[$cancer]); $j++) {
									$error[$cancer][$j] = 1;
								}
							}
						}			
						if ($question['relation_needed']) {
							$relation = $question['field_name'] . "_relation";

							$num_maternal = 0;
							$num_paternal = 0;
							$num_2nd_degree = 0;

							for ($j=0; $j < count($_POST[$relation]); $j++) {
								if (!strlen($_POST[$relation][$j])) {
									$error[$relation][$j] = 1;
								} else {
									if ($question['min_num_relative']) {
										if (strpos($_POST[$relation][$j], "Maternal") !== false) {
											$num_maternal++;
											$num_total_maternal++;
											if ($question['yes_continue']) {
												$num_total_maternal_mainq++;
											}
										} elseif (strpos($_POST[$relation][$j], "Paternal") !== false) {
											$num_paternal++;
											$num_total_paternal++;
											if ($question['yes_continue']) {
												$num_total_paternal_mainq++;
											}
										}

										if (in_array($_POST[$relation][$j],array("Mother","Sister","Daughter","Father","Brother","Son","Granddaughter","Grandson","Niece","Nephew","Male First-cousin","Female First-cousin","Great-Grandson","Great-Granddaughter"))) {
											$num_maternal++;
											$num_total_maternal++;
											$num_paternal++;
											$num_total_paternal++;
											if ($question['yes_continue']) {
												$num_total_maternal_mainq++;
												$num_total_paternal_mainq++;
											}
										}
									}
									if ($question['special_rule'] == 3) {
										$result = $conn->query("SELECT * FROM tblseconddegrel WHERE value = \"" . $_POST[$relation][$j] . "\"");
										if (mysqli_num_rows($result)) {											
											$num_2nd_degree++;
										}
									}
								}
							}							
							$min_num_relative = $question['min_num_relative'];
							if (($question['special_rule'] == 3) && ($num_2nd_degree) && ($num_2nd_degree >= 2)) {
								$min_num_relative = 2;
							}
							if (($question['min_num_relative']) || (($question['special_rule'] == 3) && ($num_2nd_degree) && ($num_2nd_degree >= 2))) {
								if (($num_maternal < $min_num_relative) && ($num_paternal < $min_num_relative)) {
									$error["min_num_relative"] = 1;
								}
							} 							
							if ($error["min_num_relative"] || (($question['special_rule'] == 3) && ($num_2nd_degree) && ($num_2nd_degree < 2))) {
								for ($j=0; $j < count($_POST[$relation]); $j++) {
									$error[$relation][$j] = 1;
								}
							}							
						}				
						if ($question['cancer_needed']) {							
							$selected_cancer = array();
							$cancer = $question['field_name'] . "_cancer";
							
							for ($j=0; $j < count($_POST[$cancer]); $j++) {
								if (!strlen($_POST[$cancer][$j])) {
									$error[$cancer][$j] = 1;
								} else {
									$selected_cancer[$_POST[$cancer][$j]] = "1";
								}								
							}
							$match_all_cancers = $question['match_all_cancers'];
							
							if ($question['special_rule'] == 7) {
								$match_all_cancers = 1;								
							}
							if ($match_all_cancers) {		
								if ((count($selected_cancer) != count($_POST[$cancer])) || (($question['special_rule'] == 4) && (!$count_col_endo))) {
									for ($j=0; $j < count($_POST[$cancer]); $j++) {
										$error[$cancer][$j] = 1;
									}
								}
							}
						}						
						if ($question['gene_mutation_needed']) {
							$gene_mutation = $question['field_name'] . "_gene_mutation";
							
							for ($j=0; $j < count($_POST[$gene_mutation]); $j++) {
								if (!strlen($_POST[$gene_mutation][$j])) {
									$error[$gene_mutation][$j] = 1;
								}
							}
						}								
						if ($question['relation_age_needed']) {
							$num_meeting_criteria = 0;
							$num_under_51 = 0;
							$num_under_50 = 0;
							
							$age_relative = $question['field_name'] . "_age_relative";
							
							for ($j=0; $j < count($_POST[$age_relative]); $j++) {
								if (strlen($_POST[$age_relative][$j])) {
									if (!ctype_digit($_POST[$age_relative][$j])) {
										$error[$age_relative][$j] = 1;
									} elseif (strlen($question['max_relative_age']) && ($_POST[$age_relative][$j] <= $question['max_relative_age'])) {
										$num_meeting_criteria++;
									} elseif (!strlen($question['max_relative_age'])) {
										$num_meeting_criteria++;
									}
									if (($question['special_rule'] == 2) && (ctype_digit($_POST[$age_relative][$j])) && ($_POST[$age_relative][$j] < 51)) {
										$num_under_51 += 1;
									}
									if ((($question['special_rule'] == 5) || ($question['special_rule'] == 6)) && (ctype_digit($_POST[$age_relative][$j])) && ($_POST[$age_relative][$j] < 50)) {
										$num_under_50 += 1;
									}
								} else {
									$error[$age_relative][$j] = 1;									
								}
							}
							$parent_age_fn = "";
							if ($question['special_rule'] == 6) {
								$parent_age_fn = $question['parent_field_name'] . "_age_relative";
								for ($j=0; $j < count($_POST[$parent_age_fn]); $j++) {
									if ((ctype_digit($_POST[$parent_age_fn][$j])) && ($_POST[$parent_age_fn][$j] < 50)) {
										$num_under_50 += 1;
									}
								}
							}
							if (($num_meeting_criteria < $question['num_to_meet_criteria_r']) || (($question['special_rule'] == 2) && (!$num_under_51)) || ((($question['special_rule'] == 5) || ($question['special_rule'] == 6)) && (!$num_under_50))) {
								for ($j=0; $j < count($_POST[$age_relative]); $j++) {
									if (($_POST[$age_relative][$j] > $question['max_relative_age']) || (!$num_under_51) || (!$num_under_50)) {
										$error[$age_relative][$j] = 1;
									}
								}
							}
							if (($question['special_rule'] == 6) && (!$num_under_50)) {
								for ($j=0; $j < count($_POST[$parent_age_fn]); $j++) {									
									$error[$parent_age_fn][$j] = 1;									
								}
							}
						}
						if ($question['yes_continue']) {
							$yes_continue++;
							
							$questions_childrens = $conn->query("SELECT * FROM tblcancerquestion WHERE parent_field_name=\"" . $question['field_name'] . "\"");
		
							$total_children += $questions_childrens->num_rows;
							
							foreach($questions_childrens as $questions_children) {
								if ($_POST[$questions_children['field_name']] == "Yes") {	
									$num_child_yes++;
								} elseif (($_POST[$questions_children['field_name']] == "No") || ($_POST[$questions_children['field_name']] == "Unknown")) {
									$num_child_no++;
								}
							}							
						}
						if ($question['field_name'] == "pnb14") {
							$num_meeting_criteria++;
						}
						
						if (($question['relation_needed']) && ($question['cancer_needed']) && ($question['relation_age_needed'])) {						
							for ($j=0; $j < count($_POST[$relation]); $j++) {							
								if ((strlen($question['relation_needed'])) && (strlen($question['cancer_needed'])) && (strlen($question['relation_age_needed']))) {
									$same_rel[$cancer_type[$i] . ":" . $_POST[$relation][$j] . ":" . $_POST[$cancer][$j] . ":" . $_POST[$age_relative][$j]] ++;
								}							
							}
						}
					} elseif (($_POST[$question['field_name']] == "No") || ($_POST[$question['field_name']] == "Unknown")) {						
						if (!$question['indent']) {
							$num_no++;
						}
					} 
				} elseif ($question['hide_yes_no']) {					
					if (strpos($_POST[$relation][$j], "Maternal") !== false) {						
						$num_total_maternal_mainq++;
						$num_total_maternal++;											
					} elseif (strpos($_POST[$relation][$j], "Paternal") !== false) {						
						$num_total_paternal_mainq++;
						$num_total_paternal++;											
					}

					if (in_array($_POST[$relation][$j],array("Mother","Sister","Daughter","Father","Brother","Son","Granddaughter","Grandson","Niece","Nephew","Male First-cousin","Female First-cousin","Great-Grandson","Great-Granddaughter"))) {
						$num_total_maternal_mainq++;
						$num_total_maternal++;
						$num_total_paternal_mainq++;
						$num_total_paternal++;											
					}					
					
					$questions_childrens = $conn->query("SELECT * FROM tblcancerquestion WHERE parent_field_name=\"" . $question['field_name'] . "\"");

					$total_children_hide = $questions_childrens->num_rows;
					
					foreach($questions_childrens as $questions_children) {
						if ($_POST[$questions_children['field_name']] == "Yes") {	
							$num_child_yes_hide++;
						} elseif (($_POST[$questions_children['field_name']] == "No") || ($_POST[$questions_children['field_name']] == "Unknown")) {
							$num_child_no_hide++;
						}
					}
					if ($num_child_yes_hide || $num_child_no_hide) {
						$yes_continue++;
						$num_yes++;
						$num_child_yes += $num_child_yes_hide;
						$num_child_no += $num_child_no_hide;
						$total_children += $total_children_hide;
					}
				}
			}				
			if ($num_set && (!count($error))) {
				if ($yes_continue && (($num_child_yes + $num_child_no) == 0)) {
					$error[$cancer_type[$i]] = "none_selected";
				} elseif ($yes_continue && (!$num_child_yes) && ($total_children != $num_child_no)) {
					$error[$cancer_type[$i]] = "none_selected";
				} elseif (($num_yes + $num_no) == 0) {
					$error[$cancer_type[$i]] = "none_selected";				
				} elseif ((!$num_yes) && ($num_questions != $num_no)) {
					$error[$cancer_type[$i]] = "none_selected";				
				}					
					
				if (($num_questions == $num_no) || ($yes_continue && ($yes_continue >= $num_yes)) && ($yes_continue && ($total_children == $num_child_no))) {					
					if (($type == "personal") && (!$outlier_qualified)) {
						//$conn->query("DELETE FROM tblqualifypers WHERE Guid_qualify=" . $_SESSION['id']);
									
						//$sql = "INSERT INTO tblqualifypers (Guid_qualify, cancer_type) VALUES (\"" . $_SESSION['id'] .  "\",\"No Cancer/None of the Above\")";
			
						//$conn->query($sql);
					}
					
					$qualification[$cancer_type[$i]] = "not qualified";
				}					
			}
			if ($num_total_maternal_mainq && ($num_total_maternal < $num_total_maternal_mainq)) {
				$qualification[$cancer_type[$i]] = "not qualified";
			} elseif ($num_total_paternal_mainq && ($num_total_paternal < $num_total_paternal_mainq)) {
				$qualification[$cancer_type[$i]] = "not qualified";
			}			
		}		
		
		if (!isset($_POST['yes_continue_same_rel'])) {
			foreach ($same_rel as $key => $value) {		
				if ($value > 1) {
					$error['same_rel'] = "1";
					$error['same_rel_list'] .= $key;
				}
			}
		}
		
		$count_values = (array_count_values($qualification));
	
		$not_qualified = 0;
	
		if (count($qualification) == $count_values["not qualified"]) {
			$not_qualified = 1;
		}		
	}
	
	require ("db/dbdisconnect.php");		
}
function display_qualification($qualification_text, $not_qualified) {
	$date = new DateTime("now", new DateTimeZone('America/New_York'));
	
	generate_header("Do I Meet the " . $insurance . " Clinical Guidelines for Hereditary Cancer Testing?");
	
	require ("db/dbconnect.php");
		
	$conn->query($sql);
	
	$personal = $conn->query("SELECT * FROM tblqualifyans WHERE cancer_personal IS NOT NULL AND Guid_qualify=" . $_SESSION['id']);

	$count_personal = mysqli_num_rows($personal);

	//gather all first degree relatives entered
	
	$first_deg_rel = array();

	$relatives = $conn->query("SELECT a.relative FROM tblfirstdegrel tr LEFT JOIN tblqualifyans a ON a.relative = tr.value WHERE Guid_qualify=" . $_SESSION['id'] . " AND a.relative IS NOT NULL");
		
	if (mysqli_num_rows($relatives)) {
		foreach($relatives as $relative) {
			array_push($first_deg_rel, $relative['relative']);
		}
	}
	
	$relatives = $conn->query("SELECT a.relative FROM tblfirstdegrel tr LEFT JOIN tbl_additional_info a ON a.relative = tr.value WHERE Guid_qualify=" . $_SESSION['id']);
		
	if (mysqli_num_rows($relatives)) {
		foreach($relatives as $relative) {
			array_push($first_deg_rel, $relative['relative']);
		}
	}
	
	$count_first_deg_rel = array_count_values($first_deg_rel);	
	
	//gather all second degree relatives entered
	
	$second_deg_rel = array();

	$relatives = $conn->query("SELECT a.relative FROM tblseconddegrel tr LEFT JOIN tblqualifyans a ON a.relative = tr.value WHERE Guid_qualify=" . $_SESSION['id'] . " AND a.relative IS NOT NULL");
		
	if (mysqli_num_rows($relatives)) {
		foreach($relatives as $relative) {
			if (($relative['relative'] == "Maternal Grandfather") || ($relative['relative'] == "Maternal Grandmother") || ($relative['relative'] == "Paternal Grandfather") || ($relative['relative'] == "Paternal Grandmother")) {
				if (!in_array($relative['relative'], $second_deg_rel)) {
					array_push($second_deg_rel, $relative['relative']);
				}
			} else {
				array_push($second_deg_rel, $relative['relative']);
			}
		}
	}
	
	$relatives = $conn->query("SELECT a.relative FROM tblseconddegrel tr LEFT JOIN tbl_additional_info a ON a.relative = tr.value WHERE Guid_qualify=" . $_SESSION['id']);
		
	if (mysqli_num_rows($relatives)) {
		foreach($relatives as $relative) {
			if (($relative['relative'] == "Maternal Grandfather") || ($relative['relative'] == "Maternal Grandmother") || ($relative['relative'] == "Paternal Grandfather") || ($relative['relative'] == "Paternal Grandmother")) {
				if (!in_array($relative['relative'], $second_deg_rel)) {
					array_push($second_deg_rel, $relative['relative']);
				}
			} else {
				array_push($second_deg_rel, $relative['relative']);
			}
		}
	}
	
	$count_second_deg_rel = array_count_values($second_deg_rel);

	//gather all third degree relatives entered
	
	$third_deg_rel = array();

	$relatives = $conn->query("SELECT relative FROM tblthirddegrel tr LEFT JOIN tblqualifyans a ON a.relative = tr.value WHERE Guid_qualify=" . $_SESSION['id']);
		
	if (mysqli_num_rows($relatives)) {
		foreach($relatives as $relative) {
			if (($relative['relative'] == "Maternal Great-Grandfather") || ($relative['relative'] == "Maternal Great-Grandmother") || ($relative['relative'] == "Paternal Great-Grandfather") || ($relative['relative'] == "Paternal Great-Grandmother")) {
				if (!in_array($relative['relative'], $third_deg_rel)) {	
					array_push($third_deg_rel, $relative['relative']);	
				}
			} else {
				array_push($third_deg_rel, $relative['relative']);		
			}
		}	
	}
	
	$relatives = $conn->query("SELECT a.relative FROM tblthirddegrel tr LEFT JOIN tbl_additional_info a ON a.relative = tr.value WHERE Guid_qualify=" . $_SESSION['id']);
	
	if (mysqli_num_rows($relatives)) {
		foreach($relatives as $relative) {
			if (($relative['relative'] == "Maternal Great-Grandfather") || ($relative['relative'] == "Maternal Great-Grandmother") || ($relative['relative'] == "Paternal Great-Grandfather") || ($relative['relative'] == "Paternal Great-Grandmother")) {
				if (!in_array($relative['relative'], $third_deg_rel)) {	
					array_push($third_deg_rel, $relative['relative']);	
				}
			} else {
				array_push($third_deg_rel, $relative['relative']);		
			}
		}	
	}
	
	$count_third_deg_rel = array_count_values($third_deg_rel);

	$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);
	
	$qualify = $result->fetch_assoc();
	
	$insurance = $qualify["insurance"];
	
	if (($qualify["insurance"] != "Aetna") && ($qualify["insurance"] != "Medicare")) {
		$insurance = "NCCN";
	}
	
	if ($not_qualified) {
		$yesorno = "no";
	} else {
		$yesorno = "yes";
	}	
	if (isset($_GET['lc']) && ($_GET['lc'] == "O")) {
?>
		<input type="hidden" id="in_office_print" value="1">
<?php
	}
?>
	<input type="hidden" name="prev_step" value="<?php echo $_POST['current_step']; ?>">
	<input type="hidden" name="fieldname" value="<?php echo $_POST['fieldname']; ?>">
	<input type="hidden" name="type" value="<?php echo $_POST['type']; ?>">	
	<input type="hidden" name="not_qualified" value="<?php echo $not_qualified; ?>">
<?php
	for ($i=0; $i < count($qualification_text); $i++) {
?>
	<input type="hidden" name="qualification_text[]" value="<?php echo $qualification_text[$i]; ?>">
<?php
	}
?>
	<section class="q_result <?php echo $yesorno ?> wrapper">		
<?php			
	if ($not_qualified) {
?>
		<input type="hidden" name="qualified" value="No">
		<h2 class="iconP">No</h2>			
		<div class="q_result_title">
			<p>You do not meet the <?php echo $insurance; ?> clinical guidelines for:</p>
<?php
	} else {
?>
		<input type="hidden" name="qualified" value="Yes">
		<h2 class="iconP">Yes</h2>			
		<div class="q_result_title">
			<p>You are likely to meet the <?php echo $insurance; ?> clinical guidelines and testing is recommended for:</p>
<?php
	}
	for ($i=0; $i < count($qualification_text); $i++) {
?>
			<input type="hidden" name="qualification_text[]" value="<?php echo $qualification_text[$i] . ""; ?>">
			<strong><?php echo $qualification_text[$i] . ""; ?></strong>

<?php 

	}
?>	
		</div>
	</section>
	
                <div class="cols wrapper">
					<section class="pedigree_info">
						<button type="button" class="close_modal toggle" data-on=".pedigree_info"></button>
						
						<ul class="q_summary">
<?php
	$guideline = array();
	$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify=" . $_SESSION['id'] . " AND gene_mutation = \"Yes\"");
	
	if (mysqli_num_rows($result)) {		
		$qualify_gene_mutation = $result->fetch_assoc();
		
		$sql = "SELECT * FROM tbloutlier WHERE gene_mutation=\"Yes\"";
		
		if ($qualify_gene_mutation['insurance'] == "Aetna") {
			$sql .= " AND insurance=\"" . $qualify_gene_mutation['insurance'] . "\" AND gender=\"" . $qualify_gene_mutation['gender'] . "\"";
		} else {
			$sql .= " AND insurance=\"NCCN\"";
		}
		
		$result = $conn->query($sql);
		
		$outlier = $result->fetch_assoc();
		
		$family_genes = $conn->query("SELECT * FROM tblqualifygene WHERE Guid_qualify=" . $_SESSION['id']);
		
		$display_gene_relation = array();		
		$display_gene =array();
		$gene_guideline_met = array();
		$gene_temp = array();
		$gene_gdmet=array();
		
		$lynch_genes = array("EPCAM","MLH1","MSH2","MSH6","PMS2");
			
		foreach($family_genes as $family_gene) {			
			if (($family_gene['gene_relation'] == "Mother") || ($family_gene['gene_relation'] == "Father") || ($family_gene['gene_relation'] == "Maternal Grandfather") || ($family_gene['gene_relation'] == "Maternal Grandmother") || ($family_gene['gene_relation'] == "Paternal Grandfather") || ($family_gene['gene_relation'] == "Paternal Grandmother") || ($family_gene['gene_relation'] == "Maternal Great-Grandfather") || ($family_gene['gene_relation'] == "Maternal Great-Grandmother") || ($family_gene['gene_relation'] == "Paternal Great-Grandfather") || ($family_gene['gene_relation'] == "Paternal Great-Grandmother")) {			
				$count[$family_gene['gene_relation']] = 1; 
			} else {
				$count[$family_gene['gene_relation']] += 1; 
			}
			
			$id = strtolower(str_replace(" ", "_", $family_gene['gene_relation'])) . $count[$family_gene['gene_relation']];
			
			if (!isset($display_gene_relation[$id])) {								
				$display_gene_relation[$id] = $family_gene['gene_relation'];
			}
			
			if (isset($gene_guideline_met[$id])) {
				$gene_gdmet=$gene_guideline_met[$id];
			} else {
				$gene_gdmet=array();
			}
			
			if (in_array($family_gene['gene'], $lynch_genes)) {				
				if (!in_array("Known Lynch syndrome mutation in the family", $guideline)) {
					array_push($guideline, "Known Lynch syndrome mutation in the family");								
				}
				if (!in_array("Known Lynch syndrome mutation in the family", $gene_gdmet)) {
					array_push($gene_gdmet, "Known Lynch syndrome mutation in the family");	
				}
			} else {				
				if (!in_array($outlier['guideline_met'], $guideline)) {					
					array_push($guideline, $outlier['guideline_met']);					
				}
				if (!in_array($outlier['guideline_met'], $gene_gdmet)) {
					array_push($gene_gdmet, $outlier['guideline_met']);
				}
			}
						
			$gene_guideline_met[$id] = $gene_gdmet;
			
			if (isset($display_gene[$id])) {
				$gene_temp = $display_gene[$id];
			} else {
				$gene_temp = array();
			}
			
			if ($family_gene['gene'] == "Both") {
				if (!in_array("BRCA1", $gene_temp)) {
					array_push($gene_temp, "BRCA1");
				}
				if (!in_array("BRCA2", $gene_temp)) {
					array_push($gene_temp, "BRCA2");
				}					
			} else {
				if (!in_array($family_gene['gene'], $gene_temp)) {
					array_push($gene_temp, $family_gene['gene']);
				}
			}
			
			$display_gene[$id] = $gene_temp;
				
			$relatives = $conn->query("SELECT * FROM tblfirstdegrel WHERE value=\"" . $family_gene['gene_relation'] . "\"");
			
			if (mysqli_num_rows($relatives)) {
				array_push($first_deg_rel, $family_gene['gene_relation']);
				$count_first_deg_rel[$family_gene['gene_relation']]++;					
			} else {
				$relatives = $conn->query("SELECT * FROM tblseconddegrel WHERE value=\"" . $family_gene['gene_relation'] . "\"");			
			
				if (mysqli_num_rows($relatives)) {
					if (($family_gene['gene_relation'] == "Maternal Grandfather") || ($family_gene['gene_relation'] == "Maternal Grandmother") || ($family_gene['gene_relation'] == "Paternal Grandfather") || ($family_gene['gene_relation'] == "Paternal Grandmother")) {
						if (!in_array($family_gene['gene_relation'], $second_deg_rel)) {
							array_push($second_deg_rel, $family_gene['gene_relation']);
							$count_second_deg_rel[$family_gene['gene_relation']]++;
						}
					} else {
						array_push($second_deg_rel, $family_gene['gene_relation']);
						$count_second_deg_rel[$family_gene['gene_relation']]++;
					}									
				} else {
					$relatives = $conn->query("SELECT * FROM tblthirddegrel WHERE value=\"" . $family_gene['gene_relation'] . "\"");				
			
					if (mysqli_num_rows($relatives)) {
						if (($family_gene['gene_relation'] == "Maternal Great-Grandfather") || ($family_gene['gene_relation'] == "Maternal Great-Grandmother") || ($family_gene['gene_relation'] == "Paternal Great-Grandfather") || ($family_gene['gene_relation'] == "Paternal Great-Grandmother")) {					
							if (!in_array($family_gene['gene_relation'], $second_deg_rel)) {
								array_push($third_deg_rel, $family_gene['gene_relation']);
								$count_third_deg_rel[$family_gene['gene_relation']]++;
							}
						} else {
							array_push($third_deg_rel, $family_gene['gene_relation']);
							$count_third_deg_rel[$family_gene['gene_relation']]++;
						}															
					}	
				}
			}			 			
		}
	
		foreach ($display_gene_relation as $rel_id => $gene_relation) {
?>
							<li id="<?php echo $rel_id; ?>">
								<h3>My <span class="maincol"><?php echo $gene_relation; ?></span></h3>								
								<div class="pInfo_type">
									<strong>Gene Mutation<?php echo ((count($display_gene[$rel_id]) > 1) ? "s" : ""); ?></strong>
									<p><?php echo implode(", ", $display_gene[$rel_id]); ?></p>
								</div>
								<div class="pInfo_type">
									<strong>Guideline Met</strong>
<?php
			foreach ($gene_guideline_met[$rel_id] as $key => $guide) {
?>
									<p><?php echo $guide; ?></p>
<?php
			}
?>
								</div>
							</li>
<?php
			unset($display_guideline);
		}
	}
	$me_cancer_personal = array();
	$me_age_personal = array();
	$me_guideline_met = array();
	
	$result = $conn->query("SELECT a.age_personal, a.cancer_personal, o.guideline_met FROM tblqualifyans a LEFT JOIN tbloutlier o ON a.Guid_outlier = o.Guid_outlier WHERE Guid_qualify=" . $_SESSION['id'] . " AND a.Guid_outlier IS NOT NULL");
	
	if (mysqli_num_rows($result)) {		
		foreach($result as $outlier) {
			if (!in_array($outlier['guideline_met'], $guideline)) {
				array_push($guideline, $outlier['guideline_met']);
			}
			array_push($me_cancer_personal, $outlier['cancer_personal']);
			array_push($me_age_personal, $outlier['age_personal']);
			array_push($me_guideline_met, $outlier['guideline_met']);			
		}
	}
	
	$personals = $conn->query("SELECT a.age_personal, a.cancer_personal, q.guideline_met FROM tblqualifyans a LEFT JOIN tblcancerquestion q ON a.Guid_question = q.Guid_question WHERE Guid_qualify=" . $_SESSION['id'] . " AND cancer_personal IS NOT NULL AND a.Guid_outlier IS NULL ORDER BY q.sort_order");	
	
	$num_personals = mysqli_num_rows($personals);
	
	if ($num_personals) {	
		foreach($personals as $personal) {			
			if (strlen($personal['guideline_met'])) {
				if (!in_array($personal['guideline_met'], $me_guideline_met)) {
					array_push($me_guideline_met, $personal['guideline_met']);
				}
			}
			array_push($me_cancer_personal, $personal['cancer_personal']);
			array_push($me_age_personal, $personal['age_personal']);
		}
		
		$result = $conn->query("SELECT q.guideline_met FROM tblqualifyans a LEFT JOIN tblcancerquestion q ON a.Guid_question = q.Guid_question WHERE Guid_qualify=" . $_SESSION['id'] . " AND a.relative IS NOT NULL AND a.Guid_outlier IS NULL GROUP BY a.Guid_question");
		
		if (mysqli_num_rows($result)) {
			foreach($result as $relative) {					
				if (!in_array($relative['guideline_met'], $me_guideline_met)) {
					array_push($me_guideline_met, $relative['guideline_met']);
				}
			}
		}
	}
	
	$result = $conn->query("SELECT * FROM tblgmcancer WHERE Guid_qualify=" . $_SESSION['id']);
		
	if (mysqli_num_rows($result)) {
		foreach($result as $cancer) {
			array_push($me_cancer_personal, $cancer['cancer']);
			array_push($me_age_personal, $cancer['cancer_age']);
		}
	}
	
	if (count($me_cancer_personal)) {		
?>
							<li id="myself">
								<h3>Me</span></h3>
								<div class="pInfo_type">
									<strong>Cancer Diagnosis</strong>
										<ul class="cancer_history">
<?php
		for ($i=0; $i < count($me_cancer_personal); $i++) {
?>
										<li><?php echo $me_cancer_personal[$i]; ?><?php echo (strlen($me_age_personal[$i]) ? " at age " . $me_age_personal[$i] : ""); ?></li>
<?php
		}
?>
									</ul>
								</div>
								
								<div class="pInfo_type">
									<strong>Guideline Met</strong>
<?php
		for ($i=0; $i < count($me_guideline_met); $i++) {
?>
									<p><?php echo $me_guideline_met[$i]; ?></p>
<?php
		}
?>
								</div>
							</li>
<?php
	} else {
?>
							<li id="myself" class="no_cancer_history">
								<h3>Me</span></h3>
								<div class="pInfo_type">
									<strong>Cancer Diagnosis</strong>
									<p>No Cancer History</p>
								</div>
							</li>
<?php
	}
	$relatives = $conn->query("SELECT a.age_relative, a.cancer_type, a.additional_cancer_type, a.relative, a.deceased, q.guideline_met, q.additional_question, q.special_rule FROM tblqualifyans a LEFT JOIN tblcancerquestion q ON a.Guid_question = q.Guid_question WHERE Guid_qualify=" . $_SESSION['id'] . " AND a.relative IS NOT NULL AND a.Guid_outlier IS NULL ORDER BY q.sort_order");
	
	$deceased_relative = array();
	
	$relation=array();
	$gdmet=array();
	$cancer_detail=array();
	
	if (mysqli_num_rows($relatives)) {
		foreach($relatives as $relative) {			
			if (($relative['relative'] == "Mother") || ($relative['relative'] == "Father") || ($relative['relative'] == "Maternal Grandfather") || ($relative['relative'] == "Maternal Grandmother") || ($relative['relative'] == "Paternal Grandfather") || ($relative['relative'] == "Paternal Grandmother") || ($relative['relative'] == "Maternal Great-Grandfather") || ($relative['relative'] == "Maternal Great-Grandmother") || ($relative['relative'] == "Paternal Great-Grandfather") || ($relative['relative'] == "Paternal Great-Grandmother")) {		
				$count[$relative['relative']] = 1; 
			} else {
				$count[$relative['relative']] += 1; 
			}
			$id = strtolower(str_replace(" ", "_", $relative['relative'])) . $count[$relative['relative']];
			if (!isset($relation[$id])) {								
				$relation[$id] = $relative['relative'];
			}
			if ($relative['deceased']) {
				array_push($deceased_relative, $id);
			}
			if (strlen($relative['guideline_met'])) {
				if (!in_array($relative['guideline_met'], $guideline_met[$id])) {
					if (isset($guideline_met[$id])) {
						$gdmet=$guideline_met[$id];
					} else {
						$gdmet=array();
					}
					array_push($gdmet, $relative['guideline_met']);
					$guideline_met[$id] = $gdmet;
				}
			}
			if (strlen($relative['cancer_type'])) {
				if (isset($cancer_detail[$id])) {
					$c_type=$cancer_detail[$id];
				} else {
					$c_type=array();
				}
				
				$display_text = $relative['cancer_type'];
				if (strlen($relative['age_relative'])) {
					$display_text .= " at age " . $relative['age_relative'];
				}
				
				array_push($c_type, $display_text);
				$cancer_detail[$id] = $c_type;
			}
			if (strlen($relative['additional_cancer_type'])) {				
				$additional_cancer[$id] = $relative['additional_cancer_type'];				
			}
			
			// if (strlen($relative['additional_question'])) {
				// $result = $conn->query("SELECT a.cancer_type, a.age_relative, q.guideline_met FROM tblqualifyans a LEFT JOIN tblcancerquestion q ON a.Guid_question = q.Guid_question WHERE Guid_qualify=" . $_SESSION['id'] . " AND q.field_name in(" . $relative['additional_question'] . ")");
				// echo "SELECT a.cancer_type, a.age_relative, q.guideline_met FROM tblqualifyans a LEFT JOIN tblcancerquestion q ON a.Guid_question = q.Guid_question WHERE Guid_qualify=" . $_SESSION['id'] . " AND q.field_name in(" . $relative['additional_question'] . ")";
				// if (mysqli_num_rows($result)) {
					// $additional = $result->fetch_assoc();
					// $additional_cancer = $additional['cancer_type'];
					// $guideline_met = $additional['guideline_met'];
					// $additional_age = $additional['age_relative'];
				// }
			// }
		}
		
		foreach ($relation as $rel_id => $rel) {
?>
							<li id="<?php echo $rel_id; ?>">
								<h3>My <span class="maincol"><?php echo $rel; ?></span></h3>
								<div class="pInfo_type">
									<strong>Cancer Diagnosis</strong>
										<ul class="cancer_history">
<?php
				foreach ($cancer_detail[$rel_id] as $key => $cancer_det) {
?>
											<li><?php echo $cancer_det; ?></li>
<?php
				}				
?>
										</ul>
								</div>
<?php				
				if (strlen($additional_cancer[$rel_id])) {
?>
								<div class="pInfo_type">
									<strong>Additional Cancer Diagnosis</strong>
									<p><?php echo $additional_cancer[$rel_id]; ?> 
<?php
					if (strlen($additional_age)) {
?>
									at age <?php echo $additional_age; ?>
<?php
					}
?>
									</p>
								</div>
<?php
				}				
?>
								<div class="pInfo_type">
									<strong>Guideline Met</strong>
<?php
				foreach ($guideline_met[$rel_id] as $key => $guide) {
?>
									<p><?php echo $guide; ?></p>
<?php
				}
?>
								</div>
							</li>
<?php			
				
		}
	}
	
	$relatives = $conn->query("SELECT age_relative, cancer_type, relative, deceased FROM tbl_additional_info WHERE Guid_qualify=" . $_SESSION['id']);
	
	$deceased_relative = array();
	
	$relation=array();
	$gdmet=array();
	$cancer_detail=array();
	
	if (mysqli_num_rows($relatives)) {
		foreach($relatives as $relative) {			
			if (($relative['relative'] == "Mother") || ($relative['relative'] == "Father") || ($relative['relative'] == "Maternal Grandfather") || ($relative['relative'] == "Maternal Grandmother") || ($relative['relative'] == "Paternal Grandfather") || ($relative['relative'] == "Paternal Grandmother") || ($relative['relative'] == "Maternal Great-Grandfather") || ($relative['relative'] == "Maternal Great-Grandmother") || ($relative['relative'] == "Paternal Great-Grandfather") || ($relative['relative'] == "Paternal Great-Grandmother")) {		
				$count[$relative['relative']] = 1; 
			} else {
				$count[$relative['relative']] += 1; 
			}
			$id = strtolower(str_replace(" ", "_", $relative['relative'])) . $count[$relative['relative']];
			if (!isset($relation[$id])) {								
				$relation[$id] = $relative['relative'];
			}
			if ($relative['deceased']) {
				array_push($deceased_relative, $id);
			}			
			if (strlen($relative['cancer_type'])) {
				if (isset($cancer_detail[$id])) {
					$c_type=$cancer_detail[$id];
				} else {
					$c_type=array();
				}
				
				$display_text = $relative['cancer_type'];
				if (strlen($relative['age_relative'])) {
					$display_text .= " at age " . $relative['age_relative'];
				}
				
				array_push($c_type, $display_text);
				$cancer_detail[$id] = $c_type;
			}				
		}
		
		foreach ($relation as $rel_id => $rel) {
?>
							<li id="<?php echo $rel_id; ?>">
								<h3>My <span class="maincol"><?php echo $rel; ?></span></h3>
								<div class="pInfo_type">
									<strong>Cancer Diagnosis</strong>
										<ul class="cancer_history">
<?php
				foreach ($cancer_detail[$rel_id] as $key => $cancer_det) {
?>
											<li><?php echo $cancer_det; ?></li>
<?php
				}				
?>
										</ul>
								</div>
<?php				
				if (strlen($additional_cancer)) {
?>
								<div class="pInfo_type">
									<strong>Additional Cancer Diagnosis</strong>
									<p><?php echo $additional_cancer; ?> 
<?php
					if (strlen($additional_age)) {
?>
									at age <?php echo $additional_age; ?>
<?php
					}
?>
									</p>
								</div>
<?php
				}				
?>
								<div class="pInfo_type">
									<strong>Guideline Met</strong>
<?php
				foreach ($guideline_met[$rel_id] as $key => $guide) {
?>
									<p><?php echo $guide; ?></p>
<?php
				}
?>
								</div>
							</li>
<?php			
				
		}
	}
	
	$count = array();
	
	$outliers = $conn->query("SELECT * FROM  tbloutlier WHERE field_name IS NULL");

	if (mysqli_num_rows($outliers)) {
		foreach($outliers as $outlier) {
			$sql = "SELECT * FROM tblqualify WHERE Guid_qualify=" . $_SESSION['id'];
			if ($outlier['insurance'] != "N/A") {
				$sql .= " AND insurance=\"" . $outlier['insurance'] . "\"";
			}
			if ($outlier['gender'] != "N/A") {				
				$sql .= " AND gender=\"" . $outlier['gender'] . "\"";
			}
			if ($outlier['ashkenazi'] != "N/A") {				
				$sql .= " AND ashkenazi IS NOT NULL ";
			}
			if ($outlier['gene_mutation'] != "N/A") {				
				$sql .= " AND gene_mutation IS NOT NULL ";
			}
			
			$result = $conn->query($sql);
			
			if (mysqli_num_rows($result)) {
				$qualify_outlier = $result->fetch_assoc();
				
				$count[$qualify_outlier['gene_relation']] += 1;
				
				$result = $conn->query("SELECT * FROM tblfirstdegrel WHERE value=\"" . $qualify_outlier['gene_relation'] . "\"");
				
				if (mysqli_num_rows($result)) {
					array_push($first_deg_rel, $qualify_outlier['gene_relation']);
					
					$count_first_deg_rel[$qualify_outlier['gene_relation']] += 1;
				}				
			}			
		}
	}
	
	$results = $conn->query("SELECT q.guideline_met FROM tblqualifyans a LEFT JOIN tblcancerquestion q ON a.Guid_question = q.Guid_question WHERE Guid_qualify=" . $_SESSION['id']);
	foreach ($results as $result) {
		if ((strlen($result['guideline_met'])) && (!in_array($result['guideline_met'], $guideline))) {
			array_push($guideline, $result['guideline_met']);				
		}
	}
	
	 
	$result = $conn->query("SELECT * FROM tblqualifypers WHERE Guid_qualify = " . $_SESSION['id'] . "  AND cancer_type =\"No Cancer/None of the Above\"");
	$no_p_cancer_history = mysqli_num_rows($result);
	$result = $conn->query("SELECT * FROM tblqualifyfam WHERE Guid_qualify = " . $_SESSION['id'] . "  AND cancer_type =\"No Cancer/None of the Above\"");
	$no_r_cancer_history += mysqli_num_rows($result);

	
	if ((!count($guideline)) || (($no_p_cancer_history) && ($no_r_cancer_history))) {
		array_push($guideline, "None");
	}
	//$result = $conn->query("SELECT * FROM tblqualifyans WHERE Guid_qualify=" . $_SESSION['id'] . " AND relative IS NOT NULL");
	//if (!mysqli_num_rows($result)) {
	//	$no_r_cancer_history ++;
	//}
	if ($no_r_cancer_history) {
?>
							<li class="no_cancer_history active">
								<h3>Family History</h3>
								<div class="pInfo_type">
									<strong>Cancer Diagnosis</strong>
									<p>No Cancer History</p>
								</div>
							</li>
<?php
	}	
?>
						</ul>
						
                        <section id="guideline_met">
			                <h3>Guideline(s) Met</h3>
<?php	
	for ($i=0; $i < count($guideline); $i++) {
?>
			                <p><?php echo $guideline[$i]; ?></p>
<?php
	}
?>
		                </section>
					</section>
					
					<div id="pedigree" class="tree">
						<ul>
<?php
	// Great Grand Parents
	if ($count_third_deg_rel['Maternal Great-Grandfather'] || $count_third_deg_rel['Maternal Great-Grandmother'] || $count_third_deg_rel['Maternal Great-Uncle']|| $count_third_deg_rel['Maternal Great-Aunt'] || $count_third_deg_rel['Paternal Great-Grandfather'] || $count_third_deg_rel['Paternal Great-Grandmother'] || $count_third_deg_rel['Paternal Great-Uncle']|| $count_third_deg_rel['Paternal Great-Aunt']) {
?>
							<li class="great_relatives">
<?php
		$relative = array();
		if ($count_third_deg_rel['Maternal Great-Grandfather']) $relative['Maternal Great-Grandfather'] = "male";
		if ($count_third_deg_rel['Maternal Great-Grandmother']) $relative['Maternal Great-Grandmother'] = "female";
		if ($count_third_deg_rel['Maternal Great-Uncle']) $relative['Maternal Great-Uncle'] = "male";
		if ($count_third_deg_rel['Maternal Great-Aunt']) $relative['Maternal Great-Aunt'] = "female";
		if ($count_third_deg_rel['Paternal Great-Grandfather']) $relative['Paternal Great-Grandfather'] = "male";
		if ($count_third_deg_rel['Paternal Great-Grandmother']) $relative['Paternal Great-Grandmother'] = "female";
		if ($count_third_deg_rel['Paternal Great-Uncle']) $relative['Paternal Great-Uncle'] = "male";
		if ($count_third_deg_rel['Paternal Great-Aunt']) $relative['Paternal Great-Aunt'] = "female";
		
		foreach ($relative as $relation => $gender) {
			generate_grandparent_html($count_third_deg_rel[$relation], $relation, $gender, $relation, $count_third_deg_rel[$relation], $deceased_relative);
		}
?>							    
							</li>
<?php
	}
?>							
						    <li>
<?php
	// Paternal Grand Parents
	$paternal_granparents_needed = 0;
	if ($count_second_deg_rel['Paternal Grandfather'] || $count_second_deg_rel['Paternal Grandmother'] || $count_second_deg_rel['Paternal Uncle']|| $count_second_deg_rel['Paternal Aunt']) {
		$paternal_granparents_needed = 1;
	}
	if ($paternal_granparents_needed) {
?>
								<div class="parents">
<?php
		$count = $count_second_deg_rel['Paternal Grandfather'];
		if ((!$count_second_deg_rel['Paternal Grandfather'])) {
			$count = 1;
		}
		generate_grandparent_html($count, "Grandfather", "male", "Paternal Grandfather", $count_second_deg_rel['Paternal Grandfather'], $deceased_relative);
		$count = $count_second_deg_rel['Paternal Grandmother'];
		
		if (!($count_second_deg_rel['Paternal Grandmother'])) {			
			$count = 1;
		}		
		generate_grandparent_html($count, "Grandmother", "female", "Paternal Grandmother", $count_second_deg_rel['Paternal Grandmother'], $deceased_relative);		
?>
								</div>
<?php		
	}
	// Maternal Grand Parents
	$maternal_granparents_needed = 0;
	
	if ($count_second_deg_rel['Maternal Grandfather'] || $count_second_deg_rel['Maternal Grandmother'] || $count_second_deg_rel['Maternal Uncle']|| $count_second_deg_rel['Maternal Aunt']) {
		$maternal_granparents_needed = 1;
	}
	if ($maternal_granparents_needed) {
?>
								<div class="parents">
<?php
		$count = $count_second_deg_rel['Maternal Grandfather'];
		if ((!$count_second_deg_rel['Maternal Grandfather'])) {
			$count = 1;
		}
		generate_grandparent_html($count, "Grandfather", "male", "Maternal Grandfather", $count_second_deg_rel['Maternal Grandfather'], $deceased_relative);
		$count = $count_second_deg_rel['Maternal Grandmother'];
		if (!($count_second_deg_rel['Maternal Grandmother'])) {
			$count = 1;
		}
		generate_grandparent_html($count, "Grandmother", "female", "Maternal Grandmother", $count_second_deg_rel['Maternal Grandmother'], $deceased_relative);		
?>
								</div>
<?php		
	}
	if ($paternal_granparents_needed || $maternal_granparents_needed) {
?>
						        <ul class="child">
<?php
	}
	// Paternal Uncle and Aunt
	if ($count_second_deg_rel['Paternal Uncle'] || $count_second_deg_rel['Paternal Aunt']) {		
		generate_html($count_second_deg_rel['Paternal Uncle'], "Paternal Uncle", "male", $deceased_relative);
		generate_html($count_second_deg_rel['Paternal Aunt'], "Paternal Aunt", "female", $deceased_relative);
	}
	if ($paternal_granparents_needed || $maternal_granparents_needed) {
?>									
						            <li class="direct">
<?php
	}
	// Parents
	if ($count_first_deg_rel['Mother'] || $count_first_deg_rel['Father'] || $count_first_deg_rel['Sister'] || $count_first_deg_rel['Brother'] || $count_second_deg_rel['Maternal Half-Sister'] || $count_second_deg_rel['Paternal Half-Sister'] || $count_second_deg_rel['Maternal Half-Brother'] || $count_second_deg_rel['Paternal Half-Brother'] || $paternal_granparents_needed || $maternal_granparents_needed) {
		$blood = "";
		if ($count_second_deg_rel['Paternal Grandfather'] || $count_second_deg_rel['Paternal Grandmother'] || $count_second_deg_rel['Paternal Uncle'] || $count_second_deg_rel['Paternal Aunt']) {
			$blood = " blood";
		}
		$spouse = "";
		if ((!$paternal_granparents_needed) && ($maternal_granparents_needed)) {
			$spouse = " spouse";
		}
?>
								        <div class="parents">
								            <button type="button" class="person<?php echo (($count_first_deg_rel['Father']) ? " ch" : " nch"); ?><?php echo $blood . $spouse; ?><?php echo ((in_array("father1", $deceased_relative)) ? " deceased" : ""); ?>"  data-qs="#father1">
									            <span class="gender male">
<?php
		if ($count_first_deg_rel['Father']) { echo "<img src=\"images/icon_ch.png\" alt=\"Cancer History\">"; }
		else {echo "<img src=\"images/icon_nch_blk.png\" alt=\"No History\">";}
?>
												</span>
												<strong>Father</strong>
								            </button>
<?php
		$blood = "";
		if ($count_second_deg_rel['Maternal Grandfather'] || $count_second_deg_rel['Maternal Grandmother'] || $count_second_deg_rel['Maternal Uncle'] || $count_second_deg_rel['Maternal Aunt']) {
			$blood = " blood";
		}
		$spouse = "";
		if (($paternal_granparents_needed) && (!$maternal_granparents_needed)) {
			$spouse = " spouse";
		}
?>
											<button type="button" class="person<?php echo (($count_first_deg_rel['Mother']) ? " ch" : " nch"); ?><?php echo $blood . $spouse; ?><?php echo ((in_array("mother1", $deceased_relative)) ? " deceased" : ""); ?>" data-qs="#mother1">
									            <span class="gender female">
<?php
		if ($count_first_deg_rel['Mother']) {
?>
												    <img src="images/icon_ch.png" alt="Cancer History">
<?php
		} else {
?>
													<img src="images/icon_nch_blk.png" alt="No History">
<?php
		}
	
?>
												</span>
									            <strong>Mother</strong>
								            </button>
								        </div>
<?php	
	}
	// Self
	if ($count_first_deg_rel['Mother'] || $count_first_deg_rel['Father'] || $count_first_deg_rel['Sister'] || $count_first_deg_rel['Brother'] || $count_second_deg_rel['Maternal Half-Sister'] || $count_second_deg_rel['Paternal Half-Sister'] || $count_second_deg_rel['Maternal Half-Brother'] || $count_second_deg_rel['Paternal Half-Brother'] || $paternal_granparents_needed || $maternal_granparents_needed) {
?>
								        <ul class="child">
<?php
	}
	generate_html($count_first_deg_rel['Sister'], "Sister", "female", $deceased_relative);
	generate_html($count_second_deg_rel['Maternal Half-Sister'], "Maternal Half-Sister", "female", $deceased_relative);
	generate_html($count_second_deg_rel['Paternal Half-Sister'], "Paternal Half-Sister", "female", $deceased_relative);
	$blood = "";
	if ($count_first_deg_rel['Mother'] || $count_first_deg_rel['Father'] || $count_first_deg_rel['Sister'] || $count_first_deg_rel['Brother'] || $count_second_deg_rel['Maternal Half-Sister'] || $count_second_deg_rel['Paternal Half-Sister'] || $count_second_deg_rel['Maternal Half-Brother'] || $count_second_deg_rel['Paternal Half-Brother'] || $paternal_granparents_needed || $maternal_granparents_needed) {
		$blood = " blood";	
?>									           
								            <li>
<?php
	}
	if ($count_first_deg_rel['Daughter'] || $count_first_deg_rel['Son']) {
		if ($qualify['gender'] == "Male") {
			$spouse_gender = "female";
		} else {
			$spouse_gender = "male";
		}
?>
										       <div class="parents">
<?php
	}
	if ($count_first_deg_rel['Daughter'] || $count_first_deg_rel['Son']) {
		if ($qualify['gender'] == "Female") {
?>
													<button type="button" class="person spouse nch">
											            <span class="gender <?php echo $spouse_gender;?>">
															<img src="images/icon_nch_blk.png" alt="No History">
														</span>
											            <strong></strong>
										            </button>
<?php
		}
	}
?>
													<button type="button" class="person<?php echo (($count_personal) ? " ch" : ""); ?><?php echo $blood; ?> me" data-qs="#myself">
											            <span class="gender <?php echo (strtolower($qualify['gender'])); ?>">
<?php
	if ($count_personal) { echo "<img src=\"images/icon_ch.png\" alt=\"Cancer History\">"; }		
?>
														</span>
											            <strong>Me</strong>
										            </button>
<?php
	if ($count_first_deg_rel['Daughter'] || $count_first_deg_rel['Son']) {
		if ($qualify['gender'] == "Male") {
?>
										            <button type="button" class="person spouse nch">
											            <span class="gender <?php echo $spouse_gender;?>">
															<img src="images/icon_nch_blk.png" alt="No History">
														</span>
											            <strong></strong>
										            </button>
<?php
		}
		if ($count_first_deg_rel['Daughter'] || $count_first_deg_rel['Son']) {
?>
										        </div>
<?php
		}
?>
										        <ul class="child">
<?php
		// Son and Daughter
		generate_html($count_first_deg_rel['Daughter'], "Daughter", "female", $deceased_relative);
		generate_html($count_first_deg_rel['Son'], "Son", "male", $deceased_relative);
?>
										        </ul>
<?php
	}
?>
									        </li>
<?php
	// Brother
	generate_html($count_first_deg_rel['Brother'], "Brother", "male", $deceased_relative);
	generate_html($count_second_deg_rel['Maternal Half-Brother'], "Maternal Half-Brother", "male", $deceased_relative);
	generate_html($count_second_deg_rel['Paternal Half-Brother'], "Paternal Half-Brother", "male", $deceased_relative);
?>												
								        </ul>
							        </li>
<?php
	// Maternal Uncle and Aunt
	if ($count_second_deg_rel['Maternal Uncle'] || $count_second_deg_rel['Maternal Aunt']) {		
		generate_html($count_second_deg_rel['Maternal Uncle'], "Maternal Uncle", "male", $deceased_relative);
		generate_html($count_second_deg_rel['Maternal Aunt'], "Maternal Aunt", "female", $deceased_relative);
	}
?>
						        </ul>
							</li>
<?php
	// Niece, Nephew, Grandosn, Granddaughter, Maternal Great-Granddaughter, Maternal Great-Grandson, Paternal Great-Granddaughter, Paternal Great-Grandson, Male First-cousin,Female First-cousin
	if ($count_second_deg_rel['Niece'] || $count_second_deg_rel['Nephew'] || $count_second_deg_rel['Grandson']|| $count_second_deg_rel['Granddaughter'] || $count_third_deg_rel['Great-Granddaughter'] || $count_third_deg_rel['Great-Grandson'] || $count_third_deg_rel['Male First-cousin'] || $count_third_deg_rel['Female First-cousin']) {
		$relative = array();
		if ($count_second_deg_rel['Niece']) $relative['Niece'] = "female";
		if ($count_second_deg_rel['Nephew']) $relative['Nephew'] = "male";
		if ($count_second_deg_rel['Granddaughter']) $relative['Granddaughter'] = "female";
		if ($count_second_deg_rel['Grandson']) $relative['Grandson'] = "male";	
		if ($count_third_deg_rel['Great-Granddaughter']) $relative['Great-Granddaughter'] = "female";
		if ($count_third_deg_rel['Great-Grandson']) $relative['Great-Grandson'] = "male";		
		if ($count_third_deg_rel['Male First-cousin']) $relative['Male First-cousin'] = "male";
		if ($count_third_deg_rel['Female First-cousin']) $relative['Female First-cousin'] = "female";
?>
							<li class="great_relatives">
<?php
		foreach ($relative as $relation => $gender) {
			if (in_array($relation, array("Niece", "Nephew", "Granddaughter", "Grandson"))) {
				$count = $count_second_deg_rel[$relation];				
			} else {
				$count = $count_third_deg_rel[$relation];
			}
				
			generate_grandparent_html($count, $relation, $gender, $relation, $count, $deceased_relative);
		}
?>
							</li>
<?php
	}
	$result = $conn->query("SELECT AES_DECRYPT(firstname_enc, 'F1rstn@m3@_%') as firstname, AES_DECRYPT(lastname_enc, 'L@stn@m3&%#') as lastname FROM tblpatient WHERE Guid_user = " . $qualify['Guid_user']);
				
	$patient = $result->fetch_assoc();	
?>
						</ul>
					</div>
					
					<div class="pKey">
						<figure class="guideline_met"><img src="images/icon_ch_blk.png" alt="Cancer History">: Cancer History Provided</figure>
						<figure class="no_history"><img src="images/icon_nch_blk.png" alt="No History">: No Cancer History Provided</figure>
					</div>
		
		<div class="side_btns">
			<button type="submit" class="s_btn back" name="back">
				<icon class="icon"></icon>
				<span>Back</span>
			</button>
			
			<button id="start_over" type="button" class="s_btn redo toggle" data-on=".overlay.resubmit">
				<icon class="icon"></icon>
				<span>Start Over</span>
			</button>
			
			<button type="button" class="s_btn print gold">
				<icon class="icon"></icon>
				<span><strong>Important:</strong>Print Summary for a Health Care Provider</span>
			</button>
		</div>
	</div>
                <div id="sig">
					<div class="line two">
					    <section>
						    <h4>Patient Name:</h4>
						    <span class="underline"><?php echo $patient['firstname'] . " " . $patient['lastname']; ?></span>
					    </section>
					
					    <section>
					        <h4>Date Completed:</h4>
						    <span class="underline"><?php echo $date->format('n/j/Y');?></span>
					    </section>
					</div>
					
					<div class="line two">
						<section>						
						    <h4>Physician Name:</h4>
<?php
	$physician_name = "";
	
	if ($qualify['provider_id'] == "Other") {
		$physician_name = $qualify['other_provider'];	
	} else {
		if (strlen($qualify['provider_id'])) {
			$result = $conn->query("SELECT first_name, last_name, title FROM tblprovider WHERE Guid_provider = " . $qualify['provider_id']);
			$physician_name = mysqli_fetch_assoc($result);
		}	
	}
?>
	
							<span class="underline"><?php echo $physician_name['first_name'] . " " . $physician_name['last_name'] . ", " . $physician_name['title']; ?></span>
						</section>
						
					    <section>
						    <h4>Signature/Date:</h4>
							<span class="underline"></span>
						</section>
					</div>
                </div>
				
	<div id="q_disclaimer" class="wrapper">
		<p id="ped_disc">This pedigree may not be a complete representation and requires more information concerning your relatives.  It is recommended you consult with a health care professional to complete it.</p>
<?php
	if ($qualify["ashkenazi"] == "Yes") {
		$result = $conn->query("SELECT * FROM tblqualifyans WHERE Guid_qualify = " . $_SESSION['id'] . " AND (cancer_personal IN(\"Breast\", \"Ovarian\", \"Pancreatic\", \"Prostate\") OR cancer_type IN(\"Breast\", \"Ovarian\", \"Pancreatic\", \"Prostate\"))");

		if(mysqli_num_rows($result)) {
?>
		<p>A screening panel for three founder mutations common in the Ashkenazi Jewish population is medically necessary first when criteria are met. If founder mutation testing is negative, full gene sequencing of BRCA1 and BRCA2 genes (reflex testing) is then considered medically necessary only if the member meets any of the criteria described above for comprehensive testing.</p>
<?php		
		}
	}
	$result = $conn->query("SELECT * FROM tblqualifyans WHERE Guid_qualify = " . $_SESSION['id'] . " AND gene_mutation = \"Yes\"");
	$total_gene_mutation = mysqli_num_rows($result);
	
	$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id'] . " AND gene_mutation = \"Yes\"");
	$total_gene_mutation = mysqli_num_rows($result);
	
	if ($total_gene_mutation) {
?>
		<p>A copy of the affected family member's test results are required to verify the family mutation indicated above.</p>
<?php
	}
?>
		<p>
<?php 
    if (($qualify["insurance"] != "Aetna") && ($qualify["insurance"] != "Medicare")) {
?>
		This assessment is based on the National Comprehensive Cancer Network (NCCN) guidelines (www.nccn.org) for BRCA-Related Breast and/or Ovarian Cancer syndrome (version 1.2019) and for High-Risk Colorectal Cancer Syndromes (version 1.2018). 
<?php
    }
    if (($qualify["insurance"] == "Aetna")) {
?>
		Since you have <?php echo $qualify["insurance"]; ?> insurance, this assessment was based on their specific medical policy guidelines (Aetna Medical Clinical Policy Number 0227 - BRCA Testing, last reviewed: 1/17/2018) that cover testing for hereditary breast and/or ovarian cancer syndromes. 
<?php
    } else if (($qualify["insurance"] == "Medicare")) { ?>
		Since you have <?php echo $qualify["insurance"]; ?> insurance, this assessment was based on their specific medical policy guidelines (Local Coverage Determination: BRCA1 and BRCA2 Genetic Testing (L36715),revision date: 01/01/2018) that cover testing for hereditary breast and/or ovarian cancer syndromes.
<?php
    } 	
?>
		To learn more about genetic testing, please speak with your genetic counselor or other healthcare provider. You can locate a genetic counselor through NSGC.org.</p>

		<p>Medical Diagnostic Laboratories' patient hereditary cancer questionnaire only determines your eligibility for certain genetic testing. Our testing only covers certain hereditary cancer syndromes such as hereditary breast and/or ovarian cancer (HBOC) and Lynch syndrome.  To determine which test(s) should be performed, you and your healthcare provider or genetic counselor should determine this based on your personal and family history. Whenever possible, it is recommended that genetic testing in a family start with a member in the family who has had cancer.</p>
	</div>
<?php

	if (isset($_GET['lc']) && ($_GET['lc'] == "F")) {
		echo patient_consent($patient['firstname'], $patient['lastname']);
		echo patient_consent($patient['firstname'], $patient['lastname']);
	}

	generate_outer_bottom($error);
	
	save_snap_shot($not_qualified);
	
	if (isset($_GET['ln']) && ($_GET['ln'] == "np")) {
		if (isset($_GET['lc']) && ($_GET['lc'] == "O")) {
		} else {
			if (isset($_GET['lc']) && ($_GET['lc'] == "F")) {
				$classEX = " ex";
			}
?>
	<div id="summary_app_modal" class="overlay summary">
		<input type="hidden" name="summary_email" id="summary_email">
		<input type="hidden" name="summary_quaily_id" id="summary_quaily_id" value="<?php echo $qualify['Guid_user']; ?>">
		<input type="hidden" name="lc" id="lc" value="<?php echo $_GET['lc']; ?>">
		<input type="hidden" name="co" id="co" value="<?php echo $_GET['co']; ?>">
		<div class="overlay_box<?php echo $classEX; ?>">						
			<p class="imp">Please provide your email address.</p>
			
			<div class="field med">
				<div class="iwrap">
					<label for="email_summary">Email</label>
					<input type="email" id="email_summary" name="email_summary" class="sameP if" value="">
					<icon class="icon istatus"></icon>
				</div>
			</div>
<?php
			if (isset($_GET['lc']) && ($_GET['lc'] == "F")) {
?>
			<section class="exFields">
				<h4>Please list your primary care provider or OBGYN's contact information to ensure that the results from this questionnaire are successfully added to your health records.</h4>

				<div class="field med">
					<div class="iwrap">
						<label for="practice_name<">Practice Name</label>
						<input type="text" id="practice_name" name="practice_name" class="sameP if" value="">
						<icon class="icon istatus"></icon>
					</div>
				</div>

				<div class="field med">
					<div class="iwrap">
						<label for="physician_name">Physician Name</label>
						<input type="text" id="physician_name" name="physician_name" class="sameP if" value="">
						<icon class="icon istatus"></icon>
					</div>
				</div>

				<div class="field med">
					<div class="iwrap">
						<label for="address">Address</label>
						<input type="text" id="address" name="address" class="sameP if" value="">
						<icon class="icon istatus"></icon>
					</div>
				</div>

				<!--<div class="field med">
					<div class="iwrap">
						<label for="email_summary">Address line 2</label>
						<input type="email" id="email_summary" name="email_summary" class="sameP if" value="">
						<icon class="icon istatus"></icon>
					</div>
				</div>-->

				<div class="field med">
					<div class="iwrap">
						<label for="city">City</label>
						<input type="text" id="city" name="city" class="sameP if" value="">
						<icon class="icon istatus"></icon>
					</div>
				</div>
						
				<div class="field med">
					<div class="iwrap">
						<label for="state">State</label>
						<select name="state" id="state" class="if selectF">
							<option value=""></option>
<?php
				$states = $conn->query("SELECT * FROM tblstate");
	
				foreach($states as $state) {
?>
							<option value="<?php echo $state['code']; ?>"><?php echo $state['name']; ?></option>
<?php
				}
?>
						</select>						
						<icon class="icon istatus"></icon>
					</div>	
				</div>

				<div class="field med">
					<div class="iwrap">
						<label for="zip">Zip</label>
						<input type="text" id="zip" name="zip" class="sameP if" value="">
						<icon class="icon istatus"></icon>
					</div>
				</div>

				<div class="field med">
					<div class="iwrap">
						<label for="phone">Phone</label>
						<input type="text" id="phone" name="phone" class="sameP if" value="">
						<icon class="icon istatus"></icon>
					</div>
				</div>
			</section>
<?php
			}	
?>
			<div class="btns">
				<button type="button" class="btn back neutral sm toggle" data-on="#summary_app_modal">
					<icon class="icon"></icon>
					<strong>Cancel</strong>
				</button>
				  
				<button class="btn" name="summary" id="summary">
					<icon class="icon"></icon>
					<strong>Continue</strong>
				</button>
			</div>
		</div>
	</div>
<?php
		}
	}
	
	$content = '			
			<p>Questionnaire was submitted successfully.</p>			
			<a href="'. ROOT  .'/login"';
			
	if (isset($_GET['ln']) && ($_GET['ln'] == "pin")) {
		$content .= 'ln=pin';
	} else {
		$content .= 'continue=Yes';
	}
	
	$content .= ' style="color:#973737"><strong>Access the questionnaire</strong></a>
				<br><p><strong>Click</strong><a href="http://www.mdlab.com/brca/" style="color:#973737"> here </a><strong>to learn more about BRCA testing</strong>';		
				
	$title = "Questionnaire successfully completed";
		
	send_email($content, $title);
	
	$title = "";
	$content = "";
	
	if ((strlen($qualify['account_number'])) || (strlen($qualify['provider_id'])) || (strlen($qualify['other_provider']))) {
		$content .= '					
					<p><strong>Provider Information</strong></p>';
					
		if (strlen($qualify['account_number'])) {
			$content .= '<p>Account: ' . $qualify['account_number'] . '</p>';
			$result = $conn->query("SELECT * FROM tblaccount WHERE account = '" . $qualify['account_number'] . "'");
	
			$account = $result->fetch_assoc();
			$content .= '<p>Account Name: ' . $account['name'] . '</p>';
			
			$content .= '<p>Address: ' . $account['city'] . ", " . $account['state'] . '</p>';
		}
	
		if (strlen($qualify['provider_id'])) { 
			$result = $conn->query("SELECT first_name, last_name FROM tblprovider WHERE Guid_provider = " . $qualify['provider_id']);
		
			$provider = $result->fetch_assoc();
			$content .= '<p>Provider: ' . $provider['first_name'] . ", " . $provider['last_name'] . '</p>';
		}
		
		if (strlen($qualify['account_number'])) {
			$result = $conn->query("SELECT * FROM tblsalesrep sr LEFT JOIN tblaccountrep ar ON sr.Guid_salesrep = ar.Guid_salesrep WHERE ar.Guid_account = " . $account['Guid_account']);
			
			$salesrep = $result->fetch_assoc();
			
			$content .= '					
					<p><strong>Genetic Consultant</strong></p>';
					
			$content .= '<p>Name: ' . $salesrep['first_name'] . " " . $salesrep['last_name'] . '</p>';
		}
		
		if (strlen($qualify['other_provider'])) { 		
			$content .= '<p>Provider: ' . $qualify['other_provider'] . '</p>';
		}
	}
	
	if (strlen($qualify['source'])) {
		$content .= '<p>Source: ' . $qualify['source'] . '</p>';
	}
	
	$content .= '					
					<p><strong>Patient Information</strong></p>';
	
	$result = $conn->query("SELECT count(*) as count FROM tbl_ss_qualify WHERE Guid_qualify=" . $_SESSION['id']);
		
	$num_submissions = $result->fetch_assoc();
			
	$content .= '<p>Number of Submissions: ' . $num_submissions['count'] . '</p>';
	
	$content .= '<p>Patient ID: ' . $_SESSION['id'] . '</p>			
			<p>Insurance: ' . $qualify["insurance"]. '</p>
			<p>Gender: ' . $qualify['gender']  . '</p>
			<p>Ashkenazi: ' . $qualify['ashkenazi']  . '</p>';			
			
	$content .= '<p>Gene Mutation: ' . $qualify['gene_mutation'] . '</p>';
	
	if ($qualify['gene_mutation'] == "Yes") {
		$genes = $conn->query("SELECT * FROM tblqualifygene WHERE Guid_qualify=" . $_SESSION['id']);
				
		foreach($genes as $gene) {		
			$content .= '
			<p>Gene Relation: ' . $gene['gene_relation'] . '</p>
			<p>Gene: ' . $gene['gene'] . '</p>';
		}
		
		$result = $conn->query("SELECT * FROM tblgmcancer WHERE Guid_qualify=" . $_SESSION['id']);
		
		if (mysqli_num_rows($result)) {
			$content .= '<p><strong>Personal Cancer History</strong></p>';
			foreach($result as $gmcancer) {
				$content .= '
				<p>Personal Cancer: ' . $gmcancer['cancer'] . '</p>
				<p>Age: ' . $gmcancer['cancer_age'] . '</p>';
			}
		}
	}
					
	$result = $conn->query("SELECT * FROM tblqualifyans WHERE cancer_personal IS NOT NULL AND Guid_qualify=" . $_SESSION['id']);	
	
	if (mysqli_num_rows($result)) {					
		$content .= '					
					<p><strong>Personal Cancer History</strong></p>';
		
		foreach ($result as $personal) {			
			$content .= '					
					<p>Cancer: ' . $personal['cancer_personal'] . '</p>
					<p>Age: ' . $personal['age_personal'] . '</p>';
		}
	} else {
		$result = $conn->query("SELECT count(*) as count FROM tblqualifypers WHERE cancer_type = 'No Cancer/None of the Above' AND Guid_qualify=" . $_SESSION['id']); 
		
		if (mysqli_num_rows($result)) {	
			$content .= '					
					<p><strong>Personal Cancer History</strong></p>';
					
			$content .= '					
					<p>No Cancer/None of the Above</p>';
		}
	}
		
	
	$result = $conn->query("SELECT * FROM tblqualifyans WHERE Guid_qualify=" . $_SESSION['id'] . " AND relative IS NOT NULL");
		
	if (mysqli_num_rows($result)) {		
		$content .= '					
					<p><strong>Family Cancer History</strong></p>';
					
		foreach ($result as $relative) {		
			$content .= '							
					<p>Relative: ' . $relative['relative'] . '</p>
					<p>Family Cancer: ' . $relative['cancer_type'] . '</p>	
					<p>Age: ' . $relative['age_relative'] . '</p>';
		}
	} else {
		$no_cancer = $conn->query("SELECT count(*) as count FROM tblqualifyfam WHERE cancer_type = 'No Cancer/None of the Above' AND Guid_qualify=" . $_SESSION['id'])->fetch_object()->count;
		
		if ($no_cancer) {
			$content .= '					
					<p><strong>Family Cancer History</strong></p>';
					
			$content .= '					
					<p>No Cancer/None of the Above</p>';
		}
	}
	
	$content .= '<p><strong>Qualification Status</strong></p>';
	
	if ($not_qualified) {
		$content .= '<p>Not Qualified</p>';
	} else {
		$content .= '<p>Qualified</p>';
	}
	
	$content .= '<p><strong>Guidelines Met</strong></p>';
	
	for ($i=0; $i < count($guideline); $i++) {
		$content .= '<p>' . $guideline[$i] . '</p>';
	}
	
	$content .= '					
					<p><strong>Environment</strong></p>';
					
	$content .= '<p>' . ENV . '</p>';
	
	if (ENV == "Live") {
		$result = $conn->query("SELECT firstname, lastname FROM tblqualify q LEFT join tbluser u ON q.Guid_user = u.Guid_user LEFT join tblpatient p ON p.Guid_user = u.Guid_user WHERE Guid_qualify =" . $_SESSION['id']);			
		
		$user = $result->fetch_assoc();
		
		if (((strtolower($user['firstname']) == "john") && (strtolower($user['lastname']) == "doe")) || ((strtolower($user['firstname']) == "jane") && (strtolower($user['lastname']) == "doe"))) {
		} else {
			send_email($content, $title, "questionnaire@mdlab.com", "", $not_qualified);
			
			if (strlen($qualify['account_number'])) {			 
				$email = $conn->query("SELECT email FROM tblaccount a left join tblaccountrep ar ON a.Guid_account = ar.Guid_account left join tblsalesrep sr ON ar.Guid_salesrep = sr.Guid_salesrep WHERE account ='" . $qualify['account_number'] . "'")->fetch_object()->email; 
				
				if (strlen($email)) {
					send_email($content, $title, $email);		
				}
			}
		}
	}
	//if ((ENV == "Dev") && ($_SESSION['id'] == "161")) {
	//	send_email($content, $title, "dkalyani@mdlab.com");
	//}
	//if ((ENV == "Dev") && ($_SESSION['id'] == "161")) {
	//	send_email($content, $title, "dkalyani@mdlab.com");
	//}
	require ("db/dbdisconnect.php");
}
function generate_html($count, $relation, $gender, $deceased_relative) {
	for ($i=0; $i < $count; $i++) {
		$data_qs = strtolower(str_replace(" ", "_", $relation)) . ($i + 1);
?>
			<li>															        
					<button type="button" class="person blood ch<?php echo ((in_array($data_qs, $deceased_relative)) ? " deceased" : ""); ?>" data-qs="<?php echo "#" . $data_qs; ?>">
						<span class="gender <?php echo $gender?>">
							<img src="images/icon_ch.png" alt="Cancer History">
						</span>
						<strong><?php echo $relation?></strong>
					</button>
				
			</li>
<?php
		}
}
function generate_grandparent_html($count, $relation, $gender, $data_qs, $cancer_diagnosed, $deceased_relative) {
	for ($i=0; $i < $count; $i++) {
		$data = strtolower(str_replace(" ", "_", $data_qs)) . ($i + 1);
?>
			<button type="button" class="person<?php echo (strlen($cancer_diagnosed) ? " ch" : " nch"); ?><?php echo ((in_array($data, $deceased_relative)) ? " deceased" : ""); ?>" data-qs="<?php echo "#" . $data; ?>">
				<span class="gender <?php echo $gender?>">
<?php

	if (strlen($cancer_diagnosed)) { echo "<img src=\"images/icon_ch.png\" alt=\"Cancer History\">"; }
	else {echo "<img src=\"images/icon_nch_blk.png\" alt=\"No History\">";}
?>
				</span>
				<strong><?php echo $relation?></strong>
			</button>
<?php
	}
}
function save_input() {
	require ("db/dbconnect.php");
		
	$date = new DateTime("now", new DateTimeZone('America/New_York'));
	
	if (count($_POST['personal_cancer'])) {		
		$conn->query("DELETE FROM tblqualifypers WHERE Guid_qualify=" . $_SESSION['id']);
		
		for ($i=0; $i < count($_POST['personal_cancer']); $i++) {
			$sql = "INSERT INTO tblqualifypers (Guid_qualify, cancer_type, Date_created) VALUES ('" . $_SESSION['id'] .  "', '" . $_POST[$_POST['type'] . '_cancer'][$i] .  "', '" . $date->format('Y-m-d H:i:s') . "')";
			
			$conn->query($sql);
		}
			
		$conn->query("DELETE FROM tblqualifyans WHERE Guid_qualify=" . $_SESSION['id'] . " AND cancer_personal IS NOT NULL AND Guid_outlier IS NULL");	
	}
	
	if (count($_POST['family_cancer'])) {
		$conn->query("DELETE FROM tblqualifyfam WHERE Guid_qualify=" . $_SESSION['id']);
		
		for ($i=0; $i < count($_POST['family_cancer']); $i++) {
			$sql = "INSERT INTO tblqualifyfam (Guid_qualify, cancer_type, Date_created) VALUES ('" . $_SESSION['id'] .  "', '" . $_POST[$_POST['type'] . '_cancer'][$i] .  "', '" . $date->format('Y-m-d H:i:s') . "')";
			
			$conn->query($sql);
		}
		
		$conn->query("DELETE FROM tblqualifyans WHERE Guid_qualify=" . $_SESSION['id'] . " AND relative IS NOT NULL");	
	}
	
	if ((isset($_POST['personal_cancer'])) && (in_array("No Cancer/None of the Above", $_POST['personal_cancer']))) {
		$conn->query("DELETE FROM tblqualifyans WHERE Guid_qualify=" . $_SESSION['id'] . " AND cancer_personal IS NOT NULL AND a.Guid_outlier IS NULL");
	}
	
	if ((isset($_POST['family_cancer'])) && (in_array("No Cancer/None of the Above", $_POST['family_cancer']))) {
		$conn->query("DELETE FROM tblqualifyans WHERE Guid_qualify=" . $_SESSION['id'] . " AND a.relative IS NOT NULL AND a.Guid_outlier IS NULL");
	}
	
	//$conn->query("DELETE FROM tblqualifyans WHERE Guid_qualify=" . $_SESSION['id']);
	
	$outliers = $conn->query("SELECT * FROM tbloutlier");
		
	foreach($outliers as $outlier) {
		if (isset($_POST[$outlier['field_name'] . "_age"])) {			
			$sql = "INSERT INTO tblqualifyans (Guid_qualify, Guid_outlier, date_created, age_personal, cancer_personal)";
			$sql .= " VALUES(" . $_SESSION['id'] . ",'" . $outlier['Guid_outlier'] . "','" . $date->format('Y-m-d H:i:s') . "','" . $conn->real_escape_string(intval($_POST[$outlier['field_name'] . "_age"])) . "','" . $outlier['cancer_type'] . "')";
			
			$conn->query($sql);
		}
	}
	
	$questions = $conn->query("SELECT * FROM tblcancerquestion ORDER BY question_type, insurance, cancer_type, sort_order");

	foreach($questions as $question) {		
		if (($_POST[$question['field_name']] == "Yes") || (($question['hide_yes_no']) && (strlen($_POST[$question['field_name'] . "_age_personal"][0])))) {			
			$sql = "INSERT INTO tblqualifyans (Guid_qualify, Guid_question, date_created,";
			
			$count = 0;
			$columns = "";
			$row_inserted = 0;
			
			if ($question['personal_age_needed']) {
				$count = count($_POST[$question['field_name'] . "_age_personal"]);
				$columns .= "age_personal,";
			}
			if ($question['personal_cancer_needed']) {				
				$count = count($_POST[$question['field_name'] . "_cancer_personal"]);
				$columns .= "cancer_personal,";				
			}
			if (strlen($columns)) {
				$columns = substr($columns, 0, -1);
				$columns .= ")";
				$sql .= $columns;
			} else {
				$sql = substr($sql, 0, -1);
				$sql .= ")";
			}
			
			for ($i=0; $i < $count; $i++) {				
				$values = " VALUES(" . $_SESSION['id'] . "," . $question['Guid_question'] . ",'" . $date->format('Y-m-d H:i:s') . "',";
				if ($question['personal_age_needed']) {
					$values .= $conn->real_escape_string(intval($_POST[$question['field_name'] . "_age_personal"][$i])) . ",";
				}
				if ($question['personal_cancer_needed']) {
					$values .= "'" . $_POST[$question['field_name'] . "_cancer_personal"][$i] . "',";	
				}			
				
				$values = substr($values, 0, -1);
				$values .= ")";
				
				$conn->query($sql . $values);
				
				$row_inserted = 1;
			}
			
			$sql = "INSERT INTO tblqualifyans (Guid_qualify, Guid_question, date_created, deceased, ";
			$count = 0;
			$columns = "";
			if ($question['relation_needed']) {
				$count = count($_POST[$question['field_name'] . "_relation"]);
				$columns .= "relative,";					
			}
			if ($question['cancer_needed']) {
				$count = count($_POST[$question['field_name'] . "_cancer"]);
				$columns .= "cancer_type,";					
			}
			if (($question['cancer_needed']) && ($question['additinal_cancer_needed'])) {
				$columns .= "additional_cancer_type,";
			}
			if ($question['gene_mutation_needed']) {
				$count = count($_POST[$question['field_name'] . "_gene_mutation"]);
				$columns .= "gene_mutation,";					
			}
			if ($question['relation_age_needed']) {
				$count = count($_POST[$question['field_name'] . "_age_relative"]);
				$columns .= "age_relative,";
			}
			if (strlen($columns)) {
				$columns = substr($columns, 0, -1);
				$columns .= ")";
				$sql .= $columns;
			} else {
				$sql = substr($sql, 0, -1);
				$sql .= ")";
			}
			
			for ($i=0; $i < $count; $i++) {				
				$values = " VALUES(" . $_SESSION['id'] . "," . $question['Guid_question'] . ",'" . $date->format('Y-m-d H:i:s') . "',";
				if (isset($_POST[$question['field_name'] . "_deceased_relative" . $i])) {					
					$values .= "'1',";
				} else {					
					$values .= "'0',";
				}
				if ($question['relation_needed']) {
					$values .= "'" . $_POST[$question['field_name'] . "_relation"][$i] . "',";		
				}				
				if ($question['cancer_needed']) {
					$values .= "'" . $_POST[$question['field_name'] . "_cancer"][$i] . "',";		
				}
				
				if (($question['cancer_needed']) && ($question['additinal_cancer_needed'])) {
					$values .= "'" . $_POST[$question['field_name'] . "_additional_cancer"][$i] . "',";
				}
				
				if ($question['gene_mutation_needed']) {
					$values .= "'" . $_POST[$question['field_name'] . "_gene_mutation"][$i] . "',";
				}
				
				if ($question['relation_age_needed']) {
					$values .= $conn->real_escape_string(intval($_POST[$question['field_name'] . "_age_relative"][$i])) . ",";
				}
				
				$values = substr($values, 0, -1);
				$values .= ")";
				
				$conn->query($sql . $values);
				
				$row_inserted = 1;
			}
			if (!$row_inserted) {
				$sql = "INSERT INTO tblqualifyans (Guid_qualify, Guid_question, date_created) VALUES(" . $_SESSION['id'] . "," . $question['Guid_question'] . ",'" . $date->format('Y-m-d H:i:s') . "')";
				$conn->query($sql);
			}
			if (($question['special_rule'] == 8) || ($question['special_rule'] == 9)) {
				$Guid_question = $conn->query("SELECT Guid_question FROM tblcancerquestion WHERE field_name = '" . $question['parent_field_name'] . "'")->fetch_object()->Guid_question; 
				
				$text = $_POST[$question['field_name'] . "_cancer"][0];
				if ($question['special_rule'] == 9) {
					$text .= " at age " . $_POST[$question['field_name'] . "_age_relative"][0];
				}
				if (isset($_POST[$question['field_name'] . "_age_relative"][1])) {
					$text .= " and " . $_POST[$question['field_name'] . "_age_relative"][1];
				}
				$sql = "UPDATE tblqualifyans SET additional_cancer_type = '" . $text . "' WHERE Guid_question = " . $Guid_question . " AND Guid_qualify = " . $_SESSION['id'];
						
				$conn->query($sql);			
			}
		}
		
		if ($_POST[$question['field_name']] == "Unknown")  {
			$result = $conn->query("SELECT * FROM tblunknownans WHERE Guid_question = ". $question['Guid_question'] . " AND Guid_qualify =" . $_SESSION['id']);
			if (!mysqli_num_rows($result)) {
				$sql = "INSERT INTO tblunknownans (Guid_qualify, Guid_question, date_created) VALUES(" . $_SESSION['id'] . "," . $question['Guid_question'] . ",'" . $date->format('Y-m-d H:i:s') . "')";
				
				$conn->query($sql);
			}
		} elseif (($_POST[$question['field_name']] == "Yes") || ($_POST[$question['field_name']] == "No")) {
			$conn->query("DELETE FROM tblunknownans WHERE Guid_question = " . $question['Guid_question'] . " AND Guid_qualify=" . $_SESSION['id']);		
		}
	}
		
	$sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = \"tblqualify\"";

	$columns = $conn->query($sql);

	foreach($columns as $column) {
		if (($column['COLUMN_NAME'] != "account_password") && (isset($_POST[$column['COLUMN_NAME']]))) {
			if (($column['COLUMN_NAME'] == "gene_mutation") && ($_POST['gene_mutation'] == "Yes")) {
				$conn->query("DELETE FROM tblunknowngene WHERE Guid_qualify=" . $_SESSION['id']);
				
				$sql = "UPDATE tblqualify SET " .  $column['COLUMN_NAME'] . " = '" . $conn->real_escape_string($_POST[$column['COLUMN_NAME']]) . "', Date_modified = '" . $date->format('Y-m-d H:i:s') . "' WHERE Guid_qualify = " . $_SESSION['id'];
				$conn->query($sql);
				
				$conn->query("DELETE FROM tblqualifygene WHERE Guid_qualify=" . $_SESSION['id']);
				
				for ($i=0; $i < count($_POST['gene_relation']); $i++) {
					$sql = "INSERT INTO tblqualifygene (Guid_qualify, gene_relation, gene, date_created) VALUES(" . $_SESSION['id'] . ", '" . $_POST['gene_relation'][$i] . "', '" . $_POST['gene'][$i] . "', '" . $date->format('Y-m-d H:i:s') . "')";
					
					$conn->query($sql);	
				}
				
				$conn->query("DELETE FROM tblgmcancer WHERE Guid_qualify=" . $_SESSION['id']);
				
				for ($i=0; $i < count($_POST['gm_cancer']); $i++) {
					$sql = "INSERT INTO tblgmcancer (Guid_qualify, cancer, cancer_age, date_created) VALUES(" . $_SESSION['id'] . ", '" . $_POST['gm_cancer'][$i] . "', '" . $conn->real_escape_string(intval($_POST['gm_cancer_age'][$i])) . "', '" . $date->format('Y-m-d H:i:s') . "')";
					
					$conn->query($sql);	
				}
			} elseif (($column['COLUMN_NAME'] == "gene_mutation") && ($_POST['gene_mutation'] != "Yes")) {
				$sql = "UPDATE tblqualify SET " .  $column['COLUMN_NAME'] . " = '" . $conn->real_escape_string($_POST[$column['COLUMN_NAME']]) . "', Date_modified = '" . $date->format('Y-m-d H:i:s') . "' WHERE Guid_qualify = " . $_SESSION['id'];
				$conn->query($sql);
				
				$conn->query("DELETE FROM tblqualifygene WHERE Guid_qualify=" . $_SESSION['id']);
				
				if (($column['COLUMN_NAME'] == "gene_mutation") && ($_POST['gene_mutation'] == "No")) {
					$conn->query("DELETE FROM tblunknowngene WHERE Guid_qualify=" . $_SESSION['id']);
				}					
			} elseif (($column['COLUMN_NAME'] == "gene_relation") || ($column['COLUMN_NAME'] == "gene") || ($column['COLUMN_NAME'] == "account_password")) {
			} else {				
				$sql = "UPDATE tblqualify SET " .  $column['COLUMN_NAME'] . " = '" . $conn->real_escape_string($_POST[$column['COLUMN_NAME']]) . "', Date_modified = '" . $date->format('Y-m-d H:i:s') . "' WHERE Guid_qualify = " . $_SESSION['id'];
				$conn->query($sql);
				if (($column['COLUMN_NAME'] == "insurance") && (isset($_POST['insurance'])) && ($_POST['insurance'] == "Other")) {
					$sql = "UPDATE tblqualify SET other_insurance = '" . $conn->real_escape_string($_POST['other_insurance']) . "', Date_modified = '" . $date->format('Y-m-d H:i:s') . "' WHERE Guid_qualify = " . $_SESSION['id'];					
					$conn->query($sql);
				} elseif (($column['COLUMN_NAME'] == "insurance") && (isset($_POST['insurance'])) && ($_POST['insurance'] != "Other")) {
					$sql = "UPDATE tblqualify SET other_insurance = NULL, Date_modified = '" . $date->format('Y-m-d H:i:s') . "' WHERE Guid_qualify = " . $_SESSION['id'];					
					$conn->query($sql);
				}
			}					
		}
	}
	
	if (isset($_POST['medicare_yes'])) {
		$sql = "UPDATE tblqualify SET insurance = 'Medicare', Other_insurance=NULL, Date_modified = '" . $date->format('Y-m-d H:i:s') . "' WHERE Guid_qualify = " . $_SESSION['id'];
		$conn->query($sql);
	}
	
	if (($_POST['current_step'] == "additional_screen") && 
			strlen($_POST['additional_relative']) && 
			strlen($_POST['additional_cancer']) && 
			strlen($_POST['additional_age'])) {
		if (($_POST['additional_cancer'] == "Breast") && (!strlen($_POST['additional_info_breast']))) {
		} elseif (($_POST['additional_cancer'] == "Prostate") && (!strlen($_POST['additional_info_prostate']))) {
		} else {			
			$sql = "INSERT INTO tbl_additional_info (Guid_qualify, relative , cancer_type, age_relative, cancer_info, deceased, ashkenazi, date_created) VALUES(" . $_SESSION['id'] . ", '" . $conn->real_escape_string($_POST['additional_relative']) . "', '" . $conn->real_escape_string($_POST['additional_cancer']) . "', '" . $conn->real_escape_string($_POST['additional_age']) . "'";
			
			if (strlen($_POST['additional_info_breast'])) {
				$sql .= ", '" . $conn->real_escape_string($_POST['additional_info_breast']) . "'";
			} elseif (strlen($_POST['additional_info_prostate'])) {
				$sql .= ", '" . $conn->real_escape_string($_POST['additional_info_prostate']) . "'";
			} else {
				$sql .= ", NULL";
			}
			
			if (strlen($_POST['additional_deceased'])) {
				$sql .= ", '" . $conn->real_escape_string($_POST['additional_deceased']) . "'";
			} else {
				$sql .= ", '0'";
			}
			
			if (strlen($_POST['additional_ashkenazi'])) {
				$sql .= ", '" . $conn->real_escape_string($_POST['additional_ashkenazi']) . "'";
			} else {
				$sql .= ", '0'";
			}
			
			$sql .= ", '" . $date->format('Y-m-d H:i:s') . "')";
					
			$conn->query($sql);
			$sql="";
		}
	}
	
	if (isset($_POST['additional_relatives']) && ($_POST['additional_relatives'] == "No")) {
		$conn->query("DELETE FROM tbl_additional_info WHERE Guid_qualify = " . $_SESSION['id']);
	}
	require ("db/dbdisconnect.php");
}
function save_snap_shot($not_qualified) {
	require ("db/dbconnect.php");
	
	$date = new DateTime("now", new DateTimeZone('America/New_York'));
		
	$unique_id = $date->format('Y-m-d H:i:s');
	
	if ($not_qualified == "2") {		
		$qualified = "Unknown";
		$status_string = "Unknown";
	} elseif ($not_qualified == "1") {
		$qualified = "No";
		$status_string = "Not Qualified";
	} else {
		$qualified = "Yes";
		$status_string = "Qualified";
	}

	$sql = "INSERT INTO tbl_ss_qualify (Guid_qualify, Guid_user, insurance, other_insurance, gender, ashkenazi, gene_mutation, gene_relation, gene, finish_later, account_number, provider_id, other_provider, source, company, deviceid, ip, qualified, Date_created) SELECT Guid_qualify, Guid_user, insurance, other_insurance, gender, ashkenazi, gene_mutation, gene_relation, gene, finish_later, account_number, provider_id, other_provider, source, company, deviceid, ip, '" . $qualified . "', '" . $unique_id . "' FROM tblqualify WHERE Guid_qualify=" . $_SESSION['id'];

	$conn->query($sql);
	
	$sql = "INSERT INTO tbl_ss_qualifypers (Guid_qualifypers, Guid_qualify, cancer_type, Date_created) SELECT Guid_qualifypers, Guid_qualify, cancer_type,  '" . $unique_id . "' FROM tblqualifypers WHERE Guid_qualify=" . $_SESSION['id'];
	
	$conn->query($sql);
	
	$sql = "INSERT INTO tbl_ss_qualifyfam (Guid_qualifyfam, Guid_qualify, cancer_type, Date_created) SELECT Guid_qualifyfam, Guid_qualify, cancer_type, '" . $unique_id . "' FROM tblqualifyfam WHERE Guid_qualify=" . $_SESSION['id'];
	
	$conn->query($sql);
	
	$sql = "INSERT INTO tbl_ss_qualifygene (Guid_qualifygene, Guid_qualify, gene_relation, gene, Date_created) SELECT Guid_qualifygene, Guid_qualify, gene_relation, gene , '" . $unique_id . "' FROM tblqualifygene WHERE Guid_qualify=" . $_SESSION['id'];
	
	$conn->query($sql);
	
	$sql = "INSERT INTO tbl_ss_qualifyans (Guid_qualifyans, Guid_outlier, Guid_qualify, Guid_question, age_personal, cancer_personal, relative, cancer_type, additional_cancer_type, age_relative, gene_mutation, deceased, Date_created) SELECT Guid_qualifyans, Guid_outlier, Guid_qualify, Guid_question, age_personal, cancer_personal, relative, cancer_type, additional_cancer_type, age_relative, gene_mutation, deceased, '" . $unique_id . "' FROM tblqualifyans WHERE Guid_qualify=" . $_SESSION['id'];

	$conn->query($sql);
	
	$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);

	$qualify = $result->fetch_assoc();
		
	$result = $conn->query("SELECT Guid_status FROM tbl_mdl_status WHERE status = 'Registered'");
			
	$mdl_status = $result->fetch_assoc();
	
	$conn->query("DELETE FROM tbl_mdl_status_log WHERE Guid_user=" . $qualify['Guid_user'] . " AND Guid_status <> " . $mdl_status['Guid_status']);
			
	if (isset($_SESSION['logid'])) {
		$sql = "UPDATE tbl_mdl_status_log SET currentstatus = 'N' WHERE Guid_status_log = " . $_SESSION['logid'];

		$conn->query($sql);	
		
		$logid = $_SESSION['logid'];
	} else {
		$results = $conn->query("SELECT * FROM tbl_mdl_status_log WHERE Guid_user = " . $qualify['Guid_user']);

		if (mysqli_num_rows($results)) {
			$result = $conn->query("SELECT s.Guid_status_log FROM tbl_mdl_status_log s LEFT JOIN tblqualify q ON q.Guid_user = s.Guid_user WHERE q.Guid_qualify = " . $_SESSION['id']);

			$status = $result->fetch_assoc();
	
			$logid = $status['Guid_status_log'];
		} else {
			$result = $conn->query("SELECT * FROM tblpatient WHERE Guid_user = " . $qualify['Guid_user']);
			
			$patient = $result->fetch_assoc();
			
			if (strlen($qualify['account_number'])) {
				$result = $conn->query("SELECT * FROM tblaccount a LEFT JOIN tblaccountrep ar ON a.Guid_account = ar.Guid_account LEFT JOIN tblsalesrep sr ON ar.Guid_salesrep = sr.Guid_salesrep WHERE a.account = " . $qualify['account_number']);
				
				$account = $result->fetch_assoc();
			}

			$sql = "INSERT INTO tbl_mdl_status_log(currentstatus, Guid_patient, Guid_status, Guid_user, Guid_account, account, Guid_salesrep, provider_id, salesrep_fname, salesrep_lname, deviceid, Recorded_by, Date, Date_created) VALUES ('Y'," . $patient['Guid_patient'] . ", " . $mdl_status['Guid_status']  . ", " . $qualify['Guid_user'] . ", '" . $account['Guid_account'] . "', '" . $qualify['account_number'] . "', '" . $account['Guid_salesrep'] . "', '" . $qualify['provider_id'] . "', '" . $account['first_name']  . "', '" . $account['last_name']  . "', '" . $qualify['deviceid']  . "', " . $qualify['Guid_user'] . ", '" . $date->format('Y-m-d H:i:s') . "', '" . $date->format('Y-m-d H:i:s') . "')";

			$conn->query($sql);
			
			$Guid_status_log  = $conn->insert_id;
			$logid = $Guid_status_log;
			
			$sql = "UPDATE tbl_mdl_status_log SET Log_group = " . $Guid_status_log . " WHERE Guid_status_log = " . $Guid_status_log;
			
			$conn->query($sql);
		}
	}
	$sql = "INSERT INTO tbl_mdl_status_log(Guid_patient, Guid_account, account, Guid_salesrep, provider_id, Guid_user, salesrep_fname, salesrep_lname, deviceid, Recorded_by, Date) select Guid_patient, Guid_account, account, Guid_salesrep, provider_id, Guid_user, salesrep_fname, salesrep_lname, deviceid, Recorded_by, Date FROM tbl_mdl_status_log WHERE Guid_status_log = " . $logid;

	$conn->query($sql);
	
	$Guid_parent_status_log  = $conn->insert_id;
	
	$result = $conn->query("SELECT Guid_status FROM tbl_mdl_status WHERE status = 'Questionnaire Completed'");
	
    $mdl_status = $result->fetch_assoc();
	
	$sql = "UPDATE tbl_mdl_status_log SET Log_group = " . $Guid_parent_status_log . ", currentstatus = 'Y', Guid_status = " . $mdl_status['Guid_status'] . ", Date_created = '" . $date->format('Y-m-d H:i:s') . "' WHERE Guid_status_log = " . $Guid_parent_status_log;
	
	$conn->query($sql);	
	
	$sql = "INSERT INTO tbl_mdl_status_log(Guid_patient, Guid_account, account, Guid_salesrep, provider_id, Guid_user, salesrep_fname, salesrep_lname, deviceid, Recorded_by, Date) select Guid_patient, Guid_account, account, Guid_salesrep, provider_id, Guid_user, salesrep_fname, salesrep_lname, deviceid, Recorded_by, Date FROM tbl_mdl_status_log WHERE Guid_status_log = " . $logid;

	$conn->query($sql);
	
	$Guid_status_log  = $conn->insert_id;
	
	$result = $conn->query("SELECT Guid_status FROM tbl_mdl_status WHERE status = '" . $status_string . "'");

    $mdl_status = $result->fetch_assoc();
	
	$sql = "UPDATE tbl_mdl_status_log SET Log_group = " . $Guid_parent_status_log . ", currentstatus = 'Y', Guid_status = " . $mdl_status['Guid_status'] . ", Date_created = '" . $date->format('Y-m-d H:i:s') . "' WHERE Guid_status_log = " . $Guid_status_log;

	$conn->query($sql);	
	
	$sql = "UPDATE tblpatient p, tblqualify q SET p.insurance_name = q.insurance, p.other_insurance = q.other_insurance, p.gender = q.gender, p.accountNumber = q.account_number, p.provider_id = q.provider_id, p.other_provider = q.other_provider, p.source = q.source WHERE p.Guid_user = q.Guid_user AND p.Guid_user = " . $qualify['Guid_user'];
	
	$conn->query($sql);	
	
	require ("db/dbdisconnect.php");
}
function perform_login(&$error) {
	require ("db/dbconnect.php");
	
	if (strlen(trim($_POST['first_name'])) && strlen(trim($_POST['last_name'])) && strlen(trim($_POST['dob'])) && isset($_GET['an'])) {
		perform_dup_search();
	}
	
	$date = new DateTime("now", new DateTimeZone('America/New_York'));
	
	if ((!isset($_GET['ln'])) && (!isset($_GET['lc']))) {
		$password = md5($conn->real_escape_string($_POST['account_password']));
	} elseif ((isset($_GET['ln']) && ($_GET['ln'] != "np")) || (isset($_GET['lc']) && ($_GET['lc'] != "PM") && ($_GET['lc'] != "O"))) {
		$password = md5($conn->real_escape_string($_POST['account_password']));
	}
	
	$Guid_role = $conn->query("SELECT Guid_role FROM tblrole WHERE role = 'Patient'")->fetch_object()->Guid_role;  
	
	$sql = "INSERT INTO tbluser (email, password, user_type, Guid_role, Date_created) VALUES ('" . $conn->real_escape_string($_POST['account_email']) .  "', '" . $password .  "', 'patient', '" . $Guid_role . "', '" . $date->format('Y-m-d H:i:s') . "')";
	
	$conn->query($sql);
	
	$Guid_user = $conn->insert_id;
	
	$first_name = $_POST['first_name'];
	$last_name = $_POST['last_name'];
	
	if (isset($_GET['lc']) && ($_GET['lc'] == "DE")) {
		$first_name = "John";
		$last_name = "Doe";
	}
	
	if (strtolower(trim($_POST['last_name'])) == "doe") {
		$sql = "UPDATE tbluser SET marked_test = '1' WHERE Guid_user = " . $Guid_user;

		$conn->query($sql);
	}
	
	$sql = "INSERT INTO tblpatient (Guid_user, salutation, firstname_enc, lastname_enc, DOB, Date_created) VALUES (" . $Guid_user .  ", '" . $conn->real_escape_string($_POST['salutation']) .  "', AES_ENCRYPT('" . $conn->real_escape_string($first_name) . "', 'F1rstn@m3@_%'), AES_ENCRYPT('" . $conn->real_escape_string($last_name) . "', 'L@stn@m3&%#'),";
	
	if (strlen($_POST['dob'])) {
		$sql .= "'" . $conn->real_escape_string($_POST['dob']) . "'";
	} else {
		$sql .= "NULL";
	}
	
	$sql .= ", '" . $date->format('Y-m-d H:i:s') . "')";

	$conn->query($sql);	
	
	$Guid_patient = $conn->insert_id;
	
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
	
	$sql = "INSERT INTO tblqualify (Guid_user, account_number, provider_id, other_provider, source, company, deviceid, ip, Date_created) VALUES (" . $Guid_user;
	
	$Guid_account = "";
	$account_number = "";
	
	if (isset($_GET['an']) && (strlen(trim($_GET['an'])))) {
		$result = $conn->query("SELECT * FROM tblaccount WHERE account  = '" . $conn->real_escape_string($_GET['an']) . "'");
		
		if (mysqli_num_rows($result)) {			
			$sql .= ", '" . trim($_GET['an']) . "'";
			$sql .= ", '" . trim($_POST['provider']) . "'";
			$account = $result->fetch_assoc();
			$account_number = trim($_GET['an']);
			$Guid_account = $account['Guid_account'];
		} else {
			$account_number = "NULL";
			$Guid_account = "NULL";
			$sql .= ", NULL, NULL";
		}
	} else {
		$account_number = "NULL";
		$Guid_account = "NULL";
		$sql .= ", NULL, NULL";
	}
	
	if (isset($_GET['an']) && (strlen(trim($_POST['other_provider'])))) {		
		$sql .= ", '" . trim($_POST['other_provider']) . "'";
	} else {
		$sql .= ", NULL";
	}
	
	if (isset($_GET['lc'])) {		
		$source = $conn->query("SELECT description FROM tblsource WHERE code ='" . $conn->real_escape_string($_GET['lc']) . "'")->fetch_object()->description;
		if (strlen($source)) {
			$sql .= ", '" . $source . "'";
		} else {
			$sql .= ", 'Unknown'";
		}
	} else {
		$sql .= ", NULL";
	}
	
	if ((isset($_GET['co'])) && (($_GET['co'] == "gen") || ($_GET['co'] == "mdl"))) {
		$sql .= ", '" . trim($_GET['co']) . "'";		
	} else {
		$sql .= ", NULL";
	}
	
	if (isset($_GET['dv'])) {
		$sql .= ", '" . $conn->real_escape_string(trim($_GET['dv'])) . "'";
	} else {
		$sql .= ", NULL";
	}
	
	$sql .=  ", '" . $ip . "', '" . $date->format('Y-m-d H:i:s') . "')";

	$conn->query($sql);

	$Guid_qualify = $conn->insert_id;

	$_SESSION["id"] = $Guid_qualify;

	$result = $conn->query("SELECT Guid_status FROM tbl_mdl_status WHERE status = 'Registered'");
	
    $mdl_status = $result->fetch_assoc();
	
	if ($Guid_account != "NULL") {
		$result = $conn->query("SELECT sr.* FROM tblaccountrep ar LEFT JOIN tblsalesrep sr ON ar.Guid_salesrep = sr.Guid_salesrep WHERE Guid_account = " . $Guid_account);

		$salesrep = $result->fetch_assoc();
		
		$salesrep_fname =  $salesrep['first_name'];
		$salesrep_lname =  $salesrep['last_name'];
		$Guid_salesrep = $salesrep['Guid_salesrep'];
	}
	
	$sql = "INSERT INTO tbl_mdl_status_log(currentstatus, Guid_patient, Guid_account, account, Guid_salesrep, provider_id, Guid_status, Guid_user, salesrep_fname, salesrep_lname, deviceid, Date, Date_created) VALUES ('Y'," . $Guid_patient . ", " . $Guid_account  . ", " . $account_number . ", ";
	
	if (isset($Guid_salesrep))  {
		$sql .= $Guid_salesrep;
	} else {
		$sql .= "NULL";
	}
	
	$sql .= ", '" . $_POST['provider'] . "', " . $mdl_status['Guid_status'] . ", " . $Guid_user . ", ";
	
	if (isset($Guid_salesrep))  {
		$sql .= "'" . $salesrep_fname . "', '" . $salesrep_lname . "'";
	} else {
		$sql .= "NULL, NULL";
	}
	
	$sql .= ", '" . $conn->real_escape_string(trim($_GET['dv'])) . "'";
	
	$sql .= ", '" . $date->format('Y-m-d H:i:s') . "', '" . $date->format('Y-m-d H:i:s') . "')";

	$conn->query($sql);
	
	$Guid_status_log  = $conn->insert_id;
	
	$_SESSION["logid"] = $conn->insert_id;
	
	$sql = "UPDATE tbl_mdl_status_log SET Log_group = " . $Guid_status_log . " WHERE Guid_status_log = " . $Guid_status_log;

	$conn->query($sql);	
		
	$content = '			
		<p>Your account was successfully created.</p>			
		<strong>Click</strong><a href="' . HTTPS_SERVER . '/?continue=Yes"" style="color:#973737"> here </a><strong>to complete the questionnaire</strong></a>
		<br><p><strong>Click</strong><a href="http://www.mdlab.com/brca/" style="color:#973737"> here </a><strong>to learn more about BRCA testing</strong>';		

	$title = "Account successfully created";
	
	send_email($content, $title);
	
	require ("db/dbdisconnect.php");
}
function update_password(&$error) {
	$uppercase = preg_match('@[A-Z]@', $_POST['account_password']);
	$lowercase = preg_match('@[a-z]@', $_POST['account_password']);
	$number = preg_match('@[0-9]@', $_POST['account_password']);
	$invalid_entry = strpos($_POST['account_password'], $_POST['account_email']);
	$invalid_entry = strpos(strtolower($_POST['account_password']), "password");
		
	if ((!$uppercase) || (!$lowercase) || (!$number) || (strlen($_POST['account_password']) < 8) || ($invalid_entry) || ($_POST['account_password'] != $_POST['confirm_password'])) {
		$error['account_password'] = 1;
		$error['confirm_password'] = 1;		
	} else {
		require ("db/dbconnect.php");
		
		$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);
		
		$qualify = $result->fetch_assoc();
		
		$date = new DateTime("now", new DateTimeZone('America/New_York'));
		
		$sql = "UPDATE tbluser SET password = '" . md5($conn->real_escape_string($_POST['account_password'])) . "', Date_modified='"  . $date->format('Y-m-d H:i:s') . "' WHERE Guid_user = " . $qualify['Guid_user'];

		$conn->query($sql);	
	}	
}
function send_email($content, $title, $email="", $additional="", $not_qualified="") {
	require ("db/dbconnect.php");	
	
	$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);
	
	$qualify = $result->fetch_assoc();
	
	$result = $conn->query("SELECT * FROM tbluser WHERE Guid_user = " . $qualify['Guid_user']);
				
	$user = $result->fetch_assoc();
	
	$message = file_get_contents('email_template.php');			
		
	$message = str_replace("%title%", $title, $message);
	
	$message = str_replace("%content%", $content, $message);
	
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	$headers .= 'From: BRCAcare Application <BRCA_Questionnaire_Support@mdlab.com>';

	if (strlen($email)) {
		if (strlen($qualify['account_number'])) {
			$result = $conn->query("SELECT sr.first_name, sr.last_name FROM tblaccount a LEFT JOIN tblaccountrep ar ON a.Guid_account = ar.Guid_account LEFT JOIN tblsalesrep sr ON ar.Guid_salesrep = sr.Guid_salesrep WHERE account = '" . $qualify['account_number'] . "'");
			$account = $result->fetch_assoc();
			$account_name = ", " . $account['first_name'] . " " . $account['last_name'];
		}
		if ($not_qualified) {
		$subject = "BRCA Questionnaire (Not Qualified" . $account_name . ")";
		} else {
			$subject = "BRCA Questionnaire (Qualified" . $account_name . ")";
		}
		
		mail($email, $subject, $message, $headers);
	} else {
		mail($user['email'], "BRCAcare Questionnaire" . $additional, $message, $headers);
	}
	
	require ("db/dbdisconnect.php");
}
function delete_saved() {
	require ("db/dbconnect.php");
	
	//$conn->query("DELETE FROM tblqualifyans WHERE Guid_qualify=" . $_SESSION['id']);
	
	if (!isset($_POST['back'])) {
		$conn->query("DELETE FROM tblqualifyfam WHERE Guid_qualify=" . $_SESSION['id']);	
		$conn->query("DELETE FROM tblqualifypers WHERE Guid_qualify=" . $_SESSION['id']);	
	} 
	
	if (isset($_POST['back'])) {
		if (($_POST['prev_step'] == "cancer_detail") && ($_POST['type'] == "personal")) {
			$conn->query("DELETE a FROM tblqualifyans a LEFT JOIN tblcancerquestion q ON a.Guid_question = q.Guid_question WHERE a.Guid_qualify = " . $_SESSION['id'] . " AND q.question_type = \"personal\"");			
		}
		if (($_POST['prev_step'] == "cancer_detail") && ($_POST['type'] == "family")) {
			$conn->query("DELETE a FROM tblqualifyans a LEFT JOIN tblcancerquestion q ON a.Guid_question = q.Guid_question WHERE a.Guid_qualify = " . $_SESSION['id'] . " AND q.question_type = \"family\"");			
		}
		if ($_POST['current_step'] == "gene_mutation") {
			$conn->query("UPDATE tblqualify SET gene_mutation=NULL, gene_relation=NULL, gene=NULL WHERE Guid_qualify=" . $_SESSION['id']);
			$conn->query("DELETE FROM tblqualifygene WHERE Guid_qualify=" . $_SESSION['id']);			
		} elseif ($_POST['current_step'] == "gender_aj") {
			$conn->query("UPDATE tblqualify SET gender=NULL, ashkenazi=NULL WHERE Guid_qualify=" . $_SESSION['id']);	
		}
	} elseif (isset($_POST['resubmit'])) {
			$conn->query("UPDATE tblqualify SET insurance=NULL, gender=NULL, ashkenazi=NULL, gene_mutation=NULL, gene_relation=NULL, gene=NULL WHERE Guid_qualify=" . $_SESSION['id']);
			$conn->query("DELETE FROM tblqualifygene WHERE Guid_qualify=" . $_SESSION['id']);
	} elseif (isset($_POST['nc_validated']) && ($_POST['nc_validated'] == "1")) {
		//$conn->query("DELETE FROM tblqualify WHERE Guid_qualify=" . $_SESSION['id']);
	}
	
	require ("db/dbdisconnect.php");
}
function get_function () {
	require ("db/dbconnect.php");
	
	$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);
	
	$qualify = $result->fetch_assoc();
	
	$func = "";
	
	if (!strlen($qualify['insurance'])) {
		$func = "generate_insurance";
	} elseif ((!strlen($qualify['gender'])) || (!strlen($qualify['ashkenazi']))) {
		$func = "generate_gender_aj";
	} elseif (($qualify['insurance'] != "Medicare") && (!strlen($qualify['gene_mutation']))) {
		$func = "generate_gene_mutation";
	} else {		
		$result_pers = $conn->query("SELECT * FROM tblqualifypers WHERE Guid_qualify = " . $_SESSION['id']);
		
		if(mysqli_num_rows($result_pers)) {
			$result_no_cancer = $conn->query("SELECT * FROM tblqualifypers WHERE Guid_qualify = " . $_SESSION['id'] . "  AND cancer_type =\"No Cancer/None of the Above\"");
			
			if(mysqli_num_rows($result_no_cancer)) {
				$result_family = $conn->query("SELECT * FROM tblqualifyfam WHERE Guid_qualify = " . $_SESSION['id']);
			
				if(mysqli_num_rows($result_family)) {
					$func = "generate_cancer_detail";
				} else {
					$func = "generate_cancer_list_family";
				}
			} else {
				$func = "generate_cancer_detail";
			}
		} else {
			$func = "generate_cancer_list_personal";
		}
	}	
	
	require ("db/dbdisconnect.php");
	
	return $func;
}
function generate_unknown_screen($qualification_text) {
	require ("db/dbconnect.php");
	
	$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);
				
	$qualify = $result->fetch_assoc();
	
	$result = $conn->query("SELECT * FROM tbluser WHERE Guid_user = " . $qualify['Guid_user']);
			
	$user = $result->fetch_assoc();	
	
	$result = $conn->query("SELECT * FROM tblpatient WHERE Guid_user = " . $qualify['Guid_user']);
			
	$patient = $result->fetch_assoc();	
	
	$content = '	
	<p>Dear ' . $patient['firstname'] . ',</p>
 	<p>You recently completed the BRCA Questionnaire to determine if you meet clinical guidelines for hereditary cancer genetic testing. It was identified that you provided insufficient clinical history information to make this determination.</p>
 	<p>Please complete the steps below:</p>
	<p style="padding-left:2em">&bull; Obtain the unknown clinical history indicated on your summary report, which is available at our website at <a href="' . HTTPS_SERVER . '">' . HTTPS_SERVER . '.</a></p>
	<p style="padding-left:2em">&bull; Select "I already have an account" and login.</p>
	<p style="padding-left:2em">&bull; Provide the missing information.</p>
	<p style="padding-left:2em">&bull; Update the answers to the questions provided.</p>
	<p style="padding-left:2em">&bull; Select "Submit" to complete the summary report.</p>
 	<p>If you need assistance or have any questions, please contact us at <a href=mailto:BRCA-Support@mdlab.com">BRCA-Support@mdlab.com</a></p>
 	<p>Best Regards,</p>
 	<p>MDL BRCA Support Team</p>';	
	
	$insurance = $qualify["insurance"];
	
	if (($qualify["insurance"] != "Aetna") && ($qualify["insurance"] != "Medicare")) {
		$insurance = "NCCN";
	}
	
	generate_header("More information is needed");
?>
	<input type="hidden" name="prev_step" value="<?php echo $_POST['current_step']; ?>">
	<input type="hidden" name="fieldname" value="<?php echo $_POST['fieldname']; ?>">	
	
	<div class="info_needed">
		<p>More information is necessary to determine whether you are likely to meet the <strong class="semi"><?php echo $insurance;?> clinical guidelines</strong> for: 
<?php
	//$content .= '<p>More information is necessary to determine whether you are likely to meet the <strong class="semi">' . $insurance . ' clinical guidelines</strong>  for: </p>';
	
	//for ($i=0; $i < count($qualification_text); $i++) {
	//	$content .= '<p>' . $qualification_text[$i] . '</p>';
?>
			<!--<strong class="blue"><?php //echo $qualification_text[$i]; ?></strong>.-->
<?php
	//}
?>
		</p>
	</div>
				
	<ol id="missing_info">
<?php
	$result = $conn->query("SELECT * FROM tblunknownans LEFT JOIN tblcancerquestion ON tblunknownans.Guid_question = tblcancerquestion.Guid_question WHERE Guid_qualify =" . $_SESSION['id']);
	
	foreach($result as $question) {		
?>
	    <li>
		    <p><?php echo $question['question']; ?></p>
		</li>
<?php
	}
	
	$result = $conn->query("SELECT * FROM tblunknowngene WHERE Guid_qualify =" . $_SESSION['id']);
	
	if (mysqli_num_rows($result)) {		
?>
	    <li>
		    <p>Gene Mutation</p>
		</li>
<?php
	}
	
	$title = "BRCAcare Questionnaire - Incomplete Status";
		
	send_email($content, $title, "", " - Insufficient Information Provided");
	
	$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);
			
	$qualify = $result->fetch_assoc();
?>
	</ol>

    <section id="info_submitted">
		<h3>Information I have provided:</h3>
		
        <ul>
		    <li>
				<span class="pi_cat">Insurance</span>
				<span class="pi"><?php echo $qualify['insurance']; ?></span>
		    </li>
			<li>
				<span class="pi_cat">Gender</span>
				<span class="pi"><?php echo $qualify['gender']; ?></span>
		    </li>
		    <li>
				<span class="pi_cat">Ashkenazi</span>
				<span class="pi"><?php echo $qualify['ashkenazi']; ?></span>
		    </li>
<?php
	$result = $conn->query("SELECT * FROM tblqualifypers WHERE Guid_qualify = " . $_SESSION['id']);
	
	if (mysqli_num_rows($result)) {
		foreach($result as $personal) {
			$personal_cancer .= $personal['cancer_type'] . ", ";
		}
		$personal_cancer = rtrim($personal_cancer, ', ');
?>
			<li>
				<span class="pi_cat">Personal Cancer</span>
				<span class="pi"><?php echo $personal_cancer; ?></span>
		    </li>
<?php
	}

	$result = $conn->query("SELECT * FROM tblqualifyfam WHERE Guid_qualify = " . $_SESSION['id']);
	
	if (mysqli_num_rows($result)) {
		foreach($result as $family) {
			$family_cancer .= $family['cancer_type'] . ", ";
		}
		$family_cancer = rtrim($family_cancer, ', ');
?>
			<li>
				<span class="pi_cat">Family Cancer</span>
				<span class="pi"><?php echo $family_cancer; ?></span>
		    </li>
<?php
	}
?>
        </ul>
    </section>
	
	<div class="btns info">
		<button type="submit" class="s_btn back" name="back">
			<icon class="icon"></icon>
			<span>Back</span>
		</button>
		
		<!--<button type="submit" class="s_btn add" name="back">
			<icon class="icon"></icon>
			<span>Add Info</span>
		</button>-->
		
		<button type="button" class="s_btn print gold">
			<icon class="icon"></icon>
			<span>Print</span>
		</button>
		
		<button type="button" class="s_btn save" name="save" id="save">
			<icon class="icon"></icon>
			<span>Finish Later</span>
		</button>
	</div>
    
    <p class="imp_instruction">Print this summary statement for your personal records</p>


	</main>
<?php
	generate_overlays($error);
?>
		</form>
        <script src="../framework/js/common.js"></script>
        <script src="../framework/js/defer.js" defer></script>
		<script src="js/should_I_test.js" defer></script>
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-18558117-27"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());

          gtag('config', 'UA-18558117-27');
        </script>
    </body>
</html>

<?php
	save_snap_shot("2");
}
function generate_overlays($error) {	
	require ("db/dbconnect.php");
	
	if (isset($_SESSION['id'])) {
	
		$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_qualify = " . $_SESSION['id']);
				
		$qualify = $result->fetch_assoc();
		
		$result = $conn->query("SELECT * FROM tbluser WHERE Guid_user = " . $qualify['Guid_user']);
				
		$user = $result->fetch_assoc();	
		
		$result = $conn->query("SELECT dob, AES_DECRYPT(firstname_enc, 'F1rstn@m3@_%') as firstname, AES_DECRYPT(lastname_enc, 'L@stn@m3&%#') as lastname  FROM tblpatient WHERE Guid_user = " . $qualify['Guid_user']);
				
	    $patient = $result->fetch_assoc();	
		
		require ("db/dbdisconnect.php");
	}		
?>		
		
	<div id="logout_app_modal" class="overlay logout">
		<input type="hidden" name="nc_validated" id="nc_validated">
		<input type="hidden" name="no_continue_quaily_id" id="no_continue_quaily_id" value="<?php echo $qualify['Guid_user']; ?>">
		<div class="overlay_box">						
			<p class="imp"></p>
			<p>Are you sure you want to exit?</p>
<?php
		if (isset($_GET['lc']) && ($_GET['lc'] == "O")) {
		} elseif (isset($_GET['ln']) && ($_GET['ln'] == "np")) {
?>
		<div id="exit_email">
			<p>Please provide your email address to continue later.</P>
			<div class="field med">
				<div class="iwrap">
					<label for="email_no_continue">Email</label>
					<input type="email" id="email_no_continue" name="email_no_continue" class="sameP if" value="">
					<icon class="icon istatus"></span></icon>
				</div>
			</div>
		</div>
<?php
		}
?>
			<div class="btns">
				<button type="button" class="btn back neutral sm toggle" data-on="#logout_app_modal">
					<icon class="icon"></icon>
					<strong>Cancel</strong>
				</button>
				  
				<button class="btn" name="no_continue" id="no_continue" formnovalidate="formnovalidate">
					<icon class="icon"></icon>
					<strong>Exit</strong>
				</button>
			</div>
		</div>
	</div>

	<div id="exit_app_modal" class="overlay exit">
		<input type="hidden" name="fl_validated" id="fl_validated">
		<input type="hidden" name="finish_later_quaily_id" id="finish_later_quaily_id" value="<?php echo $qualify['Guid_user']; ?>">
		<input type="hidden" name="finish_later_np" id="finish_later_np" value="<?php echo (isset($_GET['ln']) && ($_GET['ln'] == "np")) ? $_GET['ln'] : ""; ?>">
		<div class="overlay_box">						
			<p class="imp"></p>
<?php
	if (isset($_GET['ln']) && ($_GET['ln'] == "np")) {
?>
		<p>Please provide your email address.</P>
		<div class="field med">
			<div class="iwrap">
				<label for="email_finish_later">Email</label>
				<input type="email" id="email_finish_later" name="email_finish_later" class="sameP if" value="">
				<icon class="icon istatus"></icon>
			</div>
		</div>
		<p>Once you exit, an email will be sent to finish this evaluation.</p>
<?php
	} else {
?>
		<p>Once you exit, an email will be sent to <?php echo $user['email']; ?> to finish this evaluation.</p>
<?php
	}
?>
			<div class="btns">
				<button type="button" class="btn back neutral sm toggle" data-on="#exit_app_modal">
					<icon class="icon"></icon>
					<strong>Cancel</strong>
				</button>
				  
				<button class="btn" name="finish_later" id="finish_later">
					<icon class="icon"></icon>
					<strong>Exit</strong>
				</button>
			</div>
		</div>
	</div>
	
	<div class="overlay none">
		<div class="overlay_box">
			
			<p>We can still determine if you meet clinical guidelines for testing. However, without medical coverage, you are responsible for any out-of-pocket costs.</p>
			
			<p class="imp">Do you still want to proceed with this evaluation?</p>
			
			<div class="btns">
				<button class="btn close neutral sm" name="no_continue">
					<icon class="icon"></icon>
					<strong>Exit</strong>
				</button>
				
				<button class="btn" name="yes_continue">
					<icon class="icon"></icon>
					<strong>Yes</strong>
				</button>
				
				<!--<button class="btn gold cost" name="get_estimate">
					<icon class="icon"></icon>
					<strong>Determine My Costs</strong>
				</button>-->
			</div>
		</div>
	</div>
<?php
	if (isset($error['same_rel'])) {		
		$same_rel = explode(":", $error['same_rel_list']);				
?>
            <div class="overlay sameRel show">
				<div class="overlay_box">
					
					<p>It looks like you entered the same relative more than once.</p>
					
					<p class="imp">Please confirm if the <strong class="same_relative"><?php echo $same_rel[1]; ?></strong>'s entered are different.</p>
					
					<div class="btns">
						<button type="button" class="btn back neutral sm toggle" data-on=".overlay.sameRel">
							<icon class="icon"></icon>
							<strong>No</strong>
						</button>
						
						<button class="btn" name="yes_continue_same_rel">
							<icon class="icon"></icon>
							<strong>Yes</strong>
						</button>
					</div>
				</div>
			</div>
<?php
	}
?>
	<div class="overlay help">
		<section class="overlay_box">
			<h3>Ask for Help</h3>
			
			<div class="btns">
				<button type="button" class="btn back neutral sm toggle" data-on=".overlay.help">
					<icon class="icon"></icon>
					<strong>Cancel</strong>
				</button>
				
				<button class="btn" name="send_help">
					<icon class="icon"></icon>
					<strong>Send</strong>
				</button>
			</div>
		</section>
	</div>

	<div class="overlay resubmit">
		<section class="overlay_box">
			<p class="center">Are you <strong><?php echo $patient['firstname'] . " " . $patient['lastname']; ?></strong>?</p>
			
			<div class="btns">
				<button id="resubmit_no" class="btn back neutral sm" name="no_continue">
					<icon class="icon"></icon>
					<strong>No</strong>
				</button>
				
				<button id="resubmit_yes" class="btn" name="resubmit">
					<icon class="icon"></icon>
					<strong>Yes</strong>
				</button>
			</div>
		</section>
	</div>
<?php
	$diff = abs(strtotime(date("Y-m-d")) - strtotime($patient['dob']));

	$age = floor($diff / (365*60*60*24));

	if (($qualify['insurance'] == "Other") && isset($_POST['type']) && ($_POST['type'] == "personal") && (in_array("No Cancer/None of the Above", $_POST['personal_cancer'])) && ($age >= 65)) {	
?>
	<div id="medicare_app_modal" class="overlay show">
		<section class="overlay_box">
			<p class="center">Do you have Medicare Insurance?</p>
			
			<div class="btns">
				<button type="button" class="btn back neutral sm toggle" data-on="#medicare_app_modal">
					<icon class="icon"></icon>
					<strong>No</strong>
				</button>				
				<button id="medicare_yes" class="btn" name="medicare_yes">
					<icon class="icon"></icon>
					<strong>Yes</strong>
				</button>
			</div>
		</section>
	</div>
<?php
	}
}
function patient_consent($firstname, $lastname) {
    $pconsent = '
		<section class="form patient_consent" style="page-break-before: always;">
		    <figure class="form_logo">
				<img src="'. dirname(__FILE__) .'/questionnaire/images/logo_geneveda.png" alt="Geneveda">
				<figcaption>A Division of Medical Diagnostic Laboratories, LLC</figcaption>
			</figure>
			
			<h1 class="printTitle"><span>Patient Consent</span></h1>
			
			<p>I consent that Geneveda, a division of Medical Diagnostic Laboratories, LLC may contact my physician(s) designated below:</p>
			
			<span class="fill_in full"><img src="images/icon_edit.png" alt="edit"></span>
			
			<p>for the limited purpose of discussing the results of my Cancer History Questionnaire and whether or not I am an appropriate candidate for breast and ovarian cancer surveillance testing.</p>
			
			<ul class="form_info">
			    <li>
				    <span class="fi_type">Patient Name:</span>
					<strong class="fi_value">' . ucwords(strtolower($firstname)) . " " . ucwords(strtolower($lastname)) . '</strong>
				</li>
				<li class="column">
				    <span class="fi_type">Patient Signature:</span>
					<span class="fill_in"><img src="images/icon_edit.png" alt="edit"></span>
				</li>
				<li>
				    <span class="fi_type">Date:</span>
					<strong class="fi_value">' . date('n/j/Y'). '</strong>
				</li>
			</ul>
			
			<p>I have been provided a copy of this consent for my records.</p>
		</section>
	';
	return $pconsent;
}

function perform_dup_search() {
	require ("db/dbconnect.php");

	$result = $conn->query("select p.Guid_user FROM tblpatient p LEFT JOIN tblqualify q on p.Guid_user = q.Guid_user Where p.firstname_enc = AES_ENCRYPT('" . $conn->real_escape_string($_POST['first_name']) . "', 'F1rstn@m3@_%') AND p.lastname_enc = AES_ENCRYPT('" . $conn->real_escape_string($_POST['last_name']) . "', 'L@stn@m3&%#') AND p.dob = '" . $_POST['dob'] . "' AND DATE(p.Date_created) = '" . date("Y-m-d") . "' AND q.account_number = '" . $_GET['an'] . "'");
	
	while ($row = $result->fetch_row()) {
		$conn->query("DELETE FROM tbl_deductable_log where Guid_user IN (" . $row[0]." )");
		$conn->query("DELETE FROM tbl_mdl_note where Guid_user IN (" . $row[0].")");
		$conn->query("DELETE FROM tbl_mdl_number WHERE Guid_user IN (" . $row[0].")");
		$conn->query("DELETE FROM tbl_mdl_stats WHERE Guid_user IN (" . $row[0].")");
		$conn->query("DELETE FROM tbl_mdl_status_log WHERE Guid_user IN (" . $row[0].")");
		$conn->query("DELETE FROM tbl_revenue WHERE Guid_user IN (" . $row[0].")");
		$conn->query("DELETE FROM tblaccountverify WHERE Guid_user IN (" . $row[0].")");
		$conn->query("DELETE FROM tbladmins WHERE Guid_user IN (" . $row[0].")");
		$conn->query("DELETE FROM tblphysician WHERE Guid_user IN (" . $row[0].")");
		$conn->query("DELETE FROM tblurlconfig WHERE Guid_user IN (" . $row[0].")");

		$conn->query("DELETE FROM tbl_ss_qualifyans WHERE Guid_qualify IN (SELECT Guid_qualify FROM tblqualify WHERE Guid_user IN (" . $row[0]."))");
		$conn->query("DELETE FROM tbl_ss_qualifyfam WHERE Guid_qualify IN (SELECT Guid_qualify FROM tblqualify WHERE Guid_user IN (" . $row[0]."))");
		$conn->query("DELETE FROM tbl_ss_qualifygene WHERE Guid_qualify IN (SELECT Guid_qualify FROM tblqualify WHERE Guid_user IN (" . $row[0]."))");
		$conn->query("DELETE FROM tbl_ss_qualifypers WHERE Guid_qualify IN (SELECT Guid_qualify FROM tblqualify WHERE Guid_user IN (" . $row[0]."))");
		$conn->query("DELETE FROM tblqualifyans WHERE Guid_qualify IN (SELECT Guid_qualify FROM tblqualify WHERE Guid_user IN (" . $row[0]."))");
		$conn->query("DELETE FROM tblqualifyfam WHERE Guid_qualify IN (SELECT Guid_qualify FROM tblqualify WHERE Guid_user IN (" . $row[0]."))");
		$conn->query("DELETE FROM tblqualifygene WHERE Guid_qualify IN (SELECT Guid_qualify FROM tblqualify WHERE Guid_user IN (" . $row[0]."))");
		$conn->query("DELETE FROM tblqualifypers WHERE Guid_qualify IN (SELECT Guid_qualify FROM tblqualify WHERE Guid_user IN (" . $row[0]."))");
		$conn->query("DELETE FROM tbl_hcf_provider WHERE Guid_qualify IN (SELECT Guid_qualify FROM tblqualify WHERE Guid_user IN (" . $row[0]."))");
		$conn->query("DELETE FROM tblunknownans WHERE Guid_qualify IN (SELECT Guid_qualify FROM tblqualify WHERE Guid_user IN (" . $row[0]."))");
		$conn->query("DELETE FROM tblunknowngene WHERE Guid_qualify IN (SELECT Guid_qualify FROM tblqualify WHERE Guid_user IN (" . $row[0]."))");

		$conn->query("DELETE FROM tblqualify WHERE Guid_user IN (" . $row[0].")");
		$conn->query("DELETE FROM tbl_ss_qualify WHERE Guid_user IN  (" . $row[0].")");
		$conn->query("DELETE FROM tbluser WHERE Guid_user IN (" . $row[0].")");
		$conn->query("DELETE FROM tblpatient WHERE Guid_user IN (" . $row[0].")");
	}
}
function generate_additional_summary($error, $not_qualified, $qualification_text) {
	require ("db/dbconnect.php");
	generate_header("You provided us with the following family cancer history:");
	
	$type = "personal";
		
	if (isset($_POST['type']) && ($_POST['type'] == "family")) {			
		$type = "family";
	}
?>
	<input type="hidden" name="prev_step" value="cancer_list_family">
	<input type="hidden" name="current_step" value="additional_summary">
	<input type="hidden" name="not_qualified" value="<?php echo $not_qualified; ?>">
	<input type="hidden" name="type" value="<?php echo $type; ?>">	
<?php
	for ($i=0; $i < count($qualification_text); $i++) {
?>
	<input type="hidden" name="qualification_text[]" value="<?php echo $qualification_text[$i]; ?>">
<?php
	}
	
	generate_relative_list();
?>
	<section class="questitle">
		<h2>Do you have any additional relatives with cancer?</h2>
		
		<fieldset class="answers">
			<legend></legend>
			
			<div class="input">
				<span class="radio">
					<input type="radio" name="additional_relatives" value="No" id="additional_relatives_no">
				</span>
				<label for="additional_relatives_no">No</label>
			</div>

			<div class="input">
				<span class="radio">
					<input type="radio" name="additional_relatives" value="Yes" id="additional_relatives_yes">
				</span>
				<label for="additional_relatives_yes">Yes</label>
			</div>
		</fieldset>
	</section>							
<?php
	require ("db/dbdisconnect.php");

	if (isset($_GET['lc']) && ($_GET['lc'] == "O")) {
		$buttons = array("Back"=>"back", "Next"=>"next");	
		$class = array("Back"=>" back neutral sm", "Next"=>"");
	} else {
		$buttons = array("Back"=>"back", "Finish Later"=>"save", "Next"=>"next");	
		$class = array("Back"=>" back neutral sm", "Finish Later"=>" gold save", "Next"=>"");
	}

	generate_outer_bottom($error, $buttons, $class);

}
function generate_additional_screen($error, $not_qualified, $qualification_text) {
	require ("db/dbconnect.php");
	generate_header("Please add additional relatives.");
	
	$type = "personal";
		
	if (isset($_POST['type']) && ($_POST['type'] == "family")) {			
		$type = "family";
	}
?>
	<input type="hidden" name="prev_step" value="additional_summary">
	<input type="hidden" name="current_step" value="additional_screen">
	<input type="hidden" name="additional_relatives" id="additional_relatives" value="">
	<input type="hidden" name="type" value="<?php echo $type; ?>">
	<input type="hidden" name="not_qualified" value="<?php echo $not_qualified; ?>">
<?php
	for ($i=0; $i < count($qualification_text); $i++) {
?>
	<input type="hidden" name="qualification_text[]" value="<?php echo $qualification_text[$i]; ?>">
<?php
	}

	generate_relative_list();
	
	if (isset($_POST['additional_relatives']) && (count($error))) {
		$additional_relative = $_POST['additional_relative'];
		$additional_cancer = $_POST['additional_cancer'];
		$additional_age = $_POST['additional_age'];
		$additional_info_breast = $_POST['additional_info_breast'];
		$additional_info_prostate = $_POST['additional_info_prostate'];
		$additional_ashkenazi = $_POST['additional_ashkenazi'];
		$additional_deceased = $_POST['additional_deceased'];		
	} else {
		$additional_relative = "";
		$additional_cancer = "";
		$additional_age = "";
		$additional_info_breast = "";
		$additional_info_prostate = "";
		$additional_ashkenazi = "";
		$additional_deceased = "";
	}
?>
				<ul class="ps_info">
					<li class="ts_wrap show">
						<div class="field sm<?php echo ((!isset($_POST['additional_relative'])) ? "" : (isset($error['additional_relative']) ? " error" : " valid")) ?>">
							<div class="iwrap">
								<label for="additional_relation">Relative</label>
								<select id="additional_relation" name="additional_relative" class="if">
									<option value=""></option>
<?php
	$relations = $conn->query("SELECT value FROM (SELECT * FROM tblfirstdegrel UNION ALL SELECT * FROM tblseconddegrel UNION ALL SELECT * FROM tblthirddegrel) raw");
	foreach($relations as $relation) {
?>
							<option value="<?php echo $relation['value']; ?>"<?php echo (($additional_relative == $relation['value']) ? " selected" : "");?>><?php echo $relation['value']; ?></option>
<?php 
	}
?>
								</select>
								<icon class="icon istatus"><span class="required"></span></icon>
							</div>
						</div>

						<div class="field sm<?php echo ((!isset($_POST['additional_cancer'])) ? "" : (isset($error['additional_cancer']) ? " error" : " valid")) ?>">
							<div class="iwrap">
								<label for="additional_cancer">Cancer</label>
								<select id="additional_cancer" name="additional_cancer" class="if toggleShow">
									<option></option>
<?php
	$cancers = $conn->query("SELECT value FROM tblqcancertype WHERE value <> 'No Cancer/None of the Above' ORDER BY value");
		
	foreach ($cancers as $cancer) {
		$data_show = "";
		if ($cancer['value'] == "Breast") {
			$data_show = ' data-show=".show-1, .show-3"';
		} elseif ($cancer['value'] == "Prostate") {
			$data_show = ' data-show=".show-2, .show-3"';
		}
?>
									<option value="<?php echo $cancer['value']; ?>"<?php echo $data_show; ?><?php echo (($additional_cancer == $cancer['value']) ? " selected" : "");?>><?php echo $cancer['value']; ?></option>							
<?php
	}
?>
								</select>
								<icon class="icon istatus"><span class="required"></span></icon>
							</div>
						</div>

						<div class="field sm toggleShow show-1<?php echo ((!isset($additional_info_breast)) ? "" : (isset($error['additional_info_breast']) ? " error" : " valid")) ?><?php echo (($additional_cancer == "Breast") ? " active" : "") ?>">
							<div class="iwrap">
								<label for="additional_info_breast">Cancer Type</label>
								<select id="additional_info_breast" name="additional_info_breast" class="if">
									<option></option>
									<option value="Triple-Negative"<?php echo (($additional_info_breast == "Triple-Negative") ? " selected" : "");?>>Triple-Negative</option>
									<option value="Bilateral (cancer in both breasts at the same time)"<?php echo (($additional_info_breast == "Bilateral (cancer in both breasts at the same time)") ? " selected" : "");?>>Bilateral (cancer in both breasts at the same time)</option>
									<option value="Contralateral (cancer in both breasts at different times)"<?php echo (($additional_info_breast == "Contralateral (cancer in both breasts at different times)") ? " selected" : "");?>>Contralateral (cancer in both breasts at different times)</option>
									<option value="Unknown/None Of These"<?php echo (($additional_info_breast == "Unknown/None Of These") ? " selected" : "");?>>Unknown/None Of These</option>
								</select>
								<icon class="icon istatus"><span class="required"></span></icon>
							</div>
						</div>

						<div class="field sm toggleShow show-2<?php echo ((!isset($additional_info_prostate)) ? "" : (isset($error['additional_info_prostate']) ? " error" : " valid")) ?><?php echo (($additional_cancer == "Prostate") ? " active" : "") ?>">
							<div class="iwrap">
								<label for="additional_info_prostate">Cancer Type</label>
								<select id="additional_info_prostate" name="additional_info_prostate" class="if">
									<option></option>
									<option value="Metastatic (cancer spread to other body parts)"<?php echo (($additional_info_prostate == "Metastatic (cancer spread to other body parts)") ? " selected" : "");?>>Metastatic (cancer spread to other body parts)</option>
									<option value="High-Grade (Gleason score 7 or greater)"<?php echo (($additional_info_prostate == "High-Grade (Gleason score 7 or greater)") ? " selected" : "");?>>High-Grade (Gleason score 7 or greater)</option>				
									<option value="Unknown/None Of These"<?php echo (($additional_info_prostate == "Unknown/None Of These") ? " selected" : "");?>>Unknown/None Of These</option>
								</select>
								<icon class="icon istatus"><span class="required"></span></icon>
							</div>
						</div>

						<div class="field sm age<?php echo ((!isset($_POST['additional_age'])) ? "" : (isset($error['additional_age']) ? " error" : " valid")) ?>">
							<div class="iwrap">
								<label for="additional_age">Age Diag.</label>
								<input type="number" max="" id="additional_age" name="additional_age" class="if" value="<?php echo $additional_age; ?>">
								<icon class="icon istatus"><span class="required"></span></icon>
							</div>
						</div>

						<div class="dec_box field toggleShow show-3<?php echo ((($additional_cancer == "Breast") || ($additional_cancer == "Prostate"))? " active" : "") ?>">
							<input type="checkbox" name="additional_ashkenazi" id="additional_ashkenazi" class="if" value="1"<?php echo (strlen($additional_ashkenazi) ? " checked='checked'" : "") ?>>
							<label for="additional_ashkenazi">Ashkenazi Jewish</label>
						</div>

						<div class="dec_box">
							<input type="checkbox" name="additional_deceased" id="additional_deceased" class="if" value="1"<?php echo (strlen($additional_deceased) ? " checked='checked'" : "") ?>>
							<label for="additional_deceased">Deceased</label>
						</div>
						
						<button type="button" class="add_field">
							<strong>Add Relative</strong>
						</button>
					</li>
                </ul>				
<?php
	require ("db/dbdisconnect.php");

	if (isset($_GET['lc']) && ($_GET['lc'] == "O")) {
		$buttons = array("Back"=>"back", "Next"=>"next");	
		$class = array("Back"=>" back neutral sm", "Next"=>"");
	} else {
		$buttons = array("Back"=>"back", "Finish Later"=>"save", "Next"=>"next");	
		$class = array("Back"=>" back neutral sm", "Finish Later"=>" gold save", "Next"=>"");
	}

	generate_outer_bottom($error, $buttons, $class);
}
function generate_relative_list() {
	require ("db/dbconnect.php");
	
	$result_ans = $conn->query("SELECT * FROM tblqualifyans WHERE Guid_qualify=" . $_SESSION['id'] . " AND relative IS NOT NULL");
	
	$result_additional = $conn->query("SELECT * FROM tbl_additional_info WHERE Guid_qualify=" . $_SESSION['id']);
	
	if (mysqli_num_rows($result_ans) || mysqli_num_rows($result_additional)) {		
?>
			<table class="tbl_summary">
				<thead>
					<tr>
						<th>Relative</th>
						<th>Cancer</th>
						<th>Age Diagnosed</th>
					</tr>
				</thead>
				<tbody>
<?php
		foreach ($result_ans as $relative) {
?>
					<tr>
						<td><?php echo $relative['relative'] ?></td>
						<td><?php echo $relative['cancer_type'] ?></td>
						<td><?php echo $relative['age_relative'] ?></td>
					</tr>
<?php
		}
?>
<?php
		foreach ($result_additional as $relative) {
?>
					<tr>
						<td><?php echo $relative['relative'] ?></td>
						<td><?php echo $relative['cancer_type'] ?><?php echo (strlen($relative['cancer_info']) ? "(" . $relative['cancer_info'] . ")" : "") ?></td>
						<td><?php echo $relative['age_relative']; ?></td>
					</tr>
<?php
		}
?>
				</tbody>
			</table>
<?php
	}
	
	require ("db/dbdisconnect.php");
	
	return $html;
}
function verify_additional_info(&$error) {
	if (strlen($_POST['additional_relative']) || strlen($_POST['additional_cancer']) || strlen($_POST['additional_age'])) {		
		if (!strlen($_POST['additional_relative'])) {
			$error['additional_relative'] = 1;
		}
		if (!strlen($_POST['additional_cancer'])) {
			$error['additional_cancer'] = 1;
		} elseif (($_POST['additional_cancer'] == "Breast") && (!strlen($_POST['additional_info_breast']))) {
			$error['additional_info_breast'] = 1;
		} elseif (($_POST['additional_cancer'] == "Prostate") && (!strlen($_POST['additional_info_prostate']))) {
			$error['additional_info_prostate'] = 1;
		}
		if (!strlen($_POST['additional_age'])) {
			$error['additional_age'] = 1;
		}
	}
}
?>