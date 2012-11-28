-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 28, 2012 at 11:30 AM
-- Server version: 5.5.28
-- PHP Version: 5.3.10-1ubuntu3.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `zrm`
--

-- --------------------------------------------------------

--
-- Table structure for table `builds`
--

CREATE TABLE IF NOT EXISTS `builds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `lang` varchar(255) NOT NULL,
  `created` date NOT NULL,
  `changed` date NOT NULL,
  `resume_data` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=59 ;

-- --------------------------------------------------------

--
-- Table structure for table `career_history`
--

CREATE TABLE IF NOT EXISTS `career_history` (
  `hid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `function` text NOT NULL,
  `location` varchar(128) NOT NULL,
  `job_title` varchar(128) NOT NULL,
  `description` varchar(4096) NOT NULL,
  `lang_data` longtext NOT NULL,
  PRIMARY KEY (`hid`),
  KEY `index_by_user` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=135 ;

-- --------------------------------------------------------

--
-- Table structure for table `certification`
--

CREATE TABLE IF NOT EXISTS `certification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `name` text NOT NULL,
  `authority` text,
  `number` text,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `lang_data` longtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE IF NOT EXISTS `customer` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(4096) NOT NULL,
  `lang_data` longtext NOT NULL,
  PRIMARY KEY (`cid`),
  UNIQUE KEY `index_name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=66 ;

-- --------------------------------------------------------

--
-- Table structure for table `language`
--

CREATE TABLE IF NOT EXISTS `language` (
  `lid` varchar(16) NOT NULL,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`lid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE IF NOT EXISTS `project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `name` text NOT NULL,
  `occupation` text,
  `url` text,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `description` longtext,
  `lang_data` longtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `publicity`
--

CREATE TABLE IF NOT EXISTS `publicity` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `type` enum('Publication','Talk') NOT NULL,
  `description` varchar(4096) NOT NULL,
  `lang_data` longtext NOT NULL,
  PRIMARY KEY (`pid`),
  KEY `index_by_user` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=104 ;

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE IF NOT EXISTS `session` (
  `id` char(32) NOT NULL DEFAULT '',
  `modified` int(11) DEFAULT NULL,
  `lifetime` int(11) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `session`
--

INSERT INTO `session` (`id`, `modified`, `lifetime`, `data`) VALUES
('cu8cb8rcdo77mns5cn5qtpnm42', 1354102176, 864000, '18iK2IhmVAQlX84y-wDZmgdbbOLuDxxuZeiapf4w45n-po5VZwl7Ukos_Ar2dmKhBVYaY5TSpJZ1YNIVp0Q-CFFfHhGwj_yaUw9hUJeHxoDWZO7Gvh5NwOhJ_JKNr0iHTl359Vgmsv1YA2gGB4xX_danMsY2jNKbmLqKIGLfWNak0I_1WA-A9g5p2MyYZinttY74Op1oVJ9pty3SyPuwfFaj39WofOfRmdeUbubP3hEzv4p2Ynn1wSIFFu3WP2s4FRo9iW3k_S9_onTLsDeTF_rlTT5KkT3Iu12qAJVUPmwzGt0tsz-ihUK0pVBnLlXmEjLAI9tpa9RswlKCnAPOYUEZbeqjFdU7EZ_wyCk-uSGn2dEfqvGQz_e2ZKb_r27AlaXZBEgXG6DbBH_zOefTGU-VD7e5RGO_mHJfpa4AFjKr0XxZ8vdIw95hB6o2zXFpqqCfMaIXQ1XVfEqewTdPN0oevnVpWI4wGgY73ufCMQdavVSS5T1xBzZzBy4W8rnQ-m1TB5HwF_GHXUgZpjXVTik68-I2uhTVki6YJ3WJpCa8KTxD0Go1icogcSLqPl4iUbHECrt-NRYIqA3oQQVzpPYp_n2vPCqr3OnH5tUtkOxKIYPp5tB0bnUl36YxP1KJKZD2b82xHZsIIkg_DRnKFXYwr-74eTOTi2yaqd6VqY_yRIhCbgyEKiOmbHBZ4rYj');

-- --------------------------------------------------------

--
-- Table structure for table `skill`
--

CREATE TABLE IF NOT EXISTS `skill` (
  `sid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  PRIMARY KEY (`sid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `skill_category`
--

CREATE TABLE IF NOT EXISTS `skill_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` text NOT NULL,
  `name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=90 ;

-- --------------------------------------------------------

--
-- Table structure for table `study`
--

CREATE TABLE IF NOT EXISTS `study` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `achievement` varchar(255) NOT NULL,
  `type` enum('Education','Training') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `location` varchar(128) NOT NULL,
  `description` varchar(4096) NOT NULL,
  `lang_data` longtext NOT NULL,
  PRIMARY KEY (`sid`),
  KEY `index_by_user` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=176 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `user_login` varchar(128) NOT NULL,
  `password` varchar(40) NOT NULL,
  `user_level` int(1) NOT NULL DEFAULT '1',
  `first_name` varchar(128) NOT NULL,
  `last_name` varchar(128) NOT NULL,
  `location` varchar(128) NOT NULL,
  `birth_date` date NOT NULL,
  `birth_place` varchar(255) NOT NULL,
  `social_security` varchar(11) NOT NULL,
  `language` varchar(128) NOT NULL,
  `nationality` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `profile` varchar(4096) NOT NULL,
  `lang_data` longtext NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=50 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`uid`, `user_login`, `password`, `user_level`, `first_name`, `last_name`, `location`, `birth_date`, `birth_place`, `social_security`, `language`, `nationality`, `company`, `profile`, `lang_data`) VALUES
(49, 'admin', '21232f297a57a5a743894a0e4a801fc3', 2, 'admin', 'admin', 'admin', '2012-11-01', 'admin', 'admin', 'admin', 'admin', 'admin', 'admin', 'a:0:{}');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
