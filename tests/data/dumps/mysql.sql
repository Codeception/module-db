CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `enabled` boolean DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




insert  into `groups`(`id`,`name`,`enabled`,`created_at`) values (1,'coders',1,'2012-02-01 21:17:50');

insert  into `groups`(`id`,`name`,`enabled`,`created_at`) values (2,'jazzman',0,'2012-02-01 21:18:40');


CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` binary(16) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `is_active` bit(1) DEFAULT b'1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


insert  into `users`(`id`,`uuid`, `name`,`email`, `is_active`,`created_at`) values (1,0x11edc34b01d972fa9c1d0242ac120006,'davert','davert@mail.ua', b'1','2012-02-01 21:17:04');

insert  into `users`(`id`,`uuid`, `name`,`email`, `is_active`,`created_at`) values (2,null,'nick','nick@mail.ua', b'1','2012-02-01 21:17:15');

insert  into `users`(`id`,`uuid`, `name`,`email`, `is_active`,`created_at`) values (3,null,'miles','miles@davis.com', b'1','2012-02-01 21:17:25');

insert  into `users`(`id`,`uuid`, `name`,`email`, `is_active`,`created_at`) values (4,null,'bird','charlie@parker.com', b'0','2012-02-01 21:17:39');




CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `role` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_permissions` (`group_id`),
  KEY `FK_users` (`user_id`),
  CONSTRAINT `FK_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_permissions` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;





insert  into `permissions`(`id`,`user_id`,`group_id`,`role`) values (1,1,1,'member');

insert  into `permissions`(`id`,`user_id`,`group_id`,`role`) values (2,2,1,'member');

insert  into `permissions`(`id`,`user_id`,`group_id`,`role`) values (5,3,2,'member');

insert  into `permissions`(`id`,`user_id`,`group_id`,`role`) values (7,4,2,'admin');






CREATE TABLE `order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert  into `order`(`id`,`name`,`status`) values (1,'main', 'open');


CREATE TABLE `table_with_reserved_primary_key` (
  `unique` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  PRIMARY KEY (`unique`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert  into `table_with_reserved_primary_key`(`unique`,`name`,`status`) values (1,'main', 'open');

CREATE TABLE `composite_pk` (
  `group_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `status` varchar(255) NOT NULL,
  PRIMARY KEY (`group_id`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `no_pk` (
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `auto_increment_not_on_pk` (
    `id` int(11) NOT NULL,
    `counter` int(11) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE INDEX counter ON `auto_increment_not_on_pk` (counter);
ALTER TABLE `auto_increment_not_on_pk`
    MODIFY counter int AUTO_INCREMENT;


CREATE TABLE `auto_increment_on_composite_pk` (
    `id` int(11) NOT NULL,
    `counter` int(11) AUTO_INCREMENT NOT NULL,
PRIMARY KEY (`id`, `counter`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `empty_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field` varchar(255),
  PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
