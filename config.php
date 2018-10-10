<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database server, username, password, and database name.
define("DB_SERVER", "backend-mysql");
define("DB_USER", "root");
define("DB_PASSWORD", "dev2016");
define("DB_NAME", "dev_shareddb");
define("DB_PREFIX", "tbl");


// Login security params, should have deffernt values for each server
define("SECURE", FALSE); // FOR Production should be TRUE if https
define('PASSWORD_SALT', "ZCep0jVFDtp9Pc7eftIAXt"); //22 characters, Should be different for dev and prod
define('SESSION_NAME', "mdldevadmin"); //Should be different for dev and prod

// Paths
define("SITE_ROOT", $_SERVER["DOCUMENT_ROOT"] . "/dev/login");
define("SITE_URL", "http://".$_SERVER["HTTP_HOST"] . "/dev/login");

// Autoload classes
function __autoload($classname)
{
    require(SITE_ROOT.'/classes/'.$classname.'.class.php');
}
// An alternative
//array_walk(glob('./include/*.class.php'),create_function('$v,$i', 'return require_once($v);'));

//include global functions
require(SITE_ROOT.'/classes/PHPExcel.php');
require_once('functions.php');
require_once('functions-ss-login.php');

//Open database
$mysql_err = false;
$db = new Db(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);

//Connect($hostname, $database, $username, $password)
if(!$db){
    $mysql_err = "Could not connect to database.";
    var_dump($mysql_err);
}

// Represents all post and get variables

// $post = new post($db);
// $get = new get($db);

// Initialize current admin user

// $user = new user($db);

// Initialize config object

// $config = new config($db);

?>
