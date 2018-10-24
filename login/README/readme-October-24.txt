<<<<<<< HEAD
/* Files Updated */

- dashboard2.php
- ajaxHandlerEvents.php
- eventschedule.php
=======
/*DB table updates */
ALTER TABLE `tblpatient` ADD COLUMN loaded ENUM('Y','N') DEFAULT 'N' AFTER test_kit
ALTER TABLE `tblpatient` ADD COLUMN Guid_dmdl_patient INT(32) AFTER Guid_patient

/*Updated Files*/

- dashboard2.php
- ajaxHandlerEvents.php
>>>>>>> f87671bdb229ef9b2eb67443cc76b782ac911082
