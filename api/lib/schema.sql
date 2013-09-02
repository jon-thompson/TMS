SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS `tms` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `tms`;

CREATE TABLE IF NOT EXISTS `section` (
  `CRN` int(11) NOT NULL,
  `Term` varchar(32) NOT NULL,
  `Object` longblob NOT NULL,
  PRIMARY KEY (`CRN`,`Term`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
