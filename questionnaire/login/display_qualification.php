<?php
echo json_encode($_POST['selected_questionnaire']);
function determine_qualification(&$qualification_text) {
	require ("../db/dbconnect.php");

	$result = $conn->query("SELECT * FROM tbl_ss_qualify WHERE Guid_qualify = " . $_POST['selected_questionnaire'] . " AND Date_created='" . $_POST['selected_date'] . "'");

	$qualify = $result->fetch_assoc();

	$outliers = $conn->query("SELECT * FROM tbloutlier");

	foreach($outliers as $outlier) {
		$cancer_type = 0;
		if ($outlier['cancer_type'] == "N/A") {
			$cancer_type = 1;
		} else {
			$result = $conn->query("SELECT * FROM tbl_ss_qualifypers WHERE Guid_qualify = " . $_POST['selected_questionnaire'] . " AND Date_created='" . $_POST['selected_date'] . "' AND cancer_type = \"" . $outlier['cancer_type'] . "\"");
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
	//if ((isset($_POST['yes_continue_same_rel'])) || (isset($_POST['qualify']))) {
		if (($qualify["insurance"] == "Aetna") || ($qualify["insurance"] == "Medicare")) {
			$cancer_found_brca = 1;
		} else {
			$answers = $conn->query("SELECT * FROM tbl_ss_qualifyans WHERE Guid_qualify = " . $_POST['selected_questionnaire'] . " AND Date_created='" . $_POST['selected_date'] . "' AND Guid_outlier IS NULL");

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
	//}

	if ($cancer_found_brca) {
		array_push($qualification_text, "BRCA-Related Breast and/or Ovarian Cancer Syndrome");
	}

	if ($cancer_found_lynch) {
		array_push($qualification_text, "High Risk Colon Cancer Syndromes (Lynch syndrome)");
	}

	require ("../db/dbdisconnect.php");
}
function display_qualification($qualification_text) {
	generate_header("Do I Meet the " . $insurance . " Clincal Guidelines for Hereditary Cancer Testing?");

	require ("../db/dbconnect.php");

	$personal = $conn->query("SELECT * FROM tbl_ss_qualifyans WHERE cancer_personal IS NOT NULL AND Guid_qualify=" . $_SESSION['id']);

	$count_personal = mysqli_num_rows($personal);

	//gather all first degree relatives entered

	$first_deg_rel = array();

	$relatives = $conn->query("SELECT a.relative FROM tblfirstdegrel tr LEFT JOIN tbl_ss_qualifyans a ON a.relative = tr.value WHERE Guid_qualify=" . $_SESSION['id'] . " AND a.relative IS NOT NULL");

	if (mysqli_num_rows($relatives)) {
		foreach($relatives as $relative) {
			array_push($first_deg_rel, $relative['relative']);
		}
	}

	$count_first_deg_rel = array_count_values($first_deg_rel);

	//gather all second degree relatives entered

	$second_deg_rel = array();

	$relatives = $conn->query("SELECT a.relative FROM tblseconddegrel tr LEFT JOIN tbl_ss_qualifyans a ON a.relative = tr.value WHERE Guid_qualify=" . $_SESSION['id'] . " AND a.relative IS NOT NULL");

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

	$relatives = $conn->query("SELECT relative FROM tblthirddegrel tr LEFT JOIN tbl_ss_qualifyans a ON a.relative = tr.value WHERE Guid_qualify=" . $_SESSION['id']);

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

	$result = $conn->query("SELECT * FROM tbl_ss_qualify WHERE Guid_qualify = " . $_SESSION['id']);

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
?>
	<input type="hidden" name="prev_step" value="<?php echo $_POST['current_step']; ?>">
	<input type="hidden" name="fieldname" value="<?php echo $_POST['fieldname']; ?>">
	<input type="hidden" name="type" value="<?php echo $_POST['type']; ?>">

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
			<p>You are likely to meet the <?php echo $insurance; ?> clinical guidelines and should be tested for:</p>
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
	$result = $conn->query("SELECT * FROM tbl_ss_qualify WHERE Guid_qualify=" . $_SESSION['id'] . " AND gene_mutation = \"Yes\"");

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

	$result = $conn->query("SELECT a.age_personal, a.cancer_personal, o.guideline_met FROM tbl_ss_qualifyans a LEFT JOIN tbloutlier o ON a.Guid_outlier = o.Guid_outlier WHERE Guid_qualify=" . $_SESSION['id'] . " AND a.Guid_outlier IS NOT NULL");



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

	$personals = $conn->query("SELECT a.age_personal, a.cancer_personal, q.guideline_met FROM tbl_ss_qualifyans a LEFT JOIN tblcancerquestion q ON a.Guid_question = q.Guid_question WHERE Guid_qualify=" . $_SESSION['id'] . " AND cancer_personal IS NOT NULL AND a.Guid_outlier IS NULL ORDER BY q.sort_order");

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

		$result = $conn->query("SELECT q.guideline_met FROM tbl_ss_qualifyans a LEFT JOIN tblcancerquestion q ON a.Guid_question = q.Guid_question WHERE Guid_qualify=" . $_SESSION['id'] . " AND a.relative IS NOT NULL AND a.Guid_outlier IS NULL GROUP BY a.Guid_question");

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
										<li><?php echo $me_cancer_personal[$i]; ?> at age <?php echo $me_age_personal[$i]; ?></li>
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
	$relatives = $conn->query("SELECT a.age_relative, a.cancer_type, a.additional_cancer_type, a.relative, a.deceased, q.guideline_met, q.additional_question FROM tbl_ss_qualifyans a LEFT JOIN tblcancerquestion q ON a.Guid_question = q.Guid_question WHERE Guid_qualify=" . $_SESSION['id'] . " AND a.relative IS NOT NULL AND a.Guid_outlier IS NULL ORDER BY q.sort_order");

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
				array_push($c_type, $relative['cancer_type'] . " at age " . $relative['age_relative']);
				$cancer_detail[$id] = $c_type;
			}
			if (strlen($relative['additional_cancer_type'])) {
				$additional_cancer = $relative['additional_cancer_type'];
			}

			if (strlen($relative['additional_question'])) {
				$result = $conn->query("SELECT a.cancer_type, a.age_relative, q.guideline_met FROM tbl_ss_qualifyans a LEFT JOIN tblcancerquestion q ON a.Guid_question = q.Guid_question WHERE Guid_qualify=" . $_SESSION['id'] . " AND q.field_name in(" . $relative['additional_question'] . ")");

				if (mysqli_num_rows($result)) {
					$additional = $result->fetch_assoc();
					$additional_cancer = $additional['cancer_type'];
					$guideline_met = $additional['guideline_met'];
					$additional_age = $additional['age_relative'];
				}
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

	$results = $conn->query("SELECT q.guideline_met FROM tbl_ss_qualifyans a LEFT JOIN tblcancerquestion q ON a.Guid_question = q.Guid_question WHERE Guid_qualify=" . $_SESSION['id']);
	foreach ($results as $result) {
		if ((strlen($result['guideline_met'])) && (!in_array($result['guideline_met'], $guideline))) {
			array_push($guideline, $result['guideline_met']);
		}
	}


	$result = $conn->query("SELECT * FROM tbl_ss_qualifypers WHERE Guid_qualify = " . $_SESSION['id'] . "  AND cancer_type =\"No Cancer/None of the Above\"");
	$no_p_cancer_history = mysqli_num_rows($result);
	$result = $conn->query("SELECT * FROM tbl_ss_qualifyfam WHERE Guid_qualify = " . $_SESSION['id'] . "  AND cancer_type =\"No Cancer/None of the Above\"");
	$no_r_cancer_history += mysqli_num_rows($result);


	if ((!count($guideline)) || (($no_p_cancer_history) && ($no_r_cancer_history))) {
		array_push($guideline, "None");
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
		generate_rel_html($count_second_deg_rel['Paternal Uncle'], "Paternal Uncle", "male", $deceased_relative);
		generate_rel_html($count_second_deg_rel['Paternal Aunt'], "Paternal Aunt", "female", $deceased_relative);
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
	generate_rel_html($count_first_deg_rel['Sister'], "Sister", "female", $deceased_relative);
	generate_rel_html($count_second_deg_rel['Maternal Half-Sister'], "Maternal Half-Sister", "female", $deceased_relative);
	generate_rel_html($count_second_deg_rel['Paternal Half-Sister'], "Paternal Half-Sister", "female", $deceased_relative);
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
		generate_rel_html($count_first_deg_rel['Daughter'], "Daughter", "female", $deceased_relative);
		generate_rel_html($count_first_deg_rel['Son'], "Son", "male", $deceased_relative);
?>
											</ul>
<?php
	}
?>
										</li>
<?php
	// Brother
	generate_rel_html($count_first_deg_rel['Brother'], "Brother", "male", $deceased_relative);
	generate_rel_html($count_second_deg_rel['Maternal Half-Brother'], "Maternal Half-Brother", "male", $deceased_relative);
	generate_rel_html($count_second_deg_rel['Paternal Half-Brother'], "Paternal Half-Brother", "male", $deceased_relative);
?>
									</ul>
								</li>
<?php
	// Maternal Uncle and Aunt
	if ($count_second_deg_rel['Maternal Uncle'] || $count_second_deg_rel['Maternal Aunt']) {
		generate_rel_html($count_second_deg_rel['Maternal Uncle'], "Maternal Uncle", "male", $deceased_relative);
		generate_rel_html($count_second_deg_rel['Maternal Aunt'], "Maternal Aunt", "female", $deceased_relative);
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
	$result = $conn->query("SELECT * FROM tblpatient WHERE Guid_user = " . $qualify['Guid_user']);

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
						    <span class="underline"><?php echo date("n/j/Y", strtotime($qualify['Date_created']));?></span>
					    </section>
					</div>

					<div class="line two">
						<section>
						    <h4>Physician Signature:</h4>
							<span class="underline"></span>
						</section>

					    <section>
						    <h4>Date:</h4>
							<span class="underline"></span>
						</section>
					</div>
		</div>

	<div id="q_disclaimer" class="wrapper">
		<p id="ped_disc">This pedigree may not be a complete representation and requires more information concerning your relatives.  It is recommended you consult with a health care professional to complete it.</p>
<?php
	if ($qualify["ashkenazi"] == "Yes") {
		$result = $conn->query("SELECT * FROM tbl_ss_qualifyans WHERE Guid_qualify = " . $_SESSION['id'] . " AND (cancer_personal IN(\"Breast\", \"Ovarian\", \"Pancreatic\", \"Prostate\") OR cancer_type IN(\"Breast\", \"Ovarian\", \"Pancreatic\", \"Prostate\"))");

		if(mysqli_num_rows($result)) {
?>
		<p>A screening panel for three founder mutations common in the Ashkenazi Jewish population is medically necessary first when criteria are met. If founder mutation testing is negative, full gene sequencing of BRCA1 and BRCA2 genes (reflex testing) is then considered medically necessary only if the member meets any of the criteria described above for comprehensive testing.</p>
<?php
		}
	}
	$result = $conn->query("SELECT * FROM tbl_ss_qualifyans WHERE Guid_qualify = " . $_SESSION['id'] . " AND gene_mutation = \"Yes\"");
	$total_gene_mutation = mysqli_num_rows($result);

	$result = $conn->query("SELECT * FROM tbl_ss_qualify WHERE Guid_qualify = " . $_SESSION['id'] . " AND gene_mutation = \"Yes\"");
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
		This assessment is based on the National Comprehensive Cancer Network (NCCN) guidelines (www.nccn.org) for BRCA-Related Breast and/or Ovarian Cancer syndrome (version 1.2018) and for High-Risk Colorectal Cancer Syndromes (version 3.2017).
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
	generate_outer_bottom($error);

	$content = '
			<p>Questionnaire was submitted successfully.</p>
			<a href="https://www.mdlab.com/questionnaire/?';

	if (isset($_GET['ln']) && ($_GET['ln'] == "pin")) {
		$content .= 'ln=pin';
	} else {
		$content .= 'continue=Yes';
	}

	$content .= ' style="color:#973737"><strong>Access the questionnaire</strong></a>';

	$title = "Questionnaire successfully completed";

	send_email($content, $title);

	$title = "";

	$content = '<p>Environment: ' . ENV . '</p>';

	$content .= '
					<p><strong>Patient Information</strong></p>';

	$content .= '<p>Patient ID: ' . $_SESSION['id'] . '</p>
			<p>Insurance: ' . $qualify["insurance"]. '</p>
			<p>Gender: ' . $qualify['gender']  . '</p>
			<p>Ashkenazi: ' . $qualify['ashkenazi']  . '</p>';

	if (strlen($qualify['account_number'])) {
		$content .= '<p>Account: ' . $qualify['account_number'] . '</p>';
	}

	if (strlen($qualify['provider_id'])) {
		$result = $conn->query("SELECT name FROM tblprovider WHERE Guid_provider = " . $qualify['provider_id']);

		$provider = $result->fetch_assoc();
		$content .= '<p>Provider: ' . $provider['name'] . '</p>';
	}

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

	$result = $conn->query("SELECT * FROM tbl_ss_qualifyans WHERE cancer_personal IS NOT NULL AND Guid_qualify=" . $_SESSION['id']);

	if (mysqli_num_rows($result)) {
		$content .= '
					<p><strong>Personal Cancer History</strong></p>';

		foreach ($result as $personal) {
			$content .= '
					<p>Cancer: ' . $personal['cancer_personal'] . '</p>
					<p>Age: ' . $personal['age_personal'] . '</p>';
		}
	}

	$result = $conn->query("SELECT * FROM tbl_ss_qualifyans WHERE Guid_qualify=" . $_SESSION['id'] . " AND relative IS NOT NULL");

	if (mysqli_num_rows($result)) {
		$content .= '
					<p><strong>Family Cancer History</strong></p>';
		foreach ($result as $relative) {
			$content .= '
					<p>Relative: ' . $relative['relative'] . '</p>
					<p>Family Cancer: ' . $relative['cancer_type'] . '</p>
					<p>Age: ' . $relative['age_relative'] . '</p>';
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

	if ((ENV == "live") && ($_SESSION['id'] != "117")) {
		//send_email($content, $title, "questionnaire@mdlab.com");
	}

	//save_snap_shot();

	require ("db/dbdisconnect.php");
}
function generate_rel_html($count, $relation, $gender, $deceased_relative) {
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
?>