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

$error = array();
$userID = $_SESSION['user']["id"];
$roleInfo = getRole($db, $userID);
$roleID = $roleInfo['Guid_role'];
$role = $roleInfo['role'];
$default_account = "";

$accessRole = getAccessRoleByKey('home');
$roleIDs = unserialize($accessRole['value']);
$dataViewAccess = isUserHasAnyAccess($roleIDs, $roleID, 'view');

$isValid = TRUE;
?> 

<link rel="stylesheet" href="assets/css/brca_forms.css">
<script src="assets/js/brca_forms.js"></script>

<?php require_once 'navbar.php'; ?> 

<main class="full-width">
    <div class="box full visible ">  
        <section id="palette_top">
            <h4>             
                <ol class="breadcrumb">
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <!-- <li class="active">Event Schedule</li>  -->  
                </ol>      
            </h4>
            <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
            <a href="<?php echo SITE_URL; ?>/dashboard2.php" class="button homeIcon"></a>
            <a href="<?php echo QUESTIONNAIRE_URL; ?>" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
        </section>
        <div class="scroller event-schedule">
            <a href="#" class="patient_forms" style="margin:0">
                    <img src="./images/icon_forms.png">
                    <p>Forms</p>
            </a>

          <div id = "myModal">
            
            <div class = "modal-class">
            <span class = "close">
                <i class="fas fa-times"></i>
            </span>
            <div class="container form-container" style="margin:auto"> 

                <div class = "form-row">
                    <div id = "form-details">
                        <h2>Forms</h2>
                    </div>
                    <div id = "forms">
                        <h2>Details</h2>
                    </div>
                <ul id="accordion">
                  <li>
                    <div id = "form-bar">
                        <h2>Patient Demographics</h2>
                    </div>
                    <div class = "form-info-container">
                    <div class = "form-info col-md-8">
                        <strong>Patient Demographics</strong><br/>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                    </div>
                        <div class = "buttons">
                            <div></div>
                        	<!--<i class="fas fa-angle-left prev-button"></i>-->
                        	<!--<div class = "save">Save</div>-->
                            <div class = "page-count"><p>Page 1 of 5</p></div>
                            <i class="fas fa-angle-right next-button"></i>

                        </div>
                   </div>
                  </li>
                  <li>
                     <div id = "form-bar">
                         <h2>Insurance</h2>
                     </div>
                    <div class = "form-info-container">
                    <div class = "form-info col-md-8">
                        <strong>Insurance</strong><br/>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                    </div>
                        <div class = "buttons">
                        	<i class="fas fa-angle-left prev-button"></i>
                        	<!--<div class = "save">Save</div>-->
                            <div class = "page-count"><p>Page 1 of 5</p></div>
                            <i class="fas fa-angle-right next-button"></i>

                        </div>
                   </div>
                  </li>
                  <li>
                     <div id = "form-bar">
                         <h2>Test</h2>
                     </div>
                    <div class = "form-info-container">
                    <div class = "form-info col-md-8">
                        <strong>Test</strong><br/>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                    </div>
                        <div class = "buttons">
                        	<i class="fas fa-angle-left prev-button"></i>
                        	<!--<div class = "save">Save</div>-->
                            <div class = "page-count"><p>Page 1 of 5</p></div>
                            <i class="fas fa-angle-right next-button"></i>

                        </div>
                   </div>
                  </li>
                  <li>
                     <div id = "form-bar">
                         <h2>Genetic Counseling</h2>
                     </div>
                    <div class = "form-info-container">
                    <div class = "form-info col-md-8">
                        <strong>Genetic Counseling</strong><br/>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                    </div>
                      <div class = "buttons">
                        	<i class="fas fa-angle-left prev-button"></i>
                        	<!--<div class = "save">Save</div>-->
                            <div class = "page-count"><p>Page 1 of 5</p></div>
                            <i class="fas fa-angle-right next-button"></i>

                        </div>
                   </div>
                </li>
                 <li>
                     <div id = "form-bar">
                         <h2>Physician</h2>
                     </div>
                    <div class = "form-info-container">
                    <div class = "form-info col-md-8">
                        <strong>Physician</strong><br/>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>

                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                            <div class="f2 required col-md-6">
                                <label class="dynamic" for="serial_number"><span>Serial</span></label>
                                    <div class="group">
                                        <input id="serial_number" name="serial_number" type="text" value="" placeholder="Serial" required="">
                                        <p class="f_status">
                                            <span class="status_icons"><strong>*</strong></span>
                                        </p>
                                    </div>
                            </div>
                    </div>
                        <div class = "buttons">
                        	<i class="fas fa-angle-left prev-button"></i>
                        	<!--<div class = "save">Save</div>-->
                            <div class = "page-count"><p>Page 1 of 5</p></div>
                            <!--<i class="fas fa-angle-right next-button"></i>-->
                            <div></div>

                        </div>
                   </div>
                  </li>                
              </ul>

              <div id = "form-option-table">
                <table id="dataTableHome" class="pseudo_t table">
                                    <thead class="">
                                        <tr>
                                        <th class="text-center no-bg">
                                            <label class="switch">
                                                <input id="selectAllPrintOptions" type="checkbox">
                                                <span class="slider round">
                                                    <span id="switchLabel">Select All</span>
                                                </span>
                                            </label>
                                        </th>
                                        <th>Forms</th>
                                        </tr>
                                    </thead>
                                    <tbody> 
                                        <tr class="t_row">
                                            <td class="printSelectBlock text-center">
                                                <input name="forms[test_req_form]" type="checkbox" class="print1 report1" data-prinatble="0" />
                                            </td>
                                            <td class="left-td">
                                                <a href="http://www.mdlab.com/forms/Flyers/BRCA_Genetic_Req_IH0119_10_2018.pdf">Test Req Form</a>
                                            </td>
                                        </tr>
                                        <tr class="t_row">
                                            <td class="printSelectBlock text-center">
                                                <input name="forms[informed_consent]" type="checkbox" class="print1 report1" data-prinatble="0" />
                                            </td>
                                            <td class="left-td">
                                                <a href="http://www.mdlab.com/forms/Other/BRCA_test_Consent.pdf">Informed consent</a>
                                            </td>
                                        </tr>
                                        <tr class="t_row">
                                            <td class="printSelectBlock text-center">
                                                <input name="forms[prior_authorization]" type="checkbox" class="print1 report1" data-prinatble="0" />
                                            </td>
                                            <td class="left-td">
                                                <a href="">Prior Authorization</a>
                                            </td>
                                        </tr>
                                        <tr class="t_row">
                                            <td class="printSelectBlock text-center">
                                                <input name="forms[genetic_counseling]" type="checkbox" class="print1 report1" data-prinatble="0" />
                                            </td>
                                            <td class="left-td">
                                                <a href="http://mdlab.com/forms/Other/BRCA_Genetic_Counseling_Referral.pdf">Genetic Counseling Referral</a>
                                            </td>
                                        </tr>
                                        <tr class="t_row">
                                            <td class="printSelectBlock text-center">
                                                <input name="forms[cancer_genetic_counseling]" type="checkbox" class="print1 report1" data-prinatble="0" />
                                            </td>
                                            <td class="left-td">
                                                <a href="https://2n3md83q9tf13jkpbi242zaw-wpengine.netdna-ssl.com/wp-content/uploads/2018/10/Cancer-Referral-Form_9.2018.pdf">Cancer Genetic Counseling Referral</a>
                                            </td>
                                        </tr>
                                        <tr class="t_row">
                                            <td class="printSelectBlock text-center">
                                                <input name="forms[aetna_precertification]" type="checkbox" class="print1 report1" data-prinatble="0" />
                                            </td>
                                            <td class="left-td">
                                                <a href="http://www.aetna.com/pharmacy-insurance/healthcare-professional/documents/BRCA-precertification-request-form.pdf">Aetna Precertification Information Request Form</a>
                                            </td>
                                        </tr>
                                        <tr class="t_row">
                                            <td class="printSelectBlock text-center">
                                                <input name="forms[aim]" type="checkbox" class="print1 report1" data-prinatble="0" />
                                            </td>
                                            <td class="left-td">
                                                <a href="">AIMs Precertification</a>
                                            </td>
                                        </tr>
                                        <tr class="t_row">
                                            <td class="printSelectBlock text-center">
                                                <input name="forms[beacon]" type="checkbox" class="print1 report1" data-prinatble="0" />
                                            </td>
                                            <td class="left-td">
                                                <a href="">Beacon LBS</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
              	<div class = "buttons">
              		<button id = "info_button" class = "button">Info</button>
              		<button class = "print_button button">Print</button>
              	</div>
              </div>

          </div>
                </div>
            </div>
        </div>
        </div>





        </div>
    </div>
</main>

<?php require_once 'scripts.php'; ?>
<?php require_once 'footer.php'; ?>