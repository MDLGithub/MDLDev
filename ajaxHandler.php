<?php
	require_once('config.php');
	require ("db/dbconnect.php");

    if (isset($_POST['validate_input']) && ($_POST['validate_input'] == '1')) {
		$valid_email_format = 1;
		$valid_email = 1;
		$valid_zip = 1;
		$valid_phone = 1;
		
		validate_input($conn, trim($_POST['email']), trim($_POST['zip']), trim($_POST['phone']), $valid_email_format, $valid_email, $valid_zip, $valid_phone);
		
		echo json_encode( array('valid_email_format'=>$valid_email_format,'valid_email'=>$valid_email,'valid_zip'=>$valid_zip,'valid_phone'=>$valid_phone) );  
	} elseif (isset($_POST['send_pm_email']) && ($_POST['send_pm_email'] == '1')) {
		generate_pm_email($conn, $_POST['quaily_id'], trim($_POST['email']), $_POST['co'], HTTPS_SERVER);
	} elseif (isset($_POST['send_hcf_email']) && ($_POST['send_hcf_email'] == '1')) {
		generate_hcf_email($conn, $_POST['quaily_id'], trim($_POST['email']), $_POST['co'], HTTPS_SERVER);
	} elseif (isset($_POST['send_email']) && ($_POST['send_email'] == '1')) {
		generate_email($conn, $_POST['quaily_id'], trim($_POST['email']), HTTPS_SERVER);
	} elseif (isset($_POST['update_hcf_provider_info']) && ($_POST['update_hcf_provider_info'] == '1')) {		
		$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_user = " . $_POST['quaily_id']);

		$qualify = $result->fetch_assoc();
		
		$date = new DateTime("now", new DateTimeZone('America/New_York'));

		$sql = "INSERT INTO tbl_hcf_provider (Guid_qualify, practice_name, physician_name, address, city, state, zip, phone, Date_created) VALUES ('" . $qualify['Guid_qualify'] . "', '" . trim($_POST['practice_name']) . "', '" . trim($_POST['physician_name']) . "', '" . trim($_POST['address']) . "', '" . trim($_POST['city']) . "', '" . trim($_POST['state']) . "', '" . trim($_POST['zip']) . "', '" . trim($_POST['phone']) . "', '" . $date->format('Y-m-d H:i:s') . "')";
		
		$conn->query($sql);
	}
	
	exit();
	
	function validate_input($conn, $email, $zip, $phone, &$valid_email_format, &$valid_email, &$valid_zip, &$valid_phone){
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
		
		if (strlen($email)) {
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$valid_email_format = 0;
			} else {
				$result_user = $conn->query("SELECT * FROM tbluser WHERE email = '" . $conn->real_escape_string($email) . "'");
				
				if ($result_user->num_rows) {
					$valid_email = 0;
					
				}
			}
		}
		if (strlen($zip)) {
			if ((!preg_match ('/[0-9]{5}$/', $zip)) && (!preg_match ('/([0-9]{5})-([0-9]{4})$/', $zip))) {
				$valid_zip = 0;					
			}			
		}
		if (strlen($phone)) {
			if (is_numeric($phone)) {
				if (strlen($phone) != 10) {
					$valid_phone = 0;
				}
			} elseif (!preg_match("/^([1]-)?[0-9]{3}-[0-9]{3}-[0-9]{4}$/i", $phone)) {
				$valid_phone = 0;
			}
		}
	}
	function generate_pm_email($conn, $quaily_id, $email, $co, $HTTPS_SERVER) {		
		if (strlen($email)) {
			$message = file_get_contents('../email/email_template_pm.html');
			
			$result = $conn->query("SELECT * FROM tblqualify WHERE Guid_user = " . $quaily_id);

			$qualify = $result->fetch_assoc();

			$result = $conn->query("SELECT * FROM tblaccount WHERE account = " . $qualify['account_number']);	
				
			$account = $result->fetch_assoc();
			
			$result = $conn->query("SELECT * FROM tblpatient WHERE Guid_user = " . $quaily_id);

			$patient = $result->fetch_assoc();
			
			$x = 3; 
			$min = pow(10,$x);
			$max = pow(10,$x+1)-1;
			$pin = rand($min, $max);
			
			$logo = HTTP_SERVER . "images/practice/" . $account['logo'];
			
			$logoalt = $account['name'];
					
			$content = "
				<p style=\"font-size: 20px; color: #4c4c4c;\"><strong>Dear " . $patient['salutation'] . " " . ucwords(strtolower($patient['lastname'])) . ",</strong></p>
				
				<p style=\"color:#353535; line-height: 22px;\">Thank you for participating in our BRCAcare ID program.  This email confirms that your patient questionnaire was received by " . $account['name'] . " and the personal and/or family history information you provided has been entered into the BRCAcare Online Questionnaire to determine if you meet clinical guidelines for hereditary cancer genetic testing.  As part of this process, an account has been created for you and is accessible by selecting the following link:</p>
				
				<p><a href=\"" . $HTTPS_SERVER . "?ln=pin&continue=Yes&lc=PMR&co=" . $co . "&an=" . $qualify['account_number'] . "\" style=\"display: block; margin: 24px 0; text-decoration: none;\"><strong style=\"text-align: center; color: #0c5085; background: #f6f0d5; border: 1px solid #b9ad77; padding: 5px 0; display: block;\">Access the questionnaire</strong></a></p>
															
				<p>When prompted, please enter <strong style=\"color: #0c5085; border-bottom: 2px dashed #0c5085;\">" . $pin . "</strong> as your PIN to log into the site.</p>												
				
				<p style=\"color:#353535; line-height: 22px;\">At this time, you can review your results at this website now.  If you meet the appropriate clinical guidelines, " . $account['name'] . "'s office will contact you to schedule an appointment.  If you have any questions, please do not hesitate to contact " . $account['name'] . " at " . $account['phone_number'] . ".</p>
				
				<p>Thank you.</p>
				
				<p><strong>";
			
			if ($co == "gen") {
				$content .= "Geneveda";
			} else {
				$content .= "MDLAB";
			}
			
			$content .= " BRCAcare&reg; Support Team.</strong></p>";
			
			send_email($email, $message, $content, $logoalt, $logo);
		}
		
		$date = new DateTime("now", new DateTimeZone('America/New_York'));
		
		$sql = "UPDATE tbluser SET email = '" . $email . "', Date_modified = '" . $date->format('Y-m-d H:i:s') . "', password = '" . md5($pin) . "' WHERE Guid_user = " . $quaily_id;						
		$conn->query($sql);		
	}
	function generate_hcf_email($conn, $quaily_id, $email, $co, $HTTPS_SERVER) {
		$date = new DateTime("now", new DateTimeZone('America/New_York'));
		
		if (strlen($email)) {
			$message = file_get_contents('../email/email_template_f.html');

			$result = $conn->query("SELECT * FROM tblpatient WHERE Guid_user = " . $quaily_id);

			$patient = $result->fetch_assoc();
			
			$x = 3; 
			$min = pow(10,$x);
			$max = pow(10,$x+1)-1;
			$pin = rand($min, $max);
			
			$logo = "";

			$logoalt = "";

			$content = "
				<p style=\"font-size: 20px; color: #4c4c4c;\"><strong>Dear " . ucwords(strtolower($patient['firstname'])) . ",</strong></p>
				
				<p style=\"color:#353535; line-height: 22px;\">Thank you for completing our online BRCAcare&trade; Questionnaire with our Geneveda team at the Health Fair on " . $date->format('n/j/Y')  . " to determine if you would meet clinical guidelines for hereditary breast and ovarian cancer (HBOC) and/or Lynch syndrome genetic testing.</p>
				
				<p style=\"color:#353535; line-height: 22px;\">You can print your summary report or start the questionnaire over by selecting the following link:</p>
				
				<p><a href=\"" . $HTTPS_SERVER . "?ln=pin&continue=Yes&lc=FR&co=" . $co . "\" style=\"display: block; margin: 24px 0; text-decoration: none;\"><strong style=\"text-align: center; color: #0c5085; background: #f6f0d5; border: 1px solid #b9ad77; padding: 5px 0; display: block;\">Access the questionnaire</strong></a></p>
															
				<p>When prompted, please enter <strong style=\"color: #0c5085; border-bottom: 2px dashed #0c5085;\">" . $pin . "</strong> as your PIN to log into the site.</p>												
				
				<p style=\"color:#353535; line-height: 22px;\">If you have any questions or need any assistance, please send an email to BRCA-Support@mdlab.com.</p>
				
				<p>Best Regards,</p>

				<p><strong>";
				
			if ($co == "gen") {
				$content .= "Geneveda";
			} else {
				$content .= "MDLAB";
			}
			
			$content .= " BRCAcare&trade; Support Team.</strong></p>";						
		}
		
		$sql = "UPDATE tbluser SET email = '" . $email . "', Date_modified = '" . $date->format('Y-m-d H:i:s') . "', password = '" . md5($pin) . "' WHERE Guid_user = " . $quaily_id;						
		$conn->query($sql);
		
		send_email($email, $message, $content, $logoalt, $logo);		
	}
	function generate_email($conn, $quaily_id, $email, $HTTPS_SERVER) {
		$date = new DateTime("now", new DateTimeZone('America/New_York'));
		
		$message = file_get_contents('email_template.php');
		
		$logo = "";

		$logoalt = "";
		
		$sql = "UPDATE tbluser SET email = '" . $email . "', Date_modified = '" . $date->format('Y-m-d H:i:s') . "' WHERE Guid_user = " . $quaily_id;
		$conn->query($sql);
		
		$content = '
			<p>You can still finish!</p>
			<p>You have recently used our questionnaire to determine if you meet <strong>clinical guidelines for hereditary cancer genetic testing</strong>.</p>
			<p>Your progress in the questionnaire has been saved and you can continue at any time by clicking on the link below.</p>
			<a href="' . $HTTPS_SERVER . 'forgot-password.php" style="color:#973737"><strong>Complete the questionnaire</strong></a>
			<p><strong>Click</strong><a href="http://www.mdlab.com/brca/" style="color:#973737"> here </a><strong>to learn more about BRCA testing</strong>';
			
		send_email($email, $message, $content, $logoalt, $logo);
	}
	function send_email($email, $message, $content, $logoalt, $logo) {
		$date = new DateTime("now", new DateTimeZone('America/New_York'));
		$title = "Should I Be Screened";
		
		$message = str_replace("%logoalt%", $logoalt, $message);
				
		$message = str_replace("%logo%", $logo, $message);
		
		$message = str_replace("%title%", $title, $message);
		
		$message = str_replace("%content%", $content, $message);
		
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= 'From: BRCAcare Application <BRCA_Questionnaire_Support@mdlab.com>';				
		
		mail($email, "BRCA Questionnaire Completion Notification", $message, $headers);
	}
	/* function update_hcf_provider_info($conn, $quaily_id, $practice_name, $physician_name, $address, $city, $state, $zip, $phone) {
		$date = new DateTime("now", new DateTimeZone('America/New_York'));
										
		$result = $conn->query("SELECT * FROM tblpatient WHERE Guid_user = " . $quaily_id);
		
		$qualify = $result->fetch_assoc();
		
		$sql = "INSERT INTO tbl_hcf_provider (Guid_qualify, practice_name, physician_name, address, city, state, zip, phone, Date_created) VALUES ('" . $qualify['Guid_qualify'] .  "', '" . $conn->real_escape_string($practice_name) .  "', '" . $conn->real_escape_string($physician_name) .  "', '" . $conn->real_escape_string($address) .  "', '" . $conn->real_escape_string($city) .  "', '" . $conn->real_escape_string($state) .  "', '" . $conn->real_escape_string($zip) .  "', '". $conn->real_escape_string($phone) .  "', '"  . $date->format('Y-m-d H:i:s') . "')";

		$conn->query($sql);		
	} */
?>