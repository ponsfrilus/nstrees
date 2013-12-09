CREATE SCHEMA `nstrees` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;

CREATE TABLE `nstrees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `slug` varchar(60) DEFAULT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;