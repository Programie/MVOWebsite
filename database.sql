
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
  KEY `changeUserId` (`changeUserId`),
  CONSTRAINT `attendancelist_changeUser` FOREIGN KEY (`changeUserId`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `attendancelist_date` FOREIGN KEY (`dateId`) REFERENCES `dates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `attendancelist_user` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  KEY `locationId` (`locationId`),
  CONSTRAINT `dates_location` FOREIGN KEY (`locationId`) REFERENCES `locations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typeId` int(11) NOT NULL,
  `year` year(4) DEFAULT NULL,
  `userId` int(11) NOT NULL,
  `uploadId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `typeId` (`typeId`),
  KEY `userId` (`userId`),
  KEY `uploadId` (`uploadId`),
  CONSTRAINT `events_type` FOREIGN KEY (`typeId`) REFERENCES `eventtypes` (`id`),
  CONSTRAINT `events_upload` FOREIGN KEY (`uploadId`) REFERENCES `uploads` (`id`),
  CONSTRAINT `events_user` FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `eventtypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET latin1 NOT NULL,
  `title` varchar(100) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `NAME` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(100) CHARACTER SET latin1 NOT NULL,
  `name` varchar(100) CHARACTER SET latin1 NOT NULL,
  `title` varchar(200) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(200) CHARACTER SET latin1 NOT NULL,
  `title` varchar(200) CHARACTER SET latin1 NOT NULL,
  `town` varchar(100) CHARACTER SET latin1 NOT NULL,
  `clicks` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `name` varchar(200) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUENAME` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  KEY `userId` (`userId`),
  CONSTRAINT `messages_user` FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `musiciangroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderIndex` int(11) NOT NULL,
  `name` varchar(100) CHARACTER SET latin1 NOT NULL,
  `title` varchar(100) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `notedirectory_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET latin1 NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `notedirectory_programs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typeId` int(11) NOT NULL,
  `year` year(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `typeId` (`typeId`),
  CONSTRAINT `notedirectory_programs_type` FOREIGN KEY (`typeId`) REFERENCES `notedirectory_programtypes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `notedirectory_programtitles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `programId` int(11) NOT NULL,
  `titleId` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `programId` (`programId`),
  KEY `titleId` (`titleId`),
  CONSTRAINT `notedirectory_programtitles_program` FOREIGN KEY (`programId`) REFERENCES `notedirectory_programs` (`id`),
  CONSTRAINT `notedirectory_programtitles_title` FOREIGN KEY (`titleId`) REFERENCES `notedirectory_titles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `notedirectory_programtypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) CHARACTER SET latin1 NOT NULL,
  `showInGroups` tinyint(1) NOT NULL,
  `showNoSelection` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `notedirectory_titles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoryId` int(11) NOT NULL DEFAULT '0',
  `title` varchar(200) NOT NULL DEFAULT '',
  `composer` varchar(200) NOT NULL DEFAULT '',
  `arranger` varchar(200) NOT NULL DEFAULT '',
  `publisher` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `categoryId` (`categoryId`),
  CONSTRAINT `notedirectory_titles_category` FOREIGN KEY (`categoryId`) REFERENCES `notedirectory_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `permission` varchar(200) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `PERMISSION` (`userId`,`permission`),
  CONSTRAINT `permissions_user` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `phonenumbers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `category` set('phone','mobile','fax') NOT NULL,
  `subCategory` set('business','private') NOT NULL,
  `number` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  CONSTRAINT `phonenumbers_user` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `picturealbums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `published` tinyint(1) NOT NULL,
  `date` date NOT NULL,
  `isPublic` tinyint(1) NOT NULL,
  `coverPicture` int(11) NOT NULL,
  `title` varchar(200) CHARACTER SET latin1 NOT NULL,
  `text` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `pictures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `albumId` int(11) NOT NULL,
  `fileId` varchar(32) NOT NULL,
  `number` int(11) NOT NULL,
  `text` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `FILE` (`albumId`,`fileId`),
  CONSTRAINT `pictures_album` FOREIGN KEY (`albumId`) REFERENCES `picturealbums` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `pictureyears` (
  `year` year(4) NOT NULL,
  `coverAlbumId` int(11) NOT NULL,
  PRIMARY KEY (`year`),
  UNIQUE KEY `coverAlbumId` (`coverAlbumId`),
  CONSTRAINT `pictureyears_album` FOREIGN KEY (`coverAlbumId`) REFERENCES `picturealbums` (`id`)
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
  KEY `userId` (`userId`),
  CONSTRAINT `protocols_upload` FOREIGN KEY (`uploadId`) REFERENCES `uploads` (`id`),
  CONSTRAINT `protocols_user` FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `roomoccupancyplan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `reservedBy` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `startTime` time NOT NULL,
  `endTime` time NOT NULL,
  `endRepeat` date DEFAULT NULL,
  `weekly` tinyint(1) NOT NULL DEFAULT '0',
  `changeUserId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `changeUserId` (`changeUserId`),
  CONSTRAINT `roomoccupancyplan_user` FOREIGN KEY (`changeUserId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sessions` (
  `id` varchar(50) CHARACTER SET latin1 NOT NULL,
  `date` datetime NOT NULL,
  `data` text CHARACTER SET latin1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) CHARACTER SET latin1 NOT NULL,
  `title` varchar(200) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `usergroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET latin1 NOT NULL,
  `title` varchar(100) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUENAME` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET latin1 NOT NULL,
  `email` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `newEmail` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `newEmailChangeDate` datetime DEFAULT NULL,
  `password` varchar(128) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `resetPasswordDate` datetime DEFAULT NULL,
  `forcePasswordChange` tinyint(1) NOT NULL DEFAULT '0',
  `firstName` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `lastName` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `birthDate` date DEFAULT NULL,
  `calendarToken` varchar(32) DEFAULT NULL,
  `lastOnline` datetime DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `calendarToken` (`calendarToken`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  KEY `userId` (`userId`),
  CONSTRAINT `visits_user` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

