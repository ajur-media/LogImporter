-- Log table for version 3 stats

CREATE TABLE `log_rendertime` (
                                   `id` int(11) NOT NULL AUTO_INCREMENT,
                                   `dt` datetime DEFAULT NULL,
                                   `article_id` int(11) DEFAULT NULL,
                                   `render_time` decimal(8,6) DEFAULT NULL,
                                   `size_textbb` int(11) DEFAULT NULL,
                                   `size_html` int(11) DEFAULT NULL,
                                   `size_diff` int(11) DEFAULT NULL,
                                   `url` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
                                   PRIMARY KEY (`id`),
                                   KEY `article_id` (`article_id`),
                                   KEY `render_time` (`render_time`),
                                   KEY `size_diff` (`size_diff`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;