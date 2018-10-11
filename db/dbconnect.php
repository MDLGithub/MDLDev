<?php
	// $servername = "localhost";
	// $username = "shareddb";
	// $password = "dayterBays3!";
	// $dbname = "shareddb";

	// $conn = new mysqli($servername, $username, $password, $dbname);
	// $con = mysqli_connect($servername, $username, $password, $dbname);
	// if ($conn->connect_error) {
		// die("Connection failed: " . $conn->connect_error);
	// } 
	$conn = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} 	
?>