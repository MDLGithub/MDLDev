<?php 
    $userID = $_SESSION['user']["id"];
    $roleInfo = getRole($db, $userID);
    $role = $roleInfo['role'];
?>
<header id="app_top">
    <section id="modules">
        <h5>Module</h5>
        <ul id="mod_links">
<!--            <li>
                <a class="module">BRCAcare<sup>&#174;</sup> Estimate</a>
            </li>-->
            <li class="active">
                <a class="module">BRCAcare<sup>®</sup> Portal</a>
            </li>
        </ul>
    </section>

    <div id="app_user">
            <button type="button" id="mdl" class="toggle" data-on="#user_window"></button>
            <div id="user_window">
                <div class="user_welcome">
                    Welcome,
                    <span><?php echo getUserName($db, $_SESSION['user']['id']); ?></span>
                </div>

                <ul>                   
                    <li><a href="<?php echo SITE_URL; ?>/dashboard2.php">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>">Patients</a></li>
                    <?php if($role !== 'Physician' && $role != 'Patient') { ?>
                        <li><a href="<?php echo SITE_URL; ?>/url-configuration.php">URL</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/account-config.php">Accounts</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/devicesInventory.php">Devices</a></li>                        
                        <?php if($role=='Admin' || $role=='Sales Manager' || $role=='Sales Rep') { ?>
                            <li><a href="<?php echo SITE_URL; ?>/salesreps.php">Genetic Consultants</a></li>
                        <?php } ?>
                        <li><a href="<?php echo SITE_URL; ?>/eventschedule.php">Event Schedule</a></li>
                    <?php } ?>
                    <?php if($role=='Admin'){ ?>
                        <li><a href="<?php echo SITE_URL; ?>/access-roles.php">Access Roles</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/user-management.php">User Management</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/mdl-stats.php">MDL Stats</a></li>
                    <?php } ?>
                </ul>                
                <a href="<?php echo SITE_URL; ?>/dashboard.php?logout=1" id="log_out" name="log_out" class="button red back"><strong>Log Out</strong></a>
            </div>      

    </div>

</header>



