/*DB table updates */
ALTER TABLE `tbl_mdl_dmdl` DROP COLUMN Linked;
ALTER TABLE `tbl_mdl_dmdl` ADD COLUMN Linked ENUM('Y','N') DEFAULT 'N' AFTER PhysicianID;

ALTER TABLE `tblpatient` DROP COLUMN Linked;
ALTER TABLE `tblpatient` ADD COLUMN Linked ENUM('Y','N') DEFAULT 'N' AFTER Loaded;


TRUNCATE TABLE `tbl_mdl_dmdl`;
ALTER TABLE `tbl_mdl_dmdl` DROP COLUMN TestAbbrev;
ALTER TABLE `tbl_mdl_dmdl` ADD COLUMN TestName VARCHAR(255) AFTER TestCode;

ALTER TABLE `tbl_mdl_payors` ADD COLUMN full_name varchar(255) AFTER name;
ALTER TABLE `tblpatient` ADD COLUMN phone_number1 varchar(12) AFTER phone_number;
ALTER TABLE `tblpatient` ADD COLUMN address1 VARCHAR(255) AFTER address;




/*Updated Files*/





