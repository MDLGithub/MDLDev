<?php
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'shareddb');
define('DB_PASSWORD', 'dayterBays3!');
define('DB_DATABASE', 'shareddb');
$conn = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
} 	

/*
$sql = "SELECT * FROM `tbl_mdl_status_log` WHERE `Guid_status` IN (29,30,31)";
$results = $conn->query($sql);
$count=1;

foreach($results as $row_info) {	
	$conn->query($sql);
	
	$sql = "INSERT INTO tbl_mdl_status_log (Guid_status, currentstatus, Guid_user, Guid_patient, Guid_account, account, Guid_salesrep, salesrep_fname, salesrep_lname, Recorded_by, DATE, Date_created) select Guid_status, currentstatus, Guid_user, Guid_patient, Guid_account, account, Guid_salesrep, salesrep_fname, salesrep_lname, Recorded_by, DATE, Date_created FROM tbl_mdl_status_log WHERE Guid_status_log =  " . $row_info['Guid_status_log'];

	$conn->query($sql);
	
	$Guid_status_log = $conn->insert_id;
	
	$sql = "UPDATE tbl_mdl_status_log SET Log_group = " . $Guid_status_log . ", Guid_status = 36, Recorded_by = 9002 WHERE Guid_status_log = " . $Guid_status_log;


	$conn->query($sql);
	
	$sql = "UPDATE tbl_mdl_status_log SET Log_group = " . $Guid_status_log . " WHERE Guid_status_log = " . $row_info['Guid_status_log'];


	$conn->query($sql);
	
}
exit;
$sql = "SELECT Guid_qualify FROM `tblqualify` WHERE Guid_qualify not in (select DISTINCT Guid_qualify from tbl_ss_qualify WHERE Guid_qualify < 999999 AND Guid_qualify <> 0 AND mark_as_test = '0')";
$results = $conn->query($sql);

$prev_guid = "";
foreach($results as $row_info) {
	$sql = "SELECT ss.Guid_qualify, ss.Guid_user, ss.account_number, ss.Date_created, tp.Guid_patient, ta.Guid_account,
                tRA.Guid_salesrep, tsR.first_name, tsR.last_name  FROM `tblqualify` AS ss INNER JOIN tblpatient AS tp
                    ON (ss.Guid_user = tp.Guid_user) INNER JOIN tblaccount AS ta
                    ON (ss.account_number = ta.account)
                INNER JOIN tblaccountrep AS tRA
                    ON (ta.Guid_account = tRA.Guid_account)
                INNER JOIN tblsalesrep AS tsR
                    ON (tRA.Guid_salesrep = tsR.Guid_salesrep) WHERE ss.Guid_qualify =".$row_info['Guid_qualify'];

$results = $conn->query($sql);

$qualify = $results->fetch_assoc();

if (mysqli_num_rows($results)) {
	$sql = "INSERT INTO tbl_mdl_status_log (
			Guid_status_log, Guid_status, currentstatus, Guid_user, Guid_patient, Guid_account, account,
			Guid_salesrep, salesrep_fname, salesrep_lname, Recorded_by, DATE, Date_created)
			VALUES
			(NULL, '28', 'Y', '{$qualify['Guid_user']}', '{$qualify['Guid_patient']}', '{$qualify['Guid_account']}',
			'{$qualify['account_number']}', '{$qualify['Guid_salesrep']}', '{$qualify['first_name']}',
			'{$qualify['last_name']}', '9001', '{$qualify['Date_created']}', NOW())";



		$conn->query($sql);
		
		$Guid_status_log = $conn->insert_id;
		
		$sql = "UPDATE tbl_mdl_status_log SET Log_group = " . $Guid_status_log . " WHERE Guid_status_log = " . $Guid_status_log;
		$conn->query($sql);
}
}
exit;*/
$sql = "SELECT * FROM `tbl_ss_qualify` where mark_as_test='0' ORDER BY `Guid_qualify` ASC, Date_created desc";

$results = $conn->query($sql);

$prev_guid = "";
foreach($results as $row_info) {
	if ($row_info['Guid_qualify'] != $prev_guid) {
		$sql = "SELECT Guid_patient from tblpatient where Guid_user=".$row_info['Guid_user'];
		$result = $conn->query($sql);
		$row = $result->fetch_row();
	
		$abc = "'" . $row[0] . "', ";
			
		if (strlen($row_info['account_number'])) {
			$sql = "SELECT Guid_account from tblaccount where account=".$row_info['account_number'];
			$result = $conn->query($sql);
			$row = $result->fetch_row();			
			$abc .= "'" . $row[0] . "', ";
			
			$abc .= "'" . $row_info['account_number'] . "', ";
			
			$sql = "SELECT Guid_salesrep from tblaccountrep where Guid_account=".$row[0];
			$result = $conn->query($sql);
			$row = $result->fetch_row();
			$abc .= "'" . $row[0] . "', ";
			
			$sql = "SELECT first_name, last_name from tblsalesrep where Guid_salesrep=".$row[0];
			$result = $conn->query($sql);
			$row = $result->fetch_row();			
			$abc .= "'" . $row[0] . "', ";
			$abc .= "'" . $row[1] . "', ";			
		} else {
			$abc .= "NULL, NULL, NULL, NULL, NULL, ";
		}
		
		$sql = "INSERT INTO tbl_mdl_status_log_atul (
			Guid_status_log, Guid_status, currentstatus, Guid_user, Guid_patient, Guid_account, account,
			Guid_salesrep, salesrep_fname, salesrep_lname, Recorded_by, DATE, Date_created)
			VALUES
			(NULL, '28', 'N', '{$row_info['Guid_user']}', ";
		$sql .= $abc;
		$sql .= "'9000', '{$row_info['Date_created']}', NOW())";

		$conn->query($sql);
		
		$Guid_status_log = $conn->insert_id;
		
		$sql = "UPDATE tbl_mdl_status_log_atul SET Log_group = " . $Guid_status_log . " WHERE Guid_status_log = " . $Guid_status_log;

		$conn->query($sql);
		
		$sql = "INSERT INTO tbl_mdl_status_log_atul (
                Guid_status_log, Guid_status, currentstatus, Guid_user, Guid_patient, Guid_account, account,
                Guid_salesrep, salesrep_fname, salesrep_lname, Recorded_by, DATE, Date_created)
                VALUES
                (NULL, '36', 'Y', '" . $row_info['Guid_user'] . "', ";
		$sql .= $abc;
		$sql .= "'9000', '" . $row_info['Date_created'] . "', NOW())";
			
		$conn->query($sql);
		
		$Guid_status_log = $conn->insert_id;
		
		$sql = "UPDATE tbl_mdl_status_log_atul SET Log_group = " . $Guid_status_log . " WHERE Guid_status_log = " . $Guid_status_log;

		$conn->query($sql);
		
		if ($row_info['qualified'] == 'Yes') {
			$Guid_status = 29; 
		} elseif ($row_info['qualified'] == 'Unknown') {
			$Guid_status = 31; 
		} elseif ($row_info['qualified'] == 'No') {
			$Guid_status = 30; 
		}
		
		$sql = "INSERT INTO tbl_mdl_status_log_atul (
                Guid_status_log, Guid_status, Log_group, currentstatus, Guid_user, Guid_patient, Guid_account, account,
                Guid_salesrep, salesrep_fname, salesrep_lname, Recorded_by, DATE, Date_created)
                VALUES
                (NULL, '{$Guid_status}', '{$Guid_status_log}', 'Y', '" . $row_info['Guid_user'] . "', ";
		$sql .= $abc;
		$sql .= "'9000', '{$row_info['Date_created']}', NOW())";

        $conn->query($sql);				
		
		$prev_guid = $row_info['Guid_qualify'];
	}
} 
exit;
/*
$results = $conn->query("SELECT DISTINCT Guid_user FROM `tbl_ss_qualify`  ORDER BY Guid_user");

foreach($results as $row_list) {
	$count = 1;
	$sql = "SELECT ss.Guid_user, ss.account_number, ss.Date_created, ss.qualified, tp.Guid_patient, ta.Guid_account,
                tRA.Guid_salesrep, tsR.first_name, tsR.last_name
                FROM tbl_ss_qualify AS ss
                INNER JOIN tblpatient AS tp
                    ON (ss.Guid_user = tp.Guid_user)
                INNER JOIN tblaccount AS ta
                    ON (ss.account_number = ta.account)
                INNER JOIN tblaccountrep AS tRA
                    ON (ta.Guid_account = tRA.Guid_account)
                INNER JOIN tblsalesrep AS tsR
                    ON (tRA.Guid_salesrep = tsR.Guid_salesrep)
                WHERE ss.Guid_user = '{$row_list['Guid_user']}'  
                ORDER BY Date_created DESC
                LIMIT 1";
		echo $sql;		
	$result = $conn->query($sql);
	
	$row_info = mysqli_fetch_assoc($result);
	
	$sql = mysql_result(mysql_query("SELECT *
        	FROM mdl_status_log
            WHERE Guid_user = '{$row_list['Guid_user']}' AND Guid_status = '28'"), 0);
			
	$result = $conn->query($sql);
	
	$count_log_registered = mysqli_num_rows($result);
	$Guid_status_log = "";
	
	if (!$count_log_registered) {
		$sql = "INSERT INTO tbl_mdl_status_log (
			Guid_status_log, Guid_status, Guid_user, Guid_patient, Guid_account, account,
			Guid_salesrep, salesrep_fname, salesrep_lname, order_by, Recorded_by, DATE, Date_created)
			VALUES
			(NULL, '28', '{$row_info['Guid_user']}', '{$row_info['Guid_patient']}', '{$row_info['Guid_account']}',
			'{$row_info['account_number']}', '{$row_info['Guid_salesrep']}', '{$row_info['first_name']}',
			'{$row_info['last_name']}', '0', '9999', '{$row_info['Date_created']}', NOW())";
		$conn->query($sql);

		$Guid_status_log = $conn->insert_id;

		$sql = "UPDATE tbl_mdl_status_log SET Log_group = " . $Guid_status_log . " WHERE Guid_status_log = " . $Guid_status_log;

		$conn->query($sql);	
	}			
	
	$sql = "SELECT *
        	FROM mdl_status_log
            WHERE Guid_user = ‘{$row_list[‘Guid_user’]}’ AND ((Guid_status = '29') OR (Guid_status = '30') OR
            OR (Guid_status = '31'))";
			$result = $conn->query($sql);
	
	$count_log_status = mysqli_num_rows($result);
	
	if (!$count_log_status) {
		if ($row_info['qualified'] == 'Yes') {
			$Guid_status = 29; 
		} elseif ($row_info['qualified'] == 'Unknown') {
			$Guid_status = 31; 
		} elseif ($row_info['qualified'] == 'No') {
			$Guid_status = 30; 
		}
		
		$sql = "INSERT INTO tbl_mdl_status_log (
                Guid_status_log, Log_group, Guid_status, Guid_user, Guid_patient, Guid_account, account,
                Guid_salesrep, salesrep_fname, salesrep_lname, order_by, Recorded_by, DATE, Date_created)
                VALUES
                (NULL, '{$Guid_status_log}', '$Guid_status', '{$row_info['Guid_user']}', '{$row_info['Guid_patient']}', '{$row_info['Guid_account']}',
                '{$row_info['account_number']}', '{$row_info['Guid_salesrep']}', '{$row_info['first_name']}',
                '{$row_info['last_name']}', '0', '44', '{$row_info['Date_created']}', NOW())";
        $conn->query($sql);
	}
}*/
?>