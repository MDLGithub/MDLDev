/*DB table updates */
RENAME TABLE `tblaccessbyroles` TO `tbl_mdl_options`;

ALTER TABLE `tbl_mdl_number` CHANGE `number` `mdl_number` INT(7);

ALTER TABLE `tbl_mdl_stats` ADD COLUMN `Guid_declined_reason` INT(11) DEFAULT NULL AFTER `Guid_user`; 
ALTER TABLE `tbl_mdl_stats` ADD COLUMN `specimen_collection_date` DATETIME  DEFAULT NULL AFTER `Guid_declined_reason`; 
ALTER TABLE `tbl_mdl_stats` ADD COLUMN `date_accessioned` DATETIME  DEFAULT NULL AFTER `specimen_collection_date`; 
ALTER TABLE `tbl_mdl_stats` CHANGE `Date_reported` `date_reported` DATETIME NULL;


/*Updated Files*/
patient-info.php
user-managment.php
functions.php
mdl-stats.php
mdl-stat-details.php
mdl-stats-old-migrations.php
mdl-stat-details-config.php

admin.js





