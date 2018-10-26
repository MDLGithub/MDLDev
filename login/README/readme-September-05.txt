/*DB table updates */
ALTER TABLE tbl_mdl_status_log CHANGE `Guid_user` `Guid_patient` INT(11);
ALTER TABLE `tbl_mdl_status_log` ADD COLUMN `recorded_by` INT(11) AFTER `Guid_patient`;

/*Updated Files*/
patient-info.php
functions.php
navbar.php

// Login security params, should have deffernt values for each server



