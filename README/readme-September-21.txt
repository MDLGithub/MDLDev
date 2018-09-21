/*DB table updates */

/*Table structure for table `tbl_mdl_note` */
CREATE TABLE `tbl_mdl_note` (
  `Guid_note` int(11) NOT NULL AUTO_INCREMENT,
  `Guid_note_category` int(11) DEFAULT NULL,
  `Guid_user` int(11) DEFAULT NULL,
  `Recorder_by` int(11) DEFAULT NULL,
  `Comment` text,
  `Date` datetime DEFAULT NULL,
  `Date_created` datetime NOT NULL,
  PRIMARY KEY (`Guid_note`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `tbl_mdl_note_category` */
CREATE TABLE `tbl_mdl_note_category` (
  `Guid_note_category` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `order_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`Guid_note_category`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;


/*Updated Files*/
mdl-stat-details-config.php
mdl-stat-details.php
patient-info.php
functions.php

custom-styles.css
scripts.js
