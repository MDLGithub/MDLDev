/*DB table updates */
UPDATE tblsource SET `description` = 'Lunch' WHERE `code` = 'L';


CREATE TABLE `tbl_mdl_status_log` (
  `Guid_status_log` int(11) NOT NULL AUTO_INCREMENT,
  `Guid_status` int(11) DEFAULT NULL,
  `Guid_user` int(11) DEFAULT NULL,
  `mdl_number` int(11) DEFAULT NULL,
  `comment` text,
  `date` datetime NOT NULL,
  PRIMARY KEY (`Guid_status_log`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8



/*Updated Files*/
functions.php
patient-info.php
mdl-stats.php
custom-styles.css
scripts.js


