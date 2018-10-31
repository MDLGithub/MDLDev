/*DB changes*/
ALTER TABLE `tblpatient` ADD COLUMN dmdl_physician_id INT(32) AFTER Guid_dmdl_patient
ALTER TABLE `tbluser` ADD COLUMN Loaded ENUM('Yes','No') DEFAULT 'No' AFTER Guid_role
ALTER TABLE `tblaccount` ADD COLUMN address2 VARCHAR(128) AFTER address
ALTER TABLE `tblpatient` ADD COLUMN Guid_dmdl_physician INT(32) AFTER Guid_dmdl_patient

- dashboard2.php
- ajaxHandlerEvents.php
- functions_event.php
