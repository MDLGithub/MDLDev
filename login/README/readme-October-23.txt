/*DB table updates */

//dmdlTable
CREATE TABLE `tbl_mdl_dmdl` (
  `Guid_mdl_dmdl` int(11) NOT NULL AUTO_INCREMENT,
  `TestCode` int(32) NOT NULL,
  `TestAbbrev` varchar(50) NOT NULL,
  `MDLNumber` varchar(7) NOT NULL,
  `PatientID` int(11) DEFAULT NULL,
  `PhysicianID` int(11) DEFAULT NULL,
  `ToUpdate` enum('Y','N') DEFAULT 'Y',
  `UpdateDatetime` datetime NOT NULL,
  PRIMARY KEY (`Guid_mdl_dmdl`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1
