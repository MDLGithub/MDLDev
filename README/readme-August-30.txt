/*DB table updates */

/*Add two flags: Demo and Launch at location URL flag for location*/
INSERT INTO `tblsource` (`code`,`description`) VALUES ('DE','Demo'),('L','Launch');

/*Table structure for table `tbl_mdl_status` */
CREATE TABLE `tbl_mdl_status` (
  `Guid_status` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Guid_status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Add new column notes to stats table */
ALTER TABLE `tbl_mdl_stats` ADD COLUMN `notes` TEXT AFTER `status`;

/*Rename status to Guid_status */
ALTER TABLE `tbl_mdl_stats` CHANGE `status` `Guid_status` INT(11);

/*New Column for declined reason id*/
ALTER TABLE `tbl_mdl_stats` ADD COLUMN `Guid_declined_reason` INT(11) AFTER `Guid_status`;

*Table structure for table `tbl_mdl_status` */
CREATE TABLE `tbl_mdl_status` (
  `Guid_status` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Guid_status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8
/*Data for the table `tbl_mdl_status` */
insert  into `tbl_mdl_status`(`Guid_status`,`status`) values (1,'Pending'),(2,'Approved'),(3,'Declined');

/*Table structure for table `tbl_mdl_status_declined_reasons` */
CREATE TABLE `tbl_mdl_status_declined_reasons` (
  `Guid_declined_reason` int(11) NOT NULL AUTO_INCREMENT,
  `reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Guid_declined_reason`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Data for the table `tbl_mdl_status_declined_reasons` */
insert  into `tbl_mdl_status_declined_reasons`(`Guid_declined_reason`,`reason`) values (1,'High Deductible'),(2,'Physician Cancelled testing'),(3,'Patient did not complete Genetic Counseling');


FILES UPDATED
account-config.php
accounts.php
dashboard.php
functions.php
mdl-stats.php
patient-info.php
user-management.php
url-configuration.php

assets/js/scripts.js
assets/css/custom-styles.css

