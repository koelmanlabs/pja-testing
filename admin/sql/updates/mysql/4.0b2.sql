-- insert new config values
ALTER TABLE `#__pja_categories` ADD COLUMN `emailacljl` TINYINT NOT NULL DEFAULT '0' AFTER `email`;
INSERT INTO `#__pja_config` (`keyname`, `value`, `access`) VALUES ('flagicons_path', 'media/com_planjeagenda/images/flags/w20-webp/', '0');