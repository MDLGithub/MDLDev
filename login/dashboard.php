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

if (isset($_POST['search']) && (strlen($_POST['from_date']) || strlen($_POST['to_date']))) {
    verify_input($error);
}
?>
<?php require_once 'navbar.php'; ?> 
<!--SEARCH FORM BLOCK Start-->
<aside id="action_palette" class="" >		
    <div class="box full">
        <h4 class="box_top">Filters</h4>
        <?php if($dataViewAccess) { ?>
        <div class="boxtent scroller ">
            <form id="filter_form" action="" method="post">	
                <?php
                $date_error = "";

                if (isset($_POST['search'])) {
                    if (isset($error['from_date'])) {
                        $date_error = " error";
                    } elseif (strlen($_POST['from_date'])) {
                        $date_error = " valid";
                    }
                }
                ?>
                <?php if(isFieldVisibleByRole($roleIDs['from_date']['view'], $roleID)) {?>
                <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['from_date'])) && (strlen($_POST['from_date']))) ? " show-label" : ""; ?><?php echo $date_error; ?>">
                    <label class="dynamic" for="from_date"><span>From Date</span></label>

                    <div class="group">                       
                        <input readonly class="datepicker" type="text" id="from_date" name="from_date" value="<?php echo ((!isset($_POST['clear'])) && isset($_POST['from_date']) && strlen($_POST['from_date'])) ? $_POST['from_date'] : ""; ?>" placeholder="From Date">

                        <p class="f_status">
                            <span class="status_icons"><strong></strong></span>
                        </p>
                    </div>
                </div>
                <?php } ?>
                <?php
                $date_error = "";

                if (isset($_POST['search'])) {
                    if (isset($error['to_date'])) {
                        $date_error = " error";
                    } elseif (strlen($_POST['to_date'])) {
                        $date_error = " valid";
                    }
                }
                ?>
                <?php if(isFieldVisibleByRole($roleIDs['to_date']['view'], $roleID)) {?>
                    <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['to_date'])) && (strlen($_POST['to_date']))) ? " show-label" : ""; ?><?php echo $date_error; ?>">
                        <label class="dynamic" for="to_date"><span>To Date</span></label>

                        <div class="group">                       
                            <input readonly class="datepicker" type="text" id="to_date" name="to_date" value="<?php echo ((!isset($_POST['clear'])) && isset($_POST['to_date']) && strlen($_POST['to_date'])) ? $_POST['to_date'] : ""; ?>" placeholder="To Date" max="<?php echo date('Y-m-d'); ?>">

                            <p class="f_status">
                                <span class="status_icons"><strong></strong></span>
                            </p>
                        </div>
                    </div>
                <?php } ?>
                <?php if(isFieldVisibleByRole($roleIDs['meets_mn']['view'], $roleID)) {?>
                    <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['meets_mn'])) && (strlen($_POST['meets_mn']))) ? " show-label valid" : ""; ?>">
                        <label class="dynamic" for="meets_mn"><span>Medical Necessity</span></label>

                        <div class="group">
                            <select id="meets_mn" name="meets_mn" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['meets_mn'])) && (strlen($_POST['meets_mn']))) ? "" : "no-selection"; ?>">
                                <option value="">Medical Necessity</option>
                                <option value="yes"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['meets_mn']) && ($_POST['meets_mn'] == "yes")) ? " selected" : ""); ?>>Yes</option>
                                <option value="no"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['meets_mn']) && ($_POST['meets_mn'] == "no")) ? " selected" : ""); ?>>No</option>
                                <option value="unknown"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['meets_mn']) && ($_POST['meets_mn'] == "unknown")) ? " selected" : ""); ?>>Unknown</option>
                                <option value="incomplete"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['meets_mn']) && ($_POST['meets_mn'] == "incomplete")) ? " selected" : ""); ?>>Incomplete</option>
                            </select>

                            <p class="f_status">
                                <span class="status_icons"><strong></strong></span>
                            </p>
                        </div>
                    </div>
                <?php } ?>                
                <?php if(isFieldVisibleByRole($roleIDs['first_name']['view'], $roleID)) {?>
                    <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['first_name'])) && (strlen(trim($_POST['first_name'])))) ? " show-label valid" : ""; ?>">
                        <label class="dynamic" for="first_name"><span>First Name</span></label>

                        <div class="group">
                            <input id="first_name" name="first_name" type="text" value="<?php echo ((!isset($_POST['clear'])) && isset($_POST['first_name']) && strlen(trim($_POST['first_name']))) ? trim($_POST['first_name']) : ""; ?>" placeholder="First Name">

                            <p class="f_status">
                                <span class="status_icons"><strong></strong></span>
                            </p>
                        </div>
                    </div>
                <?php } ?>
                <?php if(isFieldVisibleByRole($roleIDs['last_name']['view'], $roleID)) {?>
                <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['last_name'])) && (strlen(trim($_POST['last_name'])))) ? " show-label valid" : ""; ?>">
                    <label class="dynamic" for="last_name"><span>Last Name</span></label>

                    <div class="group">
                        <input id="last_name" name="last_name" type="text" value="<?php echo ((!isset($_POST['clear'])) && isset($_POST['last_name']) && strlen(trim($_POST['last_name']))) ? trim($_POST['last_name']) : ""; ?>" placeholder="Last Name">

                        <p class="f_status">
                            <span class="status_icons"><strong></strong></span>
                        </p>
                    </div>
                </div>
                <?php } ?>
                <?php if(isFieldVisibleByRole($roleIDs['insurance']['view'], $roleID)) {?>
                    <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['insurance'])) && (strlen($_POST['insurance']))) ? " show-label valid" : ""; ?>">
                        <label class="dynamic" for="insurance"><span>Insurance</span></label>

                        <div class="group">
                            <select id="insurance" name="insurance" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['insurance'])) && (strlen($_POST['insurance']))) ? "" : "no-selection"; ?>">
                                <option value="">Insurance</option>
                                <option value="aetna"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['insurance']) && ($_POST['insurance'] == "aetna")) ? " selected" : ""); ?>>Aetna</option>
                                <option value="medicare"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['insurance']) && ($_POST['insurance'] == "medicare")) ? " selected" : ""); ?>>Medicare</option>
                                <option value="other"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['insurance']) && ($_POST['insurance'] == "other")) ? " selected" : ""); ?>>Other</option>
                                <option value="none"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['insurance']) && ($_POST['insurance'] == "none")) ? " selected" : ""); ?>>None</option>
                            </select>

                            <p class="f_status">
                                <span class="status_icons"><strong></strong></span>
                            </p>
                        </div>
                    </div>
                <?php }?>
                <?php 
                if (($role == "Sales Rep") || ((isset($_POST['salesrep']) && strlen($_POST['salesrep']) && (!isset($_POST['clear']))))) {
                    $query = "SELECT tblaccount.*                   
                            FROM tblsalesrep 
                            LEFT JOIN `tblaccountrep` ON  tblsalesrep.Guid_salesrep = tblaccountrep.Guid_salesrep
                            LEFT JOIN `tblaccount` ON tblaccountrep.Guid_account = tblaccount.Guid_account                    
                            WHERE tblsalesrep.Guid_user=";

                    if (isset($_POST['salesrep']) && strlen($_POST['salesrep'])) {
                        $query .= $_POST['salesrep'];
                    } else {
                        $query .= $_SESSION['user']['id'];
                    }
                } elseif ($role == "Sales Manager") {
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
                        $query .= " Guid_category IN (" . $userLinks . ") ";
                    }                     
                } else {
                    $query = "SELECT * FROM tblaccount";
                }
                $query .= " ORDER BY account";

                $accounts = $db->query($query);
                ?>
                
                <?php if(isFieldVisibleByRole($roleIDs['account']['view'], $roleID)) {?>
                    <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['account'])) && (strlen(trim($_POST['account'])))) ? " show-label valid" : ""; ?>">
                        <label class="dynamic" for="account"><span>Account</span></label>

                        <div class="group">
                            <select id="account" name="account" data-user-role="<?php echo $roleInfo['role']; ?>" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['account'])) && (strlen($_POST['account']))) ? "" : "no-selection"; ?>">
                                <option value="">Account</option>	
                                <?php
                                foreach ($accounts as $account) {
                                    $default_account .= $account['account'] . ",";
                                    ?>
                                    <option value="<?php echo $account['account']; ?>"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['account']) && ($_POST['account'] == $account['account'])) ? " selected" : ""); ?>><?php echo $account['account'] . " - " . formatAccountName($account['name']); ?></option>
                                    <?php
                                }

                                $default_account = rtrim($default_account, ',');
                                ?>
                            </select>

                            <p class="f_status">
                                <span class="status_icons"><strong></strong></span>
                            </p>
                        </div>
                    </div>
                <?php } ?>
                
                <?php if(isFieldVisibleByRole($roleIDs['provider']['view'], $roleID)) {?>
                    <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['provider'])) && (strlen($_POST['provider']))) ? " show-label valid" : ""; ?>">
                        <label class="dynamic" for="provider"><span>Provider</span></label>

                        <div class="group">
                            <select id="provider" name="provider" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['provider'])) && (strlen($_POST['provider']))) ? "" : "no-selection"; ?>">
                                <option value="">Provider</option>							
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
                                <span class="status_icons"><strong></strong></span>
                            </p>
                        </div>
                    </div>
                <?php } ?>
                <?php if(isFieldVisibleByRole($roleIDs['location']['view'], $roleID)) {?>
                    <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['location'])) && (strlen($_POST['location']))) ? " show-label valid" : ""; ?>">
                        <label class="dynamic" for="location"><span>Event</span></label>

                        <div class="group">
                            <select id="location" name="location" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['location'])) && (strlen($_POST['location']))) ? "" : "no-selection"; ?>">
                                <option value="">Event</option>							
                                <?php
                                $locations = $db->query("SELECT description FROM tblsource ORDER BY description ASC");

                                foreach ($locations as $location) {
                                    ?>
                                    <option value="<?php echo $location['description']; ?>"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['location']) && ($_POST['location'] == $location['description'])) ? " selected" : ""); ?>><?php echo $location['description']; ?></option>
                                    <?php
                                }
                                ?>
                            </select>

                            <p class="f_status">
                                <span class="status_icons"><strong></strong></span>
                            </p>
                        </div>
                    </div>
                <?php  } ?>
                <?php if(isFieldVisibleByRole($roleIDs['salesrep']['view'], $roleID)) {?>
                    <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['salesrep'])) && (strlen($_POST['salesrep']))) ? " show-label valid" : ""; ?>">
                        <label class="dynamic" for="salesrep"><span>Genetic Consultant</span></label>

                        <div class="group">
                            <select id="salesrep" name="Guid_salesrep" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['Guid_salesrep'])) && (strlen($_POST['Guid_salesrep']))) ? "" : "no-selection"; ?>">
                                <option value="">Genetic Consultant</option>							
                                <?php
                                $salesreps = $db->query("SELECT * FROM tblsalesrep GROUP BY first_name");

                                foreach ($salesreps as $salesrep) {
                                    ?>
                                    <option value="<?php echo $salesrep['Guid_salesrep']; ?>"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['Guid_salesrep']) && ($_POST['Guid_salesrep'] == $salesrep['Guid_salesrep'])) ? " selected" : ""); ?>><?php echo $salesrep['first_name']." ".$salesrep['last_name']; ?></option>
                                    <?php
                                }
                                ?>
                            </select>

                            <p class="f_status">
                                <span class="status_icons"><strong></strong></span>
                            </p>
                        </div>
                    </div>
                <?php }   ?>
                <?php if($role != 'Physician') { ?>
                <div>
                    <input id="show-tests" name="mark_test" value="1" type="checkbox" <?php echo ((!isset($_POST['clear'])) && (isset($_POST['mark_test']) && ($_POST['mark_test'] == 1)) ? " checked" : ""); ?> />
                    <label for="show-tests">Show Tests</label>                     
                </div>
                <?php } ?>
                
                <button id="filter" value="1" name="search" type="submit" class="button filter half"><strong>Search</strong></button>
                <a href="<?php ?>" class="button cancel half"><strong>Clear</strong></a>
            </form>
            <!--********************   SEARCH BY PALETTE END    ******************** -->

        </div>
        <?php } ?>
    </div>    
</aside>
<!--SEARCH FORM BLOCK END-->


<?php  
//upload .csv and save dmdl data 
$uploadMessage = "";
if(isset($_POST['dmdlUpload'])){        
    $tmpName = $_FILES['dmdlCsvUpload']['tmp_name'];
    $csvArray = array_map('str_getcsv', file($tmpName));
    $tableFields = array('TestCode','TestName','MDLNumber','PatientID', 'PhysicianID');
    unset($csvArray[0]);        
    $data = array();
    $csvArrData = array();
    for($i=0;$i<count($tableFields); $i++){           
       for($j=1;$j<=count($csvArray); $j++){
           $data[$j][$tableFields[$i]] = $csvArray[$j][$i];
       }
    }
    foreach ($data as $k=>$v){
        $checkMdlNum = $db->row("SELECT `MDLNumber` FROM `tbl_mdl_dmdl` WHERE MDLNumber=:MDLNumber", array('MDLNumber'=>$v['MDLNumber']));
        
        if(!$checkMdlNum || empty($checkMdlNum)){
            $insert = insertIntoTable($db, 'tbl_mdl_dmdl', $v);

            if($insert['insertID']){
                $uploadMessage = "<p>Data loaded successfully!</p>";
            } else {
                $uploadMessage .= "<p class='color-red'>Data loaded Error.</p>";
            }
        }
    } 
}
//updating mark as test users
if(isset($_POST['mark_as_test'])){    
    if( isset($_POST['markedRow']['user']) ){
        $markedUsers =$_POST['markedRow']['user'];
        foreach ($markedUsers as $userID=>$v){
            updateTable($db,'tbluser', array('marked_test'=>'1'), array('Guid_user'=>$userID));
        }
    }    
}

$sqlTbl = "SELECT q.*, p.*, "
        . "AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%') as firstname, AES_DECRYPT(p.lastname_enc, 'L@stn@m3&%#') as lastname, "
        . "a.name as account_name, a.Guid_account, a.Guid_category as account_category, "
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

//if ((!count($error)) && (!isset($_POST['clear'])) && (!empty($_POST))) {
if ((!isset($_POST['clear'])) && (!empty($_POST['search']))) {   
    $where = "";  $whereTest = "";  $whereIncomplete  = "";
    //Medical Necessity
    if (isset($_POST['meets_mn']) && strlen($_POST['meets_mn'])) {
        $whereTest = "";
        if($_POST['meets_mn']=='incomplete'){
            $sqlTbl  = "SELECT q.*, a.name as account_name, a.Guid_account,
                        p.*, AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%') as firstname, AES_DECRYPT(p.lastname_enc, 'L@stn@m3&%#') as lastname,
                        CONCAT (srep.first_name, ' ', srep.last_name) AS salesrep_name, srep.Guid_salesrep, 
                        u.email, u.marked_test, u.Guid_role, q.Date_created AS `date`, 
                        '1' AS incomplete FROM tblqualify q  
                        LEFT JOIN tblaccount a ON q.account_number = a.account
                        LEFT JOIN tblaccountrep arep ON arep.Guid_account = a.Guid_account 
                        LEFT JOIN tblsalesrep srep ON srep.Guid_salesrep = arep.Guid_salesrep
                        LEFT JOIN tblpatient p ON q.Guid_user = p.Guid_user  
                        LEFT JOIN tbluser u ON p.Guid_user = u.Guid_user"; 
            $where = " WHERE NOT EXISTS(SELECT * FROM tbl_ss_qualify qs WHERE q.Guid_qualify=qs.Guid_qualify) AND u.marked_test='0'";
        
        }else{
            $where = (strlen($where)) ? " AND " : " WHERE ";
            $where .= " q.qualified = '" . $_POST['meets_mn'] . "'";
        }
    }
    
    //Marked as test
    if (isset($_POST['mark_test']) && strlen($_POST['mark_test'])) {
        $whereTest = (strlen($where)) ? " AND " : " WHERE ";
        $whereTest .= " u.marked_test = '1'";
    } else {
        $whereTest = (strlen($where)) ? " AND " : " WHERE ";
        $whereTest .= " u.marked_test = '0'";
    }
    
    //From date - To Date range
    if (strlen($_POST['from_date']) && strlen($_POST['to_date'])) {
        if ($_POST['from_date'] == $_POST['to_date']) {
            $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
            $where .= " q.Date_created LIKE '%" . date("Y-m-d", strtotime($_POST['from_date'])) . "%'";
        } else {
            $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
            $where .= " q.Date_created BETWEEN '" . date("Y-m-d", strtotime($_POST['from_date'])) . "' AND '" . date("Y-m-d", strtotime($_POST['to_date'])) . "'";
        }
    }  
    
    //First Name
    if (isset($_POST['first_name']) && strlen(trim($_POST['first_name']))) {
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " LOWER(CONVERT(AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%') USING 'utf8')) LIKE '%" . strtolower($_POST['first_name']) . "%'";
    }
    //Last Name
    if (isset($_POST['last_name']) && strlen(trim($_POST['last_name']))) {
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " LOWER(CONVERT(AES_DECRYPT(lastname_enc, 'L@stn@m3&%#') USING 'utf8')) LIKE '%" . strtolower($_POST['last_name']) . "%'";
    }
    //Insurance
    if (isset($_POST['insurance']) && strlen($_POST['insurance'])) {
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " q.insurance = '" . $_POST['insurance'] . "'";
    }
    //Account
    if (isset($_POST['account']) && strlen($_POST['account'])) {
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " q.account_number = '" . $_POST['account'] . "'";
    } elseif (isset($_POST['salesrep']) && strlen($_POST['salesrep'])) {
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " q.account_number IN (" . $default_account . ")";
    }
    //Provider
    if (isset($_POST['provider']) && strlen($_POST['provider'])) {
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " q.provider_id = '" . $_POST['provider'] . "'";
    }
    //Location
    if (isset($_POST['location']) && strlen($_POST['location'])) {
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " q.source = '" . $_POST['location'] . "'";
    }
    //Genetic Consultant
    if (isset($_POST['Guid_salesrep']) && strlen($_POST['Guid_salesrep'])) {
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " srep.Guid_salesrep = '" . $_POST['Guid_salesrep'] . "'";
    }
    
    
    $postAccount = isset($_POST['account']) ? $_POST['account'] : "";
    if ((isset($role) && $role == "Sales Rep") && (!strlen($postAccount))) {
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " q.account_number IN (" . $default_account . ")";
    }
} 

if($role == 'Physician'){
    $physicianInfo = $db->row('SELECT account_id FROM tblprovider WHERE Guid_user='.$userID);
    $account_id = $physicianInfo['account_id']; 
    $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
    $where .= " q.account_number IN (" . $account_id . ")";
}

$where  .= " AND CONCAT(AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%'), ' ', AES_DECRYPT(p.lastname_enc, 'L@stn@m3&%#')) NOT LIKE '%test%' "
        . "AND CONCAT(AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%'), ' ', AES_DECRYPT(p.lastname_enc, 'L@stn@m3&%#')) NOT LIKE '%John Smith%' "
        . "AND CONCAT(AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%'), ' ', AES_DECRYPT(p.lastname_enc, 'L@stn@m3&%#')) NOT LIKE '%John Doe%' "
        . "AND CONCAT(AES_DECRYPT(p.firstname_enc, 'F1rstn@m3@_%'), ' ', AES_DECRYPT(p.lastname_enc, 'L@stn@m3&%#')) NOT LIKE '%Jane Doe%'";
if( !(isset($_POST['meets_mn']) && $_POST['meets_mn']=='incomplete')){
    $where .= "AND q.`Date_created` = (SELECT MAX(Date_created) FROM tbl_ss_qualify AS m2 WHERE q.Guid_qualify = m2.Guid_qualify)";
}


if($role == "Sales Rep"){
    $salesrepInfo = $db->row('SELECT Guid_salesrep FROM tblsalesrep WHERE Guid_user='.$userID);
    $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
    $where .= " srep.Guid_salesrep = '" . $salesrepInfo['Guid_salesrep'] . "'";
}


if($role == "Sales Manager"){    
    $userCategories = $db->query("SELECT Guid_category FROM `tbl_mdl_category_user_link` WHERE Guid_user=:Guid_user", array('Guid_user'=>$userID)); 
    $userLinks = '';
    if(!empty($userCategories)){
        foreach ($userCategories as $k=>$v){
            $userLinks .= $v['Guid_category'].', ';
        }
        $userLinks = rtrim($userLinks, ', ');
    }    
    if($userLinks != ''){
        $where .= (strlen($where) || strlen($whereTest)) ? " AND " : " WHERE ";
        $where .= " a.Guid_category IN (" . $userLinks . ") ";
    }    
}


$sqlTbl .= $whereTest;
$sqlTbl .= $whereIncomplete;
$sqlTbl .= $where;  
  
//$sqlTbl .= " GROUP BY p.Guid_user";
$sqlTbl .= " ORDER BY date DESC";
$qualify_requests = $db->query($sqlTbl);

$num_estimates = $qualify_requests;


if(isset($_GET['resetDmdlData']) && $_GET['resetDmdlData']=='1'){
    if($role=='Admin'){
        //remove Loaded Data
        $loadedDataTables = array('tbluser','tblpatient', 'tblaccount', 
                                    'tblprovider', 'tbl_mdl_number', 'tbl_mdl_status_log', 
                                    'tbl_mdl_payors', 'tbl_revenue', 'tbl_mdl_cpt_code'
                                );
        foreach ($loadedDataTables as $k=>$tableName){
            $db->query("DELETE FROM $tableName WHERE Loaded='Y'");
        } 
        //Reset Linked Flags
        $linkedDataTables = array('tbl_mdl_dmdl','tblpatient');
        foreach ($linkedDataTables as $k=>$tableName){
            $db->query("UPDATE $tableName SET Linked='N' WHERE Linked='Y'");
        } 
        
        Leave(SITE_URL."/dashboard.php");
    }
}

?>

<main class="">
    <div class="box full visible">
        <?php if($dataViewAccess){ ?>
        <section id="palette_top" class="shorter_palette_top">
            <h4  class = "palette_results" ><?php echo count($num_estimates) . " Results"; ?></h4>
            <?php echo topNavLinks($role); ?>
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
                    <div class="salesrepInfoBlock">
                        <div id="physician-gc" class="col-md-12">
                            <label class="col-md-12 col-sm-4"><?php if($salesRep['title']) { echo " ".$salesRep['title']; } ?></label>
                            <div class="imageBox col-md-6 col-sm-4">
                                <div class="pic">
                                    <img width="50" class="salesrepProfilePic" src="<?php echo $image; ?>">
                                </div>
                                <div class="name text-center">
                                    <?php if($salesRep['first_name']) { echo $salesRep['first_name']; } ?>
                                    <?php if($salesRep['last_name']) { echo " ".$salesRep['last_name']; } ?>                           
                                </div>
                            </div>

                            <div id="salesrepInfo1">
                                <div>
                                <?php if($salesRep['email']) { ?>
                                    <i class="fas fa-envelope"></i>
                                    <a href="mailto:<?php echo $salesRep['email'];?>"><?php echo $salesRep['email'];?></a>
                                <?php }?>
                                </div>
                                <div>
                                    <?php if($salesRep['phone_number']) { ?>
                                    <i class="fas fa-phone"></i>
                                    <a class="phone_us" href="tel:<?php echo $salesRep['phone_number']; ?>">><?php echo $salesRep['phone_number']; ?></a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>                                          
                </div>
                <?php } ?>
            </div>
            
            <div class="row">
                <div class="col-md-4"></div>
                <div class="col-md-8 dmdlForm">
                    <?php if($role=='Admin'){ ?> 
                    <form action="" method="POST" enctype="multipart/form-data">                                                           
                        <span class="dmdlCsvUpload">  
                            <input type="file" name="dmdlCsvUpload" />
                        </span> 
                        <button class="upload" type="submit" name="dmdlUpload">Upload</button>  
                        <span class="dmdlRefresh">  
                            <a title="Open dMDL Screen." href="<?php echo SITE_URL.'/dashboard.php?refresh=1'; ?>" class="refresh" type="submit" name="dmdlRefresh"><i class="fas fa-sync-alt"></i></a>
                        </span>     
                        <span class="dmdlRefresh">  
                            <a title="Reset dMDL Loaded Data." href="<?php echo SITE_URL.'/dashboard.php?resetDmdlData=1'; ?>" class="refresh" type="submit" name="dmdlRefresh"><i class="fa fa-clock"></i></a>
                        </span>  
                    </form>
                    <div class="uploadMsg">
                    <?php if($uploadMessage!=""){ echo $uploadMessage; }?>
                    </div>
                    <?php } ?>
                </div>
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
                           <th>Event</th>
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
                                                <?php echo formatLastName($qualify_request['lastname']); ?>
                                            </a>
                                        </td>
                                    <?php } ?>                    
                                   
                                    <?php if(isFieldVisibleByRole($roleIDs['account']['view'], $roleID)) {?>
                                        <td class="tdAccount">
                                            <?php 
                                                if( $qualify_request['account_number']!="" && !is_null($qualify_request['account_number']) && $qualify_request['account_number']!="NULL"){
                                                    $accountURL = SITE_URL . '/accounts.php?account_id='.$qualify_request['Guid_account'];
                                                    echo "<a href='".$accountURL."'>".$qualify_request['account_number']."</a>"; 
                                                    if($qualify_request['account_name']!=""){
                                                    echo "<span class='account_name'>".formatAccountName($qualify_request['account_name'])."</span>";
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

<button id="action_palette_toggle" class=""><i class="fa fa-2x fa-angle-left"></i></button>

<?php 
if(isset($_POST['dmdlUpdate'])){
    
    if(isset($_POST['dmdl']['selected'])){
        foreach ($_POST['dmdl']['selected'] as $mdlNum=>$v){
            $dmdlItem = $_POST["dmdl"]["$mdlNum"];
            $data = $_POST['dmdl'][$mdlNum];
            
            if( isset($_POST["dmdl"]["selected"][$mdlNum])) { //ceckbosx is checked
                
                $Guid_mdl_dmdl = $data['Guid_mdl_dmdl'];
                
                //patient data
                $Guid_dmdl_patient= isset($data['Guid_PatientId'])?$data['Guid_PatientId']:'';
                $Guid_dmdl_physician=isset($data['Physician']['GUID_PhysicianID'])?$data['Physician']['GUID_PhysicianID']:'';
                $account_number = isset($data['account']['number'])?$data['account']['number']:'';
                $firstname_enc = isset($data['firstname'])?$data['firstname']:'';
                $lastname_enc = isset($data['lastname'])?$data['lastname']:'';
                $dob = isset($data['dob'])?$data['dob']:'';
                $ethnicity = isset($data['ethnicity'])?$data['ethnicity']:'';                
                $phone_number = isset($data['phone_number'])?$data['phone_number']:'';
                $phone_number_home = isset($data['phone_number_home'])?$data['phone_number_home']:'';
                $gender = isset($data['gender'])?$data['gender']:'';
                $address = isset($data['address'])?$data['address']:'';
                $address1 = isset($data['address1'])?$address1:'';
                $city = isset($data['city'])?$data['city']:'';
                $state = isset($data['state'])?$data['state']:'';
                $zip = isset($data['zip'])?$data['zip']:'';
                
                if(isset($data['Physician']['FirstName'])){
                    $physician_name = $data['Physician']['FirstName'];
                }
                if(isset($data['Physician']['LastName'])){
                    $physician_name .= " ".$data['Physician']['LastName'];
                }
                
                $insurance_name = isset($data['insurance_full'])?$data['insurance_full']:'';
                $dmdl_mdl_num = $data['mdlnumber'];
                
                if(isset($dmdlItem['Possible_Match']) && $dmdlItem['Possible_Match']!=''){

                    //check if Create New selected
                    if($dmdlItem['Possible_Match']==='create_new'){ //insert new records
                        //insert into users
                        $userData = array(
                            'user_type'=>'patient',
                            'status'=>'1',
                            'Guid_role'=>'3',
                            'Loaded'=>'Y',
                            'Date_created'=>date('Y-m-d h:i:s')
                        );
                        $insertUser = insertIntoTable($db, 'tbluser', $userData);
                        if($insertUser['insertID'] && $insertUser['insertID']!=''){
                            $Guid_user = $insertUser['insertID'];
                            //insert into patients
                            $insertPatient = $db->query("INSERT INTO `tblpatient` ("
                                    . "Guid_dmdl_patient,Guid_dmdl_physician,"
                                    . "Guid_user,accountNumber,"
                                    . "firstname_enc,lastname_enc,"
                                    . "ethnicity,dob,gender,"
                                    . "address,address1,city,state,zip,"
                                    . "phone_number,phone_number_home,"
                                    . "Loaded,Linked,physician_name,insurance_name,Date_created) "
                                    . "VALUES ('$Guid_dmdl_patient','$Guid_dmdl_physician','$Guid_user','$account_number', "
                                    . "AES_ENCRYPT('$firstname_enc', 'F1rstn@m3@_%'),AES_ENCRYPT('$lastname_enc', 'L@stn@m3&%#'), "
                                    . "'$ethnicity','$dob','$gender',"
                                    . "'$address','$address1','$city','$state','$zip',"
                                    . "'$phone_number','$phone_number_home', "
                                    . "'Y','Y', '$physician_name','$insurance_name', NOW())");
                            $Guid_patient = $db->lastInsertId();
                            
                            //update mdl number
                            $mdlData = array(
                                'Guid_user'=>$insertUser['insertID'],
                                'mdl_number'=>$dmdl_mdl_num,
                                'Loaded' => 'Y'
                            );
                            $insertMDLNum = insertIntoTable($db, 'tbl_mdl_number', $mdlData);

                            //update OR insert account
                            $accountNum = $data['account']['number'];                            
                            $Guid_account = updateOrInsertAccount($db, $data['account']);

                            //update OR insert provider
                            if(isset($Guid_account) && $Guid_account!=''){
                                $apiProviderData = $data['Physician'];
                                $Guid_provider = updateOrInsertProvider($db,$accountNum,$Guid_account,$Guid_user,$apiProviderData);
                            }
                            
                            //update or insert payor and revenue data                            
                            if(isset($data['invoiceDetail']) && !empty($data['invoiceDetail'])){
                                updateOrInsertRevenue($db, $Guid_user, $data['invoiceDetail']);
                            }
                                

                            $statuses = $_POST["dmdl"]["$mdlNum"]["statuses"];

                            //insert suatuses
                            $statusLogData = array(
                                'Guid_user' =>  $Guid_user,
                                'Guid_patient'=> $Guid_patient,
                                'Guid_account' => $Guid_account,
                                'account' => $accountNum,
                                'Recorded_by' => $_SESSION['user']['id'],  
                                'provider_id' => $Guid_provider,
                                'Guid_salesrep' => "",
                                'salesrep_fname' => "",
                                'salesrep_lname' => "",
                                'deviceid' => "",
                                'Date_created'=>date('Y-m-d h:i:s')
                            );

                            $getSalesrep = $db->row("SELECT * FROM tblsalesrep srep
                                                    LEFT JOIN `tblaccountrep` accrep ON srep.`Guid_salesrep`=accrep.`Guid_salesrep`
                                                    WHERE accrep.`Guid_account`=$Guid_account");
                            if(!empty($getSalesrep)){
                                $statusLogData['Guid_salesrep'] = $getSalesrep['Guid_salesrep'];
                                $statusLogData['salesrep_fname'] = $getSalesrep['first_name'];
                                $statusLogData['salesrep_lname'] = $accountInfo['last_name'];
                            }
                            //update tbl_mdl_dmdl UpdateDatetime   
                            updateTable($db, 'tbl_mdl_dmdl', array('UpdateDatetime'=> date('Y-m-d h:i:s'),'Linked'=>'Y'), array('Guid_mdl_dmdl'=>$Guid_mdl_dmdl));
    
                            $insertDmdlStatuses = insertDmdlStatuses($db,$statuses,$statusLogData,$mdlNum,$data['Guid_mdl_dmdl']);

                        } 
                    } else { //update 

                        $Guid_patient = $dmdlItem['Possible_Match']; //patient id from admin db
                        $thisPatient = $db->row("SELECT * FROM `tblpatient` WHERE Guid_patient=:Guid_patient", array('Guid_patient'=>$Guid_patient));
                        
                        $Guid_user = $thisPatient['Guid_user'];    
                        $Guid_patient = $thisPatient['Guid_patient'];
                        //update patent table
                        $patientData = array();
                        if($thisPatient['Guid_dmdl_patient']==''){
                            $patientData['Guid_dmdl_patient'] = $data['Guid_PatientId'];
                        }
                        if($thisPatient['Guid_dmdl_physician']==''){
                            $patientData['Guid_dmdl_physician'] = $data['Physician']['GUID_PhysicianID'];
                        }
                        if($thisPatient['account_number']==''){
                            $patientData['accountNumber'] = $data['account']['number'];
                        }
                        if($thisPatient['firstname_enc']==''){
                            $patientData['firstname_enc'] = $data['firstname'];
                        }
                        if($thisPatient['lastname_enc']==''){
                            $patientData['lastname_enc'] = $data['lastname'];
                        }
                        if($thisPatient['ethnicity']==''){
                            $patientData['ethnicity'] = $data['ethnicity'];
                        }
                        if($thisPatient['dob']==''){
                            $patientData['dob'] = $data['dob'];
                        }
                        if($thisPatient['phone_number']==''){
                            $patientData['phone_number'] = $data['phone_number'];
                        }
                        if($thisPatient['phone_number_home']==''){
                            $patientData['phone_number_home'] = $data['phone_number_home'];
                        }
                        if($thisPatient['gender']==''){
                            $patientData['gender'] = $data['gender'];
                        }
                        if($thisPatient['address']==''){
                            $patientData['address'] = $data['address'];
                        }
                        if($thisPatient['address1']==''){
                            $patientData['address1'] = $data['address1'];
                        }
                        if($thisPatient['city']==''){
                            $patientData['city'] = $data['city'];
                        }
                        if($thisPatient['state']==''){
                            $patientData['state'] = $data['state'];
                        }
                        if($thisPatient['zip']==''){
                            $patientData['zip'] = $data['zip'];
                        }                        
                        if($thisPatient['physician_name']==''){
                            $patientData['physician_name'] = $data['Physician']['FirstName']." ".$data['Physician']['LastName'];
                        }
                                               
                        if($thisPatient['insurance_name']==''){
                            $patientData['insurance_name'] = $data['insurance_full'];
                        }
                        if(!empty($thisPatient)){
                            $patientData['Linked'] = 'Y';
                            $wherePatient = array('Guid_patient'=>$Guid_patient);
                            $updatePatient = updatePatientData($db,$patientData,$wherePatient); 
                        }

                        //update mdl number
                        $wherUserIs = array('Guid_user'=>$Guid_user);
                        $thisMdl = $db->query("SELECT * FROM tbl_mdl_number WHERE Guid_user=:Guid_user", $wherUserIs);
                        $mdlData['mdl_number']=$data['mdlnumber'];
                        $mdlNumMatch = False;
                        if(!empty($thisMdl)){                            
                            foreach ($thisMdl as $key => $mdlVal) {
                                if($mdlVal['mdl_number']!=''){
                                    if($mdlVal['mdl_number']==$data['mdlnumber']){
                                        $mdlNumMatch = True;
                                    }
                                }
                            } 
                        }
                                               
                        if($mdlNumMatch){
                            $updateMDLNum = updateTable($db, 'tbl_mdl_number', $mdlData, $wherUserIs);
                        } else {
                            $mdlData['Loaded']='Y';
                            $mdlData['Guid_user']=$Guid_user;
                            $insertMDLNum = insertIntoTable($db, 'tbl_mdl_number', $mdlData);
                        }

                        //update OR insert account
                        $accountNum = $data['account']['number']; 
                        $Guid_account = updateOrInsertAccount($db, $data['account']);                       

                        //update OR insert provider
                        if(isset($Guid_account) && $Guid_account!=''){
                            $apiProviderData = $data['Physician'];
                            $Guid_provider = updateOrInsertProvider($db,$accountNum,$Guid_account,$Guid_user,$apiProviderData);
                        }
                        $mdlNum = $data['mdlnumber'];
                        if( isset($_POST["dmdl"]["$mdlNum"]["statuses"])){
                            $statuses = $_POST["dmdl"]["$mdlNum"]["statuses"];
                            //insert suatuses
                            $statusLogData = array(
                                'Guid_user' =>  $Guid_user,
                                'Guid_patient'=> $Guid_patient,
                                'Guid_account' => $Guid_account,
                                'account' => $accountNum,
                                'Recorded_by' => $_SESSION['user']['id'],  
                                'provider_id' => $Guid_provider,
                                'Guid_salesrep' => "",
                                'salesrep_fname' => "",
                                'salesrep_lname' => "",
                                'deviceid' => "",
                                'Date_created'=>date('Y-m-d h:i:s')
                            );
                            $getSalesrep = $db->row("SELECT * FROM tblsalesrep srep
                                                    LEFT JOIN `tblaccountrep` accrep ON srep.`Guid_salesrep`=accrep.`Guid_salesrep`
                                                WHERE accrep.`Guid_account`=$Guid_account");
                            if(!empty($getSalesrep)){
                                $statusLogData['Guid_salesrep'] = $getSalesrep['Guid_salesrep'];
                                $statusLogData['salesrep_fname'] = $getSalesrep['first_name'];
                                $statusLogData['salesrep_lname'] = $accountInfo['last_name'];
                            }
                            //var_dump($statusLogData);
                            //update tbl_mdl_dmdl UpdateDatetime   
                            updateTable($db, 'tbl_mdl_dmdl', array('UpdateDatetime'=> date('Y-m-d h:i:s'),'Linked'=>'Y'), array('Guid_mdl_dmdl'=>$Guid_mdl_dmdl));
                            
                            $insertDmdlStatuses = insertDmdlStatuses($db,$statuses,$statusLogData,$mdlNum,$data['Guid_mdl_dmdl']);
                        }
                    }                        
                } 
            } //check if checkbox is checked(from Select All checkboxes)
        }
    }    
}

?>

<?php if( (isset($_GET['refresh'])) && $_GET['refresh']=="1" ){ ?>
<div id="manage-status-modal" class="modalBlock ">
    <div class="contentBlock refreshModal">
        <a class="close" href="<?php echo SITE_URL."/dashboard.php"; ?>">X</a> 
        <h5 class="title">
            Refresh Results
        </h5>
        <div class="content">
            <?php echo dmdl_refresh($db); ?>        
        </div>
    </div>
</div>
<?php } ?>

<div class="preloader hidden"><img src="<?php echo SITE_URL.'/assets/images/preloader.gif'; ?>" /></div>

<?php require_once 'scripts.php'; ?>
<script type="text/javascript">
    if ($('#refresh-log-table').length ) { 
        var table = $('#refresh-log-table').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            searching: false,
            lengthChange: false,
            "paging":   false,
            "info":     false,
            "bSortCellsTop": false,
            "language": {
                "emptyTable": "No match found."
            },
            "aoColumnDefs": [
              { 
                  "bSortable": false, 
                  "aTargets": [ 7,8 ] } 
            ]
        });  
    }
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