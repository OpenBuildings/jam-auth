DROP TABLE IF EXISTS `test_user_tokens`;
CREATE TABLE `test_user_tokens` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `test_user_id` int(11) UNSIGNED NOT NULL,
  `user_agent` varchar(40) NOT NULL,
  `token` varchar(40) NOT NULL,
  `type` varchar(100) NOT NULL,
  `created` int(10) UNSIGNED NOT NULL,
  `expires` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uniq_token` (`token`),
  KEY `fk_user_id` (`test_user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `test_roles_users`;
CREATE TABLE `test_roles_users` (
  `test_user_id` int(10) UNSIGNED NOT NULL,
  `test_role_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY  (`test_user_id`,`test_role_id`),
  KEY `fk_role_id` (`test_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `test_roles`;
CREATE TABLE `test_roles` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uniq_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `test_users`;
CREATE TABLE `test_users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(254) NOT NULL,
  `username` varchar(32) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL,
  `logins` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `last_login` int(10) UNSIGNED,
  `facebook_uid` varchar(100),
  `twitter_uid` varchar(100),
  `last_login_ip` VARCHAR(40),
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uniq_username` (`username`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
