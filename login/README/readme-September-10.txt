/*DB table updates */
DROP TABLE `tbl_mdl_status_declined_reasons`
ALTER TABLE `tbluser` ADD COLUMN `status` ENUM('0','1') DEFAULT '1' AFTER `user_type`;
ALTER TABLE `tbl_mdl_status_log` ADD COLUMN Date_created DATETIME NOT NULL AFTER DATE;
ALTER TABLE `tbl_mdl_stats` ADD COLUMN `account` INT(32) DEFAULT NULL AFTER `mdl_number`;
ALTER TABLE `tbl_revenue` DROP COLUMN `payor`;
ALTER TABLE `tbl_revenue` ADD COLUMN `Guid_payor` INT(11) DEFAULT NULL AFTER `Guid_user`;
ALTER TABLE `tbl_revenue` ADD COLUMN `amount` VARCHAR(255) DEFAULT NULL AFTER `Guid_payor`;
ALTER TABLE `tbl_revenue` DROP COLUMN `insurance`, DROP COLUMN `patient`;


/*Table structure for table `tbl_mdl_payors` */
CREATE TABLE `tbl_mdl_payors` (
  `Guid_payor` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`Guid_payor`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `tbl_mdl_payors` */
insert  into `tbl_mdl_payors`(`Guid_payor`,`name`) values (1,'Patient '),(2,'Anthem BCBS of KY');

/*Table structure for table `tbl_mdl_cpt_code` */
CREATE TABLE `tbl_mdl_cpt_code` (
  `Guid_cpt` int(11) NOT NULL AUTO_INCREMENT,
  `code` int(11) DEFAULT NULL,
  PRIMARY KEY (`Guid_cpt`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

/*Data for the table `tbl_mdl_cpt_code` */
insert  into `tbl_mdl_cpt_code`(`Guid_cpt`,`code`) values (1,81211),(2,81212),(3,81213),(4,81215),(5,81292),(6,81294),(7,81295),(8,81297),(9,81298),(10,81300),(11,81317),(12,81319),(13,81321),(14,81403),(15,81405),(16,81406),(17,81408),(18,81479);



/*Updated Files*/
patient-info.php
mdl-stats.php
dashbard.php
user-management.php
functions-ss-login.php
functions.php

custom-styles.css
scripts.js
admin.js





