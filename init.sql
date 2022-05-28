CREATE TABLE `DIXIT` (
  `id` varchar(8) COLLATE utf8_unicode_ci NOT NULL COMMENT 'id en base64',
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `DIXIT`
  ADD UNIQUE KEY `id` (`id`);