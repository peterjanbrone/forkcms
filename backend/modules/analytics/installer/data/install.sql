CREATE TABLE IF NOT EXISTS `analytics_keywords` (
 `id` int(11) NOT NULL auto_increment,
 `keyword` varchar(255) NOT NULL,
 `entrances` int(11) NOT NULL,
 `date` datetime NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `analytics_landing_pages` (
 `id` int(11) NOT NULL auto_increment,
 `page_path` varchar(255) NOT NULL,
 `entrances` int(11) NOT NULL,
 `bounces` int(11) NOT NULL,
 `bounce_rate` varchar(255) NOT NULL,
 `start_date` datetime NOT NULL,
 `end_date` datetime NOT NULL,
 `updated_on` datetime NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `analytics_pages` (
 `id` int(11) NOT NULL auto_increment,
 `page` varchar(255) NOT NULL,
 `date_viewed` datetime NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `analytics_referrers` (
 `id` int(11) NOT NULL auto_increment,
 `referrer` varchar(255) NOT NULL,
 `entrances` int(11) NOT NULL,
 `date` datetime NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `analytics_error_pages`(
 `id` int(11) NOT NULL auto_increment,
 `page` varchar(255) NOT NULL,
 `referrer` varchar(255),
 `date`datetime NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `analytics_error_page_visitors`(
 `id` int(11) NOT NULL auto_increment,
 `error_page_id` int(11) NOT NULL,
 `ip` varchar(60) NOT NULL,
 `browser` varchar(255) NOT NULL,
 `extension` varchar(10),
 `is_logged_in` enum('N','Y') NOT NULL DEFAULT 'N',
 `caller_is_module` enum('N','Y') NOT NULL DEFAULT 'N',
 PRIMARY KEY (`id`),
 FOREIGN KEY (`error_page_id`) REFERENCES analytics_error_pages(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;