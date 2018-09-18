/*DB table updates */

CREATE TABLE `tbl_mdl_status_log` (
  `Guid_status_log` int(11) NOT NULL AUTO_INCREMENT,
  `Log_group` int(11) DEFAULT NULL,
  `Guid_status` int(11) DEFAULT NULL,
  `Guid_user` int(11) DEFAULT NULL,
  `Guid_patient` int(11) DEFAULT NULL,
  `Guid_account` int(11) DEFAULT NULL,
  `account` int(11) DEFAULT NULL,
  `Guid_salesrep` int(11) DEFAULT NULL,
  `salesrep_fname` varchar(128) DEFAULT NULL,
  `salesrep_lname` varchar(128) DEFAULT NULL,
  `order_by` int(11) DEFAULT '0',
  `Recorded_by` int(11) DEFAULT NULL,
  `Date` datetime NOT NULL,
  `Date_created` datetime NOT NULL,
  PRIMARY KEY (`Guid_status_log`)
)

ALTER TABLE `tblpatient` ADD COLUMN `Guid_status` INT(11) DEFAULT NULL AFTER `Guid_reason`;
ALTER TABLE `tblpatient` ADD COLUMN `total_deductible` VARCHAR(255) DEFAULT NULL AFTER `Guid_status`;



/*Updated Files*/
style.min.css
custom-styles.css
admin.css

scripts.js

account-config.php
ajaxHandler.php
devicesInventory.php
functions.php
mdl-stat-details.php
patient-info.php
