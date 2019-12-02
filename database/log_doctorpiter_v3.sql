-- Log table for version 3 stats 

CREATE TABLE `log_doctorpiter` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `dt` datetime DEFAULT NULL,
   `memory_usage` int(11) DEFAULT NULL,
   `memory_peak` int(11) DEFAULT NULL,
   `mysql_query_count` int(11) DEFAULT NULL,
   `mysql_query_time` decimal(8,5) DEFAULT NULL,
   `time_total` decimal(8,5) DEFAULT NULL,
   `site_routed` varchar(20) COLLATE latin1_general_ci DEFAULT NULL,
   `site_url` varchar(512) COLLATE latin1_general_ci DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `memory_usage` (`memory_usage`),
   KEY `memory_peak` (`memory_peak`),
   KEY `mysql_query_count` (`mysql_query_count`),
   KEY `mysql_query_time` (`mysql_query_time`),
   KEY `time_total` (`time_total`),
   KEY `site_routed` (`site_routed`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;