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
$users = getUsersAndRoles($db);

$initLabels = array(
    'first_name'=>'Patient First Name', 
    'last_name'=>'Patient Last Name', 
    'account'=>'Account#', 
    'salesrep'=>'Sales Rep',
);
$initQ = 'SELECT s.Guid_status, p.Guid_patient, p.firstname, p.lastname, 
	a.account AS account_number, a.name AS account_name, 
	CONCAT(srep.`first_name`, " " ,srep.`last_name`) AS salesrep  
        FROM `tbl_mdl_stats` s 
        LEFT JOIN `tblpatient` p ON s.Guid_patient=p.Guid_patient
        LEFT JOIN `tblaccount` a ON s.Guid_account=a.Guid_account
        LEFT JOIN `tblsalesrep` srep ON s.Guid_salesrep=srep.Guid_salesrep
        WHERE s.Guid_status=:Guid_status ';
$initData=$db->query($initQ, array('Guid_status'=>$_GET['status_id']));

$labels = array(
    'mdl_number'=>'MDL#', 
    'first_name'=>'Patient First Name', 
    'last_name'=>'Patient Last Name', 
    'account'=>'Account#', 
    'account_name'=>'Account Name', 
    'salesrep'=>'Sales Rep', 
    'date'=>'Date of the most recent status', 
    'date_accessioned'=>'Date Accessioned',
    'date_reported'=>'Date Reported', 
    'insurance_paid'=>'Insurance Paid', 
    'patient_paid'=>'Patient Paid', 
    'total_paid'=>'Total Paid', 
    'insurance_name'=>'Insurance Name',
    'test_ordered'=>'Test Ordered', 
    'location'=>'Location'
);


require_once ('navbar.php');
?>

<main class="full-width">
    <div class="box full visible ">
      
        <section id="palette_top" class="shorter_palette_top">
            <h4>  
                <ol class="breadcrumb">
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="active">MDL Stats</li>                   
                </ol>      
            </h4>
            <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
            <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
        </section> 
      
        <div id="app_data" class="scroller "> 
            <h1 class="title-st1">MDL Status Details</h1>
          
            <div class="row ">
                <div class="col-md-12 text-center">
                    <table class="table">
                        <thead>
                            <tr>
                            <?php foreach ($initLabels as $k=>$v){ ?>
                                <th>
                                    <?php echo $v;?>
                                </th>
                            <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($initData as $k=>$v){ ?>
                            <tr class="text-left">                                
                                <td><?php echo $v['firstname'];?></td>                              
                                <td><?php echo $v['lastname'];?></td>                              
                                <td><?php echo $v['account_number'];?></td>                              
                                <td><?php echo $v['salesrep'];?></td>                              
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
                
           
        </div>
      
    </div>
</main>




<?php require_once('scripts.php');?>

<?php require_once('footer.php');?>