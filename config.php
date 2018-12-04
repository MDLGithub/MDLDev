<?php
// HTTP
define('HTTP_SERVER', 'http://ij.mdlab.com');

// HTTPS
//https://www.mdlab.com
define('HTTPS_HOST', 'http://ij.mdlab.com');
define('HTTPS_SERVER', HTTPS_HOST.'/');

define('DB_HOSTNAME', trim(getenv("MYSQL_HOST")));
define('DB_USERNAME', trim(getenv("MYSQL_USER")));
define('DB_PASSWORD', trim(getenv("MYSQL_PASS")));
define('DB_DATABASE', trim(getenv("DB_1")));
define('ROOT', trim(getenv("URL_2")));

define('ENV', 'Dev');