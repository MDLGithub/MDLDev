/*DB table updates */
ALTER TABLE `tblqualify` ADD COLUMN `mark_as_test` ENUM('0','1') NOT NULL DEFAULT '0' AFTER `ip`

/*Updated Files*/

patient-info.php
dashboard.php

custom-styles.css
scripts.js
admin.js
