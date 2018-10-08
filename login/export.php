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

<div class="export_filters">
    <div class="box full visible">
    <h4 class="box_top">Parameters</h4>
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

                <button id="filter" value="1" name="search" type="submit" class="button filter half"><strong>Search</strong></button>
                <button type="submit" name="clear" class="button cancel half"><strong>Clear</strong></button>
            </form>
        </div>
        <?php } else { ?>
            <p>Sorry! You don't have access to this page content. </p>
        <?php } ?>
    </div>
    <div id="admin_print"></div>
</div>

<?php require_once 'scripts.php'; ?>
<?php require_once 'footer.php'; ?>