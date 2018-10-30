<?php
	$directory = "/questionnaire/login";
	require_once('config.php');
	
	//require ("../db/dbconnect.php");
	
	$q = "SELECT * FROM tbl_ss_qualify WHERE Guid_qualify = " . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "'";
	
	$qualify = $db->row($q);
    //$qualify = $salesrep['name'];
	//$qualify = $result->fetch_assoc();
	
	$q = "SELECT * FROM tbloutlier";
	
	$outliers = $db->query($q);
	
	$qualification_text = array();
	
	$cancer_found_brca = 0;
	
	$cancer_found_lynch = 0;
	
	foreach($outliers as $outlier) {
		$cancer_type = 0;
		if ($outlier['cancer_type'] == "N/A") {
			$cancer_type = 1;
		} else {
			$q = "SELECT * FROM tbl_ss_qualifypers WHERE Guid_qualify = " . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "' AND cancer_type = \"" . $outlier['cancer_type'] . "\"";
			$cancer_type = $db->query($q);
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
					$q = "SELECT * FROM tbl_ss_qualifygene WHERE Guid_qualify = " . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "' AND gene IN (\"EPCAM\",\"MLH1\",\"MSH2\",\"MSH6\",\"PMS2\")";
					$result = $db->query($q);
					if ($result) {
						$cancer_found_lynch = 1;
					} 
					$q = "SELECT * FROM tbl_ss_qualifygene WHERE Guid_qualify = " . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "' AND gene IN (\"ATM\",\"BARD1\",\"BRCA1\",\"BRCA2\",\"BRIP1\",\"CDH1\",\"CHEK2\",\"MUTYH\",\"PALB2\",\"PTEN\",\"RAD51C\",\"RAD51D\",\"STK11\",\"TP53\")";						
					$result = $db->query($q);
					if ($result) {
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
	if (($qualify["insurance"] == "Aetna") || ($qualify["insurance"] == "Medicare")) {
		$cancer_found_brca = 1;
	} else {
		$q = "SELECT * FROM tbl_ss_qualifyans WHERE Guid_qualify = " . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "' AND Guid_outlier IS NULL";
		$answers = $db->query($q);
		if ($answers) {
			foreach($answers as $answer) {
				$q = "SELECT * FROM tblcancerquestion WHERE Guid_question = " . $answer['Guid_question'];
				$question = $db->row($q);
				
				
				$child_answer_yes = 0;
				
				if ($question['hide_yes_no']) {
					$q = "SELECT * FROM tblcancerquestion WHERE parent_field_name=\"" . $question['field_name'] . "\"";
					$questions_childrens = $db->query($q);
					foreach($questions_childrens as $questions_children) {
						if (isset($_POST[$questions_children['field_name']]) && ($_POST[$questions_children['field_name']] == "Yes")) {	
							$child_answer_yes = 1;
						}
					}
					if ($child_answer_yes) {
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

	
	if ($cancer_found_brca) {
		array_push($qualification_text, "BRCA-Related Breast and/or Ovarian Cancer Syndrome");
	}
	
	if ($cancer_found_lynch) {
		array_push($qualification_text, "High Risk Colon Cancer Syndromes (Lynch syndrome)");
	}
	
	
	$q = "SELECT * FROM tbl_ss_qualifyans WHERE cancer_personal IS NOT NULL AND Guid_qualify=" . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "'";
	
	$personal = $db->query($q);
	
	$count_personal = $personal;

	//gather all first degree relatives entered
	
	$first_deg_rel = array();

	$q = "SELECT a.relative FROM tblfirstdegrel tr LEFT JOIN tbl_ss_qualifyans a ON a.relative = tr.value WHERE Guid_qualify=" . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "' AND a.relative IS NOT NULL";
	
	$relatives = $db->query($q);
	
	if ($relatives) {
		foreach($relatives as $relative) {
			array_push($first_deg_rel, $relative['relative']);
		}
	}

	$count_first_deg_rel = array_count_values($first_deg_rel);	
	
	//gather all second degree relatives entered
	
	$second_deg_rel = array();

	$q = "SELECT a.relative FROM tblseconddegrel tr LEFT JOIN tbl_ss_qualifyans a ON a.relative = tr.value WHERE Guid_qualify=" . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "' AND a.relative IS NOT NULL";
	
	$relatives = $db->query($q);
	
	if ($relatives) {
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

	$q = "SELECT relative FROM tblthirddegrel tr LEFT JOIN tbl_ss_qualifyans a ON a.relative = tr.value WHERE Guid_qualify=" . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "'";
	
	$relatives = $db->query($q);
	
	if ($relatives) {
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

	$q = "SELECT * FROM tbl_ss_qualify WHERE Guid_qualify = " . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "'";
	
	//$qualify = $result->fetch_assoc();
	$qualify = $db->row($q);
	$insurance = $qualify["insurance"];
	
	if (($qualify["insurance"] != "Aetna") && ($qualify["insurance"] != "Medicare")) {
		$insurance = "NCCN";
	}
		
	$yesorno = strtolower($qualify['qualified']);
	
	echo '
	<section class="report">
	<header id="app_head">
				<section id="app_title">';
				
	if ($qualify['company'] == "gen") {
		echo '<figure class="app_logo_geneveda"><img src="' . $directory . '/images/logo_geneveda.png" alt="Geneveda"></figure>';
	} else {
		echo '<figure id="app_logo_mdl"><img src="https://www.mdlab.com/dev2/wp-content/themes/oceanreef/images/logo.png" alt="MDL"></figure>';
	}

echo '
					<span class="divide"></span>';
					
	if (isset($qualify['account_number'])) {		
		$query = "SELECT * FROM tblaccount WHERE account  = '" . $qualify['account_number'] . "'";
		$account = $db->row($query);
		
		if ($account) {				
			if (strlen($account['logo'])) {
				echo '<figure id="app_logo_brccare"><img src="https://www.mdlab.com/dev/images/practice/' . $account['logo'] . '" alt=""></figure>';
			} else {
				echo '<figure id="app_logo_brccare"><img class="brccare" src="' . $directory . '/assets/images/logo_brcacare.png" alt="BRCAcare"></figure>';
			}
		} else {
			echo '<figure id="app_logo_brccare"><img class="brccare" src="' . $directory . '/assets/images/logo_brcacare.png" alt="BRCAcare"></figure>';
		}
	} else {
			echo '<figure id="app_logo_brccare"><img class="brccare" src="' . $directory . '/assets/images/logo_brcacare.png" alt="BRCAcare"></figure>';
	}

echo '				
				    <h2 class="sfont">Should I Be Screened?</h2>					
				</section>
				
				<section class="unknown_req">
				    <h4><span>Please fill in all unknown information</span></h4>
				</section>
				
				<button type="button" id="exit_app" name="exit_app" class="toggle" data-on="#logout_app_modal"><strong>Exit</strong></button>';


echo '
				
			    <h1><span><?php echo $title; ?></span></h1>
			</header>';
echo '
	<section class="q_result ' . $yesorno . ' wrapper">';	

	if ($qualify["qualified"] == "Yes") {
echo '
		<input type="hidden" name="qualified" value="Yes">
		<h2 class="iconP">Yes</h2>			
		<div class="q_result_title">
			<p>You are likely to meet the ' . $insurance . ' clinical guidelines and testing is recommended for:</p>';
	} else {
echo '
		<input type="hidden" name="qualified" value="No">
		<h2 class="iconP">No</h2>			
		<div class="q_result_title">
			<p>You do not meet the ' . $insurance . ' clinical guidelines for:</p>';
	}
	for ($i=0; $i < count($qualification_text); $i++) {
echo '			
			<strong>' . $qualification_text[$i]  . '</strong>';
	}
echo '
		</div>
	</section>
	
                <div class="cols wrapper">
					<section class="pedigree_info">
						<button type="button" class="close_modal toggle" data-on=".pedigree_info"></button>
						
						<ul class="q_summary">';

	$guideline = array();
	$q = "SELECT * FROM tbl_ss_qualify WHERE Guid_qualify=" . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "' AND gene_mutation = \"Yes\"";
	
	$qualify_gene_mutation = $db->row($q);
	if ($qualify_gene_mutation) {				
		$sql = "SELECT * FROM tbloutlier WHERE gene_mutation=\"Yes\"";
		
		if ($qualify_gene_mutation['insurance'] == "Aetna") {
			$sql .= " AND insurance=\"" . $qualify_gene_mutation['insurance'] . "\" AND gender=\"" . $qualify_gene_mutation['gender'] . "\"";
		} else {
			$sql .= " AND insurance=\"NCCN\"";
		}
		
		$outlier = $db->row($sql);
		
		$q = "SELECT * FROM tbl_ss_qualifygene WHERE Guid_qualify=" . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "'";
		$family_genes = $db->query($q);
		
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
				
			$q = "SELECT * FROM tblfirstdegrel WHERE value=\"" . $family_gene['gene_relation'] . "\"";
			$relatives = $db->query($q);
			if ($relatives) {
				array_push($first_deg_rel, $family_gene['gene_relation']);
				if (isset($count_first_deg_rel[$family_gene['gene_relation']])) {
					$count_first_deg_rel[$family_gene['gene_relation']]++;					
				}
			} else {
				$q = "SELECT * FROM tblseconddegrel WHERE value=\"" . $family_gene['gene_relation'] . "\"";			
				$relatives = $db->query($q);
				if ($relatives) {
					if (($family_gene['gene_relation'] == "Maternal Grandfather") || ($family_gene['gene_relation'] == "Maternal Grandmother") || ($family_gene['gene_relation'] == "Paternal Grandfather") || ($family_gene['gene_relation'] == "Paternal Grandmother")) {
						if (!in_array($family_gene['gene_relation'], $second_deg_rel)) {
							array_push($second_deg_rel, $family_gene['gene_relation']);
							if (isset($count_second_deg_rel[$family_gene['gene_relation']])) {
								$count_second_deg_rel[$family_gene['gene_relation']]++;
							}
						}
					} else {
						array_push($second_deg_rel, $family_gene['gene_relation']);
						if (isset($count_second_deg_rel[$family_gene['gene_relation']])) {
							$count_second_deg_rel[$family_gene['gene_relation']]++;
						}
					}									
				} else {
					$q = "SELECT * FROM tblthirddegrel WHERE value=\"" . $family_gene['gene_relation'] . "\"";				
					$relatives = $db->query($q);
					if ($relatives) {
						if (($family_gene['gene_relation'] == "Maternal Great-Grandfather") || ($family_gene['gene_relation'] == "Maternal Great-Grandmother") || ($family_gene['gene_relation'] == "Paternal Great-Grandfather") || ($family_gene['gene_relation'] == "Paternal Great-Grandmother")) {					
							if (!in_array($family_gene['gene_relation'], $second_deg_rel)) {
								array_push($third_deg_rel, $family_gene['gene_relation']);
								if (isset($count_third_deg_rel[$family_gene['gene_relation']])) {
									$count_third_deg_rel[$family_gene['gene_relation']]++;
								}
							}
						} else {
							array_push($third_deg_rel, $family_gene['gene_relation']);
							if (isset($count_third_deg_rel[$family_gene['gene_relation']])) {
								$count_third_deg_rel[$family_gene['gene_relation']]++;
							}
						}															
					}	
				}
			}			 			
		}
	
		foreach ($display_gene_relation as $rel_id => $gene_relation) {
echo '
							<li id="' . $rel_id . '">
								<h3>My <span class="maincol">' . $gene_relation . '</span></h3>								
								<div class="pInfo_type">
									<strong>Gene Mutation' . ((count($display_gene[$rel_id]) > 1) ? "s" : "") . '</strong>
									<p>' . implode(", ", $display_gene[$rel_id]) . '</p>
								</div>
								<div class="pInfo_type">
									<strong>Guideline Met</strong>';

			foreach ($gene_guideline_met[$rel_id] as $key => $guide) {
echo '
									<p>' . $guide . '</p>';

			}
echo '
								</div>
							</li>';

			unset($display_guideline);
		}
	}
	$me_cancer_personal = array();
	$me_age_personal = array();
	$me_guideline_met = array();
	
	$q = "SELECT a.age_personal, a.cancer_personal, o.guideline_met FROM tbl_ss_qualifyans a LEFT JOIN tbloutlier o ON a.Guid_outlier = o.Guid_outlier WHERE Guid_qualify=" . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "' AND a.Guid_outlier IS NOT NULL";
	
	$result = $db->query($q);
	
	if ($result) {
		foreach($result as $outlier) {
			if (!in_array($outlier['guideline_met'], $guideline)) {
				array_push($guideline, $outlier['guideline_met']);
			}
			array_push($me_cancer_personal, $outlier['cancer_personal']);
			array_push($me_age_personal, $outlier['age_personal']);
			array_push($me_guideline_met, $outlier['guideline_met']);			
		}
	}
	
	$q = "SELECT a.age_personal, a.cancer_personal, q.guideline_met FROM tbl_ss_qualifyans a LEFT JOIN tblcancerquestion q ON a.Guid_question = q.Guid_question WHERE Guid_qualify=" . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "' AND cancer_personal IS NOT NULL AND a.Guid_outlier IS NULL ORDER BY q.sort_order";	
	
	//$num_personals = mysqli_num_rows($personals);
	$personals = $db->query($q);
	
	if ($personals) {	
		foreach($personals as $personal) {			
			if (strlen($personal['guideline_met'])) {
				if (!in_array($personal['guideline_met'], $me_guideline_met)) {
					array_push($me_guideline_met, $personal['guideline_met']);
				}
			}
			array_push($me_cancer_personal, $personal['cancer_personal']);
			array_push($me_age_personal, $personal['age_personal']);
		}
		
		$q = "SELECT q.guideline_met FROM tbl_ss_qualifyans a LEFT JOIN tblcancerquestion q ON a.Guid_question = q.Guid_question WHERE Guid_qualify=" . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "' AND a.relative IS NOT NULL AND a.Guid_outlier IS NULL GROUP BY a.Guid_question";
		$result = $db->query($q);
		if ($result) {
			foreach($result as $relative) {					
				if (!in_array($relative['guideline_met'], $me_guideline_met)) {
					array_push($me_guideline_met, $relative['guideline_met']);
				}
			}
		}
	}
	
	$q = "SELECT * FROM tblgmcancer WHERE Guid_qualify=" . $_POST['selected_questionnaire'];
	$result = $db->query($q);
	if ($result) {
		foreach($result as $cancer) {
			array_push($me_cancer_personal, $cancer['cancer']);
			array_push($me_age_personal, $cancer['cancer_age']);
		}
	}
	
	if (count($me_cancer_personal)) {		
echo '
							<li id="myself">
								<h3>Me</span></h3>
								<div class="pInfo_type">
									<strong>Cancer Diagnosis</strong>
										<ul class="cancer_history">';

		for ($i=0; $i < count($me_cancer_personal); $i++) {
echo '
										<li>' . $me_cancer_personal[$i] . (strlen($me_age_personal[$i]) ? " at age " . $me_age_personal[$i] : "") . '</li>';

		}
echo '
									</ul>
								</div>
								
								<div class="pInfo_type">
									<strong>Guideline Met</strong>';

		for ($i=0; $i < count($me_guideline_met); $i++) {
echo '
									<p>' . $me_guideline_met[$i] . '</p>';

		}
echo '
								</div>
							</li>';

	} else {
echo '
							<li id="myself" class="no_cancer_history">
								<h3>Me</span></h3>
								<div class="pInfo_type">
									<strong>Cancer Diagnosis</strong>
									<p>No Cancer History</p>
								</div>
							</li>';

	}
	$q = "SELECT a.age_relative, a.cancer_type, a.additional_cancer_type, a.relative, a.deceased, q.guideline_met, q.additional_question, q.special_rule FROM tbl_ss_qualifyans a LEFT JOIN tblcancerquestion q ON a.Guid_question = q.Guid_question WHERE Guid_qualify=" . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "' AND a.relative IS NOT NULL AND a.Guid_outlier IS NULL ORDER BY q.sort_order";
	$relatives = $db->query($q);
	$deceased_relative = array();
	
	$relation=array();
	$gdmet=array();
	$cancer_detail=array();
	
	if ($relatives) {
		foreach($relatives as $relative) {			
			if (($relative['relative'] == "Mother") || ($relative['relative'] == "Father") || ($relative['relative'] == "Maternal Grandfather") || ($relative['relative'] == "Maternal Grandmother") || ($relative['relative'] == "Paternal Grandfather") || ($relative['relative'] == "Paternal Grandmother") || ($relative['relative'] == "Maternal Great-Grandfather") || ($relative['relative'] == "Maternal Great-Grandmother") || ($relative['relative'] == "Paternal Great-Grandfather") || ($relative['relative'] == "Paternal Great-Grandmother")) {		
				$count[$relative['relative']] = 1; 
			} else {
				if (isset($count[$relative['relative']])) {
					$count[$relative['relative']] += 1;
				}
			}
			$id = 0;
			if (isset($count[$relative['relative']])) {
				$id = strtolower(str_replace(" ", "_", $relative['relative'])) . $count[$relative['relative']];
			}
			if (!isset($relation[$id])) {								
				$relation[$id] = $relative['relative'];
			}
			if ($relative['deceased']) {
				array_push($deceased_relative, $id);
			}
			if ((strlen($relative['guideline_met'])) && (isset($guideline_met[$id]))) {
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
			//if (strlen($relative['additional_cancer_type'])) {				
			//	$additional_cancer = $relative['additional_cancer_type'];				
			//}
			
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
echo '
							<li id="' . $rel_id . '">
								<h3>My <span class="maincol">' . $rel . '</span></h3>
								<div class="pInfo_type">
									<strong>Cancer Diagnosis</strong>
										<ul class="cancer_history">';

				foreach ($cancer_detail[$rel_id] as $key => $cancer_det) {
echo '
											<li>' . $cancer_det . '</li>';

				}				
echo '
										</ul>
								</div>';
		
				if (isset($additional_cancer)) {
echo '
								<div class="pInfo_type">
									<strong>Additional Cancer Diagnosis</strong>
									<p>' . $additional_cancer;

					if (strlen($additional_age)) {
echo '

									at age ' . $additional_age;

					}
echo '
									</p>
								</div>';

				}				
echo '
								<div class="pInfo_type">
									<strong>Guideline Met</strong>';
				if (isset($guideline_met[$rel_id])) {
					foreach ($guideline_met[$rel_id] as $key => $guide) {
echo '
									<p>' . $guide . '</p>';

					}
				}
echo '
								</div>
							</li>';
		}
	}
	$count = array();
	
	$q = "SELECT * FROM  tbloutlier WHERE field_name IS NULL";
	$outliers = $db->query($q);
	if ($outliers) {
		foreach($outliers as $outlier) {
			$sql = "SELECT * FROM tbl_ss_qualify WHERE Guid_qualify=" . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "'";
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
			
			$sql .= " AND gene_relation IS NOT NULL ";
			$qualify_outlier = $db->query($sql);
			if ($qualify_outlier) {
				
				
				$count[$qualify_outlier['gene_relation']] += 1;
				
				$q = "SELECT * FROM tblfirstdegrel WHERE value=\"" . $qualify_outlier['gene_relation'] . "\"";
				$result = $db->query($q);
				if ($result) {
					array_push($first_deg_rel, $qualify_outlier['gene_relation']);
					
					if (isset($count_first_deg_rel[$qualify_outlier['gene_relation']])) {
						$count_first_deg_rel[$qualify_outlier['gene_relation']] += 1;
					}
				}				
			}			
		}
	}
	
	$q = "SELECT q.guideline_met FROM tbl_ss_qualifyans a LEFT JOIN tblcancerquestion q ON a.Guid_question = q.Guid_question WHERE Guid_qualify=" . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "'";
	$results = $db->query($q);
	foreach ($results as $result) {
		if ((strlen($result['guideline_met'])) && (!in_array($result['guideline_met'], $guideline))) {
			array_push($guideline, $result['guideline_met']);				
		}
	}
	
	 
	$q = "SELECT * FROM tbl_ss_qualifypers WHERE Guid_qualify = " . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "' AND cancer_type =\"No Cancer/None of the Above\"";
	$no_p_cancer_history = $db->query($q);
	
	$q = "SELECT * FROM tbl_ss_qualifyfam WHERE Guid_qualify = " . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "' AND cancer_type =\"No Cancer/None of the Above\"";
	$no_r_cancer_history = $db->query($q);
	

	
	if ((!count($guideline)) || (($no_p_cancer_history) && ($no_r_cancer_history))) {
		array_push($guideline, "None");
	}
echo '
						</ul>
						
                        <section id="guideline_met">
			                <h3>Guideline(s) Met</h3>';
	
	for ($i=0; $i < count($guideline); $i++) {
echo '
			                <p>' . $guideline[$i] . '</p>';

	}
echo '
		                </section>
					</section>
					
					<div id="pedigree" class="tree">
						<ul>';

	// Great Grand Parents
	if ((isset($count_third_deg_rel['Maternal Great-Grandfather'])) || (isset($count_third_deg_rel['Maternal Great-Grandmother'])) || (isset($count_third_deg_rel['Maternal Great-Uncle'])) || (isset($count_third_deg_rel['Maternal Great-Aunt'])) ||(isset( $count_third_deg_rel['Paternal Great-Grandfather'])) || (isset($count_third_deg_rel['Paternal Great-Grandmother'])) || (isset($count_third_deg_rel['Paternal Great-Uncle'])) || (isset($count_third_deg_rel['Paternal Great-Aunt']))) {
echo '
							<li class="great_relatives">';

		$relative = array();
		if (isset($count_third_deg_rel['Maternal Great-Grandfather'])) $relative['Maternal Great-Grandfather'] = "male";
		if (isset($count_third_deg_rel['Maternal Great-Grandmother'])) $relative['Maternal Great-Grandmother'] = "female";
		if (isset($count_third_deg_rel['Maternal Great-Uncle'])) $relative['Maternal Great-Uncle'] = "male";
		if (isset($count_third_deg_rel['Maternal Great-Aunt'])) $relative['Maternal Great-Aunt'] = "female";
		if (isset($count_third_deg_rel['Paternal Great-Grandfather'])) $relative['Paternal Great-Grandfather'] = "male";
		if (isset($count_third_deg_rel['Paternal Great-Grandmother'])) $relative['Paternal Great-Grandmother'] = "female";
		if (isset($count_third_deg_rel['Paternal Great-Uncle'])) $relative['Paternal Great-Uncle'] = "male";
		if (isset($count_third_deg_rel['Paternal Great-Aunt'])) $relative['Paternal Great-Aunt'] = "female";
		
		foreach ($relative as $relation => $gender) {
			generate_grandparent_html($count_third_deg_rel[$relation], $relation, $gender, $relation, $count_third_deg_rel[$relation], $deceased_relative);
		}
echo '						    
							</li>';

	}
echo '							
						    <li>';

	// Paternal Grand Parents
	$paternal_granparents_needed = 0;
	if ((isset($count_second_deg_rel['Paternal Grandfather'])) || (isset($count_second_deg_rel['Paternal Grandmother'])) || (isset($count_second_deg_rel['Paternal Uncle'])) ||(isset( $count_second_deg_rel['Paternal Aunt']))) {
		$paternal_granparents_needed = 1;
	}
	if ($paternal_granparents_needed) {
echo '
								<div class="parents">';

		if (!isset($count_second_deg_rel['Paternal Grandfather'])) {
			$count = 1;
		} else {
			$count = $count_second_deg_rel['Paternal Grandfather'];
		}
		if (isset($count_second_deg_rel['Paternal Grandfather'])) {
		generate_grandparent_html($count, "Grandfather", "male", "Paternal Grandfather", $count_second_deg_rel['Paternal Grandfather'], $deceased_relative);
		}
		if (isset($count_second_deg_rel['Paternal Grandmother'])) {
			$count = $count_second_deg_rel['Paternal Grandmother'];
		}
		if (!isset($count_second_deg_rel['Paternal Grandmother'])) {			
			$count = 1;
		}
		if (isset($count_second_deg_rel['Paternal Grandmother'])) {
			generate_grandparent_html($count, "Grandmother", "female", "Paternal Grandmother", $count_second_deg_rel['Paternal Grandmother'], $deceased_relative);		
		}
echo '
								</div>';
	
	}
	// Maternal Grand Parents
	$maternal_granparents_needed = 0;
	
	if ((isset($count_second_deg_rel['Maternal Grandfather'])) || (isset($count_second_deg_rel['Maternal Grandmother'])) || (isset($count_second_deg_rel['Maternal Uncle'])) || (isset($count_second_deg_rel['Maternal Aunt']))) {
		$maternal_granparents_needed = 1;
	}
	if ($maternal_granparents_needed) {
echo '
								<div class="parents">';

		
		if (!isset($count_second_deg_rel['Maternal Grandfather'])) {
			$count = 1;
		} else {
			$count = $count_second_deg_rel['Maternal Grandfather'];
		}
		if (isset($count_second_deg_rel['Maternal Grandfather'])) {
			generate_grandparent_html($count, "Grandfather", "male", "Maternal Grandfather", $count_second_deg_rel['Maternal Grandfather'], $deceased_relative);
		}
		
		if (!isset($count_second_deg_rel['Maternal Grandmother'])) {
			$count = 1;
		} else {
			$count = $count_second_deg_rel['Maternal Grandmother'];
		}
		if (isset($count_second_deg_rel['Maternal Grandmother'])) {
			generate_grandparent_html($count, "Grandmother", "female", "Maternal Grandmother", $count_second_deg_rel['Maternal Grandmother'], $deceased_relative);		
		}
echo '
								</div>';
	}
	if ($paternal_granparents_needed || $maternal_granparents_needed) {
echo '
						        <ul class="child">';
	}
	// Paternal Uncle and Aunt
	if ((isset($count_second_deg_rel['Paternal Uncle'])) || (isset($count_second_deg_rel['Paternal Aunt']))) {		
		if (isset($count_second_deg_rel['Paternal Uncle'])) {
			generate_html($count_second_deg_rel['Paternal Uncle'], "Paternal Uncle", "male", $deceased_relative);
		}
		if (isset($count_second_deg_rel['Paternal Aunt'])) {
			generate_html($count_second_deg_rel['Paternal Aunt'], "Paternal Aunt", "female", $deceased_relative);
		}
	}
	if ($paternal_granparents_needed || $maternal_granparents_needed) {
echo '								
						            <li class="direct">';
	}
	// Parents
	if ((isset($count_first_deg_rel['Mother'])) || (isset($count_first_deg_rel['Father'])) || (isset($count_first_deg_rel['Sister'])) || (isset($count_first_deg_rel['Brother'])) || (isset($count_second_deg_rel['Maternal Half-Sister'])) || (isset($count_second_deg_rel['Paternal Half-Sister'])) || (isset($count_second_deg_rel['Maternal Half-Brother'])) || (isset($count_second_deg_rel['Paternal Half-Brother'])) || $paternal_granparents_needed || $maternal_granparents_needed) {
		$blood = "";
		
		if ((isset($count_second_deg_rel['Paternal Grandfather'])) && $count_second_deg_rel['Paternal Grandfather']) {
			$blood = " blood";
		} elseif ((isset($count_second_deg_rel['Paternal Grandmother'])) && $count_second_deg_rel['Paternal Grandmother']) {
			$blood = " blood";
		} elseif ((isset($count_second_deg_rel['Paternal Uncle'])) && $count_second_deg_rel['Paternal Uncle']) {
			$blood = " blood";
		} elseif ((isset($count_second_deg_rel['Paternal Aunt'])) && $count_second_deg_rel['Paternal Aunt']) {
			$blood = " blood";
		}
		$spouse = "";
		if ((!$paternal_granparents_needed) && ($maternal_granparents_needed)) {
			$spouse = " spouse";
		}
echo '
								        <div class="parents">
								            <button type="button" class="person' . ((isset($count_first_deg_rel['Father']) && $count_first_deg_rel['Father']) ? " ch" : " nch") . $blood . $spouse . ((in_array("father1", $deceased_relative)) ? " deceased" : ""). '"  data-qs="#father1">
									            <span class="gender male">';

		if (isset($count_first_deg_rel['Father']) && $count_first_deg_rel['Father']) { echo "<img src=\"$directory/assets/images/icon_ch.png\" alt=\"Cancer History\">"; }
		else {echo "<img src=\"$directory/assets/images/icon_nch_blk.png\" alt=\"No History\">";}
echo '
												</span>
												<strong>Father</strong>
								            </button>';

		$blood = "";
		if ((isset($count_second_deg_rel['Maternal Grandfather'])) && $count_second_deg_rel['Maternal Grandfather']) {
			$blood = " blood";
		} elseif ((isset($count_second_deg_rel['Maternal Grandmother'])) && $count_second_deg_rel['Maternal Grandmother']) {
			$blood = " blood";
		} elseif ((isset($count_second_deg_rel['Maternal Uncle'])) && $count_second_deg_rel['Maternal Uncle']) {
			$blood = " blood";
		} elseif ((isset($count_second_deg_rel['Maternal Aunt'])) && $count_second_deg_rel['Maternal Aunt']) {
			$blood = " blood";
		}
		
		$spouse = "";
		if (($paternal_granparents_needed) && (!$maternal_granparents_needed)) {
			$spouse = " spouse";
		}
echo '
											<button type="button" class="person' . ((isset($count_first_deg_rel['Mother']) && $count_first_deg_rel['Mother']) ? " ch" : " nch") . $blood . $spouse . ((in_array("mother1", $deceased_relative)) ? " deceased" : "") . '" data-qs="#mother1">
									            <span class="gender female">';

		if (isset($count_first_deg_rel['Mother']) && $count_first_deg_rel['Mother']) {
echo '
												    <img src="' . $directory . '/assets/images/icon_ch.png" alt="Cancer History">';
		} else {
echo '
													<img src="' . $directory . '/assets/images/icon_nch_blk.png" alt="No History">';
		}
	
echo '
												</span>
									            <strong>Mother</strong>
								            </button>
								        </div>';
	}
	// Self
	if ((isset($count_first_deg_rel['Mother'])) ||(isset( $count_first_deg_rel['Father'])) || (isset($count_first_deg_rel['Sister'])) || (isset($count_first_deg_rel['Brother'])) || (isset($count_second_deg_rel['Maternal Half-Sister'])) || (isset($count_second_deg_rel['Paternal Half-Sister'])) || (isset($count_second_deg_rel['Maternal Half-Brother'])) || (isset($count_second_deg_rel['Paternal Half-Brother'])) || $paternal_granparents_needed || $maternal_granparents_needed) {
echo '
								        <ul class="child">';
	}

	if (!isset($count_first_deg_rel['Sister'])) {
		$count_first_deg_rel['Sister'] = 0;
	}
	generate_html($count_first_deg_rel['Sister'], "Sister", "female", $deceased_relative);
	if (!isset($count_second_deg_rel['Maternal Half-Sister'])) {
		$count_second_deg_rel['Maternal Half-Sister'] = 0;
	}
	generate_html($count_second_deg_rel['Maternal Half-Sister'], "Maternal Half-Sister", "female", $deceased_relative);
	if (!isset($count_second_deg_rel['Paternal Half-Sister'])) {
		$count_second_deg_rel['Paternal Half-Sister'] = 0;
	}
	generate_html($count_second_deg_rel['Paternal Half-Sister'], "Paternal Half-Sister", "female", $deceased_relative);
	$blood = "";
	if ((isset($count_first_deg_rel['Mother'])) || (isset($count_first_deg_rel['Father'])) || (isset($count_first_deg_rel['Sister'])) || (isset($count_first_deg_rel['Brother'])) || (isset($count_second_deg_rel['Maternal Half-Sister'])) || (isset($count_second_deg_rel['Paternal Half-Sister'])) || (isset($count_second_deg_rel['Maternal Half-Brother'])) || (isset($count_second_deg_rel['Paternal Half-Brother'])) || $paternal_granparents_needed || $maternal_granparents_needed) {
		$blood = " blood";	
echo '								           
								            <li>';
	}
	if ((isset($count_first_deg_rel['Daughter'])) || (isset($count_first_deg_rel['Son']))) {
		if ($qualify['gender'] == "Male") {
			$spouse_gender = "female";
		} else {
			$spouse_gender = "male";
		}
echo '
										       <div class="parents">';
	}
	if ((isset($count_first_deg_rel['Daughter'])) || (isset($count_first_deg_rel['Son']))) {
		if ($qualify['gender'] == "Female") {
echo '
													<button type="button" class="person spouse nch">
											            <span class="gender '. $spouse_gender . '">
															<img src="' . $directory . '/assets/images/icon_nch_blk.png" alt="No History">
														</span>
											            <strong></strong>
										            </button>';
		}
	}
echo '
													<button type="button" class="person' . (($count_personal) ? " ch" : "") . $blood .' me" data-qs="#myself">
											            <span class="gender ' . (strtolower($qualify['gender'])) . '">';
	if ($count_personal) { 
		echo "<img src=\"$directory/assets/images/icon_ch.png\" alt=\"Cancer History\">"; 
	}		
echo '
														</span>
											            <strong>Me</strong>
										            </button>';
	if ((isset($count_first_deg_rel['Daughter'])) || (isset($count_first_deg_rel['Son']))) {
		if ($qualify['gender'] == "Male") {
echo '
										            <button type="button" class="person spouse nch">
											            <span class="gender ' . $spouse_gender . '">
															<img src="' . $directory . '/assets/images/icon_nch_blk.png" alt="No History">
														</span>
											            <strong></strong>
										            </button>';
		}
		if ((isset($count_first_deg_rel['Daughter'])) || (isset($count_first_deg_rel['Son']))) {
echo '
										        </div>';
		}
echo '
										        <ul class="child">';
		// Son and Daughter
		generate_html($count_first_deg_rel['Daughter'], "Daughter", "female", $deceased_relative);
		generate_html($count_first_deg_rel['Son'], "Son", "male", $deceased_relative);
echo '
										        </ul>';
	}
echo '
									        </li>';
	// Brother
	if (!isset($count_first_deg_rel['Brother'])) {
		$count_first_deg_rel['Brother'] = 0;
	}
	generate_html($count_first_deg_rel['Brother'], "Brother", "male", $deceased_relative);
	if (!isset($count_second_deg_rel['Maternal Half-Brother'])) {
		$count_second_deg_rel['Maternal Half-Brother'] = 0;
	}
	generate_html($count_second_deg_rel['Maternal Half-Brother'], "Maternal Half-Brother", "male", $deceased_relative);
	if (!isset($count_second_deg_rel['Paternal Half-Brother'])) {
		$count_second_deg_rel['Paternal Half-Brother'] = 0;
	}
	generate_html($count_second_deg_rel['Paternal Half-Brother'], "Paternal Half-Brother", "male", $deceased_relative);
echo '											
								        </ul>
							        </li>';
	// Maternal Uncle and Aunt
	if ((isset($count_second_deg_rel['Maternal Uncle'])) || (isset($count_second_deg_rel['Maternal Aunt']))) {		
		if (isset($count_second_deg_rel['Maternal Uncle'])) {		
			generate_html($count_second_deg_rel['Maternal Uncle'], "Maternal Uncle", "male", $deceased_relative);
		}
		if (isset($count_second_deg_rel['Maternal Aunt'])) {
			generate_html($count_second_deg_rel['Maternal Aunt'], "Maternal Aunt", "female", $deceased_relative);
		}
	}
echo '
						        </ul>
							</li>';
	// Niece, Nephew, Grandosn, Granddaughter, Maternal Great-Granddaughter, Maternal Great-Grandson, Paternal Great-Granddaughter, Paternal Great-Grandson, Male First-cousin,Female First-cousin
	if ((isset($count_second_deg_rel['Niece'])) || (isset($count_second_deg_rel['Nephew'])) || (isset($count_second_deg_rel['Grandson'])) || (isset($count_second_deg_rel['Granddaughter'])) || (isset($count_third_deg_rel['Great-Granddaughter'])) || (isset($count_third_deg_rel['Great-Grandson'])) || (isset($count_third_deg_rel['Male First-cousin'])) || (isset($count_third_deg_rel['Female First-cousin']))) {
		$relative = array();
		if ($count_second_deg_rel['Niece']) $relative['Niece'] = "female";
		if ($count_second_deg_rel['Nephew']) $relative['Nephew'] = "male";
		if ($count_second_deg_rel['Granddaughter']) $relative['Granddaughter'] = "female";
		if ($count_second_deg_rel['Grandson']) $relative['Grandson'] = "male";	
		if ($count_third_deg_rel['Great-Granddaughter']) $relative['Great-Granddaughter'] = "female";
		if ($count_third_deg_rel['Great-Grandson']) $relative['Great-Grandson'] = "male";		
		if ($count_third_deg_rel['Male First-cousin']) $relative['Male First-cousin'] = "male";
		if ($count_third_deg_rel['Female First-cousin']) $relative['Female First-cousin'] = "female";
echo '
							<li class="great_relatives">';
		foreach ($relative as $relation => $gender) {
			if (in_array($relation, array("Niece", "Nephew", "Granddaughter", "Grandson"))) {
				$count = $count_second_deg_rel[$relation];				
			} else {
				$count = $count_third_deg_rel[$relation];
			}
				
			generate_grandparent_html($count, $relation, $gender, $relation, $count, $deceased_relative);
		}
echo '
							</li>';
	}
	$q = "SELECT * FROM tblpatient WHERE Guid_user = " . $qualify['Guid_user'];
	$patient = $db->row($q);
	

echo '
						</ul>
					</div>
					
					<div class="pKey">
						<figure class="guideline_met"><img src="' . $directory . '/assets/images/icon_ch_blk.png" alt="Cancer History">: Cancer History Provided</figure>
						<figure class="no_history"><img src="' . $directory . '/assets/images/icon_nch_blk.png" alt="No History">: No Cancer History Provided</figure>
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
						    <span class="underline">' . $patient['firstname'] . " " . $patient['lastname']. '</span>
					    </section>
					
					    <section>
					        <h4>Date Completed:</h4>
						   <span class="underline">' . date("n/j/Y", strtotime($qualify['Date_created'])) . '</span>
					    </section>
					</div>
					
					<div class="line two">
						<section>						
						    <h4>Physician Name:</h4>';
	
	$physician_name = "";
	if ($qualify['provider_id'] == "Other") {
		$physician_name = $qualify['other_provider'];	
	} elseif (strlen($qualify['provider_id'])) {
		$q = "SELECT first_name, last_name, title FROM tblprovider WHERE Guid_provider = " . $qualify['provider_id'];	
		$result = $db->row($q);
		$physician_name = $result['first_name'] . " " . $result['last_name'] . ", " . $result['title'];
	}	
			
	echo '
							<span class="underline">' . $physician_name . '</span>
						</section>
						
					    <section>
						    <h4>Signature/Date:</h4>
							<span class="underline"></span>
						</section>
					</div>
                </div>
				
	<div id="q_disclaimer" class="wrapper">
		<p id="ped_disc">This pedigree may not be a complete representation and requires more information concerning your relatives.  It is recommended you consult with a health care professional to complete it.</p>';
	if ($qualify["ashkenazi"] == "Yes") {
		$q = "SELECT * FROM tbl_ss_qualifyans WHERE Guid_qualify = " . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "' AND (cancer_personal IN(\"Breast\", \"Ovarian\", \"Pancreatic\", \"Prostate\") OR cancer_type IN(\"Breast\", \"Ovarian\", \"Pancreatic\", \"Prostate\"))";
		$result = $db->row($q);
		
		if($result) {
echo '
		<p>A screening panel for three founder mutations common in the Ashkenazi Jewish population is medically necessary first when criteria are met. If founder mutation testing is negative, full gene sequencing of BRCA1 and BRCA2 genes (reflex testing) is then considered medically necessary only if the member meets any of the criteria described above for comprehensive testing.</p>';
		}
	}
	$q = "SELECT * FROM tbl_ss_qualifyans WHERE Guid_qualify = " . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "' AND gene_mutation = \"Yes\"";
	$total_gene_mutation = $db->query($q);	
	
	$q = "SELECT * FROM tbl_ss_qualify WHERE Guid_qualify = " . $_POST['selected_questionnaire'] . " AND Date_created = '" . $_POST['selected_date'] . "' AND gene_mutation = \"Yes\"";
	$total_gene_mutation = $db->query($q);

	
	if ($total_gene_mutation) {
echo '
		<p>A copy of the affected family member\'s test results are required to verify the family mutation indicated above.</p>';
	}
echo '
		<p>';
    if (($qualify["insurance"] != "Aetna") && ($qualify["insurance"] != "Medicare")) {
echo '
		This assessment is based on the National Comprehensive Cancer Network (NCCN) guidelines (www.nccn.org) for BRCA-Related Breast and/or Ovarian Cancer syndrome (version 1.2019) and for High-Risk Colorectal Cancer Syndromes (version 1.2018).';
    }
    if (($qualify["insurance"] == "Aetna")) {
echo '
		Since you have ' . $qualify["insurance"] . ' insurance, this assessment was based on their specific medical policy guidelines (Aetna Medical Clinical Policy Number 0227 - BRCA Testing, last reviewed: 1/17/2018) that cover testing for hereditary breast and/or ovarian cancer syndromes. ';
    } else if (($qualify["insurance"] == "Medicare")) { 
echo '
		Since you have ' . $qualify["insurance"]. ' insurance, this assessment was based on their specific medical policy guidelines (Local Coverage Determination: BRCA1 and BRCA2 Genetic Testing (L36715),revision date: 01/01/2018) that cover testing for hereditary breast and/or ovarian cancer syndromes.';
    } 	
echo '
		To learn more about genetic testing, please speak with your genetic counselor or other healthcare provider. You can locate a genetic counselor through NSGC.org.</p>

		<p>Medical Diagnostic Laboratories\' patient hereditary cancer questionnaire only determines your eligibility for certain genetic testing. Our testing only covers certain hereditary cancer syndromes such as hereditary breast and/or ovarian cancer (HBOC) and Lynch syndrome.  To determine which test(s) should be performed, you and your healthcare provider or genetic counselor should determine this based on your personal and family history. Whenever possible, it is recommended that genetic testing in a family start with a member in the family who has had cancer.</p>
	</div>
	</section>';

if ($qualify["source"] == "HealthCare Fair") {
	echo patient_consent($patient['firstname'], $patient['lastname']);
	echo patient_consent($patient['firstname'], $patient['lastname']);
}
//	require ("../db/dbdisconnect.php");

function generate_html($count, $relation, $gender, $deceased_relative) {
	for ($i=0; $i < $count; $i++) {
		$data_qs = strtolower(str_replace(" ", "_", $relation)) . ($i + 1);
?>
			<li>															        
					<button type="button" class="person blood ch<?php echo ((in_array($data_qs, $deceased_relative)) ? " deceased" : ""); ?>" data-qs="<?php echo "#" . $data_qs; ?>">
						<span class="gender <?php echo $gender?>">
							<img src="<?php echo $directory?>assets/images/icon_ch.png" alt="Cancer History">
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
function patient_consent($firstname, $lastname) {
    $pconsent = '
		<section class="form patient_consent">
		    <figure class="form_logo">
				<img src="https://www.mdlab.com/questionnaire/images/logo_geneveda.png" alt="Geneveda">
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
					<span class="fill_in"><img src="' . SITE_URL . '/images/icon_edit.png" alt="edit"></span>
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
?>