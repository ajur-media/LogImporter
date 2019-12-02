CREATE TABLE `log_doctorpiter_mobile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dt` datetime DEFAULT NULL,
  `summary` tinyint(4) DEFAULT NULL,
  `nginx` char(3) DEFAULT NULL,
  `ismobile` tinyint(4) DEFAULT NULL,
  `istablet` tinyint(4) DEFAULT NULL,
  `useragent` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nginx` (`nginx`),
  KEY `ismobile` (`ismobile`),
  KEY `summary` (`summary`),
  KEY `istablet` (`istablet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;