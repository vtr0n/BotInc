SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `answers` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `input` text CHARACTER SET utf8mb4 NOT NULL,
  `output` text CHARACTER SET utf8mb4 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `group_join` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `vk_id` int(11) NOT NULL,
  `date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `group_leave` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `vk_id` int(11) NOT NULL,
  `date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `message_new` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `vk_id` int(11) NOT NULL,
  `message` text CHARACTER SET utf8mb4 NOT NULL,
  `date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `service_token` varchar(255) NOT NULL,
  `confirmation_code` varchar(10) NOT NULL,
  `uniqid` varchar(15) NOT NULL,
  `functions` text NOT NULL,
  `hooks` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `vk_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `answers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `group_join`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `group_leave`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `message_new`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_id` (`group_id`);

ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subscriber` (`vk_id`,`group_id`);