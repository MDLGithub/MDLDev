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

if(!isset($_GET['account'])){
    Leave(SITE_URL);
}

?>
<?php require_once 'navbar.php'; ?> 


<?php

//updating mark as test users
if(isset($_POST['mark_as_test'])){
    $markedUsers =$_POST['markedRow']['user'];
    if($markedUsers){
        foreach ($markedUsers as $userID=>$v){
            updateTable($db,'tbluser', array('marked_test'=>'1'), array('Guid_user'=>$userID));
        }
    }    
}

$sqlTbl = "SELECT q.*, p.*, "
        . "a.name as account_name, "
        . "CONCAT (srep.first_name, ' ', srep.last_name) AS salesrep_name, srep.Guid_salesrep, "
        . "u.email, u.marked_test,  u.Guid_role, "
        . "q.Date_created AS date FROM tbl_ss_qualify q "
        . "LEFT JOIN tblaccount a ON q.account_number = a.account "
        . "LEFT JOIN tblaccountrep arep ON arep.Guid_account = a.Guid_account "
        . "LEFT JOIN tblsalesrep srep ON srep.Guid_salesrep = arep.Guid_salesrep "
        . "LEFT JOIN tblpatient p ON q.Guid_user = p.Guid_user "
        . "LEFT JOIN tbluser u ON q.Guid_user = u.Guid_user";
$where = "";
$whereTest = (strlen($where)) ? " AND " : " WHERE ";
$whereTest .= " u.marked_test='0' ";      
$whereIncomplete  = "";

if(isset($_GET['account']) && $_GET['account']!=''){
    //Account
    if (isset($_GET['account']) && strlen($_GET['account'])) {
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " q.account_number = '" . $_GET['account'] . "'";
    }
}


if($role == 'Physician'){
    $physicianInfo = $db->row('SELECT account_id FROM tblprovider WHERE Guid_user='.$userID);
    $account_id = $physicianInfo['account_id']; 
    $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
    $where .= " q.account_number IN (" . $account_id . ")";
}

$where  .= " AND CONCAT(p.firstname, ' ', p.lastname) NOT LIKE '%test%' "
        . "AND CONCAT(p.firstname, ' ', p.lastname) NOT LIKE '%John Smith%' "
        . "AND CONCAT(p.firstname, ' ', p.lastname) NOT LIKE '%John Doe%' "
        . "AND CONCAT(p.firstname, ' ', p.lastname) NOT LIKE '%Jane Doe%'";
if( !(isset($_POST['meets_mn']) && $_POST['meets_mn']=='incomplete')){
    $where .= "AND q.`Date_created` = (SELECT MAX(Date_created) FROM tbl_ss_qualify AS m2 WHERE q.Guid_qualify = m2.Guid_qualify)";
}


if($role == "Sales Rep"){
    $salesrepInfo = $db->row('SELECT Guid_salesrep FROM tblsalesrep WHERE Guid_user='.$userID);
    $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
    $where .= " srep.Guid_salesrep = '" . $salesrepInfo['Guid_salesrep'] . "'";
}

$sqlTbl .= $whereTest;
$sqlTbl .= $whereIncomplete;
$sqlTbl .= $where;  
  
//$sqlTbl .= " GROUP BY p.Guid_user";
$sqlTbl .= " ORDER BY date DESC";
//var_dump($sqlTbl);

$qualify_requests = $db->query($sqlTbl);

$num_estimates = $qualify_requests;


?>

<main class="full-width">
    <div class="box full visible">
        <?php if($dataViewAccess){ ?>
        <section id="palette_top" class="shorter_palette_top">
            <h4><?php echo count($num_estimates) . " Results"; ?></h4>
            <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
            <a href="<?php echo SITE_URL; ?>/dashboard2.php" class="button homeIcon"></a>
            <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit smaller_button"><strong>View Questionnaire</strong></a>
        </section>
       
        <div id="app_data" class="home_scroller">
            
            <div class="row">
                <?php 
                    if($role=='Physician'){
                      $salesRep = getProviderSalesRep($db, $_SESSION['user']['id']);
                    ?>
                <div class="col-md-6 pull-right">
                    <?php 
                        $img = ($salesRep['photo_filename']!="")? $salesRep['photo_filename']: ""; 
                        $image = ($img!="") ? SITE_URL.'/images/users/'.$img : "assets/images/default.png";
                        $address = "";
                    ?>
                    <div class="row" id="salesrepInfo1">
                        <div class="col-md-2 text-center">
                            <img width="40" src="<?php echo $image; ?>" />
                        </div>
                        <div class="col-md-5">
                            <p>
                                <?php if($salesRep['title']) { echo " ".$salesRep['title']; } ?>
                            </p>
                            <p>
                                <?php if($salesRep['first_name']) { echo $salesRep['first_name']; } ?>
                                <?php if($salesRep['last_name']) { echo " ".$salesRep['last_name']; } ?>
                            </p>                            
                        </div>
                        <div  class="col-md-5">
                            <p>
                            <?php if($salesRep['email']) { ?>
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:<?php echo $salesRep['email'];?>"><?php echo $salesRep['email'];?></a>
                                <?php }?>
                            </p>
                            <p>
                                <?php if($salesRep['phone_number']) { ?>
                                <i class="fas fa-phone"></i>
                                <a class="phone_us" href="tel:<?php echo $salesRep['phone_number']; ?>">><?php echo $salesRep['phone_number']; ?></a>
                                <?php } ?>
                            </p>
                        </div>
                    </div>                    
                </div>
                <?php } ?>
            </div>
            
            <form id="patient_information" action="" method="post" class="<?php echo $role."_table";?>">
                
                <div class="actions">
                    <button class="btn-styled btn-home" id="bulkPrint"><i class="fas fa-print"></i> Print Selected</button>
                    <?php if($role != 'Physician') { ?>
                        <button name="mark_as_test" class="btn-styled btn-home"><i class=""></i> Mark as Test</button>
                    <?php } ?>
                </div>
                <div class="formContent">
                    
                    <input name="detail_request" type="hidden" value="1">
                    <input name="date_rng" type="hidden" value="<?php echo isset($_POST['date_rng'])?$_POST['date_rng']:''; ?>">
                    <input name="meets_mn" type="hidden" value="<?php echo isset($_POST['meets_mn'])?$_POST['meets_mn']:''; ?>">
                    <input name="first_name" type="hidden" value="<?php echo isset($_POST['first_name'])?$_POST['first_name']:''; ?>">
                    <input name="last_name" type="hidden" value="<?php echo isset($_POST['last_name'])?$_POST['last_name']:''; ?>">
                    <input name="insurance" type="hidden" value="<?php echo isset($_POST['insurance'])?$_POST['insurance']:''; ?>">                   
                    <input name="selected_questionnaire" id="selected_questionnaire" type="hidden" value="">
                    <input name="selected_date" id="selected_date" type="hidden"  value="">
                
                    <table id="dataTableHome" class="pseudo_t table">

                    <?php if ($num_estimates) { ?>                 

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
                       <?php if(isFieldVisibleByRole($roleIDs['meets_mn']['view'], $roleID)) {?>
                           <th>Medical Necessity</th>
                       <?php } ?>
                       <?php if(isFieldVisibleByRole($roleIDs['from_date']['view'], $roleID)) {?>
                           <th>Date</th>
                       <?php } ?>
                       <?php if(isFieldVisibleByRole($roleIDs['first_name']['view'], $roleID)) {?>
                           <th>First Name</th>
                       <?php } ?>
                       <?php if(isFieldVisibleByRole($roleIDs['last_name']['view'], $roleID)) {?>
                           <th>Last Name</th>
                       <?php } ?>
                       
                       <?php if(isFieldVisibleByRole($roleIDs['account']['view'], $roleID)) {?>
                           <th>Account</th>
                       <?php } ?>
                     
                       <?php if(isFieldVisibleByRole($roleIDs['location']['view'], $roleID)) {?>
                           <th>Location</th>
                       <?php } ?>
                       <?php if(isFieldVisibleByRole($roleIDs['salesrep']['view'], $roleID)) {?>
                           <th>Genetic Consultant</th>  
                       <?php } ?>
                       <?php if(isFieldVisibleByRole($roleIDs['salesrep']['view'], $roleID)) {?>
                           <th>Email</th>  
                       <?php } ?>
                        </tr>
                   </thead>
                   <tbody> 
                    <?php
                        foreach ($qualify_requests as $qualify_request) {
                            $provider_name = "";
                            if (strlen($qualify_request['provider_id'])) {
                                $provider_name = $db->row("SELECT CONCAT(p.first_name, ' ', p.last_name) AS name FROM tblprovider p WHERE Guid_provider = '" . $qualify_request['provider_id'] . "'");
                                if($provider_name){
                                    $provider_name = $provider_name['name'];
                                } else {
                                    $provider_name = "";
                                }
                            }                            
                            $isIncomplete=FALSE;
                            $dataPrintable = "1";
                            if(isset($qualify_request['incomplete'])){ 
                                $isIncomplete=TRUE;
                                $dataPrintable = '2';
                            }
                            $trClass='';
                            $trClass = ($qualify_request['marked_test']=='1')?' marked_test':'';
                            if($qualify_request['Guid_role']=='6'){
                                $trClass = ' mdl_patient';
                            }
                    ?>
                            <tr class="t_row <?php echo $trClass; ?>">
                                
                                <td class="printSelectBlock text-center">
                                        <?php if(isset($qualify_request['qualified']) && $qualify_request['qualified']=='Unknown'){ ?>
                                            <input name="markedRow[user][<?php echo $qualify_request['Guid_user']; ?>]" type="checkbox" class="print1 report1" data-prinatble="0" data-selected_questionnaire="<?php echo $qualify_request['Guid_qualify']; ?>" data-selected_date="<?php echo $qualify_request['date']; ?>" />
                                        <?php } else { ?>
                                            <?php if($isIncomplete){ ?>
                                            <input type="hidden" name="q_incomplete" value="1" />
                                            <?php } ?>
                                            <input name="markedRow[user][<?php echo $qualify_request['Guid_user']; ?>]" type="checkbox" class="print1 report1" data-prinatble="<?php echo $dataPrintable; ?>" data-selected_questionnaire="<?php echo $qualify_request['Guid_qualify']; ?>" data-selected_date="<?php echo $qualify_request['date']; ?>" />
                                        <?php } ?>
                                    </td>
                                    <?php if(isset($qualify_request['qualified']) && isFieldVisibleByRole($roleIDs['meets_mn']['view'], $roleID)) {?>
                                        <td class="mn <?php echo strtolower($qualify_request['qualified']); ?>"><?php echo $qualify_request['qualified']; ?></td>
                                    <?php } else { ?>
                                        <td class="mn no">Incomplete</td>
                                    <?php } ?>
                                    <?php if(isFieldVisibleByRole($roleIDs['from_date']['view'], $roleID)) {?>
                                        <td><?php echo date("n/j/Y", strtotime($qualify_request['date'])); ?></td>
                                    <?php } ?>
                                    <?php 
                                        $accountStr = $qualify_request['account_number'] ? "&account=".$qualify_request['account_number']:"";
                                        $incompleteStr = $isIncomplete ? '&incomplete=1' : '';
                                    ?>
                                    <?php if(isFieldVisibleByRole($roleIDs['first_name']['view'], $roleID)) {?>
                                        <td>
                                            <a href="<?php echo SITE_URL."/patient-info.php?patient=".$qualify_request['Guid_user'].$accountStr.$incompleteStr; ?>">
                                            <?php echo ucfirst(strtolower($qualify_request['firstname'])); ?>
                                            </a>
                                        </td>
                                    <?php } ?>
                                    <?php if(isFieldVisibleByRole($roleIDs['last_name']['view'], $roleID)) {?>
                                        <td>
                                            <a href="<?php echo SITE_URL."/patient-info.php?patient=". $qualify_request['Guid_user'].$accountStr.$incompleteStr; ?>">
                                                <?php echo ucfirst(strtolower($qualify_request['lastname'])); ?>
                                            </a>
                                        </td>
                                    <?php } ?>                    
                                   
                                    <?php if(isFieldVisibleByRole($roleIDs['account']['view'], $roleID)) {?>
                                        <td class="tdAccount"><?php 
                                            if( $qualify_request['account_number']!="" && !is_null($qualify_request['account_number']) && $qualify_request['account_number']!="NULL"){
                                                echo $qualify_request['account_number']; 
                                                if($qualify_request['account_name']!=""){
                                                echo "<span class='account_name'>".ucwords(strtolower($qualify_request['account_name']))."</span>";
                                                }
                                            }
                                            ?>
                                        </td>
                                    <?php } ?>
                                   
                                    <?php if(isFieldVisibleByRole($roleIDs['location']['view'], $roleID)) {?>   
                                        <td><?php echo $qualify_request['source']; ?></td>
                                    <?php } ?>
                                    <?php if(isFieldVisibleByRole($roleIDs['salesrep']['view'], $roleID)) {?>
                                        <td><?php echo $qualify_request['salesrep_name']; ?></td>          
                                    <?php } ?>
                                    <?php if(isFieldVisibleByRole($roleIDs['salesrep']['view'], $roleID)) {?>
                                        <td class="mn">
                                            <?php if($qualify_request['email']==""){ ?>
                                                <img src = "<?php echo SITE_URL; ?>/assets/images/no_email_icon_30.png" />
                                            <?php } ?>
                                        </td>        
                                    <?php } ?>

                            </tr>
                        <?php }
                            }
                        ?>
                    </tbody>
                    </table>
                </div>
            </form>
        </div>
        <?php } else { ?>
            <p>Sorry! You don't have access to this page content. </p>
        <?php } ?>
    </div>
    <div id="admin_print"></div>
</main>


<?php require_once 'scripts.php'; ?>
<script type="text/javascript">
    if ($('#dataTableHome').length ) { 
        var table = $('#dataTableHome').DataTable({
                        dom: '<"top"i>rt<"bottom"flp><"wider-bottom"><"clear">',
                        orderCellsTop: true,
                        fixedHeader: true,
                        lengthMenu: [[10, 20, 30, 50, 100,-1], [10, 20, 30, 50, 100, "All"]],
                        //lengthChange: false,
                        searching: false,
                        "pageLength": 30,
                        "aoColumnDefs": [
                          { 
                              "bSortable": false, 
                              "aTargets": [ 0 ] 
                          } 
                        ]      
                    });  
    }
    
    jQuery.fn.dataTableExt.oSort['uk_date-pre']  = function(a) { 
        a = a.slice(0, -2) + ' ' + a.slice(-2);
        var date = Date.parse(a);
        return typeof date === 'number' ? date : -1;
    }    
    jQuery.fn.dataTableExt.oSort['uk_date-asc']  = function(a,b) { 
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    } 
    jQuery.fn.dataTableExt.oSort['uk_date-desc'] = function(a,b) { 
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    } 

    var table = $('#example').DataTable({
        aoColumns: [
          { sType: 'uk_date' }
        ]
    });
</script>
<?php require_once 'footer.php'; ?>