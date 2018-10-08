/*DB table updates */
ALTER TABLE `tbl_revenue` CHANGE `amount` `insurance` VARCHAR(255);
ALTER TABLE `tbl_mdl_status_log` ADD COLUMN `recorded_by` INT(11) AFTER `Guid_patient`;
ALTER TABLE `tbl_revenue` ADD COLUMN `patient` VARCHAR(255) AFTER `insurance`;

/*Updated Files*/
patient-info.php
custom-styles.css
mdl-stats.php
url-configuration.php




