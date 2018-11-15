/*DB table updates */

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


/*Updated Files*/





