<?php
ob_start();
require_once('config.php');
require_once('settings.php');
require_once('functions.php');

?>

<html>
<head>
	<link rel="stylesheet" type="text/css" href="assets/css/forms.css">
	<link rel="stylesheet" type="text/css" href="assets/css/custom-styles1.css">
	<script type="text/javascript">

	    function PrintDoc() {

	        var toPrint = document.getElementById('printarea');

	        var popupWin = window.open('', '_blank', 'width=900,height=900,location=no,left=200px');

	        popupWin.document.open();

	        popupWin.document.write('<html><title>::Preview::</title><link rel="stylesheet" type="text/css" href="assets/css/print.css" /></head><body onload="window.print()">')

	        popupWin.document.write(toPrint.innerHTML);

	        popupWin.document.write('</html>');

	        popupWin.document.close();

	    }
	</script>
</head>
<body>

	<input type="button" value="Print" class="btn" onclick="PrintDoc()"/>
	 
	<section class="form follow-up geneveda" id="printarea">
	    <header class="header">
			 <div class="col one">
				<figure class="account_logo"><img src="https://www.mdlab.com/dev/images/practice/22230-LEXINGTON%20OBGYN%20ASSOCIATES.png" alt="Lexington"></figure>

				<p>Thank you for selecting Geneveda to provide Hereditary Breast and Ovarian Cancer (HBOC) Screening for your patients. This report was prepared on <strong>September 24, 2018</strong>.</p>
			</div> 
		
			<div class="col two">
				 <figure class="lab"><img src="https://www.mdlab.com/dev/images/logo_geneveda.png" alt="Geneveda"></figure> 
				
				<table class="stat_table c2 c3">
				    <thead>
					    <tr>
						    <th><h4 class="side_head">Summary</h4></th>
							<th><img class="pBG" src="images/swatch_gen_green.png" alt="">Today</th>
							<th>Total</th>
						</tr>
					</thead>
					
					<tbody>
						<?php 
							if(isset($_POST['today'])){
								$account = $_POST['account'];
								$guid_account = $_POST['guid_account'];
								$today = $_POST['today'];

							    echo get_status_state($db, '0', array('Guid_account'=>$guid_account), array('account_id'=>$guid_account,'status_table'=>'1'), $today);
							}
						?>

					</tbody>
				</table>
			</div>
		</header>
		
		<div class="main">
			<p> <strong class="labColor">Follow-up is needed - </strong>Patients met medical neccessity and submitted specimens:</p>

			<?php 
				/*$statuses = array('28' => 'Registered Paient', '36' => 'Completed Questionnaire', '16' => 'Insufficient Informatin' , '29' => 'Medically Qualified' );
			    $filterUrlStr = "";
			    $content = '';    
			    foreach ($statuses as $key => $status) {
			        $stats1 = get_stats_info($db, $key, FALSE, $searchData);
			        $stats2 = get_stats_info_today($db, $key, FALSE, $searchData, $today);
			        $content .= "<tr class='parent'>";
			        $content .= "<td class='text-left'><span>".$status."</span></td>";            
			        $content .= '<td><a>'.$stats2['count'].'</a></td>';
			        $content .= '<td><a>'.$stats1['count'].'</a></td>';
			        $content .= "</tr>";    
			    } */   
			    //return $content;
			    $specimen = get_stats_info_today($db, 1, FALSE, array('Guid_account'=>$guid_account), $today);
			    
			?>
			
			<table id="testing_recommended" class="ftable c4 c5 c6 lC5">
			    <thead>
					<tr>
						<th>First Name</th>
						<th>Last Name</th>
						<th>DOB</th>
						<th>Guideline Applied</th>
						<th>Guideline Met</th>
						<th>Date Collected</th>
					</tr>
				</thead>
			    <tbody>
			    	<?php 
			    		if(!empty($specimen['info'])):
					    	foreach ($specimen['info'] as $key => $value) {
					    		$sql = "SELECT `firstname`,`lastname`,`dob` FROM `tblpatient` WHERE `Guid_patient`=:patient_id";
					    		$test = $db->query($sql, array('patient_id' => $value['Guid_patient']));
					    		
						    	echo "<tr>";
						    	echo "<td>".$test[0]['firstname']."</td>";
						    	echo "<td>".$test[0]['lastname']."</td>";
						    	echo "<td>".$test[0]['dob']."</td>";
						    	echo "<td></td>";
						    	echo "<td></td>";
						    	echo "<td></td>";
						    	echo "</tr>";
						    }
						endif;
				    ?>
				</tbody>
			</table>
			
			<p><strong>Insufficient information</strong> was available to determine if the following patients met clinical policy testing guidelines:</p>
			<?php $unknown = get_stats_info_today($db, 31, FALSE, array('Guid_account'=>$guid_account), $today); ?>
			<table class="ftable sf4">
			    <thead>
					<tr>
						<th>First Name</th>
						<th>Last Name</th>
						<th>DOB</th>
						<th>Information Needed</th>
					</tr>
				</thead>
			    <tbody>
				    <?php 
			    		if(!empty($unknown['info'])):
					    	foreach ($unknown['info'] as $key => $value) {
					    		$sql = "SELECT `firstname`,`lastname`,`dob` FROM `tblpatient` WHERE `Guid_patient`=:patient_id";
					    		$test = $db->query($sql, array('patient_id' => $value['Guid_patient']));
					    		
						    	echo "<tr>";
						    	echo "<td>".$test[0]['firstname']."</td>";
						    	echo "<td>".$test[0]['lastname']."</td>";
						    	echo "<td>".$test[0]['dob']."</td>";
						    	echo "<td></td>";
						    	echo "</tr>";
						    }
						endif;
				    ?>
				</tbody>
			</table>
			
			<div id="not_recommended" class="columns two">
				<div class="col">
				    <p>The following patients were also screened and based upon the information provided, screening for BRCA, HBOC, and/or Lynch Syndrome is <strong>not</strong> recommended:</p>
					<?php $notqual = get_stats_info_today($db, 30, FALSE, array('Guid_account'=>$guid_account), $today); ?>
					<table class="sTable">
						<thead>
							<tr>
							    <th>First Name</th>
								<th>Last Name</th>
								<th>DOB</th>
							</tr>
						</thead>
						<tbody>
						    <?php 
					    		if(!empty($notqual['info'])):
							    	foreach ($notqual['info'] as $key => $value) {
							    		$sql = "SELECT `firstname`,`lastname`,`dob` FROM `tblpatient` WHERE `Guid_patient`=:patient_id";
							    		$test = $db->query($sql, array('patient_id' => $value['Guid_patient']));
							    		
								    	echo "<tr>";
								    	echo "<td>".$test[0]['firstname']."</td>";
								    	echo "<td>".$test[0]['lastname']."</td>";
								    	echo "<td>".$test[0]['dob']."</td>";
								    	echo "</tr>";
								    }
								endif;
						    ?>
						</tbody>
					</table>
				</div>
				
				<div id="not_completed" class="col">
				    <p>The following patients initiated, but did not complete the questionnaire:</p>
					<?php $incomplete = get_stats_info_today($db, 16, FALSE, array('Guid_account'=>$guid_account), $today); ?>
					<table class="sTable">
						<thead>
							<tr>
							    <th>First Name</th>
								<th>Last Name</th>
								<th>DOB</th>
							</tr>
						</thead>
						<tbody>
						    <?php 
					    		if(!empty($incomplete['info'])):
							    	foreach ($incomplete['info'] as $key => $value) {
							    		$sql = "SELECT `firstname`,`lastname`,`dob` FROM `tblpatient` WHERE `Guid_patient`=:patient_id";
							    		$test = $db->query($sql, array('patient_id' => $value['Guid_patient']));
							    		
								    	echo "<tr>";
								    	echo "<td>".$test[0]['firstname']."</td>";
								    	echo "<td>".$test[0]['lastname']."</td>";
								    	echo "<td>".$test[0]['dob']."</td>";
								    	echo "</tr>";
								    }
								endif;
						    ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		
		<p>Access your physician portal any time at <span class="labColor">https://www.mdlab.com/questionnaire/login</span>. Please contact me for any additional details or if you do not have access to your portal.</p>
		
		<footer>
			<section class="gc_title">
			    <h4>Brandon Franklin, PhD</h4>
				<em>Specialty Sales Consultant, Division of Genetics and Oncology</em>
			</section>
			
			<div class="columns">
				<figure class="foogo col"><img src="images/logo_mdl_print.svg" alt=""></figure>

				<div class="gc_contact col">
					<section>
						<h5>Medical Diagnostic Laboratories, L.L.C.</h5>
						<span>www.<b class="sb">mdlab</b>.com</span>
					</section>

					<ul>
						<li><img src="images/icon_address.svg" alt="">2439 Kuser Rd</li>
						<li>Hamilton, NJ 08690</li>
					</ul>

					<ul>
						<li><img src="images/icon_phone.svg" alt="">888.414.3237</li>
						<li><img class="gc_email" src="images/icon_email.svg" alt="">bfranklin@mdlab.com</li>
					</ul>
				</div>
			</div>
		</footer>
	</section>
</body>
</html>
