/*DB table updates */

//sent by Atul - LET's SET DEFAULT 0 in this case after all updates we can check 
//if all users in tbluser table matches with our users (admin,salesrep,provider,patient)
ALTER TABLE `tbluser` ADD `Guid_role` INT(2) NOT NULL DEFAULT '0' AFTER `status`;

//new flag to for salesreps and salesrep managers
ALTER TABLE `tblsalesrep` ADD COLUMN is_manager ENUM('0', '1') DEFAULT '0' AFTER Guid_user;

//setting is_manager equal to 1 if there are values in tbluserrole
UPDATE `tblsalesrep` AS srep, 
(SELECT Guid_user, Guid_role FROM `tbluserrole` WHERE Guid_role='5') AS sManager
SET srep.is_manager = '1'
WHERE srep.Guid_user = sManager.Guid_user;

//backup tbluserrole for any case, after we won't need it
ALTER TABLE `tbluserrole` RENAME TO `tbluserrole_backup`;

----------------------------------------------------------------------------------------
UPDATE Guid_roles in user table from existing type of users
DON'T Run queries if you already have updated roles
-----------------------------------------------------------------------------------------
//Update Patients
UPDATE `tbluser` AS u, 
(SELECT Guid_user FROM `tblpatient` ) AS p
SET u.Guid_role = '3'
WHERE u.Guid_user = p.Guid_user;

//Update Providers
UPDATE `tbluser` AS u, 
(SELECT Guid_user FROM `tblprovider` ) AS p
SET u.Guid_role = '2'
WHERE u.Guid_user = p.Guid_user

//Update Salesreps
UPDATE `tbluser` AS u, 
(SELECT Guid_user FROM `tblsalesrep` WHERE  is_manager='0' ) AS srep
SET u.Guid_role = '4'
WHERE u.Guid_user = srep.Guid_user

//Update Salesrep Mangers
UPDATE `tbluser` AS u, 
(SELECT Guid_user FROM `tblsalesrep` WHERE  is_manager='1' ) AS srep
SET u.Guid_role = '5'
WHERE u.Guid_user = srep.Guid_user

//Update Admins
UPDATE `tbluser` AS u, 
(SELECT Guid_user FROM `tbladmins`) AS admin
SET u.Guid_role = '1'
WHERE u.Guid_user = admin.Guid_user
-----------------------------------------------------------------------------------------

//Moving Mark As test data to user level
ALTER TABLE `tbluser` ADD COLUMN `marked_test` ENUM("0","1") DEFAULT "0" AFTER `status`;

UPDATE `tbluser` AS u, 
(SELECT Guid_user, mark_as_test FROM `tbl_ss_qualify`) AS ssq
SET u.marked_test = ssq.mark_as_test
WHERE u.Guid_user = ssq.Guid_user AND ssq.mark_as_test='1'

UPDATE `tbluser` AS u, 
(SELECT Guid_user, mark_as_test FROM `tblqualify`) AS q
SET u.marked_test = q.mark_as_test
WHERE u.Guid_user = q.Guid_user AND q.mark_as_test='1'

//backup mark_as_test columns for now, after all tests we can remove them
ALTER TABLE `tbl_ss_qualify` CHANGE COLUMN `mark_as_test` `bb_mark_as_test` ENUM('0','1');
ALTER TABLE `tblqualify` CHANGE COLUMN `mark_as_test` `bb_mark_as_test` ENUM('0','1');

//adding new role for internal users
insert  into `tblrole`(`Guid_role`,`role`) values (6,'MDL Patient');

//adding the same columns for admin users as salesreps have, in order to moving info and don't use data
//we are going to move user data if user role changed from admin to salesreps or vice versa
ALTER TABLE `tbladmins`
ADD COLUMN `phone_number` VARCHAR(12) NOT NULL,
ADD COLUMN `region` VARCHAR(255) NOT NULL,
ADD COLUMN `title` VARCHAR(255) NOT NULL,
ADD COLUMN `address` VARCHAR(128) NOT NULL,
ADD COLUMN `city` VARCHAR(64) NOT NULL,
ADD COLUMN `state` VARCHAR(2) NOT NULL,
ADD COLUMN `zip` VARCHAR(10) NOT NULL



/*Updated Files*/
scripts.js
custom-styles.css
accounts.php
ajaxHandler.php
dashboard.php
functions.php
mdl-stat-details.php
patient-info.php
salesreps.php
user-management.php


/*Deleted Files*/
mdl-status-log.php







