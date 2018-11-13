<?php
require_once('config.php');

require ("db/dbconnect.php");

$error = array();

if (empty($_POST)) {
	generate_header("Please enter your email address");
	generate_init_html($error);
} elseif (isset($_POST['password_reset'])) {	
	password_reset($conn, $error);
	
	if (count($error)) {
		generate_header("Please enter your email address");
		generate_init_html($error);
	} else {
		generate_header("Next, please check your email and enter the reset code below.");
		generate_password_reset_html($error);
	}
} elseif (isset($_POST['submit_code'])) {
	verify_code($conn, $error);
	
	if (count($error)) {
		generate_header("Next, please check your email and enter the reset code below.");
		generate_password_reset_html($error);
	} else {
		generate_header("Now enter a new password to reset it on your account.");
		new_password_html($error);
	}
} elseif (isset($_POST['update_password'])) {
	update_password($conn, $error);
	
	if (count($error)) {
		generate_header("Now enter a new password to reset it on your account.");
		new_password_html($error);
	} else {
		generate_header("Password reset successful");
		confirmation_html();
	}
}

generate_footer();

require ("db/dbdisconnect.php");

function generate_header($title) {
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
        <meta name="viewport" content="width=device-width" />
		<title>BRCAcare | Should I Get Tested?</title>
		<link href="https://fonts.googleapis.com/css?family=Questrial" rel="stylesheet">
        <link href="style.min.css" rel="stylesheet" type="text/css">
    </head>
    <body>
		<form action="" method="post" id="app_wrap">

			<header id="app_head">
				<section id="app_title">
<?php
	if ($_GET['co'] == "gen") {
?>
					<figure class="app_logo_geneveda"><img src="images/logo_geneveda.png" alt="Geneveda"></figure>					
<?php
	} else {
?>
					<figure id="app_logo_mdl"><img src="../dev1/wp-content/themes/oceanreef/images/logo.png" alt="MDL"></figure>
<?php
	}
	
	if (isset($_GET['an'])) {
		require ("db/dbconnect.php");
			
		$result = $conn->query("SELECT * FROM tblaccount WHERE account  = '" . $conn->real_escape_string(intval($_GET['an'])) . "'");
		
		if (mysqli_num_rows($result)) {
			$account = $result->fetch_assoc();		
			if (strlen($account['logo_filename'])) {
?>
					<span class="divide"></span>
					<figure id="practice_logo" class="preload"><img data-src="images/practice/<?php echo $account['logo_filename']; ?>" alt=""></figure>
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
?>					</section>
			    <h1><?php echo $title ?></h1>
			</header>
			<main>
<?php
}
function generate_init_html($error) {
	if (isset($error['no_account'])) {
?>
				<div class="display_msg error">
					<span class="msg_type"></span>
					<p>Account does not exist</p>
				</div>
<?php
	}
?>	
				<div class="field med">
					<div class="iwrap">
						<label for="account_email">Email</label>
						<input type="email" id="account_email" name="account_email" class="if" value="" required="">
				        <icon class="icon istatus"><span class="required"></span></icon>
			        </div>
		        </div>
				
				<div class="btns">
					<a href="/questionnaire" class="btn back neutral sm toggle">
						<icon class="icon"></icon>
						<strong>Cancel</strong>
					</a>
					
					<button class="btn" name="password_reset" id="forgot_next">
					    <icon class="icon"></icon>
					    <strong>Next</strong>
				    </button>
			    </div>			
<?php
}
function generate_password_reset_html($error) {
	if (count($error)) {
?>
				<div class="display_msg error">
					<span class="msg_type"></span>
					<p>Invalid reset code entered</p>
				</div>
<?php
	}
?>
				<div class="field med">
					<div class="iwrap">
						<label for="reset_code">Reset Code</label>
						<input type="text" id="reset_code" name="reset_code" class="if" value="" required="">
						<icon class="icon istatus"><span class="required"></span></icon>
					</div>
				</div>
				
				<div class="btns">
					<a href="/questionnaire" class="btn back neutral sm toggle">
						<icon class="icon"></icon>
						<strong>Cancel</strong>
					</a>
					
					<button class="btn" name="submit_code" id="forgot_next">
						<icon class="icon"></icon>
						<strong>Next</strong>
					</button>
				</div>
<?php
}
function new_password_html($error) {
	if (count($error)) {
?>
    <div class="display_msg error">
        <span class="msg_type"></span>
        <p>Passwords do not match</p>
    </div>
<?php
	}
?>
		<input type="hidden" name= "reset_code" value="<?php echo $_POST['reset_code']; ?>">
		
		<div class="field med">
			<div class="iwrap">
				<label for="new_pass">New Password</label>
				<input type="password" id="new_pass" name="new_pass" class="sameP if" value="" required data-reqs="Be at least 8 characters,Include an uppercase letter,Include a number" pattern="(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$">
				<icon class="icon istatus"><span class="required"></span></icon>
			</div>
		</div>
		
		<div class="field med">
			<div class="iwrap">
				<label for="new_pass_confirm">Confirm New Password</label>
				<input type="password" id="new_pass_confirm" name="new_pass_confirm" class="same if" data-title="Error" data-reqs="The passwords do not match" required>
				<icon class="icon istatus"><span class="required"></span></icon>
			</div>
		</div>
			
		<div class="btns">
			<a href="/questionnaire" class="btn back neutral sm toggle">
				<icon class="icon"></icon>
				<strong>Cancel</strong>
			</a>
			
			<button class="btn" name="update_password" id="forgot_next">
				<icon class="icon"></icon>
				<strong>Next</strong>
			</button>
		</div>
<?php
}
function confirmation_html() {
?>
		<h2 class="hl_lg">Your password has been updated.</h2>

		<section class="cAlign">
		    <h3 class="sub_hl sfont">Please sign in with your new password.</h3>
		
		    <a href="<?php echo HTTPS_SERVER . "?" . $_SERVER['QUERY_STRING']; ?>" class="btn login">
			    <icon class="icon"></icon>
			    <strong>Sign In</strong>
		    </a>
	    </section>
<?php
}
function generate_footer() {
?>
			</main>
		</form>
        <script src="../framework/js/common.js"></script>
        <script src="../framework/js/defer.js" defer></script>
		<script src="js/should_I_test.js" defer></script>
    </body>
</html>
<?php
}
function password_reset($conn, &$error) {	
	$Guid_user = 0;
	
	$Guid_user = $conn->query("SELECT Guid_user FROM tbluser WHERE email = '" . trim($_POST['account_email']) . "'")->fetch_object()->Guid_user; 	

	if ($Guid_user) {
		$conn->query("DELETE FROM tblpasswordreset WHERE Guid_user=" . $Guid_user);		
		
		$reference_id = rand(10000, 99999);
		
		$date = new DateTime("now", new DateTimeZone('America/New_York') );
		
		$sql = "INSERT INTO tblpasswordreset (Guid_user, reference_id, Date_created) VALUES (\"" . $Guid_user .  "\",\"" . $reference_id . "\", \"" . $date->format('Y-m-d H:i:s') . "\")";
			
		$conn->query($sql);		
				
		$subject = 'MDLAB password reset request received';
		
		$message = file_get_contents('email_template.php');
						
		$content = '
				<p>A password reset request was received for this email address on the MDLAB website.</p>
				<p>Please enter the reset code <strong>' . $reference_id . '</strong> into the password reset screen.</p>
				<p>The password reset request must be completed <strong>within 2 hours</strong>.</p>';
		  
		$message = str_replace("%title%", "Password Reset", $message);
		
		$message = str_replace("%content%", $content, $message);
		
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= "From: no-reply@mdlab.com\r\n";
		
		mail($_POST['account_email'], $subject, $message, $headers);
		
	} else {
		$error['no_account'] = 1;
	}
}
function verify_code($conn, &$error) {
	
    if (!strlen(trim($_POST['reset_code']))) {
        $error['reset_code'] = 1;
    } else {
		$Date_created = $conn->query("SELECT Date_created FROM tblpasswordreset WHERE reference_id = '" . $_POST['reset_code'] . "'")->fetch_object()->Date_created;
		
        if (!strlen($Date_created)) {			
            $error['reset_code'] = 1;
        } else {			
			$date = new DateTime("now", new DateTimeZone('America/New_York') );
			
            $diff = StrToTime($date->format('Y-m-d H:i:s')) - StrToTime($Date_created);
            $diff /= ( 60 * 60 );

            if ($diff > 2) {				
                $error['reset_code'] = 1;                               
            }
        }
    }	
}
function update_password($conn, &$error) {
	$uppercase = preg_match('@[A-Z]@', $_POST['new_pass']);
	$lowercase = preg_match('@[a-z]@', $_POST['new_pass']);
	$number = preg_match('@[0-9]@', $_POST['new_pass']);
	$invalid_entry = strpos($_POST['new_pass'], $_POST['account_email']);
	$invalid_entry = strpos(strtolower($_POST['new_pass']), "password");	

	if (!$uppercase || !$lowercase || !$number || (strlen($_POST['new_pass']) < 8) || $invalid_entry) {		
		$error['password_reset'] = 1;
	} elseif (trim($_POST['new_pass']) != trim($_POST['new_pass_confirm'])) {
		$error['password_reset'] = 1;
	} else {
		$Guid_user = $conn->query("SELECT Guid_user FROM tblpasswordreset WHERE reference_id = '" . $_POST['reset_code'] . "'")->fetch_object()->Guid_user;
						
		$sql = "UPDATE tbluser SET password=\"" . md5(trim($_POST['new_pass'])) . "\" WHERE Guid_user =" . $Guid_user;
			
		$conn->query($sql);
		
		$conn->query("DELETE FROM tblpasswordreset WHERE Guid_user=" . $Guid_user);	
	}
}
?>