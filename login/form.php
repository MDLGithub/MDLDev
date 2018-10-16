<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="assets/css/forms.css">
	<link rel="stylesheet" type="text/css" href="assets/css/custom-styles1.css">
	<script type="text/javascript">

/*--This JavaScript method for Print command--*/

    function PrintDoc() {

        var toPrint = document.getElementById('printarea');

        var popupWin = window.open('', '_blank', 'width=350,height=150,location=no,left=200px');

        popupWin.document.open();

        popupWin.document.write('<html><title>::Preview::</title><link rel="stylesheet" type="text/css" href="print.css" /></head><body onload="window.print()">')

        popupWin.document.write(toPrint.innerHTML);

        popupWin.document.write('</html>');

        popupWin.document.close();

    }

/*--This JavaScript method for Print Preview command--*/

    function PrintPreview() {

        var toPrint = document.getElementById('printarea');

        var popupWin = window.open('', '_blank', 'width=350,height=150,location=no,left=200px');

        popupWin.document.open();

        popupWin.document.write('<html><title>::Print Preview::</title><link rel="stylesheet" type="text/css" href="Print.css" media="screen"/></head><body">')

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
					    <tr>
						    <td>Registered Patients</td>
							<td>11</td>
							<td>24</td>
						</tr>
						<tr>
						    <td>Completed Questionnaire</td>
							<td>9</td>
							<td>20</td>
						</tr>
						<tr>
						    <td>Insufficient Information</td>
							<td>2</td>
							<td>5</td>
						</tr>
						<tr>
						    <td>Medically Qualified</td>
							<td>4</td>
							<td>12</td>
						</tr>
					</tbody>
				</table>
			</div>
		</header>
		
		<div class="main">
			<p>Based upon the cited clinical policy testing guidelines, testing is <strong class="labColor">recommended</strong> for the following patients:</p>
			
			<table id="testing_recommended" class="ftable c4 c5 c6 lC5">
			    <thead>
					<tr>
						<th><img class="pBG" src="images/swatch_gen_purple.png" alt="">First Name</th>
						<th>Last Name</th>
						<th>DOB</th>
						<th>Guideline Applied</th>
						<th>Guideline Met</th>
						<th>Date Collected</th>
					</tr>
				</thead>
			    <tbody>
				    <tr>
						<td>John</td>
						<td>Doe</td>
						<td>1/1/1980</td>
						<td>Aetna</td>
						<td>BRCA</td>
						<td></td>
					</tr>
					<tr>
						<td>John</td>
						<td>Doe</td>
						<td>1/1/1980</td>
						<td>Aetna</td>
						<td>BRCA</td>
						<td></td>
					</tr>
				</tbody>
			</table>
			
			<p><strong>Insufficient information</strong> was available to determine if the following patients met clinical policy testing guidelines:</p>
			
			<table class="ftable sf4">
			    <thead>
					<tr>
						<th><img class="pBG" src="images/swatch_gen_grey.png" alt="">First Name</th>
						<th>Last Name</th>
						<th>DOB</th>
						<th>Information Needed</th>
					</tr>
				</thead>
			    <tbody>
				    <tr>
						<td>John</td>
						<td>Doe</td>
						<td>1/1/1980</td>
						<td>Genetic Mutation</td>
					</tr>
					<tr>
						<td>John</td>
						<td>Doe</td>
						<td>1/1/1980</td>
						<td>Do you have at least one first- or second-degree close blood relative in your family with breast cancer at age 45 years or younger?</td>
					</tr>
				</tbody>
			</table>
			
			<div id="not_recommended" class="columns two">
				<div class="col">
				    <p>The following patients were also screened and based upon the information provided, screening for BRCA, HBOC, and/or Lynch Syndrome is <strong>not</strong> recommended:</p>
				
					<table class="sTable">
						<thead>
							<tr>
							    <th>First Name</th>
								<th>Last Name</th>
								<th>DOB</th>
							</tr>
						</thead>
						<tbody>
						    <tr>
							    <td>Jane</td>
								<td>Smith</td>
								<td>3/3/1985</td>
							</tr>
						</tbody>
					</table>
				</div>
				
				<div id="not_completed" class="col">
				    <p>The following patients initiated, but did not complete the questionnaire:</p>
				
					<table class="sTable">
						<thead>
							<tr>
							    <th>First Name</th>
								<th>Last Name</th>
								<th>DOB</th>
							</tr>
						</thead>
						<tbody>
						    <tr>
							    <td>Irma</td>
								<td>Redulus</td>
								<td>9/18/1965</td>
							</tr>
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




	
	