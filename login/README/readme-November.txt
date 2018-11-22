/*DB table updates */

ALTER TABLE `tbl_mdl_dmdl ` ADD COLUMN Linked ENUM('Yes','No') DEFAULT 'No' AFTER PhysicianID
ALTER TABLE `tblpatient` ADD COLUMN Linked ENUM('Yes','No') DEFAULT 'No' AFTER Loaded

ALTER TABLE `tblpatient` DROP COLUMN loaded
ALTER TABLE `tblpatient` ADD COLUMN Loaded ENUM('Y','N') DEFAULT 'N' AFTER test_kit
ALTER TABLE `tblaccount` ADD COLUMN Loaded ENUM('Y','N') DEFAULT 'N' AFTER account
ALTER TABLE `tblprovider` ADD COLUMN Loaded ENUM('Y','N') DEFAULT 'N' AFTER account_id
ALTER TABLE `tblsalesrep` ADD COLUMN Loaded ENUM('Y','N') DEFAULT 'N' AFTER is_manager
ALTER TABLE `tbl_mdl_number` ADD COLUMN Loaded ENUM('Y','N') DEFAULT 'N' AFTER mdl_number
ALTER TABLE `tbl_mdl_status_log` ADD COLUMN Loaded ENUM('Y','N') DEFAULT 'N' AFTER currentstatus

ALTER TABLE `tbluser` DROP COLUMN Loaded
ALTER TABLE `tbluser` ADD COLUMN Loaded ENUM('Y','N') DEFAULT 'N' AFTER marked_test
ALTER TABLE `tbluser` ADD COLUMN Updated ENUM('Y','N') DEFAULT 'N' AFTER Loaded

//november 15 ------------------------------------------------
ALTER TABLE `tblpatient` ADD COLUMN `ethnicity` varchar(255) AFTER lastname_enc
ALTER TABLE `tblpatient` ADD COLUMN `address1` varchar(255) AFTER address
ALTER TABLE `tblpatient` ADD COLUMN `account_number` VARCHAR(32) AFTER Guid_user
Alter table `tblpatient` ADD Column phone_number_home varchar(12) AFTER phone_number
ALTER TABLE `tblpatient` DROP COLUMN `dmdl_physician_id`

ALTER TABLE `tblaccount` CHANGE `address2` `address1` varchar(128)

ALTER TABLE `tbl_mdl_payors` ADD COLUMN PayID varchar(128) AFTER name 
ALTER TABLE `tbl_mdl_payors` ADD COLUMN Loaded ENUM('Y','N') DEFAULT 'N' AFTER PayID
ALTER TABLE `tbl_mdl_cpt_code` ADD COLUMN Loaded ENUM('Y','N') DEFAULT 'N' AFTER code

ALTER TABLE `tbl_revenue` ADD COLUMN Loaded ENUM('Y','N') DEFAULT 'N' AFTER amount



//november 19
INSERT INTO `tblstate` VALUES ('PR', 'Puerto Rico');

CREATE TABLE `tbl_mdl_category` (
  `Guid_category` INT(11) NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(60) DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`Guid_category`)
)

INSERT INTO `tbl_mdl_category` (`slug`,`name`) VALUES  ('corporate', 'Corporate'), ('geneveda ', 'Geneveda ')

ALTER TABLE `tblaccount` ADD COLUMN `Guid_category` INT(11) DEFAULT '1' AFTER `Guid_account`;


//November 20
TRUNCATE TABLE `tbl_mdl_category`;
ALTER TABLE `tbl_mdl_category` ADD COLUMN `description` TEXT AFTER `name`;
INSERT INTO `tbl_mdl_category` (`slug`,`name`,`description`) VALUES  ('geneveda ', 'Geneveda', 'Default Category For manually created accounts.'), ('corporate', 'Corporate', 'Category for automatically loaded accounts.')

CREATE TABLE `tbl_mdl_category_user_link` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `Guid_user` VARCHAR(60) DEFAULT NULL,
  `Guid_category` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
);


//November 21
ALTER TABLE `tblpatient` CHANGE `account_number` `accountNumber` VARCHAR(32);


CREATE TABLE IF NOT EXISTS `tbl_mdl_updates_log` (
                    `Guid_updates_log` INT(11) NOT NULL AUTO_INCREMENT,
                    `function_name` VARCHAR(128) DEFAULT NULL,
                    `description` VARCHAR(255) DEFAULT NULL,
                    `isUpdated` ENUM('Y','N') DEFAULT 'N',
                    `Date` DATETIME NOT NULL,
                    PRIMARY KEY (`Guid_updates_log`)
                );





