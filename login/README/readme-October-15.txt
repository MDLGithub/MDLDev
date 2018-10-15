/*DB table updates */
ALTER TABLE `tblsalesrep` ADD COLUMN color_matrix VARCHAR(16) AFTER color;
ALTER TABLE `tbl_mdl_status`  MODIFY `access_roles` TEXT;

/*Updated Files*/

ajaxHandlerEvents.php
eventschedule.php
function_event.php
dashboard2.php
