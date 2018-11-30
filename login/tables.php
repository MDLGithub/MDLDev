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
if($role!='Admin'){
    Leave(SITE_URL);
}

require_once 'navbar.php'; 

if(isset($_GET['loadTable'])){
    $pageTitle = 'MDL <i>`'.$_GET['loadTable'].'`</i> table Data';
} else {
    $pageTitle = 'MDL Tables';
}

?>

<main class="full-width">
    <div class="box full visible ">
      
        <section id="palette_top" class="shorter_palette_top">
            <h4>  
                <ol class="breadcrumb">
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>                  
                    <li><a href="<?php echo SITE_URL; ?>/tables.php">MDL Tables</a></li>                  
                </ol>      
            </h4>
            <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
            <a href="<?php echo SITE_URL; ?>/dashboard2.php" class="button homeIcon"></a>
            <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
        </section> 
      
        <div id="app_data" class="scroller "> 
            <div class="row">                
                <div class="col-md-12">
                    <h2 class="pB-15"><?php echo $pageTitle;?></h2>
                    
                    <?php
                    if(isset($_GET['loadTable'])&&$_GET['loadTable']!=''){
                        echo loadTableData($db, $_GET['loadTable'], 'table', 'dataTable');
                    } else {
                        $tables = $db->query("SHOW TABLES FROM ".DB_NAME);
                        $i=1;
                        foreach ($tables as $k=>$v){
                            $tblName = array_shift($v);
                            echo "<p><span class='tableNums'>".$i.".</span>  <a href='".SITE_URL."/tables.php?loadTable=$tblName'>".$tblName.'</a></p>';
                            $i++;                            
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once 'scripts.php'; ?>
<script type="text/javascript">
   
    if ($('#dataTable').length ) {
        var table = $('#dataTable').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            //searching: false,
            lengthChange: false,
            "order": [[ 1, "asc" ]]
        });   
    }
    
</script>
<?php require_once 'footer.php'; ?>