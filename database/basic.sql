CREATE TABLE `admin_logs` (
  `id` mediumint(9) NOT NULL,
  `time` datetime NOT NULL,
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `class` varchar(30) NOT NULL,
  `item_id` mediumint(8) UNSIGNED NOT NULL,
  `action` varchar(30) NOT NULL DEFAULT '',
  `log` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `admin_logs`
  MODIFY `id` mediumint(9) NOT NULL AUTO_INCREMENT;

CREATE TABLE `admin_permissions` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `section_id` tinyint(3) UNSIGNED NOT NULL,
  `role_id` tinyint(3) UNSIGNED NOT NULL,
  `view` enum('0','1') NOT NULL DEFAULT '0',
  `edit` enum('0','1') NOT NULL DEFAULT '0',
  `add` enum('0','1') NOT NULL DEFAULT '0',
  `delete` enum('0','1') NOT NULL DEFAULT '0',
  `export` enum('0','1') NOT NULL DEFAULT '0',
  `admin` enum('0','1') NOT NULL DEFAULT '0',
  `nav` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `admin_permissions` (`id`, `section_id`, `role_id`, `view`, `edit`, `add`, `delete`, `export`, `admin`, `nav`) VALUES
(1, 1, 1, '1', '1', '1', '1', '1', '1', '1'),
(2, 2, 1, '1', '1', '1', '1', '1', '1', '1'),
(3, 3, 1, '1', '1', '1', '1', '1', '1', '1'),
(4, 4, 1, '1', '1', '1', '1', '1', '1', '1');

ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `admin_permissions`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

CREATE TABLE `admin_roles` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(30) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `admin_roles` (`id`, `name`) VALUES
(1, 'Admin');

ALTER TABLE `admin_roles`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `admin_roles`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

CREATE TABLE `admin_sections` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `type` enum('admin','model','controller','dashboard') NOT NULL DEFAULT 'tab',
  `ref` varchar(30) NOT NULL DEFAULT '',
  `class` varchar(30) NOT NULL DEFAULT '',
  `name` varchar(30) NOT NULL DEFAULT '',
  `tab` varchar(30) NOT NULL DEFAULT '',
  `ord` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `admin_sections` (`id`, `type`, `ref`, `class`, `name`, `tab`, `ord`) VALUES
(1, 'admin', 'admins', 'AdminUsers', 'Admins', '', 1),
(4, 'admin', 'logs', 'AdminLogs', 'Logs', '', 2),
(2, 'admin', 'roles', 'AdminRoles', 'Roles', '', 3),
(3, 'admin', 'sections', 'AdminSections', 'Sections', '', 4);

ALTER TABLE `admin_sections`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `admin_sections`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

CREATE TABLE `admin_users` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `role_id` tinyint(3) UNSIGNED NOT NULL,
  `level` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `admin_users` (`id`, `user_id`, `role_id`, `level`) VALUES
(1, 1, 1, 3);

ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `admin_users`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

CREATE TABLE `autologin` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `ip` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `autologin`
  ADD PRIMARY KEY (`id`);

CREATE TABLE `sessions` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `user_id` mediumint(8) UNSIGNED DEFAULT NULL,
  `time` datetime NOT NULL,
  `ip` varchar(20) NOT NULL,
  `url` varchar(255) NOT NULL,
  `data` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

CREATE TABLE `users` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `fbuid` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(32) NOT NULL,
  `username` varchar(255) NOT NULL,
  `username_link` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `class` varchar(20) NOT NULL DEFAULT 'member',
  `photo` varchar(255) DEFAULT NULL,
  `lang` varchar(5) NOT NULL DEFAULT 'en-en',
  `timezone` varchar(30) NOT NULL DEFAULT 'Europe/Madrid',
  `time` datetime NOT NULL,
  `time_last` datetime DEFAULT NULL,
  `time_last_goal` datetime DEFAULT NULL,
  `ip_last` varchar(20) DEFAULT NULL,
  `active` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `act_code` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users` (`id`, `fbuid`, `email`, `password`, `username`, `username_link`, `link`, `class`, `photo`, `lang`, `timezone`, `time`, `time_last`, `time_last_goal`, `ip_last`, `active`, `act_code`) VALUES
(1, NULL, 'info@manythings.pro', '94b578c5cf8feeabc130c386144f1d9a', 'Admin', 'admin', 'admin_1', 'member', NULL, 'en-en', 'Europe/Madrid', '2014-08-08 19:39:39', '2016-11-22 00:24:06', '0000-00-00 00:00:00', '::1', 1, NULL);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;