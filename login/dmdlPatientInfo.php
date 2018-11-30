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

$pageTitle = '';

if(isset($_GET['patientId'])&&isset( $_GET['physicianId'])){
    require_once 'classes/xmlToArrayParser.php';
    ini_set("soap.wsdl_cache_enabled", 0);
    try {
        $opts = array('ssl' => array('ciphers'=>'RC4-SHA'));
        $client = new SoapClient(
        'https://patientpayment.mdlab.com/MDL.WebService/BillingWebService?wsdl',
        array ('stream_context' => stream_context_create($opts),"exceptions"=>0));
    } catch (Exception $e) { 
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        $headers .= 'From: billingcustomerservice@mdlab.com' . "\r\n";
        $message = "faultcode: " . $e->faultcode . ", faultstring: " . $e->faultstring;
        $subject = "SOAP Fault";  
        mail('agokhale@mdlab.com', $subject, $message, $headers);
        trigger_error("SOAP Fault: (faultcode: {$e->faultcode}, faultstring: {$e->faultstring})", E_USER_ERROR);
        return;
    }
    $param = array(
        "patientId" => $_GET['patientId'], 
        "physicianId" => $_GET['physicianId'],
        "mdlNumber" => $_GET['mdlNumber']
    );
    $result = (array)$client->GetGeneticResultsMDL($param);

    $domObj = new xmlToArrayParser($result['GetGeneticResultsMDLResult']); 
    $domArr = $domObj->array; 
    
    $pageTitle = 'dMDL Patient API Data';
}
?>

<main class="full-width">
    <div class="box full visible ">
      
        <section id="palette_top" class="shorter_palette_top">
            <h4>  
                <ol class="breadcrumb">
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>                  
                </ol>      
            </h4>
            <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" name="log_out" class="button red back logout"></a>
            <a href="<?php echo SITE_URL; ?>/dashboard2.php" class="button homeIcon"></a>
            <a href="https://www.mdlab.com/questionnaire" target="_blank" class="button submit"><strong>View Questionnaire</strong></a>
        </section> 
      
        <div id="app_data" class="scroller "> 
            <h1 class="title-st1 fs-20 "><?php echo $pageTitle;?></h1>
            <?php
            if(isset($_GET['patientId'])&&isset( $_GET['physicianId'])){
                if($domObj->parse_error){ 
                    echo $domObj->get_xml_error();            
                } else {
                    var_dump($result);
                    echo "<pre>";
                    print_r($domArr);                    
                }
            }
            ?>
            <?php
            if(isset($_GET['loadTable'])&&$_GET['loadTable']!=''){
                echo loadTableData($db, $_GET['loadTable'], 'table', 'dataTable');
            }
            ?>
            <?php
            if(isset($_GET['deleteLoadedData'])&&$_GET['deleteLoadedData']=='1'){
                $loadedDataTables = array('tbluser','tblpatient', 'tblaccount', 
                                            'tblprovider', 'tbl_mdl_number', 'tbl_mdl_status_log', 
                                            'tbl_mdl_payors', 'tbl_revenue', 'tbl_mdl_cpt_code'
                                        );
                foreach ($loadedDataTables as $k=>$tableName){
                    $db->query("DELETE FROM $tableName WHERE Loaded='Y'");
                }                
            }
            ?>
            <?php
            if(isset($_GET['restLinkedData'])&&$_GET['restLinkedData']=='1'){
                $linkedDataTables = array('tbl_mdl_dmdl','tblpatient');
                foreach ($linkedDataTables as $k=>$tableName){
                    $db->query("UPDATE $tableName SET Linked='N' WHERE Linked='Y'");
                }                
            }
            ?>
            <?php
            if(isset($_GET['truncate_dmdltable'])&&$_GET['truncate_dmdltable']=='1'){
                $db->query("TRUNCATE TABLE tbl_mdl_dmdl");
                                
            }
            ?>
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