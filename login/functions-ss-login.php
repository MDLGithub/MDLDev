<?php

function sec_session_start() {
    $session_name = SESSION_NAME;   // Set a custom session name 
    $secure = SECURE;
    // This stops JavaScript being able to access the session id.
    $httponly = true;
    // Forces sessions to only use cookies.
    if (ini_set('session.use_only_cookies', 1) === FALSE) {
        header("Location: ".SITE_URL."/error.php?err=Could not initiate a safe session (ini_set)");
        exit();
    }
    // Gets current cookies params.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);
    // Sets the session name to the one set above.
    session_name($session_name);
    if(!isset($_SESSION)){ 
        session_start();  // Start the PHP session  
    }    
    session_regenerate_id();    // regenerated the session, delete the old one. 
}
function login($email, $password, $db) {
    $db->bind("email",$email);
    $user = $db->row("SELECT * FROM `".DB_PREFIX."user` WHERE email = :email"); 
    $showMsg = true;
    if ($user) { 
        if (checkbrute($user['email'], $db) == true) {
            // Account is locked 
            // Send an email to user saying their account is locked 
            $showMsg = false;
            $message = "Your account has been locked for 2 hours due to the multiple failed login attempts. Please try again later. If you need immediate assistance, please contact your support team.";
            $returnData = array('status'=>false, $showMsg=>false, 'message'=>$message);
            return $returnData;
        } else {            
            // Check if the password in the database matches 
            // the password the user submitted.
            if ($user["password"] == encode_password($password)) {
                // Password is correct!
                // Get the user-agent string of the user.
                $user_browser = $_SERVER['HTTP_USER_AGENT'];
                // XSS protection as we might print this value
                $Guid_user = preg_replace("/[^0-9]+/", "", $user['Guid_user']);
                $_SESSION['user']['id'] = $Guid_user;
                // XSS protection as we might print this value                
                $_SESSION['user']['type'] = $user['user_type'];
                $_SESSION['user']['email'] = $user['email'];
                $_SESSION['login_string'] = hash('sha512', encode_password($password) . PASSWORD_SALT . $user_browser);
                // Login successful. 
                $returnData = array('status'=>true);
                return $returnData;
            } else {
                // Password is not correct 
                // We record this attempt in the database 
                $showMsg = true;
                $now = time();                
                $ipAddr = get_client_ip();                
                if (!$db->query("INSERT INTO tbluser_login_attempts (email, ip, time) VALUES ('$email', '$ipAddr', '$now')")) {
                    header("Location: ".SITE_URL."/error.php?err=Database error: tbluser_login_attempts");
                    exit();
                }
                $message = "Username or password is not correct.";
                $returnData = array('status'=>false, $showMsg=>true, 'message'=>$message);
                return $returnData;
            }
        }
       
    } else {
        //User is not found
        $message = "User is not found. Pleas check your login name and try again";
        $returnData = array('status'=>false, $showMsg=>true, 'message'=>$message);
        return $returnData;
    }
}
function checkbrute($email, $db) {
    // Get timestamp of current time 
    $now = time();
    // All login attempts are counted from the past 2 hours. 
    $valid_attempts = $now - (2 * 60 * 60);
    $db->bind("email",$email);
    $stmt = $db->query("SELECT time FROM tbluser_login_attempts WHERE email = :email AND time > '$valid_attempts'");
  
    if (!empty($stmt)) { 
        if (count($stmt) > 5) { // If there have been more than 5 failed logins 
            return true;
        } else {
            return false;
        }
    } 
    return false;
}
function login_check($db) {
    // Check if all session variables are set 
    if (isset($_SESSION['user']['id'], $_SESSION['user']['email'], $_SESSION['login_string'])) {
        $Guid_user = $_SESSION['user']['id'];
        $login_string = $_SESSION['login_string'];
        $username = $_SESSION['user']['email'];
        // Get the user-agent string of the user.
        $user_browser = $_SERVER['HTTP_USER_AGENT'];
        $db->bindMore( array('Guid_user'=>$Guid_user, 'status'=>'1') );
        $stmt = $db->row("SELECT * FROM tbluser WHERE Guid_user=:Guid_user AND status=:status LIMIT 1");
        
        if (!empty($stmt)) {
            // If the user exists get variables from result.
            $password = $stmt['password'];
            $login_check = hash('sha512', $password . PASSWORD_SALT . $user_browser);
            if ($login_check == $login_string) {// Logged In!!!! 
                return true;
            } else { // Not logged in 
                return false;
            }
        } else { // Not logged in 
            return false;
        }        
    } else { // Not logged in 
        return false;
    }
}
// Function to get the client IP address
function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}
function esc_url($url) {
    if ('' == $url) {
        return $url;
    }
    $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);    
    $strip = array('%0d', '%0a', '%0D', '%0A');
    $url = (string) $url;   
    $count = 1;
    while ($count) {
        $url = str_replace($strip, '', $url, $count);
    }    
    $url = str_replace(';//', '://', $url);
    $url = htmlentities($url);    
    $url = str_replace('&amp;', '&#038;', $url);
    $url = str_replace("'", '&#039;', $url);
    if ($url[0] !== '/') {
        // We're only interested in relative links from $_SERVER['PHP_SELF']
        return '';
    } else {
        return $url;
    }
}

function logout(){
    // Unset all session values 
    $_SESSION = array();
    // get session parameters 
    $params = session_get_cookie_params();
    // Delete the actual cookie. 
    setcookie(session_name(),'', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    // Destroy session 
    session_destroy();
}