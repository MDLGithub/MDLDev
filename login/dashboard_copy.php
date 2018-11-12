<?php
ob_start();
require_once('settings.php');
require_once('config.php');
require_once('header.php');

if (!isUserLogin()) {
    Leave(SITE_URL);
}
if (isset($_GET['logout'])) {
    doLogout();
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
<aside id="action_palette" >		
    <div class="box full">
        <h4 class="box_top">Filters</h4>
        <?php if($dataViewAccess) { ?>
        <div class="boxtent scroller">
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
                
                <?php if(isFieldVisibleByRole($roleIDs['account']['view'], $roleID)) {?>
                    <div class="f2<?php echo ((!isset($_POST['clear'])) && (isset($_POST['account'])) && (strlen(trim($_POST['account'])))) ? " show-label valid" : ""; ?>">
                        <label class="dynamic" for="account"><span>Account</span></label>

                        <div class="group">
                            <select id="account" name="account" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['account'])) && (strlen($_POST['account']))) ? "" : "no-selection"; ?>">
                                <option value="">Account</option>	
                                <?php
                                foreach ($accounts as $account) {
                                    $default_account .= $account['account'] . ",";
                                    ?>
                                    <option value="<?php echo $account['account']; ?>"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['account']) && ($_POST['account'] == $account['account'])) ? " selected" : ""); ?>><?php echo $account['account'] . " - " . ucwords(strtolower($account['name'])); ?></option>
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
                        <label class="dynamic" for="location"><span>Location</span></label>

                        <div class="group">
                            <select id="location" name="location" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['location'])) && (strlen($_POST['location']))) ? "" : "no-selection"; ?>">
                                <option value="">Location</option>							
                                <?php
                                $locations = $db->query("SELECT description FROM tblsource");

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
                            <select id="salesrep" name="salesrep" class="<?php echo ((!isset($_POST['clear'])) && (isset($_POST['salesrep'])) && (strlen($_POST['salesrep']))) ? "" : "no-selection"; ?>">
                                <option value="">Genetic Consultant</option>							
                                <?php
                                $salesreps = $db->query("SELECT * FROM tblsalesrep GROUP BY first_name");

                                foreach ($salesreps as $salesrep) {
                                    ?>
                                    <option value="<?php echo $salesrep['Guid_user']; ?>"<?php echo ((!isset($_POST['clear'])) && (isset($_POST['salesrep']) && ($_POST['salesrep'] == $salesrep['Guid_user'])) ? " selected" : ""); ?>><?php echo $salesrep['first_name']." ".$salesrep['last_name']; ?></option>
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
                
                <!-- <fieldset class="cbox">
                     <label for="auto_apply">
                         <input id="auto_apply" type="checkbox" checked>
                         <strong>Auto Apply</strong>
                     </label>
                 </fieldset> -->

                <button id="filter" value="1" name="search" type="submit" class="button filter half"><strong>Search</strong></button>
                <button type="submit" name="clear" class="button cancel half"><strong>Clear</strong></button>
            </form>
            <!--********************   SEARCH BY PALETTE END    ******************** -->

        </div>
        <?php } ?>
    </div>
    
</aside>

<?php

if(isset($_POST['mark_as_test'])){
    $markedUsers =$_POST['markedRow']['user'];
    if($markedUsers){
        foreach ($markedUsers as $userID=>$v){
            updateTable($db,'tbl_ss_qualify', array('mark_as_test'=>'1'), array('Guid_user'=>$userID));
        }
    }
    
}

$sqlTbl = "SELECT *, u.email, q.Date_created AS date FROM tbl_ss_qualify q LEFT JOIN tblpatient p ON q.Guid_user = p.Guid_user LEFT JOIN tbluser u ON p.Guid_user = u.Guid_user";
$where = "";
$where .= (strlen($where)) ? " AND " : " WHERE ";
$where .= " q.mark_as_test='0'";

//if ((!count($error)) && (!isset($_POST['clear'])) && (!empty($_POST))) {
if ((!isset($_POST['clear'])) && (!empty($_POST['search']))) {
    $where = "";
    //if (isset($_POST['date_rng'])) {
    if (strlen($_POST['from_date']) && strlen($_POST['to_date'])) {
        if ($_POST['from_date'] == $_POST['to_date']) {
            $where = " WHERE q.Date_created LIKE '%" . date("Y-m-d", strtotime($_POST['from_date'])) . "%'";
        } else {
            $where = " WHERE q.Date_created BETWEEN '" . date("Y-m-d", strtotime($_POST['from_date'])) . "' AND '" . date("Y-m-d", strtotime($_POST['to_date'])) . "'";
        }
    }
    if (isset($_POST['meets_mn']) && strlen($_POST['meets_mn'])) {
        $where .= (strlen($where)) ? " AND " : " WHERE ";
        $where .= " q.qualified = '" . $_POST['meets_mn'] . "'";
    }

    if (isset($_POST['first_name']) && strlen(trim($_POST['first_name']))) {
        $where .= (strlen($where)) ? " AND " : " WHERE ";
        $where .= " p.firstname = '" . $_POST['first_name'] . "'";
    }

    if (isset($_POST['last_name']) && strlen(trim($_POST['last_name']))) {
        $where .= (strlen($where)) ? " AND " : " WHERE ";
        $where .= " p.lastname = '" . $_POST['last_name'] . "'";
    }

    if (isset($_POST['insurance']) && strlen($_POST['insurance'])) {
        $where .= (strlen($where)) ? " AND " : " WHERE ";
        $where .= " q.insurance = '" . $_POST['insurance'] . "'";
    }

    if (isset($_POST['provider']) && strlen($_POST['provider'])) {
        $where .= (strlen($where)) ? " AND " : " WHERE ";
        $where .= " q.provider_id = '" . $_POST['provider'] . "'";
    }

    if (isset($_POST['location']) && strlen($_POST['location'])) {
        $where .= (strlen($where)) ? " AND " : " WHERE ";
        $where .= " q.source = '" . $_POST['location'] . "'";
    }

    if (isset($_POST['mark_test']) && strlen($_POST['mark_test'])) {
        $where .= (strlen($where)) ? " AND " : " WHERE ";
        $where .= " q.mark_as_test = '1'";
    }

    if (isset($_POST['account']) && strlen($_POST['account'])) {
        $where .= (strlen($where)) ? " AND " : " WHERE ";
        $where .= " q.account_number = '" . $_POST['account'] . "'";
    } elseif (isset($_POST['salesrep']) && strlen($_POST['salesrep'])) {
        $where .= (strlen($where)) ? " AND " : " WHERE ";
        $where .= " q.account_number IN (" . $default_account . ")";
    }
}
$postAccount = isset($_POST['account']) ? $_POST['account'] : "";
if ((isset($role) && $role == "Sales Rep") && (!strlen($postAccount))) {
    $where .= (strlen($where)) ? " AND " : " WHERE ";
    $where .= " q.account_number IN (" . $default_account . ")";
}

$sqlTbl .= $where;


$sqlTbl .= " GROUP BY p.Guid_user";
$sqlTbl .= " ORDER BY date DESC";
$qualify_requests = $db->query($sqlTbl);

$num_estimates = $qualify_requests;

?>

<main>
    <div class="box full visible">
        <?php if($dataViewAccess){ ?>
        <section id="palette_top">
            <h4><?php echo count($num_estimates) . " Results"; ?></h4>
            <a href="<?php echo baseUrl(); ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
            <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
        </section>

        <div id="app_data" class="scroller">
            
            <div class="row">
                <?php 
                    if($role=='Physician'){
                      $salesRep = getProviderSalesRep($db, $_SESSION['user']['id']);
                    ?>
                <div class="col-md-6 pull-right">
                    <?php 
                        $img = ($salesRep['photo_filename']!="")? $salesRep['photo_filename']: ""; 
                        $image = ($img!="") ? baseUrl().'/images/users/'.$img : "assets/images/default.png";
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
            
            <form id="patient_information" action="" method="post">
                
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
                
                    <section class="pseudo_t">

                    <?php
                    if ($num_estimates) {
                    ?>
                            <div class="col_group"></div>
                            <div class="col_group"></div>
                            <div class="col_group"></div>
                            <div class="col_group"></div>
                            <div class="col_group"></div>

                             <h2 class="t_row head">
                                <p>
                                    <label class="switch">

                                        <input id="selectAllPrintOptions" type="checkbox">
                                        <span class="slider round">
                                            <span id="switchLabel">Select All</span>
                                        </span>
                                    </label>
                                </p>
                                <?php if(isFieldVisibleByRole($roleIDs['meets_mn']['view'], $roleID)) {?>
                                    <p>Medical<br>Necessity</p>
                                <?php } ?>
                                <?php if(isFieldVisibleByRole($roleIDs['from_date']['view'], $roleID)) {?>
                                    <p>Date</p>
                                <?php } ?>
                                <?php if(isFieldVisibleByRole($roleIDs['first_name']['view'], $roleID)) {?>
                                    <p>First<br>Name</p>
                                <?php } ?>
                                <?php if(isFieldVisibleByRole($roleIDs['last_name']['view'], $roleID)) {?>
                                    <p>Last<br>Name</p>
                                <?php } ?>
                                <?php if(isFieldVisibleByRole($roleIDs['insurance']['view'], $roleID)) {?>
                                    <p>Insurance</p>
                                <?php } ?>
                                <?php if(isFieldVisibleByRole($roleIDs['account']['view'], $roleID)) {?>
                                    <p>Account</p>
                                <?php } ?>
                                <?php if(isFieldVisibleByRole($roleIDs['provider']['view'], $roleID)) {?>
                                    <p>Provider</p>
                                <?php } ?>
                                <?php if(isFieldVisibleByRole($roleIDs['location']['view'], $roleID)) {?>
                                    <p>Location</p>
                                <?php } ?>
                                <?php if(isFieldVisibleByRole($roleIDs['salesrep']['view'], $roleID)) {?>
                                    <p>Genetic<br>Consultant</p>  
                                <?php } ?>
                                <?php if(isFieldVisibleByRole($roleIDs['salesrep']['view'], $roleID)) {?>
                                    <p>Email</p>  
                                <?php } ?>
                            </h2>
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
                            $q = "SELECT CONCAT(sr.first_name, ' ', sr.last_name) AS name FROM tblaccount a LEFT JOIN tblaccountrep ar ON a.Guid_account=ar.Guid_account LEFT JOIN tblsalesrep sr ON ar.Guid_salesrep = sr.Guid_salesrep WHERE a.account = '" . $qualify_request['account_number'] . "'";

                            $salesrep = $db->row($q);
                            $salesrep = $salesrep['name'];
                            ?>
                                <div data-id="<?php //echo $estimate_request['Guid_brcaestimate']; ?>" class="t_row">
                                    <p class="printSelectBlock">
                                        <input name="markedRow[user][<?php echo $qualify_request['Guid_user']; ?>]" type="checkbox" class="print1 report1" data-selected_questionnaire="<?php echo $qualify_request['Guid_qualify']; ?>" data-selected_date="<?php echo $qualify_request['date']; ?>" />
                                    </p>
                                    <?php if(isFieldVisibleByRole($roleIDs['meets_mn']['view'], $roleID)) {?>
                                        <p class="mn <?php echo strtolower($qualify_request['qualified']); ?>"><?php echo $qualify_request['qualified']; ?></p>
                                    <?php } ?>
                                    <?php if(isFieldVisibleByRole($roleIDs['from_date']['view'], $roleID)) {?>
                                        <p><?php echo date("n/j/Y", strtotime($qualify_request['date'])); ?></p>
                                    <?php } ?>
                                    <?php if(isFieldVisibleByRole($roleIDs['first_name']['view'], $roleID)) {?>
                                        <p><?php echo ucfirst($qualify_request['firstname']); ?></p>
                                    <?php } ?>
                                    <?php if(isFieldVisibleByRole($roleIDs['last_name']['view'], $roleID)) {?>
                                        <p><?php echo ucfirst($qualify_request['lastname']); ?></p>
                                    <?php } ?>                    
                                    <?php if(isFieldVisibleByRole($roleIDs['insurance']['view'], $roleID)) {?>
                                        <p><?php echo $qualify_request['insurance']; ?></p>
                                    <?php } ?>
                                    <?php if(isFieldVisibleByRole($roleIDs['account']['view'], $roleID)) {?>
                                        <p><?php echo $qualify_request['account_number']; ?></p>
                                    <?php } ?>
                                    <?php if(isFieldVisibleByRole($roleIDs['provider']['view'], $roleID)) {?>   
                                        <p><?php echo $provider_name; ?></p>
                                    <?php } ?>
                                    <?php if(isFieldVisibleByRole($roleIDs['location']['view'], $roleID)) {?>   
                                        <p><?php echo $qualify_request['source']; ?></p>
                                    <?php } ?>
                                    <?php if(isFieldVisibleByRole($roleIDs['salesrep']['view'], $roleID)) {?>
                                        <p><?php echo $salesrep ? $salesrep : ''; ?></p>          
                                    <?php } ?>
                                    <?php if(isFieldVisibleByRole($roleIDs['salesrep']['view'], $roleID)) {?>
                                        <p class="mn">
                                            <?php if($qualify_request['email']==""){ ?>
                                                <img src = "<?php echo baseUrl(); ?>/assets/images/no_email_icon_30.png" />
                                            <?php } ?>
                                        </p>        
                                    <?php } ?>

                                </div>
                                <?php
                            }
                        } else {
                            ?>
                                <p>There are no Pending BRCAcare<sup>&reg;</sup> Estimate Requests</p>
                            <?php
                        }
                        ?>
                    </section>
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
require_once 'scripts.php';
require_once 'footer.php';
?>