/*DB table updates */
TRUNCATE TABLE `tbl_mdl_status`;
ALTER TABLE `tbl_mdl_status` ADD COLUMN `parent_id` INT(11) DEFAULT '0' AFTER `Guid_status`;
ALTER TABLE `tbl_mdl_status` ADD COLUMN `order_by` INT(11) DEFAULT '0' AFTER `status`;
ALTER TABLE `tbl_mdl_status_log` CHANGE `Guid_status` `status_ids` TEXT NOT NULL;
UPDATE `tbl_mdl_status_log` SET status_ids = "" WHERE status_ids IS NOT NULL;
ALTER TABLE `tbl_mdl_status_log` DROP COLUMN mdl_number, DROP COLUMN COMMENT; 


/*Updated Files*/
patient-info.php
ajaxHandler.php
functions.php
mdl-stats.php

custom-styles.css

scripts.js





