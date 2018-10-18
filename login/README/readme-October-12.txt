/*DB table updates */
ALTER TABLE `tblprovider` ADD COLUMN Guid_account INT(11) AFTER provider_id

UPDATE tblprovider t1 INNER JOIN tblaccount t2 ON t1.account_id = t2.account
SET t1.Guid_account = t2.Guid_account 


/*Updated Files*/
admin.css
custom-styles.css
scripts.js
account-config.php
accounts.php
dashboard.php
functions.php
header.php
index.php
login.php
mdl-stats.php
navbar.php
patient-info.php
salesreps.php
user-management.php





