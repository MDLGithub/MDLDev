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
$userID = $_SESSION['user']["id"];
$roleInfo = getRole($db, $userID);
$role = $roleInfo['role'];

if($role!="Admin"){
    Leave(SITE_URL."/no-permission.php");
}

$userTables = array(
    'admin'=>'tbladmins',
    'patient'=>'tblpatient',
    'mdlpatient'=>'tblpatient',
    'salesrep'=>'tblsalesrep',
    'salesrepmgr'=>'tblsalesrep',
    'provider'=>'tblprovider'
);
$selectQ = "";$adminQ="";$patientsQ="";$mdlpatientsQ="";$salesrepsQ="";$salesrepsMgrQ="";$providersQ="";

foreach ($userTables as $k=>$v){
    $thisTable = $v;   
    
    if($k=='admin'){
        $roleID = '1'; 
    } elseif ($k=='patient') {
        $roleID = '3'; 
    } elseif ($k=='salesrep') {
       $roleID = '4'; 
    } elseif ($k=='salesrepmgr') {
       $roleID = '5'; 
    } elseif ($k=='provider') {
        $roleID = '2'; 
    }elseif ($k=='mdlpatient') {
        $roleID = '6'; 
    } 
    
    $selectQ = "SELECT u.Guid_user, u.status, u.Guid_role, u.marked_test,u.Loaded,uInfo.first_name, uInfo.last_name, u.email, r.role ";
    if($k=='patient' || $k=='mdlpatient'){
        $selectQ = "SELECT u.Guid_user, u.status, u.Guid_role, u.marked_test,u.Loaded, uInfo.Guid_patient, "
                . "aes_decrypt(uInfo.firstname_enc, 'F1rstn@m3@_%') AS first_name, "
                . "aes_decrypt(uInfo.lastname_enc, 'L@stn@m3&%#') AS last_name, u.email, r.role ";
    }
    $selectQ .= "FROM tbluser u ";
    $selectQ .= "LEFT JOIN `tblrole` r ON u.Guid_role=r.Guid_role ";
    $selectQ .= "LEFT JOIN $thisTable uInfo ON u.Guid_user=uInfo.Guid_user ";  
    
    $selectQ .= "WHERE u.Guid_role=$roleID ";  
    
    if(isset($_POST['search'])){
        if(isset($_POST['first_name']) && $_POST['first_name']!=""){
            if($k=='patient' || $k=='mdlpatient'){
                $selectQ .= " AND aes_decrypt(uInfo.firstname_enc, 'F1rstn@m3@_%') LIKE '%".escape($_POST['first_name'])."%'";
            }else{
                $selectQ .= " AND uInfo.first_name LIKE '".escape($_POST['first_name'])."%'";
            }
        }
        if(isset($_POST['last_name']) && $_POST['last_name']!=""){
            if($k=='patient' || $k=='mdlpatient'){
                $selectQ .= " AND aes_decrypt(uInfo.lastname_enc, 'L@stn@m3&%#') LIKE '%".escape($_POST['last_name'])."%'";
            }else{
                $selectQ .= " AND uInfo.last_name LIKE '%".escape($_POST['last_name'])."%'";
            }
        }
        if(isset($_POST['email']) && $_POST['email']!=""){            
            $selectQ .= " AND u.email = '".escape($_POST['email'])."'";            
        }
        if(isset($_POST['Guid_role']) && $_POST['Guid_role']!=""){            
            $selectQ .= " AND u.Guid_role = '".$_POST['Guid_role']."'";            
        }
        if(isset($_POST['status']) && $_POST['status']!=""){            
            $selectQ .= " AND u.status = '".$_POST['status']."'";            
        }
        if(isset($_POST['marked_test']) && $_POST['marked_test']=="1"){            
            $selectQ .= " AND u.marked_test = '1'";            
        }
        if(isset($_POST['Loaded']) && $_POST['Loaded']=="1"){            
            $selectQ .= " AND u.Loaded = 'Y'";            
        }
        if(isset($_POST['locked_users']) && $_POST['locked_users']=="1"){            
            //$getLockedEmails = checkbrute($user['email'], $db);  
            $now = time();
            $valid_attempts = $now - (2 * 60 * 60);            
            $attemptsQ = "SELECT email FROM tbluser_login_attempts WHERE time > '$valid_attempts' GROUP BY `email`";
            $lockedEmails = $db->query($attemptsQ);
            $emails = '';
            //var_dump($lockedEmails);
            if(!empty($lockedEmails)){
                foreach ($lockedEmails as $key=>$val){
                    $emails .= "'".$val['email']."', ";
                }
                $emails = rtrim($emails, ', ');
            }
            if($emails){
                $selectQ .= " AND u.email IN(".$emails.")";
            } else {
                $selectQ = "";
            }         
        }
    }   
   
    if($k=='admin'){
        $adminQ = $selectQ;
    } elseif ($k=='patient') {
        $patientsQ = $selectQ;
    } elseif ($k=='mdlpatient') {
        $mdlpatientsQ = $selectQ;
    } elseif ($k=='salesrep') {
        $salesrepsQ = $selectQ;
    } elseif ($k=='salesrepmgr') {
        $salesrepsMgrQ = $selectQ;
    } elseif ($k=='provider') {
        $providersQ = $selectQ;
    }
    
}

$query1 = (isset($adminQ) && $adminQ!="")?$db->query($adminQ):array();
$query2 = (isset($patientsQ) && $patientsQ!="")?$db->query($patientsQ):array();
$query3 = (isset($mdlpatientsQ) && $mdlpatientsQ!="")?$db->query($mdlpatientsQ):array();
$query4 = (isset($salesrepsQ) && $salesrepsQ!="")?$db->query($salesrepsQ):array();
$query5 = (isset($salesrepsMgrQ) && $salesrepsMgrQ!="")?$db->query($salesrepsMgrQ):array();
$query6 = (isset($providersQ) && $providersQ!="")?$db->query($providersQ):array();




$users = array_merge($query1,$query2,$query3,$query4,$query5,$query6);

$thisMessage="";

if(isset($_GET['delete-user']) && $_GET['delete-user']!=""){
    deleteUserByID($db, $_GET['delete-user']);    
    Leave(SITE_URL."/user-management.php");
}

require_once ('navbar.php');
?>

<!--SEARCH FORM BLOCK Start-->
<aside id="action_palette" >		
    <div class="box full">
        <h4 class="box_top">Filters</h4>
        
        <div class="boxtent scroller ">
            <form id="filter_form" action="" method="post">             
                <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['first_name'])) && (strlen(trim($_POST['first_name'])))) ? " show-label valid" : ""; ?>">
                    <label class="dynamic" for="first_name"><span>First Name</span></label>
                    <div class="group">
                        <input id="first_name" name="first_name" type="text" value="<?php echo ((!isset($_POST['clear'])) && isset($_POST['first_name']) && strlen(trim($_POST['first_name']))) ? trim($_POST['first_name']) : ""; ?>" placeholder="First Name">
                        <p class="f_status">
                            <span class="status_icons"><strong></strong></span>
                        </p>
                    </div>
                </div>
                <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['last_name'])) && (strlen(trim($_POST['last_name'])))) ? " show-label valid" : ""; ?>">
                    <label class="dynamic" for="first_name"><span>Last Name</span></label>
                    <div class="group">
                        <input id="last_name" name="last_name" type="text" value="<?php echo ((!isset($_POST['clear'])) && isset($_POST['last_name']) && strlen(trim($_POST['last_name']))) ? trim($_POST['last_name']) : ""; ?>" placeholder="Last Name">
                        <p class="f_status">
                            <span class="status_icons"><strong></strong></span>
                        </p>
                    </div>
                </div>
                <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['email'])) && (strlen(trim($_POST['email'])))) ? " show-label valid" : ""; ?>">
                    <label class="dynamic" for="first_name"><span>Last Name</span></label>
                    <div class="group">
                        <input id="email" name="email" type="email" value="<?php echo ((!isset($_POST['clear'])) && isset($_POST['email']) && strlen(trim($_POST['email']))) ? trim($_POST['email']) : ""; ?>" placeholder="Email">
                        <p class="f_status">
                            <span class="status_icons"><strong></strong></span>
                        </p>
                    </div>
                </div>
                
                <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['Guid_role'])) && (strlen($_POST['Guid_role']))) ? " show-label valid" : ""; ?>">
                    <label class="dynamic" for="Guid_role"><span>User Role</span></label>

                    <div class="group">
                        <select id="Guid_role" name="Guid_role" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['Guid_role'])) && (strlen($_POST['Guid_role']))) ? "" : "no-selection"; ?>">
                            <option value="">User Role</option>							
                            <?php
                            $roles = $db->query("SELECT * FROM `tblrole` ORDER BY role ASC");
                            foreach ($roles as $k=>$v) {
                                $selected = (!isset($_POST['clear']) && isset($_POST['Guid_role']) && $_POST['Guid_role']==$v['Guid_role']) ?  "selected " : "";
                            ?>
                                <option <?php echo $selected; ?> value="<?php echo $v['Guid_role']; ?>"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['Guid_role']) && ($_POST['Guid_role'] == $v['Guid_role'])) ? " selected" : ""); ?>><?php echo $v['role']; ?></option>
                                <?php
                            }
                            ?>
                        </select>

                        <p class="f_status">
                            <span class="status_icons"><strong></strong></span>
                        </p>
                    </div>
                </div>
                
                <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['status'])) && (strlen($_POST['status']))) ? " show-label valid" : ""; ?>">
                    <label class="dynamic" for="Guid_status"><span>Status</span></label>
                    <?php 
                    $selectedActive = (!isset($_POST['clear']) && isset($_POST['status']) && $_POST['status']=='1') ?  "selected " : "";
                    $selectedInactive = (!isset($_POST['clear']) && isset($_POST['status']) && $_POST['status']=='0') ?  "selected " : "";
                    ?>
                    <div class="group">
                        <select id="Guid_status" name="status" class="<?php echo (!isset($_POST['clear']) && isset($_POST['status']) && strlen($_POST['status']) ) ? "" : "no-selection"; ?>">
                            <option value="">Status</option>                                
                            <option <?php echo $selectedActive; ?> value="1" >Active</option>
                            <option <?php echo $selectedInactive; ?> value="0" >Inactive</option>
                        </select>
                        <p class="f_status">
                            <span class="status_icons"><strong></strong></span>
                        </p>
                    </div>
                </div>
                
                <div>
                    <input id="show-tests" name="marked_test" value="1" type="checkbox" <?php echo ((!isset($_POST['clear'])) && (isset($_POST['marked_test']) && ($_POST['marked_test'] == 1)) ? " checked" : ""); ?> />
                    <label for="show-tests">Marked As Test</label>                     
                </div>
                <div>
                    <input id="Loaded" name="Loaded" value="1" type="checkbox" <?php echo ((!isset($_POST['clear'])) && (isset($_POST['Loaded']) && ($_POST['Loaded'] == 1)) ? " checked" : ""); ?> />
                    <label for="Loaded">Loaded</label>                     
                </div>
                <div>
                    <input id="locked_users" name="locked_users" value="1" type="checkbox" <?php echo ((!isset($_POST['clear'])) && (isset($_POST['locked_users']) && ($_POST['locked_users'] == 1)) ? " checked" : ""); ?> />
                    <label for="locked_users">Locked Users</label>                     
                </div>
                
                <button id="filter" value="1" name="search" type="submit" class="button filter half"><strong>Search</strong></button>
                <button type="submit" name="clear" class="button cancel half"><strong>Clear</strong></button>
            </form>
            <!--********************   SEARCH BY PALETTE END    ******************** -->

        </div>
    </div>    
</aside>
<!--SEARCH FORM BLOCK END-->


<main >
    <?php 
    
    if(isset($_GET['update']) ){ 
        $thisMessage = "Changes have been saved";
    }
    if($thisMessage != ""){  ?>
    <section id="msg_display" class="show success">
        <h4><?php echo $thisMessage;?></h4>
    </section>
    <?php } ?> 
    <div class="box full visible ">  
        <section id="palette_top">
            <h4  class="um_palette_header">  
                <ol class="breadcrumb">
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="active">User Management</li>                   
                </ol>      
            </h4>
            <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
            <a href="<?php echo SITE_URL; ?>/dashboard2.php" class="button homeIcon"></a>
            <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit user-mng-button"><strong>View Questionnaire</strong></a>
        </section>
        <div class="scroller">  
            <div class="row">               
                <div class="col-md-12">                    
                    <a class="add-new-button" href="<?php echo SITE_URL; ?>/user-management.php?action=add">
                        <span class="fas fa-user-plus" aria-hidden="true"></span> Add 
                    </a>
                    <a id="delete-marked-test-users" class="add-new-button pull-right">
                        <span class="fas fa-history" ></span> Delete Test Users 
                    </a>
                </div>  
                <div class="col-md-12 text-right user-managemnet-bg">
                    <span class="admin">◾&#xfe0e; Admin</span>
                    <span class="salesrep">◾&#xfe0e; Sales Rep</span>
                    <span class="provider">◾&#xfe0e; Physician</span>
                    <span class="marked_test">◾&#xfe0e; Test Users</span>
                    <span class="mdl_patient">◾&#xfe0e; MDL Patient</span>
                </div>
            </div>               
            <div class="row">
                <div class="col-md-12">
                    <table id="dataTable" class="display user-table" style="width:100%">
                        <thead>
                            <tr>
                                <th class="actions">#</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th class="noFilter actions text-center">Status</th>
                                <th class="noFilter actions text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $k=>$user){  ?>
                            <?php 
                            $trClass = ""; $patientInfoLink = "";
                            if($user['marked_test']=='1'){
                                $trClass = "marked_test";
                            }
                            if($user['Guid_role']=='1'){
                                $trClass = "admin";
                            }
                            if($user['Guid_role']=='2'){
                                $trClass = "provider";
                            }
                            if($user['Guid_role']=='4' || $user['Guid_role']=='5'){
                                $trClass = "salesrep";
                            }
                            if($user['Guid_role']=='6'){
                                $trClass = "mdl_patient";
                                $patientInfoLink = SITE_URL.'/patient-info.php?patient='.$user['Guid_user'];
                            }
                            if($user['Guid_role']=='3'){
                                $patientInfoLink = SITE_URL.'/patient-info.php?patient='.$user['Guid_user'];
                            }
                            
                            ?>
                            <tr class="<?php echo $trClass;?>">
                                <?php if($patientInfoLink!=''){ ?>
                                <td><a href="<?php echo $patientInfoLink; ?>"><?php echo $user['Guid_user']; ?></a></td>
                                <td><a href="<?php echo $patientInfoLink; ?>"><?php echo ucfirst(strtolower($user['first_name'])); ?></a></td>
                                <td><a href="<?php echo $patientInfoLink; ?>"><?php echo formatLastName($user['last_name']); ?></a></td>
                                <?php } else { ?>
                                <td><?php echo $user['Guid_user']; ?></td>
                                <td><?php echo ucfirst(strtolower($user['first_name'])); ?></td>
                                <td><?php echo formatLastName($user['last_name']); ?></td>
                                <?php } ?>                                
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo isset($user['role'])?$user['role']:'Patient'; ?></td>
                                <td class="text-center fs-20">
                                    <?php                                     
                                        if (checkbrute($user['email'], $db) == true){
                                            echo "<span data-user-email='".$user['email']."' class='locked-user fas fa-user-lock'></span>";
                                        } else {
                                            if($user['status']=='1') {
                                                echo "<span class='fas fa-user-check mn yes'></span>"; 
                                            } else {
                                                echo "<span class='fas fa-user-alt-slash mn no'></span>"; 
                                            }
                                        }
                                    ?>
                                </td>
                                <td class="">   
                                    <?php 
                                        $editUrl = "";
                                        if($user['Guid_user'] != ""){
                                            $editUrl = '&id='.$user['Guid_user'];
                                        } else {
                                            if(isset($user['Guid_patient'])){
                                                $editUrl = '&id='.$user['Guid_user'].'&patient_id='.$user['Guid_patient'];
                                            }
                                        }
                                    ?>
                                    <a href="<?php echo SITE_URL; ?>/user-management.php?action=update<?php echo $editUrl; ?>">
                                        <span class="fas fa-pencil-alt" aria-hidden="true"></span>
                                    </a> 
                                    <?php if($user['marked_test']=='1' && $user['Guid_role']!='6'){?>
                                    <a id="test-user" class="deleteUser" title="Remove User and History" data-user-id="<?php echo $user['Guid_user']; ?>" >
                                        <span class="far fa-trash-alt" aria-hidden="true"></span> 
                                    </a>
                                    <?php } ?>
                                    <?php if($user['Guid_role']=='6'){?>
                                    <a id="mdl-user" class="deleteUser" title="Remove History" data-user-id="<?php echo $user['Guid_user']; ?>" >
                                        <span class="far fa-trash-alt" aria-hidden="true"></span> 
                                    </a>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>           
        </div>
    </div>
</main>
<button id="action_palette_toggle" class=""><i class="fa fa-2x fa-angle-left"></i></button>

<?php     
    $first_name = "";
    $last_name = "";
    $photo_filename = "";
    $result = false;
    $message = "";
    if(isset($_POST['save_user'])){
        extract($_POST);
        $userData['email'] = $email;
        $userData['status'] = $status;
        
        if(isset($_POST['password']) &&$_POST['password'] != ""){
            $userData['password'] = encode_password($password);
        }   
        if(isset($_POST['mark_as_test']) && $_POST['mark_as_test'] != ""){
            $userData['marked_test'] = '1';
        } else {
            $userData['marked_test'] = '0';
        }   
        if(isset($_POST['last_name']) && $_POST['last_name']=='Doe'){
            $userData['marked_test'] = '1';
        }
        if(isset($Guid_role) && $Guid_role != ""){
            if($Guid_role=='1'){
                $userData['user_type'] = 'admin';
                $roleName = 'Admin';
            } elseif ($Guid_role=='2') {
                $userData['user_type'] = 'provider';
                $roleName = 'Physician';
            } elseif ($Guid_role=='3') {
                $userData['user_type'] = 'patient';
                $roleName = 'Patient';
            }elseif ($Guid_role=='6') {
                $userData['user_type'] = 'mdl-patient';
                $roleName = 'MDL Patient';
            } elseif ($Guid_role=='4') {
                $userData['user_type'] = 'salesrep';
                $roleName = 'Sales Rep';
            } elseif ($Guid_role=='5') {
                $userData['user_type'] = 'salesmgr';
                $roleName = 'Sales Manager';
            }            
        }
        
        $fName = isset($_POST['first_name']) ? $_POST['first_name'] : "";
        $lName = isset($_POST['last_name']) ? $_POST['last_name'] : "";
        if($Guid_role=='3' || $Guid_role=='6'){
            $userDetails = array('firstname_enc'=>$fName, 'lastname_enc'=>$lName);                    
        } else {
            $userDetails = array('first_name'=>$fName, 'last_name'=>$lName);
            if($_FILES["photo_filename"]["name"] != ""){
                $fileName = $_FILES["photo_filename"]["name"];        
                $userDetails['photo_filename'] = $fileName;
                $uploadMsg = uploadFile('photo_filename', 'images/users/');
            }
        }
        
        if($Guid_user == ""){ //insert User 
            //checking for email unique
            $isMailExists = $db->row("SELECT `email` FROM tbluser WHERE email=:email", array('email'=> escape($email)));
            if(!$isMailExists){
                unset($userData['Guid_user']);
                $userData['Date_created'] = date('Y-m-d H:i:s');             
                $userData['Guid_role'] = $Guid_role;                   
                $inserUser = insertIntoTable($db, 'tbluser', $userData);              
                $Guid_user = $inserUser['insertID'];
                saveUserDetails($db, $Guid_user, $Guid_role, $userDetails);
                //update user category relationship if user is Sales Manager
                if(isset($_POST['Guid_category'])){
                    $catIDs = $_POST['Guid_category'];
                    saveCategoryUserLinks($db, $Guid_user, $catIDs);                       
                } else {
                    $db->query("DELETE FROM `tbl_mdl_category_user_link` WHERE Guid_user=:Guid_user", array('Guid_user'=>$Guid_user));
                }
                Leave(SITE_URL."/user-management.php?update");
            } else {
                $message = "User with this email already exists.";
            }
        }else{ //update user info            
            $isMailExists = $db->row("SELECT `email` FROM tbluser WHERE `email`='".$email."' AND `Guid_user`<>$Guid_user");
            if(!$isMailExists){
                $userData['Date_modified'] = date('Y-m-d H:i:s');
                $userData['Guid_role'] = $Guid_role;                
                $whereUser = array('Guid_user'=>$Guid_user);
                updateTable($db, 'tbluser', $userData, $whereUser);
                if($Guid_role=='2'){ // also need to update account for Physician
                    $checkProvider = $db->row("SELECT Guid_provider, Guid_user FROM tblprovider WHERE Guid_user=:Guid_user", $whereUser);
                    $account_id = escape($_POST['account_id']);
                    
                    if(isset($checkProvider['Guid_provider']) && $checkProvider['Guid_provider']!="" ){
                        updateTable($db, 'tblprovider', array('account_id'=>$account_id), $whereUser);
                    }else{
                        insertIntoTable($db, 'tblprovider', array('account_id'=>$account_id, 'Guid_user'=>$Guid_user));
                    }                    
                }                 
                //check if role is changed, then move all data to that user by role table and remove previous
                $prevRole = $db->row('SELECT Guid_role FROM `tbluser` WHERE Guid_user=:Guid_user', $whereUser);
                if($prevRole['Guid_role'] == $Guid_role){                     
                    saveUserDetails($db, $Guid_user, $Guid_role, $userDetails); 
                } else { 
                    //need to move user info data to proper table and delete prev
                    moveUserData($db, $Guid_user, $userDetails, $Guid_role, $prevRole['Guid_role']);
                } 
                //update user category relationship if user is Sales Manager
                if(isset($_POST['Guid_category'])){
                    $catIDs = $_POST['Guid_category'];
                    saveCategoryUserLinks($db, $Guid_user, $catIDs);                       
                } else {
                    $db->query("DELETE FROM `tbl_mdl_category_user_link` WHERE Guid_user=:Guid_user", array('Guid_user'=>$Guid_user));
                }
                Leave(SITE_URL."/user-management.php?update");
            } else {
                $message = "User with this email already exists.";
            }
        }        
    } 
    
    if(isset($_GET['action']) && $_GET['action'] !="" ){ 
        $userID = $_GET['id'];
        $user = getUserAndRole($db, $userID); 
       
        //we can change only roles admin and sales reps
        if($_GET['action']=='add'){
            $allRoles = $db->query('SELECT * FROM tblrole ORDER BY role ASC'); 
        }else{
            if(in_array($user['Guid_role'], array('1','4','5'))){ //Admin, Sales Rep, Sales mgr
                $allRoles = $db->query('SELECT * FROM tblrole WHERE Guid_role IN(1,4,5) ORDER BY role ASC'); 
            }
            if(in_array($user['Guid_role'], array('3','6'))){ //Patient, MDL Patient
                $allRoles = $db->query('SELECT * FROM tblrole WHERE Guid_role IN(3,6) ORDER BY role ASC'); 
            }
        }
        $patientID = isset($_GET['patient_id'])?$_GET['patient_id']:"";
        $userDetails = getUserDetails($db, $user['role'], $userID, $patientID);
        
        if($userDetails){
            extract($userDetails);
        }
        if(isset($_POST)){
            $userDetails = $_POST;
            if($userDetails){
                extract($userDetails);
                $user['email'] = $email;
                $user['role'] = $roleName;                
            }            
        }   
    if($_GET['action']=="update"){
        if($first_name=='' || $last_name==''){
            $modalTitle = "Update User";
        }else {
            $modalTitle = ucfirst(strtolower($first_name))." ".ucfirst(strtolower($last_name));
        }
    } else {
        $modalTitle = "Add New User";
    }
    
?>
<div id="manage-status-modal" class="modalBlock">
    <div class="contentBlock">
        
        <a class="close" href="<?php echo SITE_URL."/user-management.php"; ?>">X</a>        
        <h5 class="providersTitle"><?php echo $modalTitle; ?></h5>
        <div class="content">
            <?php if($message!=""){ ?>
                <div class="error text-center" id="message"><?php echo $message; ?></div>
            <?php } ?>
            <form id="userForm" action="" method="POST" enctype="multipart/form-data"> 
            <div class="row">                
                <input type="hidden" name="Guid_user" value="<?php echo isset($user['Guid_user'])?$user['Guid_user']:''; ?>" />
                <div class="col-md-12">                   
                    <div class="f2 <?php echo ($first_name!="")?"valid show-label":"";?>">
                        <label class="dynamic" for="form_first_name"><span>First Name</span></label>
                        <div class="group">
                            <input autocomplete="off" name="first_name" value="<?php echo $first_name; ?>" type="text" class="form-control" id="form_first_name" placeholder="First Name">
                            <p class="f_status">
                                <span class="status_icons"><strong>*</strong></span>
                            </p>
                        </div>
                    </div>
                    <div class="f2 <?php echo ($last_name!="")?"valid show-label":"";?>">
                        <label class="dynamic" for="form_last_name"><span>Last Name</span></label>
                        <div class="group">
                            <input autocomplete="off" name="last_name" value="<?php echo $last_name; ?>" type="text" class="form-control" id="form_last_name" placeholder="Last Name">
                            <p class="f_status">
                                <span class="status_icons"><strong>*</strong></span>
                            </p>
                        </div>
                    </div>
                    <?php 
                    $rolesArr = array('Admin', 'Sales Rep', 'Sales Manager', 'Patient', 'MDL Patient');
                    if( $_GET['action']=='add' || in_array($user['role'], $rolesArr)) { ?>
                    <div class="f2 required <?php echo ($user['role']!="")?"valid show-label":"";?>">
                        <label class="dynamic" for="reason_not"><span>User Role</span></label>
                        <div class="group">
                            <?php $selUserRole = isset($_POST['user_type'])?$_POST['user_type']:$user['role'];?>
                            <select required id="user_type" name="Guid_role" class="<?php echo ($user['role']=="")?'no-selection':''; ?> ">
                                <option value="">User Role</option>
                                <?php foreach ($allRoles as $role){ ?>
                                    <option <?php echo ($selUserRole==$role['role']) ? " selected": ""; ?> value="<?php echo $role['Guid_role']; ?>"><?php echo $role['role']; ?></option>   
                                <?php } ?>
                            </select>
                            <p class="f_status">
                                <span class="status_icons"><strong></strong></span>
                            </p>
                        </div>
                    </div>
                    <?php } else { ?>
                    <div class="f2 required <?php echo ($user['role']!="")?"valid show-label":"";?>">
                        <label class="dynamic" for="reason_not"><span>User Role</span></label>
                        <div class="group">
                            <input value="<?php echo $user['role'];?>" type="text" disabled="" class="form-control" placeholder="User Role">
                            <input name="Guid_role" value="<?php echo $user['Guid_role'];?>" type="hidden">
                            <p class="f_status">
                                <span class="status_icons"><strong></strong></span>
                            </p>                           
                        </div>
                    </div>
                    <?php }  ?>  
                    <?php if($roleInfo['role']=='Admin') { ?>
                        <div id="userCategoryDropdown">
                        <?php if($user['role']=='Sales Manager'){ ?>
                        <div class="selectCategory">
                            <label class="title"><span>Select Category</span></label>
                            <?php
                                $categories = $db->query("SELECT * FROM `tbl_mdl_category` ");      
                                $userCategories = $db->query("SELECT Guid_category FROM `tbl_mdl_category_user_link` WHERE Guid_user=:Guid_user", array('Guid_user'=>$user['Guid_user'])); 
                                $userLinks = array();
                                if(!empty($userCategories)){
                                    foreach ($userCategories as $k=>$v){
                                        $userLinks[] = $v['Guid_category'];
                                    }
                                }
                            ?>      
                            <?php foreach ($categories as $k=>$v) { ?>
                                <div>
                                    <input <?php if(in_array($v['Guid_category'], $userLinks)){echo "checked"; } ?> id="<?php echo $v['Guid_category']; ?>" type='checkbox' name='Guid_category[]' value="<?php echo $v['Guid_category']; ?>">
                                    <label for="<?php echo $v['Guid_category']; ?>"><?php echo $v['name']; ?> </label>
                                </div>                              
                            <?php }?>
                        </div>
                        <?php } ?>
                        </div>
                    <?php } ?>
                    <?php if( $_GET['action']=='update' && $user['role']=='Physician') { ?>
                    <div class="f2 required <?php echo ($user['account']!="")?"valid show-label":"";?>">
                        <label class="dynamic" for="form_account_id"><span>Account</span></label>
                        <div class="group">
                            <?php 
                                $accounts = $db->query("SELECT Guid_account, account, name FROM tblaccount Order BY account ASC");
                                $getAccountId = $db->row("SELECT account_id FROM tblprovider WHERE Guid_user=:Guid_user", array('Guid_user'=>$_GET['id']) );
                                $accountID = $getAccountId['account_id'];
                            ?>
                            <select required id="form_account_id" name="account_id" class="<?php echo ($accountID=="")?'no-selection':''; ?> ">
                                <option value="">Account</option>
                                <?php foreach ($accounts as $k=>$v) { ?>
                                <?php $selected = (isset($accountID) && $accountID==$v['account'])?" selected":""; ?>
                                <option <?php echo $selected; ?> value="<?php echo $v['account']; ?>"><?php echo $v['account']."-". ucwords(strtolower($v['name'])); ?></option>                                
                                <?php }?>
                            </select>
                            <p class="f_status">
                                <span class="status_icons"><strong></strong></span>
                            </p>
                        </div>
                    </div>
                    <?php } ?>
                    
                    <div class="f2 required <?php echo ($user['status']!="")?"valid show-label":"";?>">
                        <label class="dynamic" for="form_status"><span>Status</span></label>
                        <div class="group">
                            <?php $userStatus = isset($_POST['status'])?$_POST['status']:$user['status'];?>
                            <select required id="form_status" name="status" class="<?php echo ($user['status']=="")?'no-selection':''; ?> ">
                                <option value="">Status</option>
                                <option <?php echo ($userStatus=='1') ? " selected": ""; ?> value="1">Active</option>   
                                <option <?php echo ($userStatus=='0') ? " selected": ""; ?> value="0">Inactive</option>   
                            </select>
                            <p class="f_status">
                                <span class="status_icons"><strong></strong></span>
                            </p>
                        </div>
                    </div>
                    <div class="f2 required <?php echo ($user['email']!="")?"valid show-label":"";?>">
                        <label class="dynamic" for="form_email"><span>Email</span></label>
                        <div class="group">
                            <input autocomplete="off" required="" name="email" value="<?php echo $user['email']; ?>" type="text" class="form-control" id="form_email" placeholder="Email">
                            <p class="f_status">
                                <span class="status_icons"><strong>*</strong></span>
                            </p>
                        </div>
                    </div>  
                    <?php $passRequred = isset($_GET['add'])?' required':''; ?>
                    <div class="f2 <?php echo $passRequred; ?> ">
                        <label class="dynamic" for="form_password"><span>Password</span></label>
                        <div class="group">
                            <input autocomplete="off" <?php echo $passRequred; ?> name="password" type="password" class="form-control" id="form_password" placeholder="Password">
                            <p class="f_status">
                                <span class="status_icons"><strong>*</strong></span>
                            </p>
                        </div>
                    </div>                    
                    <?php if($user['role'] != 'Patient') { //For patients we dont have photo field in DB ?>
                    <div class="row">
                        <div class="col-md-10 padd-0">
                            <div class="f2 <?php echo ($photo_filename!="")?"valid show-label":"";?>">
                                <label class="dynamic" for="photo"><span>Photo</span></label>
                                <div class="group">
                                    <input id="file" value="<?php echo $photo_filename; ?>" name="photo_filename" class="userLogoInput form-control pT-5" type="file" placeholder="Photo"/>
                                    <p class="f_status">
                                        <span class="status_icons"><strong>*</strong></span>
                                    </p>
                                </div>
                            </div>                    
                        </div>
                        <?php $image = (!isset($photo_filename) || $photo_filename=="")?"/assets/images/default.png":"/images/users/".$photo_filename; ?>
                        <div id="profile-pic" class="col-md-2 padd-0 pT-15 text-center">
                            <img id="image" width="40" src="<?php echo SITE_URL.$image; ?>" />
                        </div>
                    </div>    
                    <?php } ?>
                    <?php if(in_array($user['Guid_role'], array('3','6'))){  ?>
                     <?php 
                     $checked = "";
                     if(isset($_POST['mark_as_test'])){
                         $checked = " checked";
                     } else {
                         if($user['marked_test']=='1'){
                             $checked = " checked";
                         }
                     }
                     ?>
                    <div class="row">
                        <div class="col-md-12">
                            <input <?php echo $checked; ?> <?php ?> id="form_show-tests" name="mark_as_test" value="1" type="checkbox">
                            <label for="show-tests">Mark As Test</label>   
                        </div>
                    </div>
                    <?php }  ?>                   
                    
                </div>                
            </div>
            <div class="row actionButtons">
                <div class="col-md-6 col-md-offset-3 pT-20">
                    <button name="save_user" type="submit" class="button btn-inline" style = "margin: auto; display: block !important;">Save</button>
                </div>
            </div>
            
        </form>   
        </div>
    </div>    
</div>
<?php } ?>

<!-- Unlock user and show logs modal Box -->
<div id="login-attempt-log-box" class="modalBlock">
    <div class="contentBlock">
        <a class="close">X</a>        
        <h2 class="text-center">Login Attempts</h2>
        <h2 id="locked-user-email" class="pB-10"></h2>       
        <table class="table">
            <thead>
                <tr>
                    <th>IP</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody id="login-attempt-log-content">                
            </tbody>
        </table>
        <div class="actions text-center">
            <button id="unlock-user" class="btn btn-primary">Unlock User</button>
        </div>
    </div>
</div>

<?php require_once('scripts.php');?>
<script type="text/javascript">  
    
        var table = $('#dataTable').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            //searching: false,
            //lengthChange: false,
            "pageLength": 25,
            "order": [[ 0, "desc" ]],
            "aoColumnDefs": [
              { 
                  
                  "bSortable": false, 
                  "aTargets": [ 5,6 ] } 
            ]
        });   
 
</script>
<?php require_once('footer.php');?>