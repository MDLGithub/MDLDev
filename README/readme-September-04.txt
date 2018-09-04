/*DB table updates */


ALTER TABLE `tblsalesrep` ADD `color` VARCHAR(16) NOT NULL AFTER `zip`

CREATE TABLE `tbluser_login_attempts` (
  `Guid_attempts` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(100) NOT NULL,
  `ip` 	VARCHAR(100) NOT NULL,
  `time` VARCHAR(30) NOT NULL,
  PRIMARY KEY (`Guid_attempts`)
) ENGINE=INNODB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Updated Files*/
all .php, .js, .css files

in coonfig.php need to add this lines

// Login security params, should have deffernt values for each server
define("SECURE", TRUE); // FOR Production should be TRUE if https
define('PASSWORD_SALT', "ZCep0jVFDtp9Pc7eftIAXt"); //22 characters, Should be different for dev and prod
define('SESSIN_NAME', "mdldevadmin"); //Should be different for dev and prod

require_once('functions-ss-login.php');

