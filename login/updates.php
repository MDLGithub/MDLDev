<?php
ob_start();
require_once('config.php');
require_once('settings.php');
require_once('header.php');
require_once('update-functions.php');
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



/**
 * Run function when clicked Update button
 */
if(isset($_POST['doUpdate'])){
    $data = $_POST;
    $doUpdate = doUpdate($db,$data);
    if(isset($doUpdate['message'])){
        $message = $doUpdate['message'];
    }
}
?>
<?php require_once 'navbar.php'; ?> 
<main class="full-width">    
    <div class="box full visible">
        <section id="palette_top">
            <h4>
            <ol class="breadcrumb">
                <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                <li class="active">
                    Updates 
                </li>
            </ol>
            </h4>
            <?php echo topNavLinks($role); ?>
        </section>
        <div class="row">
            <div class="col-md-12">
                <?php if(isset($message)){ ?>
                <div class="messageBox">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
                    <?php echo $message; ?>
                </div>
                <?php } ?>
                <div class="pB-30">
                    <h2>Updates Log Table</h2>
                </div>
                <table class="table">
            <thead>
                <tr>
                    <th>Function</th>
                    <th>Title</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
            
            <?php 
            $getUpdateData=$db->query("SELECT * FROM tbl_mdl_updates_log ORDER BY Guid_updates_log DESC"); 
            if(!empty($getUpdateData)){
                foreach ($getUpdateData as $k=>$v) {?>
                    <tr>
                        <td><?php echo $v['function_name']; ?></td>
                        <td><?php echo $v['description']; ?></td>
                        <td class="text-center">
                        <?php if($v['isUpdated']=='N'){ ?>
                            <form action="" method="POST">
                                <input name="runFunction" type="hidden" value="<?php echo $v['function_name']; ?>">
                                <input name="doUpdate" class="btn btn-small" type="submit" value="Update">
                            </form>
                        <?php } else { ?>
                            <div class="updateDone">
                                <i class="far fa-check-circle"></i>
                            </div>
                        <?php }  ?>
                        </td>
                    </tr>
            <?php                
                }
            } 
            ?>
            </tbody>
            </table>
        </table>
            </div>
        </div>
    </div>
</main>

<?php require_once('scripts.php');?>
<?php require_once('footer.php');?>