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
$roleIDs = unserialize($accessRole['role_ids']);
$dataViewAccess = isUserHasAnyAccess($roleIDs, $roleID, 'view');

if (isset($_POST['search']) && (strlen($_POST['from_date']) || strlen($_POST['to_date']))) {
    verify_input($error);
}
?>
<?php require_once 'navbar.php'; ?> 
<!--SEARCH FORM BLOCK Start-->

<!--SEARCH FORM BLOCK END-->

<?php

if(isset($_POST['mark_as_test'])){
    $markedUsers =$_POST['markedRow']['user'];
    if($markedUsers){
        foreach ($markedUsers as $userID=>$v){
            updateTable($db,'tbl_ss_qualify', array('mark_as_test'=>'1'), array('Guid_user'=>$userID));
        }
    }    
}

$sqlTbl  =  "SELECT q.*, p.*, u.email, q.Date_created AS date FROM tbl_ss_qualify q "
            . "LEFT JOIN tblpatient p ON q.Guid_user = p.Guid_user "
            . "LEFT JOIN tbluser u ON q.Guid_user = u.Guid_user";
$where = "";
$whereTest = (strlen($where)) ? " AND " : " WHERE ";
$whereTest .= " q.mark_as_test='0' ";      
$whereIncomplete  = "";


//if ((!count($error)) && (!isset($_POST['clear'])) && (!empty($_POST))) {
if ((!isset($_POST['clear'])) && (!empty($_POST['search']))) {
   
    $where = "";  $whereTest = "";  $whereIncomplete  = "";   
    
    if (isset($_POST['mark_test']) && strlen($_POST['mark_test'])) {
        $whereTest = (strlen($where)) ? " AND " : " WHERE ";
        $whereTest .= " q.mark_as_test = '1'";
    } else {
        $whereTest = (strlen($where)) ? " AND " : " WHERE ";
        $whereTest .= " q.mark_as_test = '0'";
    }
    
    if (isset($_POST['meets_mn']) && strlen($_POST['meets_mn'])) {
        $whereTest = "";
        if($_POST['meets_mn']=='incomplete'){
            $sqlTbl  = "SELECT q.*,p.*, u.email, q.Date_created AS `date` FROM tblqualify q  
                        LEFT JOIN tblpatient p ON q.Guid_user = p.Guid_user  
                        LEFT JOIN tbluser u ON p.Guid_user = u.Guid_user"; 
            $where = " WHERE NOT EXISTS(SELECT * FROM tbl_ss_qualify qs WHERE q.Guid_qualify=qs.Guid_qualify)";
        }else{
            $where = (strlen($where)) ? " AND " : " WHERE ";
            $where .= " q.qualified = '" . $_POST['meets_mn'] . "'";
        }
    }
    
    //if (isset($_POST['date_rng'])) {
    if (strlen($_POST['from_date']) && strlen($_POST['to_date'])) {
        if ($_POST['from_date'] == $_POST['to_date']) {
            $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
            $where .= " q.Date_created LIKE '%" . date("Y-m-d", strtotime($_POST['from_date'])) . "%'";
        } else {
            $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
            $where .= " q.Date_created BETWEEN '" . date("Y-m-d", strtotime($_POST['from_date'])) . "' AND '" . date("Y-m-d", strtotime($_POST['to_date'])) . "'";
        }
    }    

    if (isset($_POST['first_name']) && strlen(trim($_POST['first_name']))) {
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " p.firstname = '" . $_POST['first_name'] . "'";
    }

    if (isset($_POST['last_name']) && strlen(trim($_POST['last_name']))) {
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " p.lastname = '" . $_POST['last_name'] . "'";
    }

    if (isset($_POST['insurance']) && strlen($_POST['insurance'])) {
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " q.insurance = '" . $_POST['insurance'] . "'";
    }

    if (isset($_POST['provider']) && strlen($_POST['provider'])) {
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " q.provider_id = '" . $_POST['provider'] . "'";
    }

    if (isset($_POST['location']) && strlen($_POST['location'])) {
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " q.source = '" . $_POST['location'] . "'";
    }
    

    if (isset($_POST['account']) && strlen($_POST['account'])) {
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " q.account_number = '" . $_POST['account'] . "'";
    } elseif (isset($_POST['salesrep']) && strlen($_POST['salesrep'])) {
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " q.account_number IN (" . $default_account . ")";
    }
    
    $postAccount = isset($_POST['account']) ? $_POST['account'] : "";
    if ((isset($role) && $role == "Sales Rep") && (!strlen($postAccount))) {
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " q.account_number IN (" . $default_account . ")";
    }
    
   
}

if($role == 'Physician'){
    $physicianInfo = $db->row('SELECT Guid_provider FROM tblprovider WHERE Guid_user='.$userID);
    $physicianID = $physicianInfo['Guid_provider']; 
    $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
    $where .= " q.provider_id='".$physicianID."'";
}

$where  .= " AND CONCAT(p.firstname, ' ', p.lastname) NOT LIKE '%test%' "
        . "AND CONCAT(p.firstname, ' ', p.lastname) NOT LIKE 'John Doe' "
        . "AND CONCAT(p.firstname, ' ', p.lastname) NOT LIKE 'Jane Doe'";

$sqlTbl .= $whereTest;
$sqlTbl .= $whereIncomplete;
$sqlTbl .= $where;  
  
$sqlTbl .= " GROUP BY p.Guid_user";
$sqlTbl .= " ORDER BY date DESC";

$qualify_requests = $db->query($sqlTbl);

$num_estimates = $qualify_requests;


?>

<main class = "full-width non_responsive">
    <div class="box full visible">
        <?php if($dataViewAccess){ ?>
        <section id="palette_top" class="shorter_palette_top">
            <h4><?php echo count($num_estimates) . " Results"; ?></h4>
            <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
            <a href="<?php echo QUESTIONNAIRE_URL; ?>" target="_blank" class="button submit smaller_button"><strong>View Questionnaire</strong></a>
        </section>

        <div id="app_data" class="home_scroller">
            <!--<div class = "row">
              <div class = "col-md-12">  -->
              <div id = "stats">
                <div id = "calendar_container">
                    <div id = "calendar_header"></div>
                    <div id = "performance_chart">
                        <p>Me Top Performer</p>
                    </div>
                    <div id = "calendar">
                        <div class = "day_header">
                            <p>All Genetic Consultants</p>
                            <div class = "genetic-buttons">
                                <a href = "#" class = "details">Details</a>
                                <a href = "#" class = "summary">Summary</a>
                            </div>
                            <a href = "#" class = "button submit calendar_button"><strong>Full Calender</strong></a>
                        </div>
                        <div class = "day">
                            <p>Sun 2</p>
                            
                            <div class = "notifications">
                                <div class = "cal_note">
                                <strong>Woman2Woman</strong>
                                    <p>Brandon Franklin</p>
                                    <span>12<i class="fa fa-cloud"></i> |</span>
                                    <span>8<i class="fa fa-cloud"></i>|</span>
                                    <span>3<i class="fa fa-cloud"></i>|</span>
                                    <span>1<i class="fa fa-cloud"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class = "day"><p>Mon 3</p></div>
                        <div class = "day"><p>Tue 4</p></div>
                        <div class = "day"><p>Wed 5</p></div>
                        <div class = "day"><p>Thu 6</p></div>
                        <div class = "day"><p>Fri 7</p></div>
                        <div class = "day"><p>Sat 8</p></div>
                    </div>
                </div>

                <div id = "chart_stats">
                    <div class = "chart_header"></div>
                    <div class = "top_accounts"></div>
                    <div class = "top_consultants"></div>
                </div>
            <!--</div>
            </div>-->
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
</script>
<?php require_once 'footer.php'; ?>