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
if(isset($_GET['patient']) && $_GET['patient'] !="" ){     
    $Guid_user = $_GET['patient'];
    $patientInfoUrl = SITE_URL.'/patient-info.php?patient='.$Guid_user;
    if( isset($_GET['account']) &&$_GET['account']!=""){
        $patientInfoUrl .= '&account='.$_GET['account'];
    }
    if( isset($_GET['incomplete']) &&$_GET['incomplete']=="1"){
        $patientInfoUrl .= '&incomplete=1';   
    }
    
    $sqlQualify = "SELECT q.Guid_qualify,q.Guid_user,q.insurance,q.other_insurance,q.Date_created,q.provider_id,q.`mark_as_test`, 
                    CONCAT(prov.first_name,' ',prov.last_name) provider,
                    p.*, u.email ";
    if(isset($_GET['incomplete'])){
        $sqlQualify .= "FROM tblqualify q ";
    } else {
        $sqlQualify .= "FROM tbl_ss_qualify q ";
    }
    $sqlQualify .= "LEFT JOIN tblpatient p ON q.Guid_user = p.Guid_user
                    LEFT JOIN tbluser u ON q.Guid_user = u.Guid_user 
                    LEFT JOIN tblprovider prov ON prov.Guid_provider = q.provider_id
                    WHERE q.Guid_user=:Guid_user ORDER BY q.`Date_created` DESC LIMIT 1";
    $qualifyResult = $db->row($sqlQualify, array('Guid_user'=>$Guid_user));    
    
    $mdlInfoQ = "SELECT * FROM tbl_mdl_number WHERE Guid_user=:Guid_user";
    $mdlInfo = $db->row($mdlInfoQ, array('Guid_user'=>$Guid_user));
    
    $Guid_qualify = $qualifyResult['Guid_qualify'];
    
    $sqlSSQualify = "SELECT ssq.* FROM tbl_ss_qualify ssq WHERE ssq.Guid_qualify=:Guid_qualify  ORDER BY Date_created DESC";
    $ssQualifyResult = $db->query($sqlSSQualify, array('Guid_qualify'=>$Guid_qualify));
    
    $errorMsgMdlStats = "";
    if(isset($_POST['save'])){
        
        $numSize = strlen($_POST['mdl_number']);
        if(isset($_POST['mdl_number'])&&$_POST['mdl_number']!=""){
            if(!isset($_POST['mark_as_test']) && !isset($_POST['mark_as_test_incomplate']) ){
                if(isset($_POST['mdl_number']) && $numSize != 7){
                    $isValid = false;
                    $errorMsgMdlStats .= "MDL# must contain 7 digits only <br/>";
                }
            }
        }
        if($isValid){
            $userData = array();
            if(isset($_POST['email']) && $_POST['email']!=''){
                $userData['email'] = $_POST['email'];
            }
        
            if(!empty($userData)){
                $userData['Date_modified'] = date('Y-m-d H:i:s');
                $whereUser = array('Guid_user'=>$_GET['patient']);
                //check if user exists
                $isUserExists=$db->row("SELECT * FROM tbluser WHERE Guid_user=:Guid_user", $whereUser);            
                if($isUserExists){//update user                
                    $updateUser = updateTable($db, 'tbluser', $userData, $whereUser);
                    saveUserRole($db, $_GET['patient'], '3');
                } else { //insert user
                    $userData['user_type'] = 'patient';
                    $userData['Date_created'] = date('Y-m-d H:i:s');
                    $inserUser = insertIntoTable($db, 'tbluser', $userData);
                    if($inserUser['insertID']){            
                        $inserRole = insertIntoTable($db, 'tbluserrole', array('Guid_user'=>$inserUser['insertID'], 'Guid_role'=>'3'));
                    }
                }            
            }
            
            if(isset($_POST['dob']) && $_POST['dob']!=""){
                $dob= date('Y-m-d h:i:s', strtotime($_POST['dob']));
                updateTable($db, 'tblpatient', array('dob'=>$dob), array('Guid_user'=>$_GET['patient']));
            }
            
            if(isset($_POST['total_deductible']) && $_POST['total_deductible']!=""){                
                updateTable($db, 'tblpatient', array('total_deductible'=> $_POST['total_deductible']), array('Guid_user'=>$_GET['patient']));
            }
            
             //update patient table for reason and cpecimen collected values
            $wherePatient = array('Guid_user'=>$_GET['patient']);
            $patientData = array();
            if(isset($_POST['specimen_collected'])&&$_POST['specimen_collected']!=""){
                $patientData['specimen_collected']=$_POST['specimen_collected']; 
            }
            if(isset($_POST['Guid_reason'])&&$_POST['Guid_reason']!=""){
                $patientData['Guid_reason']=$_POST['Guid_reason']; 
            } else {
                $patientData['Guid_reason']="";
            }
            if(!empty($patientData)){
                $updatePatient = updateTable($db, 'tblpatient', $patientData, $wherePatient);
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
            
            //mark user as a test
            $markedUserID = $_GET['patient'];            
            if(isset($_POST['mark_as_test'])){  
                updateTable($db,'tbl_ss_qualify', array('mark_as_test'=>'1'), array('Guid_user'=>$markedUserID));
            } else {
                updateTable($db,'tbl_ss_qualify', array('mark_as_test'=>'0'), array('Guid_user'=>$markedUserID));
            }
            if(isset($_POST['mark_as_test_incomplate'])){  
                updateTable($db,'tblqualify', array('mark_as_test'=>'1'), array('Guid_user'=>$markedUserID));
            } else {
                updateTable($db,'tblqualify', array('mark_as_test'=>'0'), array('Guid_user'=>$markedUserID));
            }
            
           //add revenue data if exists
            if(isset($_POST['revenueAdd']) && !empty($_POST['revenueAdd'])){                
                $revData = $_POST['revenueAdd'];                
                $size = count($revData['date_paid']);
                for($i=0; $i<$size; $i++){
                    $date_paid = ($revData['date_paid'][$i] != "")?date('Y-m-d h:i:s', strtotime($revData['date_paid'][$i])):"";
                    $dataRevenue = array(
                        'Guid_user'=>$_GET['patient'],
                        'date_paid'=>$date_paid,
                        'payor'=>$revData['payor'][$i],
                        'insurance'=>$revData['insurance'][$i],
                        'patient'=>$revData['patient'][$i]
                    );        
                    insertIntoTable($db, 'tbl_revenue', $dataRevenue);
                }
            }
            //update 
            if(isset($_POST['revenueEdit']) && !empty($_POST['revenueEdit'])){
                $revenues = $_POST['revenueEdit'];
                foreach ($revenues as $revenueKey => $revenueData){
                    $whereRevenue = array('Guid_revenue'=>$revenueKey);
                    $date_paid = ($revenueData['date_paid'] != "")?date('Y-m-d h:i:s', strtotime($revenueData['date_paid'])):"";
                    $dataRevenue = array(
                        'date_paid'=>$date_paid,
                        'payor'=>$revenueData['payor'],
                        'insurance'=>$revenueData['insurance'],
                        'patient'=>$revenueData['patient']
                    ); 
                    $updateReveue = updateTable($db, 'tbl_revenue', $dataRevenue, $whereRevenue);            
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
    if(isset($_GET['delete-deductible']) && $_GET['delete-deductible']!=""){
        deleteByField($db,'tbl_deductable_log', 'Guid_deductable', $_GET['delete-deductible']);        
        Leave($patientInfoUrl);
    }
    //delete revenue row
    if(isset($_GET['delete-revenue']) && $_GET['delete-revenue']!=""){
        deleteByField($db,'tbl_revenue', 'Guid_revenue', $_GET['delete-revenue']);
        Leave($patientInfoUrl);
    }
    //delete status log row and update lats status log id in patients table
    if(isset($_GET['delete-status-log']) && $_GET['delete-status-log']!=""){
        $Guid_patient = $qualifyResult['Guid_patient'];        
        deleteByField($db,'tbl_mdl_status_log', 'Log_group', $_GET['group']);
        updateCurrentStatusID($db, $Guid_patient);
        Leave($patientInfoUrl);
    }
    //delete note log
    if(isset($_GET['delete-note-log']) && $_GET['delete-note-log']!=""){
        deleteByField($db,'tbl_mdl_note', 'Guid_note', $_GET['delete-note-log']);
        Leave($patientInfoUrl);
    }
  
 } ?>


<?php 
    if(isset($_GET['account'])&&$_GET['account']!=""){
        $accountQ = "SELECT a.Guid_account, a.account, a.name AS account_name, "
                    . "sr.Guid_salesrep, sr.first_name AS salesrep_fname, sr.last_name AS salesrep_lname, CONCAT(sr.first_name, ' ', sr.last_name) AS salesrep_name "
                    . "FROM tblaccount a "
                    . "LEFT JOIN tblaccountrep ar ON a.Guid_account=ar.Guid_account "
                    . "LEFT JOIN tblsalesrep sr ON ar.Guid_salesrep = sr.Guid_salesrep "
                    . "WHERE a.account = '" . $_GET['account'] . "'";
        $accountInfo = $db->row($accountQ);        
    } else {
        $accountInfo = FALSE;
    }
?>
<?php require_once 'navbar.php'; ?> 
<main class="full-width">
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
            <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit smaller_button"><strong>View Questionnaire</strong></a>
        </section>

        <div id="app_data" class="scroller">
            <div class="row" id="patient-info-box">   
                <div class="col-md-12">
                <?php if(isset($message)){ ?>
                <div class="error-text"><?php echo $message; ?></div>
                <?php } ?>
                <h2 class="text-center"><?php echo ucfirst($qualifyResult['firstname'])." ".ucfirst($qualifyResult['lastname']);?></h2>
                
                <form id="mdlInfoForm" action="" method="POST" > 
                        <input type="hidden" name="save" value="1"/>
                        <input type="hidden" name="account" value="<?php echo isset($_GET['account'])?$_GET['account']:$mdlInfo['account'] ?>"/>
                        <div class="row">
                            <div class="col-md-6 pInfo">
                                <p><label>Date of Birth: </label><input type="text" name="dob" class="datepicker" value="<?php echo ($qualifyResult['dob']!="")?date("n/j/Y", strtotime($qualifyResult['dob'])):""; ?>" autocomplete="off" /></p>
                                <p><label>Email: </label><input type="email" name="email" value="<?php echo $qualifyResult['email']; ?>" autocomplete="off"/> </p>
                                <p class="capitalize"><label>Insurance: </label><?php echo $qualifyResult['insurance'];
                                                                                        if($qualifyResult['other_insurance']!="" && $qualifyResult['other_insurance']!="Other"){
                                                                                            echo " (".$qualifyResult['other_insurance'].")";
                                                                                        }
                                                                                ?>                                  
                                </p>
                                <?php if($accountInfo) { ?>
                                <p><label>Account: </label><?php echo $accountInfo['account'];
                                                                if($accountInfo['account_name']!=""){
                                                                    echo " - ". ucwords(strtolower($accountInfo['account_name']));
                                                                }
                                                            ?>
                                </p>
                                <p><label>Genetic Consultant: </label><?php echo $accountInfo['salesrep_name']; ?></p>
                                <?php } ?>
                                <p><label>Health Care Providers: </label><?php echo $qualifyResult['provider']; ?>
                                
                            </div>
                            <div class="col-md-6 pB-30">                    
                                <div class="row">
                                    <div id="message" class="error-text">
                                    <?php if($errorMsgMdlStats){ ?>   
                                        <!--Form Error messages go here-->
                                        <?php echo $errorMsgMdlStats; ?>
                                    <?php } ?>
                                    </div>
                                    <div id="specimenRadioBox" class="<?php echo ($qualifyResult['specimen_collected']=='Yes')?'hidden':"";?>" >
                                        <h5>Specimen collected?</h5>
                                        <div class="col-md-4 pL-0">
                                            <div id="specimen">
                                                <input id="specimen-collected-cbox" <?php echo ($qualifyResult['specimen_collected']=='Yes')?"checked":"";?> type="radio" name="specimen_collected" value="Yes" /> Yes &nbsp;&nbsp;
                                                <?php if($qualifyResult['specimen_collected'] !== 'Yes'){ ?>
                                                <input id="specimen-notcollected-cbox" <?php echo ($qualifyResult['specimen_collected']=='No')?"checked":"";?> type="radio" name="specimen_collected" value="No" /> No
                                                <?php } ?>                                               
                                                <div class="pick-date">                                                    
                                                    <label>Date: </label>
                                                    <input id="redirectUrl" type="hidden" value="<?php echo $patientInfoUrl; ?>" />
                                                    <input id="Guid_user" type="hidden" value="<?php echo $_GET['patient']; ?>" />
                                                    <input id="account" type="hidden" value="<?php echo isset($_GET['account'])?$_GET['account']:""; ?>" />
                                                    <input type="text" class="datepicker" value="<?php echo date('n/j/Y'); ?>">
                                                    <button id="save-specimen-collected" class="btn btn-specimen btn-inline" type="button">OK</button>
                                                    <button id="cancel-specimen-collected" class="btn btn-specimen btn-inline" type="button">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="select-reson" class="col-md-8 <?php echo ( is_null($qualifyResult['specimen_collected']) || $qualifyResult['specimen_collected']=='Yes')?"hidden":"";?>">
                                        <div class="f2">
                                            <!--<label class="dynamic" for="reason_not"><span>Reasons for not taking the test</span></label>-->
                                            <?php $reasons = $db->selectAll('tbl_reasons');?>
                                            <div class="group">
                                                <select id="reason" name="Guid_reason" class="no-selection">
                                                    <option value="">Reasons for not taking the test</option>	
                                                    <?php foreach ($reasons as $k=>$v){?>
                                                        <option <?php echo ($qualifyResult['Guid_reason']==$v['Guid_reason'])?"selected":""; ?> value="<?php echo $v['Guid_reason']; ?>"><?php echo $v['reason']; ?></option>
                                                    <?php } ?>
                                                </select>
                                                <p class="f_status">
                                                    <span class="status_icons"><strong></strong></span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="mdlInfoBox" class="pInfo <?php echo ($qualifyResult['specimen_collected']!='Yes')?'hidden':"";?>">
                                        <p>
                                            <label>MDL#:</label>
                                            <?php 
                                            $mdlNumber = isset($_POST['mdl_number'])?$_POST['mdl_number']:$mdlInfo['mdl_number'];
                                            $mdlClass = (strlen($mdlNumber)!=0 && strlen($mdlNumber)<7)?' error error-border' : '';
                                            ?>
                                            <input type="number" autocomplete="off" class="mdlnumber <?php echo $mdlClass; ?>" name="mdl_number" value="<?php echo $mdlNumber; ?>" />
                                        </p>
                                    </div> 
                                    
                                    <div id="statusLogs"  class="col-md-12 clearfix padd-0">
                                        <h5 class="pT-30">
                                            Notes:                                            
                                            <a title="Add Note" class="pull-right" href="<?php echo $patientInfoUrl."&note_log=add";?>">
                                                <span class="fas fa-plus-circle" aria-hidden="true"></span>  Add
                                            </a>                                            
                                        </h5>
                                        <table class="table">
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
                                                    $nQ =  "SELECT n.*, p.firstname, p.lastname, cat.name AS category 
                                                            FROM `tbl_mdl_note` n
                                                            LEFT JOIN `tblpatient` p ON n.Guid_user=p.Guid_user
                                                            LEFT JOIN `tbl_mdl_note_category` cat ON n.Guid_note_category=cat.Guid_note_category
                                                            WHERE n.Guid_user=:Guid_user"; 
                                                    $notes = $db->query($nQ, array('Guid_user'=>$_GET['patient']));
                                                    
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
                                <a title="Add New Test Status Log" class="pull-right" href="<?php echo $patientInfoUrl."&status_log=add";?>">
                                    <span class="fas fa-plus-circle" aria-hidden="true"></span>  Add
                                </a>
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
                                $qStatusLog = 'SELECT sl.Guid_status_log,sl.Log_group, sl.Guid_user, sl.Guid_status, sl.Date, s.parent_id '
                                            . 'FROM tbl_mdl_status_log sl '
                                            . 'LEFT JOIN tblpatient p ON sl.Guid_patient=p.Guid_user '
                                            . 'LEFT JOIN tbl_mdl_status s ON sl.Guid_status=s.Guid_status '
                                            . 'WHERE sl.Guid_user='.$patientID.'  AND s.parent_id="0" '
                                            . 'ORDER BY sl.date DESC, s.order_by DESC';
                                
                                $ststusLogs = $db->query($qStatusLog);
                                foreach ($ststusLogs as $k=>$v){ 
                                ?>
                                    <tr>
                                        <td><?php echo date("n/j/Y", strtotime($v['Date'])); ?></td> 
                                        <td><?php echo get_status_names( $db, $v['Guid_status'], $v['Guid_user'], $v['Log_group'] ); ?></td>   
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
                                <?php } ?>
                            </tbody>
                        </table>
                        </div>
                    </div>
                    <div id="pLogs" class="row <?php echo (!$qualifyResult['specimen_collected'] || $qualifyResult['specimen_collected']=='No')?"hidden":"";?>">
                        <div id="deductable-log" class="col-md-6">
                            <?php 
                                $whereUser = array('Guid_user'=>$_GET['patient']);
                                $deductableLogs = $db->query('SELECT * FROM tbl_deductable_log WHERE Guid_user=:Guid_user', $whereUser); 
                               
                            ?>
                            <h5>
                                Deductible Log: 
                                <a class="pull-right" id="add-deductable-log">
                                    <span class="fas fa-plus-circle" aria-hidden="true"></span>  Add
                                </a>  
                                <?php if(!$qualifyResult['total_deductible']){ ?>
                                <a class="pull-right  mR-10" id="add-patient-deductable">
                                    <span class="fas fa-plus-circle" aria-hidden="true"></span>  Deductible
                                </a>  
                                <?php } ?>
                                <span id="total-deductible" class="<?php echo (!$qualifyResult['total_deductible'])?"hidden":"";?> pull-right">
                                    $<input value="<?php echo ($qualifyResult['total_deductible']!="")?$qualifyResult['total_deductible']:""; ?>" name="total_deductible" placeholder="Total Deductible" type="number" min="0.00" step="0.01">
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
                                            <th class="text-center wh-100">Action</th>
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
                                <a title="Add Revenue" class="pull-right" href="<?php echo $patientInfoUrl."&add_revenue=1";?>">
                                    <span class="fas fa-plus-circle" aria-hidden="true"></span>  Add
                                </a>
                            </h5>
                            <div class="revenue-form"></div>
                           
                            <table id="revenue-table" class="table">
                                <thead>
                                    <tr>
                                        <th>Date Paid</th>
                                        <th>Payor</th>
                                        <th>CPT</th>
                                        <th>Amount $</th>
                                        <th class="text-center wh-100">Action</th>
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
                    <div class="row actionButtons pB-30">
                        <div class="col-md-12">
                            <?php if($role=='Admin' ||$role=='Sales Rep' || $role=='Sales Manager' ){ ?>
                            <span class="pull-left markTest"> 
                                <?php if(isset($_GET['incomplete'])&&$_GET['incomplete']==1){ ?>
                                <input <?php echo $qualifyResult['mark_as_test']=='1'?' checked': ''; ?> id="mark-as-test" type="checkbox" name="mark_as_test_incomplate" value="1" /> 
                                <?php } else { ?>
                                <input <?php echo $qualifyResult['mark_as_test']=='1'?' checked': ''; ?> id="mark-as-test" type="checkbox" name="mark_as_test" value="1" /> 
                                <?php } ?>
                                <label for="mark-as-test">Mark As Test</label>
                            </span>
                            <?php } ?>
                            <button id="save-patient-info" name="save" type="submit" class="button btn-inline pull-right">Save</button>
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
</main>



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
<?php if(isset($_GET['add_revenue']) || (isset($_GET['edit_revenue'])&&$_GET['edit_revenue']!="") ){ ?>
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
                            $cpt_codes = $db->selectAll('tbl_mdl_cpt_code', ' ORDER BY code DESC');
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
        $count = count($statusIDS);
        foreach ($statusIDS as $k => $statusID) {
            $whereEditStatus = array('Guid_status'=>$statusID);
            $editStatusData = array(
                'status' => $statusNames[$k],
                'visibility' => $statusVisibility[$k],
                'order_by' => $statusOrder[$k]
            );
            
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
                                <th class="status_name">Status Name</th>
                                <th>Order</th>
                                <th>Visibility</th>
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
                            $categories = $db->selectAll("tbl_mdl_note_category");
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
            'Date'=>$date,
            'Date_created'=>date('Y-m-d h:i:s')
        );
        
        if(isset($_POST['Guid_status_log']) && $_POST['Guid_status_log']!=""){
            //update log
            $thisLog = $db->row("SELECT * FROM tbl_mdl_status_log WHERE Guid_status_log=:Guid_status_log", array('Guid_status_log'=>$_POST['Guid_status_log']));
            $statusLogData['Date_created'] = $thisLog['Date_created'];
            $LogGroup = $thisLog['Log_group'];
            //delete old log
            deleteByField($db, 'tbl_mdl_status_log', 'Log_group', $LogGroup);
            saveStatusLog($db, $statusIDs, $statusLogData);
            //update last status id in patient table too
            updateCurrentStatusID($db, $Guid_patient);
            Leave($patientInfoUrl);
        } else {
            //insert log
            saveStatusLog($db, $statusIDs, $statusLogData);
            updateCurrentStatusID($db, $Guid_patient);
            Leave($patientInfoUrl);
        }   
       
    } 
?>
<?php 
    if(isset($_GET['status_log'])){ 
        $title= ($_GET['status_log']=='add')?"Add Status Log":"Update Status Log";
        if(isset($_GET['log_id'])&&$_GET['log_id']!=""){
            $logRow = $db->row("SELECT * FROM tbl_mdl_status_log WHERE Guid_status_log=:Guid_status_log", array('Guid_status_log'=>$_GET['log_id']));
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
                            echo get_selected_log_dropdown($db, $logRow['Log_group']); 
                        }else{
                            echo get_status_dropdown($db, $parent_id='0'); 
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
    if(isset($_GET['note_log'])){ 
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
                            $categories = $db->selectAll('tbl_mdl_note_category');
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