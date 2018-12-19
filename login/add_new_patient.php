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
$uploadMessage = "";
$xmlLink = "";


$accessRole = getAccessRoleByKey('home');
$roleIDs = unserialize($accessRole['value']);
$dataViewAccess = isUserHasAnyAccess($roleIDs, $roleID, 'view');

$isValid = TRUE;
if(isset($_GET['patient']) && $_GET['patient'] !="" ){     
    $Guid_user = $_GET['patient'];
    $patientInfoUrl = SITE_URL.'/patient-info.php?patient='.$Guid_user;
    if( isset($_GET['account']) &&$_GET['account']!=""){
        $patientInfoUrl .= '&account='.$_GET['account'];
    }
    if( isset($_GET['incomplete']) &&$_GET['incomplete']=="1"){
        $patientInfoUrl .= '&incomplete=1';   
    }    

  
 } ?>




<link rel="stylesheet" href="assets/css/brca_forms.css">


<?php require_once 'navbar.php'; ?> 

<main class="full-width">
    <input type="hidden" id="guid_patient" />
    <input type="hidden" id="post" value='<?php echo $patientInfo; ?>' />

    <div class="box full visible">

        
        <section id="palette_top">
            <h4>                
                <ol class="breadcrumb">
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="active">Add New Patient</li>                   
                </ol>                
            </h4>
            <a href="<?php echo SITE_URL; ?>/patient-info.php?logout=1" name="log_out" class="button red back logout"></a>
            <a href="<?php echo SITE_URL; ?>/dashboard2.php" class="button homeIcon"></a>
            <a href="<?php echo QUESTIONNAIRE_URL; ?>" target="_blank" class="button submit smaller_button"><strong>View Questionnaire</strong></a>
        </section>

    <div id="add-new-patient-block">

  
        <div class="container form-container" style="margin:auto"> 
            <div class = "form-row">
                <div id = "add-new-patient-button">
                    <h2>Add New</h2>
                </div>
                <div id = "all-new-patients-button">
                    <h2>All</h2>
                </div>
              
                
            <div id="add-new-patient">

                <div class = "col-md-offset-3 col-md-6">
                    <h1 >Add New Patient</h1>
                    <form>
                        <div class="f2 required col-md-12">
                            <label class="dynamic" for="serial_number"><span>First Name</span></label>
                                <div class="group">
                                    <input id="serial_number" name="serial_number" type="text" value="" placeholder="First Name" required="">
                                    <p class="f_status">
                                        <span class="status_icons"><strong>*</strong></span>
                                    </p>
                                </div>
                        </div>    
                        <div class="f2 required col-md-12">
                            <label class="dynamic" for="serial_number"><span>Last Name</span></label>
                                <div class="group">
                                    <input id="serial_number" name="serial_number" type="text" value="" placeholder="Last Name" required="">
                                    <p class="f_status">
                                        <span class="status_icons"><strong>*</strong></span>
                                    </p>
                                </div>
                        </div> 
                        <div class="f2 required col-md-12">
                            <label class="dynamic" for="serial_number"><span>Date of Birth</span></label>
                                <div class="group">
                                    <input id="serial_number" name="serial_number" type="text" value="" placeholder="Date of Birth" required="">
                                    <p class="f_status">
                                        <span class="status_icons"><strong>*</strong></span>
                                    </p>
                                </div>
                        </div> 
                        <div class="f2 required col-md-12">
                            <label class="dynamic" for="serial_number"><span>Physician</span></label>
                                <div class="group">
                                     <select id="provider" name="provider" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['provider'])) && (strlen($_POST['provider']))) ? "" : "no-selection"; ?>">
                                <option value="">Physician</option>							
                                <?php
                                $default_account = ltrim($default_account, ',');
                                if($default_account){
                                $query = "SELECT * FROM tblprovider WHERE account_id IN (" . $default_account . ") GROUP BY first_name";

                                $providers = $db->query($query);
                                foreach ($providers as $provider) {
                                    ?>
                                    <option value="<?php echo $provider['Guid_provider']; ?>"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['provider']) && ($_POST['provider'] == $provider['Guid_provider'])) ? " selected" : ""); ?>><?php echo $provider['first_name']." ".$provider['last_name']; ?></option>
                                    <?php
                                }
                                }
                                ?>
                            </select>
                            <p class="f_status">
                                <span class="status_icons"><strong>*</strong></span>
                            </p>
                                </div>
                        </div> 
                        <div class="f2 required col-md-12">
                            <label class="dynamic" for="serial_number"><span>Email</span></label>
                                <div class="group">
                                    <input id="serial_number" name="serial_number" type="text" value="" placeholder="Email" required="">
                                    <p class="f_status">
                                        <span class="status_icons"><strong>*</strong></span>
                                    </p>
                                </div>
                        </div> 
                        <button class = "save-button button">Save</button>      
                    </div> 
                    </form>  
            </div>
              <div id = "new-patient-table">
              	<button class = "print_button button delete">Delete All</button>
              	<div class = "col-lg-offset-2 col-lg-8 col-md-offset-1 col-md-10">
                <h1>All Clipboard Patients</h1>
                <h2>12345 Woman2Woman</h2>
                <table class="pseudo_t table without_scroll">
                    <thead class="">
                        <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>DOB</th>
                        <th>Physician</th>
                        <th>Device</th>
                        <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody> 
                        <tr class="t_row">
                            <td class="left-td"><a href = "#">James</a></td>
                            <td class="left-td"><a href = "#">Smith</a></td>
                            <td class="left-td">12/15/2018</td>
                            <td class="left-td">Bob Smith</td>
                            <td class="left-td">Dropdown</td>
                            <td class="left-td">
                              <a href = "#"><span class="fas fa-pencil-alt"></span></a>
                                <a href = "#"><span class="far fa-trash-alt"></span></a>
                                <a href = "#"><span class = "questionnaire_icon"><img src = "assets/images/open_icon_30.png"></span></a>
								<a href = "#"><span class="fas fa-user-plus"></span></a>

                            </td>
                        </tr>
                        <tr class="t_row">
                            <td class="left-td"><a href = "#">James</a></td>
                            <td class="left-td"><a href = "#">Smith</a></td>
                            <td class="left-td">12/15/2018</td>
                            <td class="left-td">Bob Smith</td>
                            <td class="left-td">Dropdown</td>
                            <td class="left-td">
                              <a href = "#"><span class="fas fa-pencil-alt"></span></a>
                                <a href = "#"><span class="far fa-trash-alt"></span></a>
                                <a href = "#"><span class = "questionnaire_icon"><img src = "assets/images/open_icon_30.png"></span></a>
								<a href = "#"><span class="fas fa-user-plus"></span></a>

                            </td>
                        </tr>
                        <tr class="t_row">
                            <td class="left-td"><a href = "#">James</a></td>
                            <td class="left-td"><a href = "#">Smith</a></td>
                            <td class="left-td">12/15/2018</td>
                            <td class="left-td">Bob Smith</td>
                            <td class="left-td">Dropdown</td>
                            <td class="left-td">
                                <a href = "#"><span class="fas fa-pencil-alt"></span></a>
                                <a href = "#"><span class="far fa-trash-alt"></span></a>
                                <a href = "#"><span class = "questionnaire_icon"><img src = "assets/images/open_icon_30.png"></span></a>
								<a href = "#"><span class="fas fa-user-plus"></span></a>

                            </td>
                        </tr>
                        <tr class="t_row">
                            <td class="left-td"><a href = "#">James</a></td>
                            <td class="left-td"><a href = "#">Smith</a></td>
                            <td class="left-td">12/15/2018</td>
                            <td class="left-td">Bob Smith</td>
                            <td class="left-td">Dropdown</td>
                            <td class="left-td">
                                <a href = "#"><span class="fas fa-pencil-alt"></span></a>
                                <a href = "#"><span class="far fa-trash-alt"></span></a>
                                <a href = "#"><span class = "questionnaire_icon"><img src = "assets/images/open_icon_30.png"></span></a>
								<a href = "#"><span class="fas fa-user-plus"></span></a>

                            </td>
                        </tr>
                        <tr class="t_row">
                            <td class="left-td"><a href = "#">James</a></td>
                            <td class="left-td"><a href = "#">Smith</a></td>
                            <td class="left-td">12/15/2018</td>
                            <td class="left-td">Bob Smith</td>
                            <td class="left-td">Dropdown</td>
                            <td class="left-td">
                                <a href = "#"><span class="fas fa-pencil-alt"></span></a>
                                <a href = "#"><span class="far fa-trash-alt"></span></a>
                                <a href = "#"><span class = "questionnaire_icon"><img src = "assets/images/open_icon_30.png"></span></a>
								<a href = "#"><span class="fas fa-user-plus"></span></a>
                            </td>
                        </tr>
                        
      
                    </tbody>
                </table>
      			</div>
              </div>
        

    </div>

          </div>
</main>


<?php require_once 'scripts.php'; ?>
<?php require_once 'footer.php'; ?>