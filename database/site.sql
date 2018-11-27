CREATE TABLE `users_members` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `user_id` mediumint(8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users_members` (`id`, `user_id`) VALUES
(1, 1);

ALTER TABLE `users_members`
  ADD PRIMARY KEY (`id`);

 ALTER TABLE `users_members`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;