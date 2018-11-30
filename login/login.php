<?php 
ob_start();
sec_session_start();
if (login_check($db)) {
    Leave(SITE_URL.'/dashboard.php');
}
$showMsg = "";
$message = "";

if (isset($_POST['email'], $_POST['password'])) {    
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password']; // The hashed password. 
    $login = login($email, $password, $db);
    if ( $login['status'] == true) {
        // Login success 
        $userID = $_SESSION['user']["id"];
        $roleInfo = getRole($db, $userID);
        $role = $roleInfo['role'];
        if($role=='Physician'){
            Leave(SITE_URL.'/accounts.php');
        }else{
            Leave(SITE_URL.'/dashboard.php');
        }
    } else {
        $showMsg = '1';
        $message = $login['message'];
    }    
} 

require_once './header.php'; 
?>

<form  action="" method="post">
<section id="admin_login" class="center_box">
    
    <div class="v_center">
        <div class="box">
            <h4 class="box_top">BRCAcare<sup>&reg;</sup> Questionnaire Admin</h4>

            <div class="boxtent">
                
                 <?php if( $showMsg && $showMsg != "" ){ ?>	
                        <div class="warning">
                          <strong>Warning!</strong> <?php echo $message; ?>
                        </div>
                <?php } ?>
                
                <div class="f2 required">
                    <label class="dynamic" for="username"><span>Email</span></label>

                    <div class="group">
                        <input id="username" name="email" type="text" value="" placeholder="Email" required>

                        <p class="f_status">
                            <span class="status_icons"><strong>*</strong></span>
                        </p>
                    </div>
                </div>

                <div class="f2 required">
                    <label class="dynamic" for="password"><span>Password</span></label>

                    <div class="group">
                        <input id="password" name="password" type="password" value="" placeholder="Password" required>

                        <p class="f_status">
                            <span class="status_icons"><strong>*</strong></span>
                        </p>
                    </div>
                </div>

                <div class="box_btns">
                    <button type="submit" class="submit button" name="login">
                        <strong>Login</strong>
                    </button>
                </div>
            </div>
        </div>
    </div>
   
</section> 
</form>


<?php require_once './scripts.php'; ?>
<?php require_once './footer.php'; ?>