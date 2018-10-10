/*DB table updates */
//if visibility=1 show statuses for dashboard
ALTER TABLE `tbl_mdl_status` ADD COLUMN `visibility` ENUM('0','1') DEFAULT '1' AFTER `status`;

//updated table for tbl_mdl_stats
DROP TABLE IF EXISTS `tbl_mdl_stats`;

CREATE TABLE `tbl_mdl_stats` (
  `Guid_stats` int(11) NOT NULL AUTO_INCREMENT,
  `Guid_patient` int(11) DEFAULT NULL,
  `Guid_status` int(11) DEFAULT NULL,
  `Guid_account` int(11) DEFAULT NULL,
  `Guid_salesrep` int(11) DEFAULT NULL,
  `Guid_user` int(11) DEFAULT NULL,
  `Date_reported` datetime DEFAULT NULL
  PRIMARY KEY (`Guid_stats`)
);

//new table for mdl numbers
CREATE TABLE `tbl_mdl_number` (
  `Guid_mdl` int(11) NOT NULL AUTO_INCREMENT,
  `Guid_user` int(11) DEFAULT NULL,
  `number` int(7) DEFAULT NULL,
  `comment` text,
  PRIMARY KEY (`Guid_mdl`)
)


/*Updated Files*/
mdl-stats.php
patient-info.php
functions.php

custom-styles.css
scripts.js


assets/images/dates_icon.svg
