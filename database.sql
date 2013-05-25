SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE TABLE `attendancelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dateId` int(11) DEFAULT NULL,
  `userId` int(11) DEFAULT NULL,
  `changeUserId` int(11) DEFAULT NULL,
  `changeTime` datetime NOT NULL,
  `status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`dateId`,`userId`),
  KEY `dateId` (`dateId`),
  KEY `userId` (`userId`),
  KEY `changeUserId` (`changeUserId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `dates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `startDate` datetime NOT NULL,
  `endDate` datetime DEFAULT NULL,
  `groups` text CHARACTER SET latin1 NOT NULL,
  `title` varchar(200) CHARACTER SET latin1 NOT NULL,
  `description` text CHARACTER SET latin1 NOT NULL,
  `locationId` int(11) DEFAULT NULL,
  `showInAttendanceList` tinyint(1) NOT NULL DEFAULT '0',
  `bold` tinyint(1) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `locationId` (`locationId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typeId` int(11) NOT NULL,
  `year` year(4) DEFAULT NULL,
  `userId` int(11) NOT NULL,
  `uploadId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `typeId` (`typeId`),
  KEY `userId` (`userId`),
  KEY `uploadId` (`uploadId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `eventtypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET latin1 NOT NULL,
  `title` varchar(100) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `NAME` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `featurerequests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `description` text NOT NULL,
  `status` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(100) CHARACTER SET latin1 NOT NULL,
  `name` varchar(100) CHARACTER SET latin1 NOT NULL,
  `title` varchar(200) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `jserrors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `ip` varchar(50) CHARACTER SET latin1 NOT NULL,
  `message` varchar(200) CHARACTER SET latin1 NOT NULL,
  `file` varchar(200) CHARACTER SET latin1 NOT NULL,
  `line` int(11) NOT NULL,
  `url` varchar(200) CHARACTER SET latin1 NOT NULL,
  `userAgent` varchar(200) CHARACTER SET latin1 NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(200) CHARACTER SET latin1 NOT NULL,
  `title` varchar(200) CHARACTER SET latin1 NOT NULL,
  `town` varchar(100) CHARACTER SET latin1 NOT NULL,
  `clicks` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `name` varchar(200) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUENAME` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `validTill` date DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `targetGroups` text CHARACTER SET latin1 NOT NULL,
  `userId` int(11) NOT NULL,
  `text` text CHARACTER SET latin1 NOT NULL,
  `attachedFiles` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `moviecategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `movieorders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `movieId` int(11) NOT NULL,
  `buy` tinyint(1) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `movieId` (`movieId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `movies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventYear` year(4) NOT NULL,
  `releaseDate` date NOT NULL,
  `discTypeId` int(11) NOT NULL,
  `discs` int(11) NOT NULL DEFAULT '1',
  `price` decimal(5,2) DEFAULT NULL,
  `borrowable` tinyint(1) NOT NULL DEFAULT '1',
  `borrowed` tinyint(1) NOT NULL DEFAULT '0',
  `borrowedTo` varchar(100) CHARACTER SET latin1 NOT NULL,
  `categoryId` int(11) NOT NULL,
  `comment` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `discTypeId` (`discTypeId`),
  KEY `categoryId` (`categoryId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `movietypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET latin1 NOT NULL,
  `title` varchar(100) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `musiciangroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderIndex` int(11) NOT NULL,
  `name` varchar(100) CHARACTER SET latin1 NOT NULL,
  `title` varchar(100) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `notedirectory_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `notedirectory_programs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typeId` int(11) NOT NULL,
  `year` year(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `typeId` (`typeId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `notedirectory_programtitles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `programId` int(11) NOT NULL,
  `titleId` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `programId` (`programId`),
  KEY `titleId` (`titleId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `notedirectory_programtypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) CHARACTER SET latin1 NOT NULL,
  `showInGroups` tinyint(1) NOT NULL,
  `showNoSelection` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `notedirectory_titles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoryId` int(11) NOT NULL DEFAULT '0',
  `title` varchar(200) NOT NULL DEFAULT '',
  `composer` varchar(200) NOT NULL DEFAULT '',
  `arranger` varchar(200) NOT NULL DEFAULT '',
  `publisher` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `categoryId` (`categoryId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `permission` varchar(200) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `PERMISSION` (`userId`,`permission`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `picturealbums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `permission` varchar(100) CHARACTER SET latin1 NOT NULL,
  `coverPicture` int(11) NOT NULL,
  `name` varchar(100) CHARACTER SET latin1 NOT NULL,
  `title` varchar(200) CHARACTER SET latin1 NOT NULL,
  `text` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ALBUM` (`date`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `pictures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `albumId` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `text` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `PICTURE` (`albumId`,`number`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `pictureyears` (
  `year` year(4) NOT NULL,
  `coverAlbumId` int(11) NOT NULL,
  PRIMARY KEY (`year`),
  UNIQUE KEY `coverAlbumId` (`coverAlbumId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `protocols` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `uploadId` int(11) NOT NULL,
  `groups` text CHARACTER SET latin1 NOT NULL,
  `date` date NOT NULL,
  `name` varchar(200) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uploadId` (`uploadId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) CHARACTER SET latin1 NOT NULL,
  `title` varchar(200) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `usergroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET latin1 NOT NULL,
  `title` varchar(100) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUENAME` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET latin1 NOT NULL,
  `email` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `newEmail` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `newEmailChangeDate` datetime DEFAULT NULL,
  `password` varchar(128) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `resetPasswordDate` datetime DEFAULT NULL,
  `firstName` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `lastName` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `birthDate` date DEFAULT NULL,
  `phonePrivate1` varchar(200) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `phonePrivate2` varchar(200) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `phoneWork` varchar(200) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `phoneMobile` varchar(200) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `fax` varchar(200) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `calendarToken` varchar(32) DEFAULT NULL,
  `lastOnline` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `calendarToken` (`calendarToken`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `firstVisitDate` datetime NOT NULL,
  `firstVisitPath` varchar(100) NOT NULL,
  `lastVisitDate` datetime NOT NULL,
  `lastVisitPath` varchar(100) NOT NULL,
  `userId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `visit` (`ip`,`date`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


ALTER TABLE `attendancelist`
  ADD CONSTRAINT `attendancelist_changeUser` FOREIGN KEY (`changeUserId`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `attendancelist_date` FOREIGN KEY (`dateId`) REFERENCES `dates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `attendancelist_user` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `dates`
  ADD CONSTRAINT `dates_location` FOREIGN KEY (`locationId`) REFERENCES `locations` (`id`);

ALTER TABLE `events`
  ADD CONSTRAINT `events_upload` FOREIGN KEY (`uploadId`) REFERENCES `uploads` (`id`),
  ADD CONSTRAINT `events_type` FOREIGN KEY (`typeId`) REFERENCES `eventtypes` (`id`),
  ADD CONSTRAINT `events_user` FOREIGN KEY (`userId`) REFERENCES `users` (`id`);

ALTER TABLE `featurerequests`
  ADD CONSTRAINT `featurerequests_user` FOREIGN KEY (`userId`) REFERENCES `users` (`id`);

ALTER TABLE `messages`
  ADD CONSTRAINT `messages_user` FOREIGN KEY (`userId`) REFERENCES `users` (`id`);

ALTER TABLE `movieorders`
  ADD CONSTRAINT `movieorders_user` FOREIGN KEY (`userId`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `movieorders_movie` FOREIGN KEY (`movieId`) REFERENCES `movies` (`id`);

ALTER TABLE `movies`
  ADD CONSTRAINT `movies_category` FOREIGN KEY (`categoryId`) REFERENCES `moviecategories` (`id`),
  ADD CONSTRAINT `movies_type` FOREIGN KEY (`discTypeId`) REFERENCES `movietypes` (`id`);

ALTER TABLE `notedirectory_programs`
  ADD CONSTRAINT `notedirectory_programs_type` FOREIGN KEY (`typeId`) REFERENCES `notedirectory_programtypes` (`id`);

ALTER TABLE `notedirectory_programtitles`
  ADD CONSTRAINT `notedirectory_programtitles_title` FOREIGN KEY (`titleId`) REFERENCES `notedirectory_titles` (`id`),
  ADD CONSTRAINT `notedirectory_programtitles_program` FOREIGN KEY (`programId`) REFERENCES `notedirectory_programs` (`id`);

ALTER TABLE `notedirectory_titles`
  ADD CONSTRAINT `notedirectory_titles_category` FOREIGN KEY (`categoryId`) REFERENCES `notedirectory_categories` (`id`);

ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_user` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pictures`
  ADD CONSTRAINT `pictures_album` FOREIGN KEY (`albumId`) REFERENCES `picturealbums` (`id`);

ALTER TABLE `pictureyears`
  ADD CONSTRAINT `pictureyears_album` FOREIGN KEY (`coverAlbumId`) REFERENCES `picturealbums` (`id`);

ALTER TABLE `protocols`
  ADD CONSTRAINT `protocols_upload` FOREIGN KEY (`uploadId`) REFERENCES `uploads` (`id`),
  ADD CONSTRAINT `protocols_user` FOREIGN KEY (`userId`) REFERENCES `users` (`id`);

ALTER TABLE `visits`
  ADD CONSTRAINT `visits_user` FOREIGN KEY (`userId`) REFERENCES `visits` (`userId`) ON DELETE SET NULL ON UPDATE SET NULL;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
