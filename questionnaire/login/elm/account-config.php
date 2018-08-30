<?php

require_once('settings.php'); 
require_once('config.php'); 
require_once('header.php');

if(!isUserLogin()){
    Leave(SITE_URL);
}

if(isset($_GET['logout'])){
    doLogout();
    Leave(SITE_URL);
}
?>
<div class="container">
      <!-- Static navbar -->
      <?php require_once 'navbar.php';?> 
      
      <div class="url_config_box">
        <div class="row">
          <div class="col-md-12 text-center">
            <h2 class="page-title-1">Account Configuration</h2><br/>
          </div>
        </div>
      </div>
</div>

<?php require_once('footer.php');