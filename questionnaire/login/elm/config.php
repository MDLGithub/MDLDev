<?php

// Database server, username, password, and database name.
define("DB_SERVER", "localhost");
define("DB_USER", "shareddb");
define("DB_PASSWORD", "dayterBays3!");
define("DB_NAME", "shareddb");
define("DB_PREFIX", "tbl");

// Paths
define("SITE_ROOT", $_SERVER["DOCUMENT_ROOT"]);
define("SITE_URL", "http://".$_SERVER["HTTP_HOST"]);

// Autoload classes
function __autoload($classname)
{
    require(SITE_ROOT.'/questionnaire/login/elm/classes/'.$classname.'.class.php');
}
// An alternative
//array_walk(glob('./include/*.class.php'),create_function('$v,$i', 'return require_once($v);')); 

//include global functions
require_once('functions.php');

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