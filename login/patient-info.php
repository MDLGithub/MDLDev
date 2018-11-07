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
//check if patient (the same as Guid_user) empty 
if(!isset($_GET['patient']) || $_GET['patient']==''){
    Leave(SITE_URL);
}

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
    
    $sqlQualify = "SELECT q.Guid_qualify,q.Guid_user,q.insurance,
                    q.other_insurance,q.account_number,q.Date_created as qDate,
                    q.provider_id, q.deviceid, q.source, 
                    CONCAT(prov.first_name,' ',prov.last_name) provider, prov.title,
                    p.*, aes_decrypt(firstname_enc, 'F1rstn@m3@_%') as firstname, 
                    aes_decrypt(lastname_enc, 'L@stn@m3&%#') as lastname, 
                    u.email, u.marked_test ";
    
    if(isset($_GET['incomplete'])){
        $sqlQualify .= "FROM tblqualify q ";
    } else {
        $sqlQualify .= "FROM tbl_ss_qualify q ";
    }
    $sqlQualify .= "LEFT JOIN tblpatient p ON q.Guid_user = p.Guid_user
                    LEFT JOIN tbluser u ON q.Guid_user = u.Guid_user 
                    LEFT JOIN tblprovider prov ON prov.Guid_provider = q.provider_id
                    WHERE q.Guid_user=:Guid_user ";
    if($role == 'Sales Rep'){
        $salesrepAccountIDs = getSalesrepAccounts($db, $userID);        
        $sqlQualify .= " AND q.account_number IN (" . $salesrepAccountIDs . ") ";
    }
    if($role=='Physician'){
        $physicianInfo = $db->row('SELECT account_id FROM tblprovider WHERE Guid_user='.$userID);
        $account_id = $physicianInfo['account_id']; 
        $sqlQualify .= " AND q.account_number IN (" . $account_id . ")";
    }
    $sqlQualify .= "ORDER BY q.`Date_created` DESC LIMIT 1";
    $qualifyResult = $db->row($sqlQualify, array('Guid_user'=>$Guid_user));  

    $patientData = $qualifyResult;
    unset($patientData['firstname_enc']);
    unset($patientData['lastname_enc']);

    $patientInfo = json_encode($patientData);

    //If one is a physician or sales rep, 
    //should not be able to see other patients that they are not allowed to
    if(!$qualifyResult){
        Leave(SITE_URL);
    }
        
    $mdlInfoQ = "SELECT * FROM tbl_mdl_number WHERE Guid_user=:Guid_user";
    $mdlInfo = $db->row($mdlInfoQ, array('Guid_user'=>$Guid_user));
    
    $Guid_qualify = $qualifyResult['Guid_qualify'];
    
    $sqlSSQualify = "SELECT ssq.* FROM tbl_ss_qualify ssq WHERE ssq.Guid_qualify=:Guid_qualify  ORDER BY Date_created DESC";
    $ssQualifyResult = $db->query($sqlSSQualify, array('Guid_qualify'=>$Guid_qualify));
    
    $errorMsgMdlStats = "";
    if(isset($_POST['save'])){
       
        $numSize = strlen($_POST['mdl_number']);
        if(isset($_POST['mdl_number'])&&$_POST['mdl_number']!=""){
            if(!isset($_POST['mark_as_test'])){
                if(isset($_POST['mdl_number']) && $numSize != 7){
                    $isValid = false;
                    $errorMsgMdlStats .= "MDL# must contain 7 digits only <br/>";
                }
            }
        }
        if($isValid){
            
            //total_deductible
            if(isset($_POST['total_deductible']) && $_POST['total_deductible']!=""){                
                updateTable($db, 'tblpatient', array('total_deductible'=> $_POST['total_deductible']), array('Guid_user'=>$_GET['patient']));
            }
            
            //test kit
            if(isset($_POST['test_kit'])){  
                updateTable($db,'tblpatient', array('test_kit'=>'1'), array('Guid_user'=>$_GET['patient']));
            } else {
                updateTable($db,'tblpatient', array('test_kit'=>'0'), array('Guid_user'=>$_GET['patient']));
            }
            
            //mark user as a test            
            if(isset($_POST['mark_as_test'])){  
                updateTable($db,'tbluser', array('marked_test'=>'1'), array('Guid_user'=>$_GET['patient']));
            } else {
                updateTable($db,'tbluser', array('marked_test'=>'0'), array('Guid_user'=>$_GET['patient']));
            }           

            //Update MDL# info
            if(isset($_POST['mdl_number'])){
                $mdlNumberData['mdl_number']=$_POST['mdl_number'];
            }
            if(isset($_POST['comment'])){
                $mdlNumberData['comment']= escape(CleanXSS($_POST['comment']));
            }
            $mdlNumberData['Guid_user'] = $_GET['patient'];
            if(!empty($mdlInfo)){//update existing mdl number info
                $whereMdlNum = array('Guid_user'=>$_GET['patient']); 
                if($mdlNumberData){
                   updateTable($db, 'tbl_mdl_number', $mdlNumberData, $whereMdlNum); 
                }
            } else {//insert mdl number info                
                if($mdlNumberData){                    
                   insertIntoTable($db, 'tbl_mdl_number', $mdlNumberData); 
                }
            }           
           
            //add deductable log 
            if(isset($_POST['deductableAdd']) && !empty($_POST['deductableAdd'])){
                $dedData = $_POST['deductableAdd'];                
                $size = count($dedData['date_checked']);
                for($i=0; $i<$size; $i++){                
                    $date_checked = ($dedData['date_checked'][$i] != "")?date('Y-m-d h:i:s', strtotime($dedData['date_checked'][$i])):"";
                    $dataDeductable = array(
                        'Guid_user'=>$_GET['patient'],
                        'date_checked'=>$date_checked,
                        'checked_by'=>$dedData['checked_by'][$i],
                        'deductable'=>$dedData['deductable'][$i]
                    );  
                    insertIntoTable($db, 'tbl_deductable_log', $dataDeductable);
                }
            }   
            //update deductable log 
            if(isset($_POST['deductableEdit']) && !empty($_POST['deductableEdit'])){
                $deductables = $_POST['deductableEdit'];        
                foreach ($deductables as $key => $val){
                    $whereDeductable = array('Guid_deductable'=>$key);
                    $date_checked = ($val['date_checked'] != "")?date('Y-m-d h:i:s', strtotime($val['date_checked'])):"";
                    $dataDeductable = array(
                        'date_checked'=>$date_checked,
                        'checked_by'=>$val['checked_by'],
                        'deductable'=>$val['deductable']
                    ); 
                    $updateReveue = updateTable($db, 'tbl_deductable_log', $dataDeductable, $whereDeductable);            
                }
            } 
                        
            
            $url=$patientInfoUrl."&u";
            Leave($url);
        }
    }
    //delete deductible log row 
    if(isset($_GET['delete-deductible']) && $_GET['delete-deductible']!="" && $role=='Admin'){
        deleteByField($db,'tbl_deductable_log', 'Guid_deductable', $_GET['delete-deductible']);        
        Leave($patientInfoUrl);
    }
    //delete revenue row
    if(isset($_GET['delete-revenue']) && $_GET['delete-revenue']!="" && $role=='Admin'){
        deleteByField($db,'tbl_revenue', 'Guid_revenue', $_GET['delete-revenue']);
        Leave($patientInfoUrl);
    }
    //delete status log row and update lats status log id in patients table
    if(isset($_GET['delete-status-log']) && $_GET['delete-status-log']!="" && $role=='Admin'){
        $Guid_patient = $qualifyResult['Guid_patient'];        
        deleteByField($db,'tbl_mdl_status_log', 'Log_group', $_GET['group']);
        updateCurrentStatusID($db, $Guid_patient);
        Leave($patientInfoUrl);
    }
    //delete note log
    if(isset($_GET['delete-note-log']) && $_GET['delete-note-log']!="" && $role=='Admin'){
        deleteByField($db,'tbl_mdl_note', 'Guid_note', $_GET['delete-note-log']);
        Leave($patientInfoUrl);
    }
  
 } ?>


<?php 
    if(isset($qualifyResult['account_number'])&&$qualifyResult['account_number']!=""){
        $accountQ = "SELECT a.Guid_account, a.account, a.name AS account_name, "
                    . "sr.Guid_salesrep, sr.first_name AS salesrep_fname, sr.last_name AS salesrep_lname, CONCAT(sr.first_name, ' ', sr.last_name) AS salesrep_name "
                    . "FROM tblaccount a "
                    . "LEFT JOIN tblaccountrep ar ON a.Guid_account=ar.Guid_account "
                    . "LEFT JOIN tblsalesrep sr ON ar.Guid_salesrep = sr.Guid_salesrep "
                    . "WHERE a.account = '" . $qualifyResult['account_number'] . "'";
        $accountInfo = $db->row($accountQ);        
    } else {
        $accountInfo = FALSE;
    }
?>

<link rel="stylesheet" href="assets/css/brca_forms.css">
<script src="assets/js/brca_forms.js"></script>

<?php require_once 'navbar.php'; ?> 

<main class="full-width">
    <input type="hidden" id="guid_patient" />
    <input type="hidden" id="post" value='<?php echo $patientInfo; ?>' />
        <?php 
        $thisMessage = "";
        if(isset($_GET['u']) || isset($_GET['i']) ){ 
                $thisMessage = "Changes have been saved";
        }
        ?>
        <?php if($thisMessage != ""){ ?>
        <section id="msg_display" class="show success">
            <h4><?php echo $thisMessage;?></h4>
        </section>
        <?php } ?>
    <div class="box full visible">
        <?php if($dataViewAccess){ ?>
        
        <section id="palette_top">
            <h4>                
                <ol class="breadcrumb">
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="active">Patient Info</li>                   
                </ol>                
            </h4>
            <a href="<?php echo SITE_URL; ?>/patient-info.php?logout=1" name="log_out" class="button red back logout"></a>
            <a href="<?php echo SITE_URL; ?>/dashboard2.php" class="button homeIcon"></a>
            <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit smaller_button"><strong>View Questionnaire</strong></a>
        </section>

        <div id="app_data" class="scroller">
            <div class="row" id="patient-info-box">   
                <div class="col-md-12">
                <?php if(isset($message)){ ?>
                <div class="error-text"><?php echo $message; ?></div>
                <?php } ?>
               
                <h2 class="text-center"><?php echo ucfirst(strtolower($qualifyResult['firstname']))." ".formatLastName($qualifyResult['lastname']);?></h2>
                <a class="patient_forms">
                    <img src="./images/icon_forms.png" />
                    <p>Forms</p>
                </a>
                <div class="row">
                     <div id="message" class="error-text text-center">
                        <?php if($errorMsgMdlStats){ ?>   
                            <!--Form Error messages go here-->
                            <?php echo $errorMsgMdlStats; ?>
                        <?php } ?>
                    </div>
                    <div id="specimenRadioBox" class="<?php echo ($qualifyResult['specimen_collected']=='Yes')?'hidden':"";?>" >
                        <h5 class="inline">Specimen collected?</h5>                        
                        <a class="yes" href="<?php echo $patientInfoUrl.'&status_log=add&specimen=yes'?>"><i class="fas fa-tint"></i> Yes</a> &nbsp;&nbsp;
                        <a class="no" href="<?php echo $patientInfoUrl.'&status_log=add&specimen=no'?>"><i class="fas fa-tint-slash"></i> No</a>
                    </div>
                    <?php if( isset($qualifyResult['specimen_collected']) && $qualifyResult['specimen_collected']!=NULL && $qualifyResult['specimen_collected']!='0' ){ ?>
                    <div id="mdlInfoBox" class="pInfo <?php echo ($qualifyResult['specimen_collected']!='Yes')?'hidden':"";?>">
                        <p>
                            <label>MDL#:</label>
                            <?php 
                            $mdlNumber = isset($_POST['mdl_number'])?$_POST['mdl_number']:$mdlInfo['mdl_number'];
                            $mdlClass = (strlen($mdlNumber)!=0 && strlen($mdlNumber)<7)?' error error-border' : '';
                            ?>
                            <?php if($role=='Admin') {?>
                            <input type="number" autocomplete="off" class="mdlnumber <?php echo $mdlClass; ?>" name="mdl_number" value="<?php echo $mdlNumber; ?>" />
                            <?php } else { 
                                echo $mdlNumber;
                            }  ?>
                        </p>
                    </div> 
                    <?php } ?>
                </div>
                
                
                <div class="row">
                    <div class="col-md-6 pInfo ">
                        <div class="row bordered">
                        <div class="col-md-11">
                                <p><label>Date of Birth: </label><?php echo ($qualifyResult['dob']!="")?date("n/j/Y", strtotime($qualifyResult['dob'])):""; ?></p>
                                <p><label>Email: </label><?php echo $qualifyResult['email']; ?></p>
                                <p class="capitalize"><label>Insurance: </label><?php echo $qualifyResult['insurance'];
                                            if($qualifyResult['other_insurance']!="" && $qualifyResult['other_insurance']!="Other"){
                                                echo " (".$qualifyResult['other_insurance'].")";
                                            }?>                                  
                                </p>
                                <p><label>Account: </label><a href="<?php echo SITE_URL.'/accounts.php?account_id='.$accountInfo['Guid_account']; ?>"><?php echo $qualifyResult['account_number']; ?></a>
                                    <?php
                                        if($accountInfo['account_name']!=""){
                                            echo " - ". ucwords(strtolower($accountInfo['account_name']));
                                        }
                                    ?>
                                </p>
                                <p><label>Genetic Consultant: </label><?php echo $accountInfo['salesrep_name']; ?></p>

                                <p><label>Health Care Providers: </label><?php echo $qualifyResult['provider']; if($qualifyResult['title']!=''){ echo ", ".$qualifyResult['title']; } ?>
                                <p>
                                    <label>Event: </label><?php echo $qualifyResult['source']; ?> 
                                </p>
                                
                                <input type="hidden" value="<?php echo $qualifyResult['provider_id']; ?>" />
                                <input type="hidden" value="<?php echo $qualifyResult['deviceid']; ?>" />
                            </div>
                        
                            <div class="col-md-1">
                                <a title="Edit Patient Info" href="<?php echo $patientInfoUrl."&edit_patient_info=1";?>">
                                    <span class="fas fa-cogs fs-20" aria-hidden="true"></span>
                                </a>
                            </div>
                        </div>
                    </div> 

                                                
                            
                    <div class="col-md-6 pB-30">                    
                        <div class="row">
                            

                            <div id="statusLogs"  class="col-md-12 clearfix padd-0">
                                <h5 class="notes">
                                    Notes:                                    
                                    <a title="Add Note" class="pull-right" href="<?php echo $patientInfoUrl."&note_log=add";?>">
                                        <span class="fas fa-plus-circle" aria-hidden="true"></span>  Add
                                    </a>                                     
                                </h5>
                                <table class="table valignTop">
                                    <thead>
                                        <th>Date</th>
                                        <th>Category &nbsp;&nbsp; 
                                            <?php if($role=='Admin'){ ?>
                                            <a title="Edit Statuses"  href="<?php echo $patientInfoUrl.'&manage_note_category=edit'; ?>" >
                                                <span class="fas fa-pencil-alt" aria-hidden="true"></span>
                                            </a>
                                            <a title="Add New Status"  href="<?php echo $patientInfoUrl.'&manage_note_category=add'; ?>" >
                                                <span class="fas fa-plus-circle" aria-hidden="true"></span> 
                                            </a>
                                            <?php } ?>
                                        </th>
                                        <th>Recorded By</th>
                                        <th>Comment</th>
                                        <?php if($role=='Admin'){ ?><th class="text-center wh-100">Action</th><?php } ?>
                                    </thead>
                                    <tbody>
                                        <?php //note_id
                                            $nQ =  "SELECT DISTINCT n.*, AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%') as firstname, aes_decrypt(p.lastname_enc, 'L@stn@m3&%#') as lastname, cat.name AS category 
                                                    FROM `tbl_mdl_note` n
                                                    LEFT JOIN `tblpatient` p ON n.Guid_user=p.Guid_user
                                                    LEFT JOIN `tbl_mdl_note_category` cat ON n.Guid_note_category=cat.Guid_note_category
                                                    WHERE n.Guid_user=:Guid_user"; 
                                            $notes = $db->query($nQ, array('Guid_user'=>$_GET['patient']));
                                            //var_dump($nQ);
                                            foreach ($notes as $k=>$v) { 
                                                $userInfo = getUserFullInfo($db, $v['Recorded_by']);
                                        ?>
                                            <tr>
                                                <td><?php echo date("n/j/Y", strtotime($v['Date'])); ?></td>
                                                <td><?php echo $v['category']; ?></td>
                                                <td><?php echo $userInfo['first_name']." ".$userInfo['last_name']; ?></td>
                                                <td><?php echo $v['Comment']; ?></td>                                                   
                                                <?php if($role=='Admin'){ ?>
                                                    <td class="text-center">
                                                        <div class="action-btns">
                                                            <a href="<?php echo $patientInfoUrl."&note_log=edit&note_id=".$v['Guid_note'];?>" class="">
                                                                <span class="fas fa-pencil-alt"></span>
                                                            </a>
                                                            <a href="<?php echo $patientInfoUrl.'&delete-note-log='.$v['Guid_note']; ?>" onclick="javascript:confirmationDeleteNooteLog($(this));return false;" class="color-red">
                                                                <span class="far fa-trash-alt"></span> 
                                                            </a>   
                                                        </div>
                                                    </td>   
                                                <?php } ?>
                                            </tr>
                                        <?php } ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                 
                <form id="mdlInfoForm" action="" method="POST" >
                    <?php $mdlNumber = isset($_POST['mdl_number'])?$_POST['mdl_number']:$mdlInfo['mdl_number']; ?>
                    <input type="hidden" name="save" value="1"/>
                    <input type="hidden" name="account" value="<?php echo isset($_GET['account'])?$_GET['account']:(isset($mdlInfo['account'])?$mdlInfo['account']:""); ?>"/>
                    <input type="hidden" name="qDate" value="<?php echo $qualifyResult['qDate']; ?>"/>
                    <input type="hidden" name="Guid_qualify" value="<?php echo $qualifyResult['Guid_qualify']; ?>"/>
                    <input type="hidden"  name="mdl_number" id="mdlNumber" value="<?php echo $mdlNumber; ?>" />     
                    <div class="row pT-30">
                        <div id="questionaryInfo"  class="col-md-6">
                            <h5>
                                Submission History: 
                                <!-- <a class="pull-right" id="add-deductable-log">
                                    <span class="fas fa-plus-circle" aria-hidden="true"></span>  Add
                                </a>-->
                            </h5>
                            <table class="table">
                            <thead>
                                <th>Print</th>
                                <th>Qualified</th>
                                <th>Date</th>
                                <th>Clinical History</th>
                            </thead>
                            <tbody>
                                <?php 
                                    foreach ($ssQualifyResult as $k=>$v){ 
                                        $Guid_qualify = $v['Guid_qualify'];
                                        $Date_created = $v['Date_created'];
                                        //$qFam = $db->query("SELECT * FROM `tblqualifyfam` WHERE `Guid_qualify`=:Guid_qualify AND `Date_created`=:Date_created", array('Guid_qualify'=>$Guid_qualify, 'Date_created'=>$Date_created));
                                        //$queryPers = "SELECT * FROM `tbl_ss_qualifypers` WHERE `Guid_qualify`=:Guid_qualify AND `Date_created`=:Date_created";
                                        //$qPers = $db->query($queryPers, array('Guid_qualify'=>$Guid_qualify, 'Date_created'=>$Date_created));
                                        $qAns = $db->query("SELECT * FROM `tbl_ss_qualifyans` WHERE `Guid_qualify`=:Guid_qualify AND `Date_created`=:Date_created", array('Guid_qualify'=>$Guid_qualify, 'Date_created'=>$Date_created));
                                        $qualifyedClass = "";
                                        if($v['qualified'] == 'No'){
                                            $qualifyedClass = "mn no";
                                        } elseif ($v['qualified'] == 'Yes') {
                                            $qualifyedClass = "mn yes";
                                        }
                                       
                                    ?>
                                    <tr>
                                        <td>
                                            <?php if($v['qualified'] == 'Unknown'){ ?>
                                                <span class="not-printable" ></span>
                                            <?php } else { ?>
                                                <button class="print report" data-selected_date="<?php echo $v['Date_created']; ?>" data-selected_questionnaire="<?php echo $v['Guid_qualify']; ?>" ></button>
                                            <?php } ?>
                                        </td>
                                        <td class="<?php echo $qualifyedClass;?>"><?php echo $v['qualified']; ?></td>
                                        <td><?php echo date("n/j/Y h:i:s A", strtotime($v['Date_created'])); ?></td> 
                                        <td>
                                            <p>
                                                <?php 
                                                $personal = "<label>Personal: </label> "; 
                                                $family = "<label>Family: </label> ";
                                                if(!empty($qAns)){
                                                    $ansPersonal = "";
                                                    $ansFam = "";
                                                    foreach ($qAns as $k=>$v) {
                                                        $ansPersonalType =  $v['cancer_personal'];                                                        
                                                        if(strpos(trim($ansPersonalType), ' ') == false){
                                                            $ansPersonalType .=  " Cancer";
                                                        }
                                                        $ansPersonal .= $ansPersonalType;
                                                        if($v['age_personal'] && $v['age_personal']!=""){
                                                            $ansPersonal .= " (Age ". $v['age_personal']."); ";
                                                        }
                                                        if($v['age_personal']==""&&$v['age_personal']==""){
                                                            $ansPersonal = "No Cancer History";
                                                        }
                                                        
                                                        
                                                        $ansFamType =  $v['cancer_type'];
                                                        if(strpos(trim($ansFamType), ' ') == false){
                                                            $ansFamType .=  " Cancer";
                                                        }
                                                        $ansFam .= $v['relative'].", ".$ansFamType;
                                                        if($v['age_relative'] && $v['age_relative']!=""){
                                                            $ansFam .= " (Age ". $v['age_relative']."); ";
                                                        }
                                                        if($v['cancer_type']==""&&$v['relative']==""){
                                                            $ansFam = "No Cancer History";
                                                        }  
                                                       
                                                    } 
                                                    $ansPersonal = rtrim($ansPersonal,'; ');
                                                    $ansFam = rtrim($ansFam,'; ');
                                                    echo "<p>".$personal.$ansPersonal."</p>";                                                    
                                                    echo "<p>".$family.$ansFam."</p>";                                                    
                                                } else {
                                                    echo "<p>".$personal." No Cancer History</p>";                                                    
                                                    echo "<p>".$family." No Cancer History</p>"; 
                                                }
                                                ?>
                                               
                                            </p>
                                            
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        </div>
                        <div id="statusLogs"  class="col-md-6">
                            <h5>
                                Test Status Change Log:
                                <?php if(isset($qualifyResult['specimen_collected']) && $qualifyResult['specimen_collected']=='Yes'){ ?>
                                <?php if($role=='Admin'){ ?>
                                <a title="Add New Test Status Log" class="pull-right" href="<?php echo $patientInfoUrl."&status_log=add";?>">
                                    <span class="fas fa-plus-circle" aria-hidden="true"></span>  Add
                                </a>
                                <?php } ?>
                                <?php } ?>
                            </h5>
                            <table class="table">
                            <thead>
                                <th>Date</th>
                                <th>Statuses &nbsp;&nbsp; 
                                    <?php if($role=='Admin'){ ?>
                                    <a title="Edit Statuses"  href="<?php echo $patientInfoUrl.'&manage_status=edit'; ?>" >
                                        <span class="fas fa-pencil-alt" aria-hidden="true"></span>
                                    </a>
                                    <a title="Add New Status"  href="<?php echo $patientInfoUrl.'&manage_status=add'; ?>" >
                                        <span class="fas fa-plus-circle" aria-hidden="true"></span> 
                                    </a>
                                    <?php } ?>
                                </th>
                                <?php if($role=='Admin'){ ?><th class="text-center wh-100">Action</th><?php } ?>
                            </thead>
                            <tbody>
                                <?php 
                                $patientID=$_GET['patient'];
                                $qStatusLog = 'SELECT sl.Guid_status_log,sl.Log_group, sl.Guid_user, sl.Guid_status,  '
                                            . 'DATE(sl.Date) AS logDate, s.parent_id, s.access_roles '
                                            . 'FROM tbl_mdl_status_log sl '
                                            . 'LEFT JOIN tblpatient p ON sl.Guid_patient=p.Guid_user '
                                            . 'LEFT JOIN tbl_mdl_status s ON sl.Guid_status=s.Guid_status '
                                            . 'WHERE sl.Guid_user='.$patientID.'  AND s.parent_id="0" '
                                            . 'ORDER BY logDate DESC, s.order_by DESC';
                               
                                $ststusLogs = $db->query($qStatusLog);
                                
                                foreach ($ststusLogs as $k=>$v){ 
                                   
                                    if( $role=='Admin' || (isset($v['access_roles']) && $v['access_roles']!="")){
                                        $access_roles = unserialize($v['access_roles']);
                                        if($role=='Admin' || key_exists($roleID, $access_roles)){
                                       
                                       
                                ?>
                                    <tr>
                                        <td><?php echo date("n/j/Y", strtotime($v['logDate'])); ?></td> 
                                        <td>
                                            <?php get_nested_statuses( $db, $v['Guid_status'], $v['Guid_user'], $v['Log_group'] ); ?>
					</td>   
                                        <?php if($role=='Admin'){ ?>
                                        <td class="text-center">
                                            <div class="action-btns">
                                                <a href="<?php echo $patientInfoUrl."&status_log=edit&log_id=".$v['Guid_status_log'];?>" class="">
                                                    <span class="fas fa-pencil-alt"></span>
                                                </a>
                                                <a href="<?php echo $patientInfoUrl.'&delete-status-log='.$v['Guid_status_log'].'&group='.$v['Log_group']; ?>" onclick="javascript:confirmationDeleteStatusLog($(this));return false;" class="color-red">
                                                    <span class="far fa-trash-alt"></span> 
                                                </a>   
                                            </div>
                                        </td>   
                                        <?php } ?>
                                    </tr>
                                <?php } } } ?>
                            </tbody>
                        </table>
                        </div>
                    </div>
                    <?php if($role!="Physician"){ ?>   
                    <div id="pLogs" class="row <?php echo (!$qualifyResult['specimen_collected'] || $qualifyResult['specimen_collected']=='No')?"hidden":"";?>">
                        <div id="deductable-log" class="col-md-6">
                            <?php 
                                $whereUser = array('Guid_user'=>$_GET['patient']);
                                $deductableLogs = $db->query('SELECT * FROM tbl_deductable_log WHERE Guid_user=:Guid_user', $whereUser); 
                               
                            ?>
                            <h5>
                                Deductible Log: 
                                <?php if($role=='Admin'){ ?>
                                <a class="pull-right" id="add-deductable-log">
                                    <span class="fas fa-plus-circle" aria-hidden="true"></span>  Add
                                </a>                                 
                                <?php if(!$qualifyResult['total_deductible']){ ?>
                                <a class="pull-right  mR-10" id="add-patient-deductable">
                                    <span class="fas fa-plus-circle" aria-hidden="true"></span>  Deductible
                                </a>  
                                <?php } ?>
                                <?php } ?>
                                <span id="total-deductible" class="<?php echo (!$qualifyResult['total_deductible'])?"hidden":"";?> pull-right">
                                    <?php if($role=='Admin'){ ?>
                                    $<input value="<?php echo ($qualifyResult['total_deductible']!="")?$qualifyResult['total_deductible']:""; ?>" name="total_deductible" placeholder="Total Deductible" type="number" min="0.00" step="0.01">
                                    <?php } else { ?>
                                    <?php echo "$".formatMoney($qualifyResult['total_deductible']); ?>
                                    <?php } ?>
                                </span>
                                
                            </h5>
                            <div class="deductable-form">
                                
                            </div>
                            <div id="deductable-table-form" >
                                <table id="deductable-table" class="table">
                                    <thead>
                                        <tr>
                                            <th>Date Checked</th>
                                            <th>Checked By</th>
                                            <th>Deductible $</th>
                                            <?php if($role=='Admin'){ ?><th class="text-center wh-100">Action</th><?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $dedSum = 0;
                                        foreach ($deductableLogs as $k=>$v) {
                                            if($v['deductable']!=""){
                                                $dedSum += $v['deductable'];    
                                            }
                                        ?>
                                        <tr id="<?php echo $v['Guid_deductable']; ?>">
                                            <td><span class="editable_date_checked"><?php echo (!preg_match("/0{4}/" , $v['date_checked'])) ? date('n/j/Y', strtotime($v['date_checked'])) : ""; ?></span></td>
                                            <td><span class="editable_checked_by"><?php echo $v['checked_by']; ?></span></td>
                                            <td>$<span class="editable_deductable"><?php echo formatMoney($v['deductable']); ?></span></td>
                                            <?php if($role=='Admin'){ ?>
                                            <td class="text-center">
                                                <div class="action-btns">
                                                <a data-id="<?php echo $v['Guid_deductable']; ?>" class="edit_deductable">
                                                    <span class="fas fa-pencil-alt"></span>
                                                </a>
                                                <a href="<?php echo $patientInfoUrl.'&delete-deductible='.$v['Guid_deductable']; ?>" onclick="javascript:confirmationDeleteDeductible($(this));return false;" class="color-red">
                                                    <span class="far fa-trash-alt"></span> 
                                                </a>
                                                </div>
                                            </td>
                                            <?php } ?>
                                        </tr> 
                                        <?php } ?>
                                        <tr class="priceSum">
                                        <?php if(count($deductableLogs) > 1){ ?> 
                                            <td>&nbsp;&nbsp;</td>                                
                                            <td class="text-right">Total: &nbsp;&nbsp;</td>
                                            <td class="strong">$<span><?php echo formatMoney($dedSum); ?></span></td>
                                            <td>&nbsp;&nbsp;</td>       
                                        <?php } ?>
                                        </tr> 
                                    </tbody>
                                </table>
                            </div>
                        </div>
                         
                        
                        <div id="revenue" class="col-md-6">
                            <h5>
                                Revenue:
                                <?php if($role=='Admin'){ ?>
                                <a title="Add Revenue" class="pull-right" href="<?php echo $patientInfoUrl."&add_revenue=1";?>">
                                    <span class="fas fa-plus-circle" aria-hidden="true"></span>  Add
                                </a>
                                <?php } ?>
                            </h5>
                            <div class="revenue-form"></div>
                           
                            <table id="revenue-table" class="table">
                                <thead>
                                    <tr>
                                        <th>Date Paid</th>
                                        <th>Payor</th>
                                        <th>CPT</th>
                                        <th>Amount $</th>
                                        <?php if($role=='Admin'){ ?>
                                        <th class="text-center wh-100">Action</th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $revenueQ = 'SELECT r.*, p.name AS payor, cpt.code '
                                            . 'FROM tbl_revenue r '
                                            . 'LEFT JOIN tbl_mdl_payors p ON r.Guid_payor=p.Guid_payor '
                                            . 'LEFT JOIN tbl_mdl_cpt_code cpt ON r.Guid_cpt=cpt.Guid_cpt '
                                            . 'WHERE Guid_user=:Guid_user';
                                    $revenues = $db->query($revenueQ, array('Guid_user'=>$_GET['patient']));
                                    $revSum = 0; 
                                    foreach ($revenues as $k=>$v) {
                                        if($v['amount']!=""){
                                        $revSum += $v['amount'];
                                        }
                                    ?>
                                    <tr id="<?php echo $v['Guid_revenue']; ?>">
                                        <td><?php echo (!preg_match("/0{4}/" , $v['date_paid'])) ? date('n/j/Y', strtotime($v['date_paid'])) : ""; ?></td>
                                        <td><?php echo $v['payor']; ?></td>
                                        <td><?php echo $v['code']; ?></td>
                                        <td>$<?php echo formatMoney($v['amount']); ?></td>
                                        <?php if($role=='Admin'){ ?>
                                        <td class="text-center">
                                            <div class="action-btns">
                                            <a href="<?php echo $patientInfoUrl.'&edit_revenue='.$v['Guid_revenue']; ?>" class="">
                                                <span class="fas fa-pencil-alt"></span>
                                            </a>
                                            <a href="<?php echo $patientInfoUrl.'&delete-revenue='.$v['Guid_revenue']; ?>" onclick="javascript:confirmationDeleteRevenue($(this));return false;" class="color-red">
                                                <span class="far fa-trash-alt"></span> 
                                            </a>    
                                            </div>
                                        </td>
                                        <?php } ?>
                                    </tr> 

                                    <?php } ?>     
                                    <tr class="priceSum">
                                    <?php if(count($revenues) > 1){ ?>                                     
                                        <td>&nbsp;&nbsp;</td>                                
                                        <td>&nbsp;&nbsp;</td>                                
                                        <td class="text-right">Total: &nbsp;&nbsp;</td>
                                        <td class="strong">$<span class=""><?php echo formatMoney($revSum); ?></span></td>
                                        <td>&nbsp;&nbsp;</td>       
                                    <?php } ?>
                                    </tr> 
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php } ?>       
                        
                    <div class="row actionButtons pB-30">
                        <div class="col-md-12">
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <?php if( $qualifyResult['source']=='HealthCare Fair' && ($role=='Admin' ||$role=='Sales Rep' || $role=='Sales Manager') ){ ?>
                                    <span class="pull-left markTest">                               
                                        <input id="test-kit" <?php echo $qualifyResult['test_kit']=='1'?' checked': ''; ?>  type="checkbox" name="test_kit" value="1" /> 
                                        <label for="test-kit">Test kit has been given to the patient</label>
                                    </span><br/>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">                                    
                                    <?php if($role=='Admin' ||$role=='Sales Rep' || $role=='Sales Manager' ){ ?>
                                    <span class="pull-left markTest">                               
                                        <input id="mark-as-test" <?php echo $qualifyResult['marked_test']=='1'?' checked': ''; ?>  type="checkbox" name="mark_as_test" value="1" /> 
                                        <label for="mark-as-test">Mark As Test</label>
                                    </span>
                                    <?php } ?>
                                </div>
                                <div class="col-md-4"></div>
                                <div class="col-md-4">
                                    <?php if($role=='Admin' ||$role=='Sales Rep' || $role=='Sales Manager' ){ ?>
                                        <button id="save-patient-info" name="save" type="submit" class="button btn-inline pull-right">Save</button>
                                    <?php } ?>   
                                </div>
                            </div>
                        </div>
                    </div>
                        
                </form>                
            </div>
        </div> 
            
        
          
        </div>
        <?php } else { ?>
            <p>Sorry! You don't have access to this page content. </p>
        <?php } ?>
    </div>
    <div id="admin_print"></div>
    <div id="patient_brca_forms" class="modalBlock" style="display: none;">
        <div class="contentBlock patientForms">
        <span class = "close"></span>
        <div class="container form-container" style="margin:auto"> 
            <div class = "form-row">
                <div id = "forms">
                    <h2>Forms</h2>
                </div>
                <div id = "form-details">
                    <h2>Details</h2>
                </div>
                <div class = "patient_name"><?= ucfirst(strtolower($qualifyResult['firstname']))." ".ucfirst(strtolower($qualifyResult['lastname'])) ?></div>
                <button class = "print_button button" id = "form-print"><i class="fas fa-print"></i> Print</button>
            <ul id="accordion">
              <li>
                <div id = "form-bar">
                    <h2>Patient Demographics</h2>
                </div>
                <div class = "form-info-container">
                <div class = "form-info col-md-8 patient_demographics">
                    <strong class = "fh">Patient Demographics</strong><br/>
                        <div class="f2 required col-md-6 form_field first_name">
                            <label class="dynamic" for="form_first_name"><span>First name</span></label>
                                <div class="group">
                                    <input id="form_first_name" name="form_first_name" type="text" value="<?php echo $qualifyResult['firstname'] ?>" placeholder="First name" required="">
                                    <p class="f_status">
                                        <span class="status_icons"><strong>*</strong></span>
                                    </p>
                                </div>
                        </div>
                        <div class="f2 required col-md-6 form_field last_name">
                            <label class="dynamic" for="form_last_name"><span>Last name</span></label>
                                <div class="group">
                                    <input id="form_last_name" name="form_last_name" type="text" value="<?php echo $qualifyResult['lastname'] ?>" placeholder="Last name" required="">
                                    <p class="f_status">
                                        <span class="status_icons"><strong>*</strong></span>
                                    </p>
                                </div>
                        </div>
                        <div class="f2 required col-md-6 form_field dob">
                            <label class="dynamic" for="form_dob"><span>DOB</span></label>
                                <div class="group">
                                    <input id="form_dob" name="form_dob" type="text" value="<?php echo $qualifyResult['dob'] ?>" placeholder="Date of birth" required="">
                                    <p class="f_status">
                                        <span class="status_icons"><strong>*</strong></span>
                                    </p>
                                </div>
                        </div>
                        <div class="f2 required col-md-6 form_field addr1">
                            <label class="dynamic" for="form_addr1"><span>Address line 1</span></label>
                                <div class="group">
                                    <input id="form_addr1" name="form_addr1" type="text" value="<?php echo $qualifyResult['address'] ?>" placeholder="Address line 1" required="">
                                    <p class="f_status">
                                        <span class="status_icons"><strong>*</strong></span>
                                    </p>
                                </div>
                        </div>
                        <div class="f2 required col-md-6 form_field addr2">
                            <label class="dynamic" for="form_addr2"><span>Address line 2</span></label>
                                <div class="group">
                                    <input id="form_addr2" name="form_addr2" type="text" value="" placeholder="Address line 2" required="">
                                    <p class="f_status">
                                        <span class="status_icons"><strong>*</strong></span>
                                    </p>
                                </div>
                        </div>
                        <div class="f2 required col-md-6 form_field city">
                            <label class="dynamic" for="form_city"><span>City</span></label>
                                <div class="group">
                                    <input id="form_city" name="form_city" type="text" value="<?php echo $qualifyResult['city'] ?>" placeholder="City" required="">
                                    <p class="f_status">
                                        <span class="status_icons"><strong>*</strong></span>
                                    </p>
                                </div>
                        </div>
                        <div class="f2 required col-md-6 form_field state">
                            <label class="dynamic" for="form_state"><span>State</span></label>
                                <div class="group">
                                    <input id="form_state" name="form_state" type="text" value="<?php echo $qualifyResult['state'] ?>" placeholder="State" required="">
                                    <p class="f_status">
                                        <span class="status_icons"><strong>*</strong></span>
                                    </p>
                                </div>
                        </div>
                        <div class="f2 required col-md-6 form_field zip">
                            <label class="dynamic" for="form_zip"><span>Zip</span></label>
                                <div class="group">
                                    <input id="form_zip" name="form_zip" type="text" value="<?php echo $qualifyResult['zip'] ?>" placeholder="Zip" required="">
                                    <p class="f_status">
                                        <span class="status_icons"><strong>*</strong></span>
                                    </p>
                                </div>
                        </div>
                        <div class="f2 required col-md-6 form_field phone">
                            <label class="dynamic" for="form_phone"><span>Phone</span></label>
                                <div class="group">
                                    <input id="form_phone" name="form_phone" type="text" value="<?php echo $qualifyResult['phone_number'] ?>" placeholder="Phone" required="">
                                    <p class="f_status">
                                        <span class="status_icons"><strong>*</strong></span>
                                    </p>
                                </div>
                        </div>
                        <div class="f2 required col-md-6 form_field ethnicity">
                            <label class="dynamic" for="form_ethnicity"><span>Ethnicity</span></label>
                                <div class="group">
                                    <input id="form_ethnicity" name="form_ethnicity" type="text" value="<?php ?>" placeholder="Ethnicity" required="">
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
                    <strong class = "fh">Insurance</strong><br/>
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
                    <strong class = "fh">Test</strong><br/>
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
                    <strong class = "fh">Genetic Counseling</strong><br/>
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
                    <strong class = "fh">Physician</strong><br/>
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
                <table id="dataTableHome" class="pseudo_t table without_scroll">
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
                                <input name="forms" value="test_req_form" type="checkbox" class="print1 report1" data-prinatble="0" />
                            </td>
                            <td class="left-td">
                                <a href= "./forms/BRCA_Genetic_Req_IH0119_10_2018.pdf" target="_blank">BRCA Test Requisition</a>
                            </td>
                        </tr>
                        <tr class="t_row">
                            <td class="printSelectBlock text-center">
                                <input name="forms" value="informed_consent" type="checkbox" class="print1 report1" data-prinatble="0" />
                            </td>
                            <td class="left-td">
                                <a href="./forms/BRCA_test_Consent.pdf" target="_blank">Informed consent</a>
                            </td>
                        </tr>
                        <tr class="t_row">
                            <td class="printSelectBlock text-center">
                                <input name="forms" value="prior_authorization" type="checkbox" class="print1 report1" data-prinatble="0" />
                            </td>
                            <td class="left-td">
                                <a href="./forms/Prior Authorization Reqest form.pdf" target="_blank">Prior Authorization</a>
                            </td>
                        </tr>
                        <tr class="t_row">
                            <td class="printSelectBlock text-center">
                                <input name="forms" value="genetic_counseling" type="checkbox" class="print1 report1" data-prinatble="0" />
                            </td>
                            <td class="left-td">
                                <a href="./forms/BRCA_Genetic_Counseling_Referral.pdf" target="_blank">Genetic Counseling Referral</a>
                            </td>
                        </tr>
                        <tr class="t_row">
                            <td class="printSelectBlock text-center">
                                <input name="forms" value="cancer_genetic_counseling" type="checkbox" class="print1 report1" data-prinatble="0" />
                            </td>
                            <td class="left-td">
                                <a href="./forms/Cancer-Referral-Form_9.2018.pdf" target="_blank">Cancer Genetic Counseling Referral</a>
                            </td>
                        </tr>
                        <tr class="t_row">
                            <td class="printSelectBlock text-center">
                                <input name="forms" value="aetna_precertification" type="checkbox" class="print1 report1" data-prinatble="0" />
                            </td>
                            <td class="left-td">
                                <a href="./forms/BRCA-precertification-request-form.pdf" target="_blank">Aetna Precertification Information Request</a>
                            </td>
                        </tr>
                        <tr class="t_row">
                            <td class="printSelectBlock text-center">
                                <input name="forms" disabled value="aim" type="checkbox" class="print1 report1" data-prinatble="0" />
                            </td>
                            <td class="left-td">
                                <span>AIMs Precertification</span>
                            </td>
                        </tr>
                        <tr class="t_row">
                            <td class="printSelectBlock text-center">
                                <input name="forms" disabled value="beacon" type="checkbox" class="print1 report1" data-prinatble="0" />
                            </td>
                            <td class="left-td">
                                <span>Beacon LBS</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
              	<div class = "buttons">
              	</div>
              </div>
          </div>
</main>



<?php
    if(isset($_POST['save_patient_info'])){ 
        extract($_POST);
        $userData = array();
        if(isset($_POST['email']) && $_POST['email']!=''){
            $userData['email'] = $_POST['email'];
        }
        
        $userData['Guid_role'] = '3';
        if(!empty($userData)){
            $userData['Date_modified'] = date('Y-m-d H:i:s');
            $whereUser = array('Guid_user'=>$_GET['patient']);
            //check if user exists
            $isUserExists=$db->row("SELECT * FROM tbluser WHERE Guid_user=:Guid_user", $whereUser);            
            if($isUserExists){//update user                
                $updateUser = updateTable($db, 'tbluser', $userData, $whereUser);                    
            } else { //insert user
                $userData['user_type'] = 'patient';
                $userData['Date_created'] = date('Y-m-d H:i:s');
                $userData['Guid_role']='3';
                $inserUser = insertIntoTable($db, 'tbluser', $userData);
            }            
        }

        if(isset($_POST['dob']) && $_POST['dob']!=""){
            $dob= date('Y-m-d h:i:s', strtotime($_POST['dob']));
            updateTable($db, 'tblpatient', array('dob'=>$dob), array('Guid_user'=>$_GET['patient']));
        }

         
        
        $dateCreated = $_POST['qDate'];

        if(isset($_POST['incomplate'])){  
            $updateQualify = " UPDATE `tblqualify` "
                    . "SET `source`=:source,account_number=:account_number, "
                    . "insurance=:insurance, other_insurance=:other_insurance,"
                    . "provider_id=:provider_id "
                    . "WHERE `Date_created`=:Date_created AND Guid_qualify=:Guid_qualify";                    
        }else{
            $updateQualify = " UPDATE `tbl_ss_qualify` "
                    . "SET `source`=:source,account_number=:account_number, "
                    . "insurance=:insurance, other_insurance=:other_insurance,"
                    . "provider_id=:provider_id "
                    . "WHERE `Date_created`=:Date_created AND Guid_qualify=:Guid_qualify";
        }
        $db->query( $updateQualify, array(
                                        'source'=>$source ,
                                        'account_number'=>$account_number,
                                        'insurance'=>$insurance,
                                        'other_insurance'=>$other_insurance,
                                        'provider_id'=>$provider_id,
                                        'Guid_qualify'=>$Guid_qualify, 
                                        'Date_created'=>$dateCreated
                                    ));
        
        
       Leave($patientInfoUrl.'&u');
        
    }
?>
<?php if( (isset($_GET['edit_patient_info'])) && $_GET['edit_patient_info']=="1" ){ ?>
<div id="manage-status-modal" class="modalBlock ">
    <div class="contentBlock patientInfo">
        <a class="close" href="<?php echo $patientInfoUrl; ?>">X</a> 
        <h5 class="title">
            Update Patient Info
        </h5>
        <div class="content">
            <form class="paientInfo" action="" method="POST">
               
               <input type="hidden" name="qDate" value="<?php echo $qualifyResult['qDate']; ?>"/>
               <input type="hidden" name="Guid_qualify" value="<?php echo $qualifyResult['Guid_qualify']; ?>"/>

                <p>
                    <label>Date of Birth: </label>
                    <input type="text" name="dob" class="datepicker" value="<?php echo ($qualifyResult['dob']!="")?date("n/j/Y", strtotime($qualifyResult['dob'])):""; ?>" autocomplete="off" />
                </p>
                <p><label>Email: </label>
                    <input type="email" name="email" value="<?php echo $qualifyResult['email']; ?>" autocomplete="off"/> </p>
                <p class="capitalize"><label>Insurance: </label>
                    <input type="text" name="insurance" value="<?php echo $qualifyResult['insurance']; ?>" autocomplete="off" />
                </p>
                <p class="capitalize"><label>Other Insurance: </label>
                    <input type="text" name="other_insurance" value="<?php echo $qualifyResult['other_insurance']; ?>" autocomplete="off" />
                </p>
                <?php if($role!='Physician'){  ?>
                <p>
                    <label>Account: </label>                    
                    <?php 
                    if (($role == "Sales Rep") || ((isset($_POST['salesrep']) && strlen($_POST['salesrep']) && (!isset($_POST['clear']))))) {
                        $query = "SELECT 
                        tblaccount.*                   
                        FROM tblsalesrep 
                        LEFT JOIN `tblaccountrep` ON  tblsalesrep.Guid_salesrep = tblaccountrep.Guid_salesrep
                        LEFT JOIN `tblaccount` ON tblaccountrep.Guid_account = tblaccount.Guid_account                    
                        WHERE tblsalesrep.Guid_user=";

                        if (isset($_POST['salesrep']) && strlen($_POST['salesrep'])) {
                            $query .= $_POST['salesrep'];
                        } else {
                            $query .= $_SESSION['user']['id'];
                        }
                    } else {
                        $query = "SELECT * FROM tblaccount";
                    }
                    $query .= " ORDER BY account";
                    $accounts = $db->query($query);                   
                    ?>
                    <select class="patientAccount" name="account_number">
                        <option value="">Select Account</option>
                        <?php 
                        
                        foreach ($accounts as $k=>$v){ 
                        $selected = $qualifyResult['account_number']==$v['account'] ? ' selected' : '';
                        ?>
                        <option <?php echo $selected; ?> value="<?php echo $v['account']; ?>" ><?php echo $v['account'] .'-'. ucwords(strtolower($v['name'])); ?></option>
                        <?php } ?>
                    </select>  
                </p>
                <?php } else { ?>
                    <input type="hidden" name="account_number" value="<?php echo $qualifyResult['account_number']; ?>" />
                <?php } ?>
                <p>
                    <label>Health Care Provider: </label>
                    <select id="pInfoAccountProviders" name="provider_id">
                        <option value="">Select Provider</option>
                        <?php 
                        //$tblproviders = $db->query('SELECT * FROM tblprovider WHERE account_id='.$qualifyResult['account_number']);
                        if(isset($qualifyResult['account_number'])&&$qualifyResult['account_number']!=""){
                        $tblproviders = $db->query('SELECT pr.* FROM tblprovider pr '                                
                                                . 'LEFT JOIN tbluser u ON u.`Guid_user`=pr.`Guid_user`'
                                                . ' WHERE account_id='.$qualifyResult['account_number'].' AND u.status="1" ');
                        foreach ($tblproviders as $k=>$v){ 
                        $selected = $qualifyResult['provider_id']==$v['Guid_provider'] ? ' selected' : '';
                        ?>
                        <option <?php echo $selected; ?> value="<?php echo $v['Guid_provider']; ?>" ><?php echo $v['first_name'].' '.$v['last_name']; ?></option>
                        <?php }} ?>
                    </select> 
                </p>
                
                
                <?php if($role!='Physician'){  ?>
                <p>
                    <label>Event: </label>
                    <select name="source">
                        <option value="">Select Location</option>
                        <?php 
                        $sources = $db->selectAll('tblsource', ' ORDER BY `description` ASC');
                        foreach ($sources as $k=>$v){ 
                        $selected = $qualifyResult['source']==$v['description'] ? ' selected' : '';
                        ?>
                        <option <?php echo $selected; ?> value="<?php echo $v['description']; ?>" ><?php echo $v['description']; ?></option>
                        <?php } ?>
                    </select>   
                </p>
                <?php } else { ?>
                    <input type="hidden" name="source" value="<?php echo $qualifyResult['source']; ?>" />
                <?php }  ?>
                    
                <div class="text-right pT-10">
                    <button class="button btn-inline" name="save_patient_info" type="submit">Save</button>
                    <a href="<?php echo $patientInfoUrl; ?>" class="btn-inline btn-cancel">Cancel</a>                   
                </div>                
            </form> 
        </div>
    </div>
</div>
<?php } ?>

    
    
<?php
    if(isset($_POST['manage_revenue'])){ 
        $date_paid = ($_POST['date_paid'] != "")?date('Y-m-d h:i:s', strtotime($_POST['date_paid'])):"";
        $revenueData=array(
            'Guid_user'=>$_POST['Guid_user'], 
            'Guid_payor'=>$_POST['Guid_payor'], 
            'Guid_cpt'=>$_POST['Guid_cpt'],
            'amount'=>$_POST['amount'],
            'date_paid'=>$date_paid
        );
        if(isset($_POST['Guid_revenue']) && $_POST['Guid_revenue']!=""){ //update
            $where = array('Guid_revenue'=>$_POST['Guid_revenue']);
            $updateRevenue = updateTable($db, 'tbl_revenue', $revenueData, $where);
            Leave($patientInfoUrl);
        } else { //insert            
            $insertRevenue = insertIntoTable($db, 'tbl_revenue', $revenueData);
            if($insertRevenue['insertID']!=""){
                Leave($patientInfoUrl);
            }
        }
    } 
    if(isset($_GET['edit_revenue'])&&$_GET['edit_revenue']!=""){
       $getRevenueQ = 'SELECT r.*, p.name AS payor, cpt.code '
                    . 'FROM tbl_revenue r '
                    . 'LEFT JOIN tbl_mdl_payors p ON r.Guid_payor=p.Guid_payor '
                    . 'LEFT JOIN tbl_mdl_cpt_code cpt ON r.Guid_cpt=cpt.Guid_cpt '
                    . 'WHERE Guid_revenue=:Guid_revenue';
        $revenueRow = $db->row($getRevenueQ, array('Guid_revenue'=>$_GET['edit_revenue']));
        extract($revenueRow);
    }
?>
<?php if($role=='Admin' && (isset($_GET['add_revenue']) || (isset($_GET['edit_revenue'])&& $_GET['edit_revenue']!="")) ){ ?>
<div id="manage-status-modal" class="modalBlock ">
    <div class="contentBlock">
        <a class="close" href="<?php echo $patientInfoUrl; ?>">X</a>        
        
        <h5 class="title">
            Add Revenue
        </h5>
        <div class="content">
            <!--<div class="status-list">list here...</div>-->
            <div class="add-status-form">
                <form action="" method="POST">
                <h4 class="text-center"></h4>
                <?php if(isset($message)){ ?>
                    <div class="text-center success-text"><?php echo $message; ?></div>
                <?php } ?>
                <input type="hidden" name="Guid_user" value="<?php echo $_GET['patient']; ?>" />
                <input type="hidden" name="Guid_revenue" value="<?php echo (isset($Guid_revenue)&&$Guid_revenue!="")?$Guid_revenue:""; ?>" />
                <div class="">
                    <input value="<?php echo (isset($date_paid)&&$date_paid!="")?date("n/j/Y",strtotime($date_paid)):"" ?>" class="datepicker" autocomplete="off" id="date_paid" name="date_paid" type="text" placeholder="Date">
                </div>
                <div class="f2 <?php echo (isset($Guid_payor)&&$Guid_payor!="")? "valid show-label":"" ?> ">
                    <label class="dynamic" for="Guid_payor"><span>Payor</span></label>
                    <div class="group">
                        <select name="Guid_payor">
                            <option value=" ">Select Payor</option>
                            <?php 
                            $payors = $db->selectAll('tbl_mdl_payors', ' ORDER BY name DESC');
                            foreach ($payors as $k=>$v){
                            ?>
                            <option <?php echo (isset($Guid_payor)&&$Guid_payor==$v['Guid_payor'])?" selected":""; ?> value="<?php echo $v['Guid_payor']; ?>"><?php echo $v['name']; ?></option>
                            <?php } ?>
                        </select>                            
                        <p class="f_status">
                            <span class="status_icons"><strong></strong></span>
                        </p>
                    </div>
                </div>
                <div class="f2 <?php echo (isset($Guid_cpt)&&$Guid_cpt!="")? "valid show-label":"" ?> ">
                    <label class="dynamic" for="Guid_cpt"><span>CPT Code</span></label>
                    <div class="group">
                        <select name="Guid_cpt">
                            <option value=" ">Select CPT Code</option>
                            <?php 
                            $cpt_codes = $db->selectAll('tbl_mdl_cpt_code', ' ORDER BY code ASC');
                            foreach ($cpt_codes as $k=>$v){
                            ?>
                            <option <?php echo (isset($Guid_cpt)&&$Guid_cpt==$v['Guid_cpt'])?" selected":""; ?> value="<?php echo $v['Guid_cpt']; ?>"><?php echo $v['code']; ?></option>
                            <?php } ?>
                        </select>                            
                        <p class="f_status">
                            <span class="status_icons"><strong></strong></span>
                        </p>
                    </div>
                </div>
                <div class="f2 <?php echo (isset($amount)&&$amount!="")? "valid show-label":"" ?>">
                    <label class="dynamic" for="amount"><span>Amount $</span></label>
                    <div class="group">
                        <input value="<?php echo isset($amount)? $amount: ""; ?>" autocomplete="off" name="amount" placeholder="Amount $" type="number" min="0.00" step="0.01">                        
                        <p class="f_status">
                            <span class="status_icons"><strong></strong></span>
                        </p>
                    </div>
                </div>
                
                 <div class="text-right pT-10">
                    <button class="button btn-inline" name="manage_revenue" type="submit" >Save</button>
                    <!--<button onclick="goBack();" type="button" class="btn-inline btn-cancel">Cancel</button>-->                   
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php
    if(isset($_POST['manage_status'])){ 
        $statusData=array('parent_id'=>$_POST['parent_id'], 'status'=>$_POST['status'], 'order_by'=>$_POST['order_by']);
        $insertStatus = insertIntoTable($db, 'tbl_mdl_status', $statusData);
        if($insertStatus['insertID']!=""){
            $message = "New Status Inserted.";
        }
    } 
?>
<?php if(isset($_GET['manage_status']) && $_GET['manage_status']=='add' && $role=='Admin'){ ?>
<div id="manage-status-modal" class="modalBlock ">
    <div class="contentBlock">
        <a class="close" href="<?php echo $patientInfoUrl; ?>">X</a>        
        
        <h5 class="title">
            Add New Status
        </h5>
        <div class="content">
            <!--<div class="status-list">list here...</div>-->
            <div class="add-status-form">
                <form action="" method="POST">
                <h4 class="text-center"></h4>
                <?php if(isset($message)){ ?>
                    <div class="text-center success-text"><?php echo $message; ?></div>
                <?php } ?>
                 
                <div class="f2 ">
                    <label class="dynamic" for="status"><span>Status Name</span></label>
                    <div class="group">
                        <input required autocomplete="off" id="status" name="status" type="text" value="" placeholder="Status Name">
                        <p class="f_status">
                            <span class="status_icons"><strong></strong></span>
                        </p>
                    </div>
                </div>
                <div class="f2  ">
                    <label class="dynamic" for="parent"><span>Status Parent</span></label>
                    <div class="group">
                        
                            <?php 
                                echo get_nested_status_dropdown($db);
                            ?>
                        <p class="f_status">
                            <span class="status_icons"><strong></strong></span>
                        </p>
                    </div>
                </div>
                <div class="f2 ">
                    <label class="dynamic" for="order_by"><span>Order By</span></label>
                    <div class="group">
                        <input type="number" min="0" step="1" autocomplete="off" id="order_by" name="order_by"  value="" placeholder="Order By">
                        <p class="f_status">
                            <span class="status_icons"><strong></strong></span>
                        </p>
                    </div>
                </div>
                
                 <div class="text-right pT-10">
                    <button class="button btn-inline" name="manage_status" type="submit" >Save</button>
                    <!--<button onclick="goBack();" type="button" class="btn-inline btn-cancel">Cancel</button>-->                   
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php 
if(isset($_POST['edit_statuses'])){    
    if(isset($_POST['status']['Guid_status'])){
        
        $statusIDS = $_POST['status']['Guid_status'];
        $statusNames = $_POST['status']['name'];
        $statusOrder = $_POST['status']['order'];
        $statusVisibility = $_POST['status']['visibility'];
        $access_roles = $_POST['status']['roles'];
        $count = count($statusIDS);
        foreach ($statusIDS as $k => $statusID) {            
            $whereEditStatus = array('Guid_status'=>$statusID);
            $editStatusData = array(
                'status' => $statusNames[$k],
                'visibility' => $statusVisibility[$k],
                'access_roles' => $access_roles,
                'order_by' => $statusOrder[$k]
            );
            if(isset($access_roles[$statusID])){                
                $editStatusData['access_roles'] = serialize($access_roles[$statusID]);
            }
            
            updateTable($db, 'tbl_mdl_status', $editStatusData, $whereEditStatus);
        }
        $message = "All data saved.";
    }
} 
?>
<?php if(isset($_GET['manage_status']) && $_GET['manage_status']=='edit' && $role=='Admin'){ ?>
<div id="manage-status-modal" class="modalBlock editStausesModal">
    <div class="contentBlock">
        <a class="close" href="<?php echo $patientInfoUrl; ?>">X</a>       
        
        <h5 class="title">
            Edit Statuses
        </h5>
        <div class="content">
            <!--<div class="status-list">list here...</div>-->
            <div class="edit-status-form">
                <form action="" method="POST">
                    <?php if(isset($message)){ ?>
                        <div class="text-center success-text"><?php echo $message; ?></div>
                    <?php } ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="w-50">Status#</th>
                                <th class="status_name">Status Name</th>
                                <th>Order</th>
                                <th>Visibility</th>
                                <th>Roles <span class="toggleRoles pull-right far fa-eye-slash"></span></th>
                            </tr>
                        </thead>
                        <tbody>                            
                            <?php echo get_nested_ststus_editable_rows($db); ?>                            
                        </tbody>
                    </table>
                
                
                    <div class="text-right pT-10">
                       <button class="button btn-inline" name="edit_statuses" type="submit" >Save</button>
                       <a href="<?php echo $patientInfoUrl; ?>" class="btn-inline btn-cancel">Cancel</a>                   
                   </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php
    if(isset($_POST['manage_category'])){ 
        $categoryData=array('name'=>$_POST['name'], 'order_by'=>$_POST['order_by']);
        $insertNoteCat = insertIntoTable($db, 'tbl_mdl_note_category', $categoryData);
        if($insertNoteCat['insertID']!=""){
            $message = "New Category Inserted.";
        }
    } 
?>
<?php if(isset($_GET['manage_note_category']) && $_GET['manage_note_category']=='add' && $role=='Admin'){ ?>
<div id="manage-status-modal" class="modalBlock ">
    <div class="contentBlock">
        <a class="close" href="<?php echo $patientInfoUrl; ?>">X</a>        
        
        <h5 class="title">
            Add Note Category
        </h5>
        <div class="content">
            <!--<div class="status-list">list here...</div>-->
            <div class="add-status-form">
                <form action="" method="POST">
                <h4 class="text-center"></h4>
                <?php if(isset($message)){ ?>
                    <div class="text-center success-text"><?php echo $message; ?></div>
                <?php } ?>
                 
                <div class="f2 ">
                    <label class="dynamic" for="name"><span>Category Name</span></label>
                    <div class="group">
                        <input required autocomplete="off" id="name" name="name" type="text" value="" placeholder="Category Name">
                        <p class="f_status">
                            <span class="status_icons"><strong></strong></span>
                        </p>
                    </div>
                </div>                
                <div class="f2 ">
                    <label class="dynamic" for="order_by"><span>Order By</span></label>
                    <div class="group">
                        <input type="number" min="0" step="1" autocomplete="off" id="order_by" name="order_by"  value="" placeholder="Order By">
                        <p class="f_status">
                            <span class="status_icons"><strong></strong></span>
                        </p>
                    </div>
                </div>
                
                 <div class="text-right pT-10">
                    <button class="button btn-inline" name="manage_category" type="submit" >Save</button>
                    <!--<button onclick="goBack();" type="button" class="btn-inline btn-cancel">Cancel</button>-->                   
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<?php 
if(isset($_POST['edit_categories'])){    
    if(isset($_POST['category']['Guid_note_category'])){
        $categoryIDS = $_POST['category']['Guid_note_category'];
        $categoryNames = $_POST['category']['name'];
        $categoryOrder = $_POST['category']['order_by'];
        $count = count($categoryIDS);
        foreach ($categoryIDS as $k => $categoryID) {
            $whereEditCategory = array('Guid_note_category'=>$categoryID);
            $editCategoryData = array(
                'name' => $categoryNames[$k],
                'order_by' => $categoryOrder[$k]
            );            
            updateTable($db, 'tbl_mdl_note_category', $editCategoryData, $whereEditCategory);
        }
        $message = "Category data saved";
    }
} 
?>
<?php if(isset($_GET['manage_note_category']) && $_GET['manage_note_category']=='edit' && $role=='Admin'){ ?>
<div id="manage-status-modal" class="modalBlock editStausesModal">
    <div class="contentBlock">
        <a class="close" href="<?php echo $patientInfoUrl; ?>">X</a>       
        
        <h5 class="title">
            Edit Note Categories
        </h5>
        <div class="content">
            <!--<div class="status-list">list here...</div>-->
            <div class="edit-status-form">
                <form action="" method="POST">
                    <?php if(isset($message)){ ?>
                        <div class="text-center success-text"><?php echo $message; ?></div>
                    <?php } ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="status_name">Category Name</th>
                                <th>Order By</th>
                            </tr>
                        </thead>
                        <tbody>                            
                            <?php 
                            $categories = $db->selectAll("tbl_mdl_note_category", " ORDER BY order_by ASC");
                            foreach ($categories as $k=>$v){ 
                            ?>
                            <tr>
                                <td>
                                    <input type="hidden" name="category[Guid_note_category][]" value="<?php echo $v['Guid_note_category']; ?>">
                                    <input type="text" name="category[name][]" value="<?php echo $v['name']; ?>">
                                </td>
                                <td><input type="text" name="category[order_by][]" value="<?php echo $v['order_by']; ?>"></td>
                            </tr>
                            <?php }?>                            
                        </tbody>
                    </table>
                
                
                    <div class="text-right pT-10">
                       <button class="button btn-inline" name="edit_categories" type="submit" >Save</button>
                       <a href="<?php echo $patientInfoUrl; ?>" class="btn-inline btn-cancel">Cancel</a>                   
                   </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php
   
    if(isset($_POST['manage_status_log'])){ 
        $statusIDs = $_POST['status'];
        $date=($_POST['date']!="")?date('Y-m-d h:i:s',strtotime($_POST['date'])):"";
        $Guid_patient = $qualifyResult['Guid_patient']; 
        
        $statusLogData = array(
            'Guid_user' => $_POST['Guid_user'],
            'Guid_patient'=> $Guid_patient,
            'Guid_account' => $accountInfo['Guid_account'],
            'account' => $accountInfo['account'],
            'Guid_salesrep' => $accountInfo['Guid_salesrep'],
            'salesrep_fname' => $accountInfo['salesrep_fname'],
            'salesrep_lname' => $accountInfo['salesrep_lname'],
            'Recorded_by' => $_SESSION['user']['id'],  
            'provider_id' => $qualifyResult['provider_id'],
            'deviceid' => $qualifyResult['deviceid'],
            'Date'=>$date,
            'Date_created'=>date('Y-m-d h:i:s')
        );
        
        if(isset($_POST['Guid_status_log']) && $_POST['Guid_status_log']!=""){//update log
		
            $thisLog = $db->row("SELECT * FROM tbl_mdl_status_log WHERE Guid_status_log=:Guid_status_log", array('Guid_status_log'=>$_POST['Guid_status_log']));
            $statusLogData['Date_created'] = $thisLog['Date_created'];
            $LogGroup = $thisLog['Log_group'];
            //delete old log
            deleteByField($db, 'tbl_mdl_status_log', 'Log_group', $LogGroup);
            saveStatusLog($db, $statusIDs, $statusLogData);
            //update last status id in patient table too
            updateCurrentStatusID($db, $Guid_patient);
            Leave($patientInfoUrl);
        } else {//insert log		
            if(isset($_POST['specimenCollected']) && $_POST['specimenCollected']=='no'){
                updateTable($db, 'tblpatient', array('specimen_collected'=>'No'), array('Guid_patient'=>$Guid_patient));
            }
            if(isset($_POST['specimenCollected']) && $_POST['specimenCollected']=='yes'){
                updateTable($db, 'tblpatient', array('specimen_collected'=>'Yes'), array('Guid_patient'=>$Guid_patient));
            }
            saveStatusLog($db, $statusIDs, $statusLogData);
            updateCurrentStatusID($db, $Guid_patient);
            Leave($patientInfoUrl);
        }  
    } 
	
?>
<?php 
    if(isset($_GET['status_log']) && $role=='Admin' || isset($_GET['specimen'])){ 
        $title= ($_GET['status_log']=='add')?"Add Status Log":"Update Status Log";
        if(isset($_GET['log_id'])&&$_GET['log_id']!=""){
            $logRowQ = "SELECT * FROM tbl_mdl_status_log WHERE Guid_status_log=:Guid_status_log";
            $logRow = $db->row($logRowQ, array('Guid_status_log'=>$_GET['log_id']));
        }
        
?>
<div id="manage-status-modal" class="modalBlock ">
    <div class="contentBlock">
        <a class="close" href="<?php echo $patientInfoUrl; ?>">X</a>        
        
        <h5 class="title"><?php echo $title;?> </h5>
        <div class="content">
            <!--<div class="status-list">list here...</div>-->
            <div class="add-status-form">
                <form action="" method="POST">
                <h4 class="text-center"></h4>
                <?php if(isset($message)){ ?>
                    <div class="text-center success-text"><?php echo $message; ?></div>
                <?php } ?>
                <input type="hidden" name="Guid_status_log" value="<?php echo (isset($_GET['log_id'])&&$_GET['log_id']!="")?$_GET['log_id']:""; ?>" />
                <input type="hidden" name="Guid_user" value="<?php echo $_GET['patient']; ?>" />
                
                <div class="col-md-12">
                    <input required class="datepicker" autocomplete="off" id="status" name="date" type="text" value="<?php echo (isset($logRow['Date'])&&$logRow['Date']!="") ? date('n/j/Y', strtotime($logRow['Date'])) : date('n/j/Y'); ?>" placeholder="Date">
                </div>               
                <div class="col-md-12 clearfix" id="status-dropdowns-box">                                            
                    <?php 
                        if(isset($_GET['log_id']) && $_GET['log_id']!="" ){
                            if(!empty($logRow)){
                                echo get_selected_log_dropdown($db, $logRow['Log_group']);
                            } else {
                                Leave($patientInfoUrl);
                            }
                        }else{
                            if(isset($_GET['specimen'])){
                                if($_GET['specimen']=='yes'){
                                    echo "<input type='hidden' name='specimenCollected' value='yes'>";
                                    echo get_status_dropdown($db, $parent_id='0', $Guid_status='1');
                                }else{
                                    echo "<input type='hidden' name='specimenCollected' value='no'>";
                                    echo get_status_dropdown($db, $parent_id='0', $Guid_status='37');
                                }
                            }else{
                                echo get_status_dropdown($db, $parent_id='0'); 
                            }
                        }
                    ?>                            
                </div>             
                
                 <div class="text-right pT-10">
                    <button class="button btn-inline" name="manage_status_log" type="submit" >Save</button>
                    <!--<button onclick="goBack();" type="button" class="btn-inline btn-cancel">Cancel</button>-->                   
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php } ?>


<?php
    if(isset($_POST['manage_note_log'])){ 
      
        $date=($_POST['date']!="")?date('Y-m-d h:i:s',strtotime($_POST['date'])):"";
        
        $noteLogData = array(            
            'Guid_note_category'=> $_POST['Guid_note_category'],
            'Guid_user' => $_POST['Guid_user'],
            'Recorded_by' => $_SESSION['user']['id'], 
            'Comment' => $_POST['comment'],
            'Date'=>$date            
        );        
        
        if(isset($_POST['Guid_note']) && $_POST['Guid_note']!=""){
            //update log
            $where = array('Guid_note'=>$_POST['Guid_note']);
            updateTable($db, 'tbl_mdl_note', $noteLogData, $where);
            Leave($patientInfoUrl);
        } else {
            //insert log
            $noteLogData['Date_created']=date('Y-m-d h:i:s');
            insertIntoTable($db, 'tbl_mdl_note', $noteLogData);            
            Leave($patientInfoUrl);
        }   
       
    } 
?>
<?php 
    if(isset($_GET['note_log'])) { 
        $title= ($_GET['note_log']=='add')?"Add Note":"Update Note";
        if(isset($_GET['note_id'])&&$_GET['note_id']!=""){
            $catLogRow = $db->row("SELECT * FROM tbl_mdl_note WHERE Guid_note=:Guid_note", array('Guid_note'=>$_GET['note_id']));
        }
        
?>
<div id="manage-status-modal" class="modalBlock ">
    <div class="contentBlock">
        <a class="close" href="<?php echo $patientInfoUrl; ?>">X</a>        
        
        <h5 class="title"><?php echo $title;?> </h5>
        <div class="content">
            <!--<div class="status-list">list here...</div>-->
            <div class="add-status-form">
                <form action="" method="POST">
                <h4 class="text-center"></h4>
                <?php if(isset($message)){ ?>
                    <div class="text-center success-text"><?php echo $message; ?></div>
                <?php } ?>
                <input type="hidden" name="Guid_note" value="<?php echo (isset($_GET['note_id'])&&$_GET['note_id']!="")?$_GET['note_id']:""; ?>" />
                <input type="hidden" name="Guid_user" value="<?php echo $_GET['patient']; ?>" />
                
                <div class="col-md-12">
                    <input required class="datepicker" autocomplete="off" id="status" name="date" type="text" value="<?php echo (isset($catLogRow['Date'])&&$catLogRow['Date']!="") ? date('n/j/Y', strtotime($catLogRow['Date'])) : date('n/j/Y'); ?>" placeholder="Date">
                </div>
                
                <div class="col-md-12 clearfix">                                            
                    <div class="f2   show-label s">
                    <div class="group">
                        <select required name="Guid_note_category">
                            <option value="">Select category</option>
                        <?php 
                            $categories = $db->selectAll('tbl_mdl_note_category', " ORDER BY order_by ASC");
                            foreach ($categories as $k=>$v){
                                $selected = (isset($catLogRow['Guid_note_category']) && $catLogRow['Guid_note_category']==$v['Guid_note_category']) ? " selected":"";
                        ?>
                            <option <?php echo $selected; ?> value="<?php echo $v['Guid_note_category']; ?>"><?php echo $v['name']; ?></option>
                        <?php } ?>   
                        </select>
                        <p class="f_status">
                            <span class="status_icons"><strong></strong></span>
                        </p>
                    </div>
                    </div>                            
                </div>         
                <div class="col-md-12 clearfix">                                            
                    <div class="f2">                        
                        <textarea required name="comment"><?php echo (isset($catLogRow['Comment'])) ? $catLogRow['Comment'] : ''; ?></textarea>
                    </div>                            
                </div>         
                
                 <div class="text-right pT-10">
                    <button class="button btn-inline" name="manage_note_log" type="submit" >Save</button>
                    <!--<button onclick="goBack();" type="button" class="btn-inline btn-cancel">Cancel</button>-->                   
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php } ?>


<?php require_once 'scripts.php'; ?>
<?php require_once 'footer.php'; ?>