/*DB table updates */
ALTER TABLE `tbl_mdl_status_log` DROP order_by;
ALTER TABLE `tblpatient` DROP `Guid_status`;
ALTER TABLE `tbl_mdl_status_log` ADD COLUMN `currentstatus` ENUM('Y','N') DEFAULT 'N' AFTER `Guid_status`;


/*Updated Files*/
ajaxHandler.php
functions.php
mdl-stats.php
mdl-stat-details.php
patient-info.php





