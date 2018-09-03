<?php
ob_start();
require_once('settings.php');
require_once('config.php');
require_once('header.php');
require_once ('navbar.php');


if (!isUserLogin()) {
    Leave(SITE_URL);
}
if (isset($_GET['logout'])) {
    doLogout();
    Leave(SITE_URL);
}
$userID = $_SESSION['user']["id"];
$roleInfo = getRole($db, $userID);
$role = $roleInfo['role'];

if($role!="Admin"){
    Leave(SITE_URL."/no-permission.php");
}
$users = getUsersAndRoles($db);

$query = 'SELECT  u.Guid_user, u.email, 
	p.Guid_patient, p.firstname AS first_name, p.lastname AS last_name, 
	st.mdl_number, st.Guid_user, st.date_reported,
	r.amount AS revenue,
	s.Guid_salesrep, s.first_name AS slaserep_fName, s.last_name AS salesrep_lName,
	a.Guid_account, a.account, a.name 
        FROM tbl_mdl_stats st 
        LEFT JOIN tbluser u ON st.Guid_user=u.Guid_user
        LEFT JOIN tblpatient p ON u.Guid_user=p.Guid_user 
        LEFT JOIN tbl_revenue r ON u.Guid_user=r.Guid_user 
        LEFT JOIN tblsalesrep s ON u.Guid_user=s.Guid_user
        LEFT JOIN tblaccountrep sac ON s.Guid_salesrep=sac.Guid_salesrep
        LEFT JOIN tblaccount a ON sac.Guid_account=a.Guid_account
        WHERE p.Guid_user!="" AND st.mdl_number!="" ';

$result = $db->query($query);
if($result){
    $revenueTotal = 0;
    foreach ($result as $k=>$v){
        $revenueTotal += $v['revenue'];
    }
}
?>
<main class="full-width">   
    <div class="box full visible ">  
        <section id="palette_top">
            <h4>  
                <ol class="breadcrumb">
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="active">MDL Stats</li>                   
                </ol>      
            </h4>
            <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
            <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
        </section>
        <div class="scroller">  
            <?php if(isset($revenueTotal)){ ?>
            <div class="row">                   
                <div class="col-md-12 text-right priceSum pR-30">
                    Total Revenue:&nbsp;&nbsp;&nbsp;
                    $<?php echo formatMoney($revenueTotal); ?>
                </div>                 
            </div>   
            <?php } ?>            
            <div class="row">
                <div class="col-md-12">
                    <table id="dataTable" class="display" style="width:100%">
                        <thead>
                            <tr>
                                <th  class="actions">MDL#</th>                                
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Account</th>
                                <th>Genetic Consultant</th>
                                <th>Date Reported</th>
                                <th>Revenue</th>
                                <!--<th class="noFilter actions text-center">Actions</th>-->
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $k=>$v){ ?>
                            <tr>
                                <td><?php echo $v['mdl_number']; ?></td>
                                <td><a target="_blank" href="<?php echo SITE_URL."/patient-info.php?patient=".$v['Guid_user']; ?>"><?php echo $v['first_name']; ?></a></td>
                                <td><a target="_blank" href="<?php echo SITE_URL."/patient-info.php?patient=".$v['Guid_user']; ?>"><?php echo $v['last_name']; ?></a></td>
                                <td><?php echo $v['account']." ".$v['name']; ?></td>
                                <td><?php echo $v['slaserep_fName']." ".$v['salesrep_lName']; ?></td>
                                <td><?php echo (!preg_match("/0{4}/" , $v['date_reported'])) ? date('n/j/Y', strtotime($v['date_reported'])) : ""; ?></td>
                                <td>$<?php echo formatMoney($v['revenue']); ?></td>
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
<script type="text/javascript">  
    if ($('#dataTable').length ) {
        var table = $('#dataTable').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            //searching: false,
            //lengthChange: false,
            "pageLength": 50,
            "aoColumnDefs": [
              { 
                  
                  //"bSortable": false, 
                  "aTargets": [ '3' ] } 
            ]
        });   
    }
</script>
<?php require_once('footer.php');?>