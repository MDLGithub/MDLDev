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

$users = getUsersAndRoles($db);
$searchData = array();
if(isset($_POST['filter'])){
    $searchData = $_POST;
}

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
            <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
        </section> 
      
        <div id="app_data" class="scroller "> 
            <h1 class="title-st1">MDL Statuses</h1>
          
            <div class="row ">
                <div class="col-md-12 text-center">
                    <form action="" method="POST">
                        <div class="row ">
                            <div class="col-md-12 text-center">
                                <div class="h-filters">
                                    <label>From: </label> 
                                   
                                    <input name="from_date" class="date datepicker" type="text" autocomplete="off" value="<?php echo (isset($_POST['from_date']))?$_POST['from_date']:"";?>" />
                                    <label>To: </label> <input name="to_date" class="date datepicker" type="text" autocomplete="off" value="<?php echo (isset($_POST['to_date']))?$_POST['to_date']:"";?>" />
                                    <label>MDL#: </label> <input name="mdl_number" class="stat_mdl_number" type="text" autocomplete="off" value="<?php echo isset($_POST['mdl_number'])?$_POST['mdl_number']:"";?>"/>
                                    <select name="Guid_salesrep" class="salesrep">
                                        <option value="">Genetic Consultant</option>
                                        <?php $salesReps = $db->query("SELECT Guid_salesrep, CONCAT(first_name,' ', last_name) AS name FROM tblsalesrep ORDER BY name "); ?>
                                        <?php foreach ($salesReps as $k=>$v){ ?>
                                        <?php $selected = (isset($_POST['Guid_salesrep'])&&$_POST['Guid_salesrep']==$v['Guid_salesrep'])?" selected": ""; ?>
                                            <?php if (trim($v['name'])!=""){ ?>
                                        <option <?php echo $selected; ?> value="<?php echo ucfirst(strtolower($v['Guid_salesrep'])); ?>"><?php echo $v['name']; ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    </select>
                                    <select name="Guid_account" class="account">
                                        <option value="">Account</option>
                                        <?php $accounts = $db->query("SELECT Guid_account, name, account FROM tblaccount ORDER BY account"); ?>
                                        <?php foreach ($accounts as $k=>$v){ ?>
                                        <?php $selected = (isset($_POST['Guid_account'])&&$_POST['Guid_account']==$v['Guid_account'])?" selected": ""; ?>
                                            <?php if (trim($v['name'])!=""){ ?>
                                            <?php $dots = strlen($v['name']) <= 35 ? ' ' : '...'; ?>
                                            <option <?php echo $selected; ?>  value="<?php echo $v['Guid_account']; ?>"><?php echo $v['account']." - ".ucfirst(strtolower(substr($v['name'], 0, 35))).$dots; ?></option>
                                            <?php } ?>
                                        <?php } ?>                                        
                                    </select>
                                    <button name="filter" type="submit" class="" >Filter</button>
                                </div>
                            </div>
                        </div>
                        <div class="row pT-30">
                            <div class="col-md-6 col-md-offset-3">
                                <table class="table stats-table">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th class="wh-100">Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php echo get_status_table_rows($db, '0', $searchData);?>
                                    </tbody>
                                </table>
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
</main>




<?php require_once('scripts.php');?>

<?php require_once('footer.php');?>