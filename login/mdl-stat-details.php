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
$roleID = $roleInfo['Guid_role'];

$accessRole = getAccessRoleByKey('mdlStats');
$roleIDs = unserialize($accessRole['value']);
$dataViewAccess = isUserHasAnyAccess($roleIDs, $roleID, 'view');

if($role!="Admin"){
    //Leave(SITE_URL."/no-permission.php");
}

$users = getUsersAndRoles($db);

//exclude test users from mdl stats
$testUserIds = getTestUserIDs($db);
$markedTestUserIds = getMarkedTestUserIDs($db);

$initLabels = array(
    'first_name'=>'Patient First Name', 
    'last_name'=>'Patient Last Name', 
    'account'=>'Account#', 
    'salesrep'=>'Sales Rep',
);

$initQ = 'SELECT s.Guid_status, s.Guid_user, s.Loaded, s.Date, s.Date_created, p.Guid_patient, 
    aes_decrypt(p.firstname_enc, "F1rstn@m3@_%") as firstname, 
    aes_decrypt(p.lastname_enc, "L@stn@m3&%#") as lastname, 
    a.Guid_account, a.account AS account_number, a.Guid_category,
    a.name AS account_name, a.address AS location,  
    num.mdl_number,
    CONCAT(srep.`first_name`, " " ,srep.`last_name`) AS salesrep  
    FROM `tbl_mdl_status_log` s 
    LEFT JOIN `tblpatient` p ON s.Guid_patient=p.Guid_patient
    LEFT JOIN `tblaccount` a ON s.Guid_account=a.Guid_account
    LEFT JOIN `tblsalesrep` srep ON s.Guid_salesrep=srep.Guid_salesrep
    Left JOIN `tbl_mdl_number` num ON s.Guid_user=num.Guid_user
    WHERE s.Guid_status=:Guid_status 
    AND s.currentstatus="Y" ';

if ($role == "Sales Manager") {
    $query = "SELECT * FROM tblaccount WHERE ";
    $userCategories = $db->query("SELECT Guid_category FROM `tbl_mdl_category_user_link` WHERE Guid_user=:Guid_user", array('Guid_user'=>$_SESSION['user']['id'])); 
    $userLinks = '';
    if(!empty($userCategories)){
        foreach ($userCategories as $k=>$v){
            $userLinks .= $v['Guid_category'].', ';
        }
        $userLinks = rtrim($userLinks, ', ');
    }    
    if($userLinks != ''){
        $initQ .= " AND a.Guid_category IN (" . $userLinks . ") ";
    }                     
}


if($markedTestUserIds!=""){
    $initQ.='AND s.Guid_user NOT IN('.$markedTestUserIds.') ';
}
if($testUserIds!=""){
    $initQ.='AND s.Guid_user NOT IN('.$testUserIds.') '; 
}
    
//adding filter conditions 
$searchData = array();
if(isset($_GET['salesrep'])&&$_GET['salesrep']!=""){

    if($role=='Sales Rep'){
        $thisSalserep = $db->row("SELECT Guid_salesrep FROM tblsalesrep WHERE Guid_user=:Guid_user", array('Guid_user'=>$userID));
        $initQ .= 'AND s.Guid_salesrep='.$thisSalserep['Guid_salesrep'].' ';
        $searchData['Guid_salesrep'] = $thisSalserep['Guid_salesrep'];                
    } else {
        $initQ .= 'AND s.Guid_salesrep='.$_GET['salesrep'].' ';
        $searchData['Guid_salesrep'] = $_GET['salesrep'];
    }
}
if(isset($_GET['account'])&&$_GET['account']!=""){
    $initQ .= 'AND a.Guid_account='.$_GET['account'].' ';
    $searchData['Guid_account'] = $_GET['account'];
}
if(isset($_GET['mdnum'])&&$_GET['mdnum']!=""){
    $initQ .= 'AND num.mdl_number='.$_GET['mdnum'].' ';
    $searchData['mdl_number'] = $_GET['mdnum'];
}
if( isset($_GET['from']) && isset($_GET['to']) ){
    $searchData['from_date'] = $_GET['from'];
    $searchData['to_date'] = $_GET['to'];
    if ($_GET['from'] == $_GET['to']) {
        $initQ .= " AND s.Date LIKE '%" . date("Y-m-d", strtotime($_GET['from'])) . "%'";
    } else {
        $initQ .= " AND s.Date BETWEEN '" . date("Y-m-d", strtotime($_GET['from'])) . "' AND '" . date("Y-m-d", strtotime($_GET['to'])) . "'";
    }
}

$initQ.='AND s.Guid_patient<>"0"';
    
if(isset($_GET['status_id'])&& $_GET['status_id']!=""){
    $initData=$db->query($initQ, array('Guid_status'=>$_GET['status_id']));
} else {
    $initData = array();
}

$labels = array(
    'mdl_number'=>array(
        'name' => 'MDL#',
        'class' => 'dropdownFilter'
    ),
    'first_name'=>array(
        'name' => 'Patient First Name',
        'class' => ''
    ), 
    'last_name'=>array(
        'name' => 'Patient Last Name',
        'class' => ''
    ),  
    'account'=>array(
        'name' => 'Account#',
        'class' => 'dropdownFilter'
    ),   
    'account_name'=>array(
        'name' => 'Account Name',
        'class' => ''
    ),  
    'salesrep'=>array(
        'name' => 'Sales Rep', 
        'class' => 'dropdownFilter'
    ),
    'date'=>array(
        'name' => 'Date of the most recent status', 
        'class' => ''
    ), 
    'date_accessioned'=>array(
        'name' => 'Date Accessioned', 
        'class' => ''
    ),
    'date_reported'=>array(
        'name' => 'Date Reported',
        'class' => ''
    ), 
    'insurance_paid'=>array(
        'name' => 'Insurance Paid', 
        'class' => ''
    ), 
    'patient_paid'=>array(
        'name' => 'Patient Paid',
        'class' => ''
    ),  
    'total_paid'=>array(
        'name' => 'Total Paid',
        'class' => ''
    ),   
    'insurance_name'=>array(
        'name' => 'Insurance Name',
        'class' => 'dropdownFilter'
    ),  
    'test_ordered'=>array(
        'name' => 'Test Ordered', 
        'class' => ''
    ),  
    'location'=>array(
        'name' => 'Location', 
        'class' => 'dropdownFilter'
    ),
    'Loaded'=>array(
        'name' => 'Loaded', 
        'class' => ''
    )
);

$configOptions = getOption($db, 'stat_details_config');
$optionVal = unserialize($configOptions['value']);

require_once ('navbar.php');
?>

<main class="full-width">
    <div class="box full visible ">
        <?php if($dataViewAccess) { ?>
        <section id="palette_top" class="shorter_palette_top">
            <h4>  
                <ol class="breadcrumb">
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="active">MDL Stats</li>                   
                </ol>      
            </h4>
            <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
            <a href="<?php echo SITE_URL; ?>/dashboard2.php" class="button homeIcon"></a>
            <a href="<?php echo QUESTIONNAIRE_URL; ?>" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
        </section> 
      
        <div id="app_data" class="scroller "> 
            <?php $parent = isset($_GET['parent'])?$_GET['parent']:""; ?>
            <h1 class="title-st1">
                Status: <?php echo getStatusName($db, $_GET['status_id'], $parent); ?>
                <a class="pull-right" href="<?php echo SITE_URL."/mdl-stat-details-config.php"?>"  style="font-size:30px; margin-right: 30px;position:absolute;right:0;">
                    <i class="fas fa-cogs "></i>
                </a>
            </h1>
            <?php if(isset($_GET['status_id']) && $_GET['status_id']!=""){ ?>
            <div class="row ">
                <div class="col-md-12 text-center">
                    <table id="dataTable" class="table">
                        <thead>
                            <tr>
                            <?php foreach ($labels as $k=>$v){ ?>                                
                                <?php 
                                $isVisibleForStatus = isFieldVisibleForStatus($db, $k, $_GET['status_id']);
                                $isVisibleForRole = isFieldVisibleForRole($db, $k, $roleID);
                                if($isVisibleForStatus&&$isVisibleForRole){
                                    echo '<th class="'.$v['class'].'">';
                                    if(isset($optionVal[$k]['label'])){
                                        echo $optionVal[$k]['label']; 
                                    } else {
                                        echo $v['name'];
                                    } 
                                    echo '</th>';
                                }
                                ?>
                            <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($initData as $k=>$v){ ?>
                            <?php 
                                $Guid_user = $v['Guid_user'];
                                $revenue = getRevenueStat($db, $v['Guid_user']);
                                $patientInfoUrl = SITE_URL.'/patient-info.php?patient='.$v['Guid_user']; 
                                if($v['account_number'] && $v['account_number']!=''){
                                    $patientInfoUrl .= '&account='.$v['account_number'];
                                }
                                $incomplateStr = "";
                                $incomplateQ = "SELECT q.Guid_qualify,q.Guid_user, '1' AS incomplete FROM tblqualify q  
                                                WHERE NOT EXISTS(SELECT qs.Guid_qualify FROM tbl_ss_qualify qs WHERE q.Guid_qualify=qs.Guid_qualify) 
                                                AND q.Guid_user=$Guid_user";
                                $incomplateR = $db->query($incomplateQ);
                                if($incomplateR){
                                    $incomplateStr = "&incomplete=1";
                                }
                            ?>
                            <tr class="text-left"> 
                                
                                <?php if(isFieldVisibleForStatus($db, 'mdl_number', $_GET['status_id']) && isFieldVisibleForRole($db, 'mdl_number', $roleID)){ ?>
                                <td><?php echo $v['mdl_number'];?></td> 
                                <?php } ?>
                                
                                <?php if(isFieldVisibleForStatus($db, 'first_name', $_GET['status_id']) && isFieldVisibleForRole($db, 'first_name', $roleID)){ ?>
                                <td><a href="<?php echo $patientInfoUrl.$incomplateStr; ?>"><?php echo (isset($v['firstname'])&&$v['firstname']!='') ? ucfirst(strtolower($v['firstname'])) : '------';?></a></td>                              
                                <?php } ?>
                                
                                <?php if(isFieldVisibleForStatus($db, 'last_name', $_GET['status_id']) && isFieldVisibleForRole($db, 'last_name', $roleID)){ ?>
                                <td><a href="<?php echo $patientInfoUrl.$incomplateStr; ?>"><?php echo formatLastName($v['lastname']); ?></a></td>                              
                                <?php } ?>
                                
                                <?php if(isFieldVisibleForStatus($db, 'account', $_GET['status_id']) && isFieldVisibleForRole($db, 'account', $roleID)){ ?>
                                <td><?php echo $v['account_number'];?></td>                              
                                <?php } ?>
                                
                                <?php if(isFieldVisibleForStatus($db, 'account_name', $_GET['status_id']) && isFieldVisibleForRole($db, 'account_name', $roleID)){ ?>
                                <td><?php echo formatAccountName($v['account_name']); ?></td>                              
                                <?php } ?>
                                
                                <?php if(isFieldVisibleForStatus($db, 'salesrep', $_GET['status_id']) && isFieldVisibleForRole($db, 'salesrep', $roleID)){ ?>
                                <td><?php echo $v['salesrep'];?></td>                              
                                <?php } ?>
                                
                                <?php if(isFieldVisibleForStatus($db, 'date', $_GET['status_id']) && isFieldVisibleForRole($db, 'date', $roleID)){ ?>
                                <td><?php echo date("n/j/Y", strtotime($v['Date'])); ?></td>                              
                                <?php } ?>
                                
                                <?php if(isFieldVisibleForStatus($db, 'date_accessioned', $_GET['status_id']) && isFieldVisibleForRole($db, 'date_accessioned', $roleID)){ ?>
                                <td>???</td>   
                                <?php } ?>
                                
                                <?php if(isFieldVisibleForStatus($db, 'date_reported', $_GET['status_id']) && isFieldVisibleForRole($db, 'date_reported', $roleID)){ ?>
                                <td><?php echo date("n/j/Y", strtotime($v['Date_created'])); ?></td>                              
                                <?php } ?>
                                
                                <?php if(isFieldVisibleForStatus($db, 'insurance_paid', $_GET['status_id']) && isFieldVisibleForRole($db, 'insurance_paid', $roleID)){ ?>
                                <td><?php echo "$".formatMoney($revenue['insurance_paid']); ?></td>  
                                <?php } ?>
                                
                                <?php if(isFieldVisibleForStatus($db, 'patient_paid', $_GET['status_id']) && isFieldVisibleForRole($db, 'patient_paid', $roleID)){ ?>
                                <td><?php echo "$".formatMoney($revenue['patient_paid']); ?></td>
                                <?php } ?>
                                
                                <?php if(isFieldVisibleForStatus($db, 'total_paid', $_GET['status_id']) && isFieldVisibleForRole($db, 'total_paid', $roleID)){ ?>
                                <td><?php echo "$".formatMoney($revenue['total']); ?></td> 
                                <?php } ?>
                                
                                <?php if(isFieldVisibleForStatus($db, 'insurance_name', $_GET['status_id']) && isFieldVisibleForRole($db, 'insurance_name', $roleID)){ ?>
                                <td><?php echo $revenue['insurance_name']; ?></td> 
                                <?php } ?>
                                
                                <?php if(isFieldVisibleForStatus($db, 'test_ordered', $_GET['status_id']) && isFieldVisibleForRole($db, 'test_ordered', $roleID)){ ?>
                                <td>??</td> 
                                <?php } ?>
                                
                                <?php if(isFieldVisibleForStatus($db, 'location', $_GET['status_id']) && isFieldVisibleForRole($db, 'location', $roleID)){ ?>
                                <td><?php echo $v['location']; ?></td> 
                                <?php } ?>
                                
                                <?php if(isFieldVisibleForStatus($db, 'Loaded', $_GET['status_id']) && isFieldVisibleForRole($db, 'Loaded', $roleID)){ ?>
                                <td><?php echo $v['Loaded']; ?></td> 
                                <?php } ?>
                            </tr>
                            <?php } ?>
                        </tbody>
                        <?php $userRevenuTotals = getStatusRevenueTotals($db, $_GET['status_id'], $searchData);                  ?>
                        <tfoot class="strong">
                            <?php if(isFieldVisibleForStatus($db, 'insurance_paid', $_GET['status_id']) && isFieldVisibleForRole($db, 'insurance_paid', $roleID)){ ?>
                            <tr>
                                <td class=" text-right" colspan="2">Insurance Total: </td>
                                <td class=" text-left" colspan="2"><?php echo "$".formatMoney($userRevenuTotals['insurance_total']); ?></td>
                            </tr>
                            <?php } ?>
                            <?php if(isFieldVisibleForStatus($db, 'patient_paid', $_GET['status_id']) && isFieldVisibleForRole($db, 'patient_paid', $roleID)){ ?>
                            <tr>
                                <td class=" text-right" colspan="2">Patient Total: </td>
                                <td class=" text-left" colspan="2"><?php echo "$".formatMoney($userRevenuTotals['patient_total']); ?></td>
                            </tr>
                            <?php } ?>
                            <?php if(isFieldVisibleForStatus($db, 'total_paid', $_GET['status_id']) && isFieldVisibleForRole($db, 'total_paid', $roleID)){ ?>
                            <tr>
                                <td class=" text-right" colspan="2">Total: </td>
                                <td class=" text-left" colspan="2"><?php echo "$".formatMoney($userRevenuTotals['total']); ?></td>
                            </tr>
                            <?php } ?>
                        </tfoot>
                    </table>
                </div>
            </div>
            <?php } ?>
                
           
        </div>
        <?php } else { ?>
            <p>Sorry! You don't have access to this page content. </p>
        <?php } ?>
    </div>
</main>




<?php require_once('scripts.php');?>
<script type="text/javascript">  
    if ($('#dataTable').length ) {
        var table = $('#dataTable').DataTable({
            
                fixedHeader: true,
                lengthMenu: [[10, 20, 30, 50, 100,-1], [10, 20, 30, 50, 100, "All"]],
                
                "pageLength": 20
        });   
    }
</script>

<?php require_once('footer.php');?>