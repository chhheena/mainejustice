DROP TABLE IF EXISTS `#__rssfactory`;
CREATE TABLE `#__rssfactory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `protocol` enum('http','ftp') NOT NULL DEFAULT 'http',
  `url` text NOT NULL,
  `title` text NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `nrfeeds` smallint(6) NOT NULL DEFAULT '1',
  `cat` int(11) NOT NULL,
  `date` datetime DEFAULT NULL,
  `rsserror` tinyint(1) DEFAULT NULL,
  `last_error` mediumtext,
  `last_refresh_stories` int(11) DEFAULT NULL,
  `encoding` varchar(30) DEFAULT NULL,
  `enablerefreshwordfilter` tinyint(1) NOT NULL DEFAULT '0',
  `refreshallowedwords` text NOT NULL,
  `refreshbannedwords` text NOT NULL,
  `refreshexactmatchwords` text NOT NULL,
  `i2c_enabled` int(1) NOT NULL DEFAULT '1',
  `i2c_author` int(11) NOT NULL DEFAULT '0',
  `i2c_publishing_period` int(11) NOT NULL DEFAULT '180',
  `i2c_sectionid` int(11) NOT NULL DEFAULT '0',
  `i2c_catid` int(11) NOT NULL DEFAULT '11',
  `i2c_frontpage` tinyint(4) NOT NULL DEFAULT '0',
  `i2c_published` tinyint(1) NOT NULL,
  `i2c_prepend` text NOT NULL,
  `i2c_append` text NOT NULL,
  `i2c_full_article` tinyint(1) NOT NULL DEFAULT '0',
  `i2c_enable_word_filter` tinyint(1) NOT NULL DEFAULT '1',
  `i2c_words_white_list` text NOT NULL,
  `i2c_words_black_list` text NOT NULL,
  `i2c_words_exact_list` text NOT NULL,
  `i2c_words_replacements` text NOT NULL,
  `ftp_host` varchar(255) NOT NULL,
  `ftp_username` varchar(255) NOT NULL,
  `ftp_password` varchar(255) NOT NULL,
  `ftp_path` text NOT NULL,
  `params` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cat` (`cat`),
  KEY `userid` (`userid`),
  KEY `nrfeeds` (`nrfeeds`),
  KEY `ordering` (`ordering`),
  KEY `published` (`published`),
  KEY `id_published` (`id`,`published`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#__rssfactory_ad_category_map`;
CREATE TABLE `#__rssfactory_ad_category_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adId` int(11) NOT NULL,
  `categoryId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `categoryId` (`categoryId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#__rssfactory_ads`;
CREATE TABLE `#__rssfactory_ads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(250) DEFAULT NULL,
  `adtext` text,
  `categories_assigned` mediumtext,
  `published` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#__rssfactory_cache`;
CREATE TABLE `#__rssfactory_cache` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `rssid` int(11) DEFAULT NULL,
  `rssurl` text,
  `date` datetime NOT NULL,
  `channel_title` text,
  `channel_link` text,
  `channel_description` text,
  `channel_category` text,
  `item_title` text,
  `item_description` text,
  `item_link` text,
  `item_source` text,
  `item_date` datetime DEFAULT NULL,
  `item_enclosure` text,
  `item_hash` varchar(40) NOT NULL DEFAULT '',
  `archived` tinyint(1) NOT NULL DEFAULT '0',
  `hits` int(11) NOT NULL DEFAULT '0',
  `comments` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rssid` (`rssid`),
  KEY `item_hash` (`item_hash`),
  KEY `archived` (`archived`),
  KEY `item_date` (`item_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#__rssfactory_comments`;
CREATE TABLE `#__rssfactory_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` tinyint(1) NOT NULL,
  `item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `text` mediumtext NOT NULL,
  `published` tinyint(1) NOT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#__rssfactory_favorites`;
CREATE TABLE `#__rssfactory_favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `feed_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_feed_id` (`feed_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#__rssfactory_submitted`;
CREATE TABLE `#__rssfactory_submitted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` text NOT NULL,
  `title` varchar(255) NOT NULL,
  `userid` int(11) NOT NULL,
  `comment` text NOT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#__rssfactory_voting`;
CREATE TABLE `#__rssfactory_voting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cacheId` int(11) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL,
  `voteValue` int(1) NOT NULL DEFAULT '0',
  `voteHash` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cacheId` (`cacheId`),
  KEY `userid` (`userid`),
  KEY `voteHash` (`voteHash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
