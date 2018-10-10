/*DB table updates */
ALTER TABLE `tblqualify` ADD COLUMN `mark_as_test` ENUM('0','1') NOT NULL DEFAULT '0' AFTER `ip`
ALTER TABLE `tblprovider` MODIFY `provider_id` VARCHAR(32);


/*Updated Files*/

patient-info.php
dashboard.php
ajaxHandler.php
functions.php
accounts.php
custom-styles.css
scripts.js
admin.js
