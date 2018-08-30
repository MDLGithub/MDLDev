<?php  
    require_once('settings.php'); 
    require_once('config.php'); 

    if(!isUserLogin()){
        Leave(SITE_URL);
    }

    if(isset($_GET['logout'])){
        doLogout();
        Leave(SITE_URL);
    }
    
    require_once 'header.php';
?>


<div class="container">

      <!-- Static navbar -->
      <?php require_once 'navbar.php';?> 

    <div class="url_config_box">
       <h2 class="page-title-1 text-left">Dashboard</h2>
    </div>


</div>

<?php 
    require_once 'footer.php';
?>