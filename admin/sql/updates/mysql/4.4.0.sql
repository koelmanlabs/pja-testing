-- delete values

-- new values
INSERT INTO `#__pja_config` (`keyname`, `value`, `access`) VALUES ('access_level_locked_events', '[\"1\"]', '0');
INSERT INTO `#__pja_config` (`keyname`, `value`, `access`) VALUES ('access_level_locked_venues', '[\"1\"]', '0');
INSERT INTO `#__pja_config` (`keyname`, `value`, `access`) VALUES ('access_level_locked_categories', '[\"1\"]', '0');
ALTER TABLE `#__pja_venues` ADD `color` VARCHAR(7) NOT NULL AFTER `alias`;

-- change values
UPDATE `#__pja_config` SET `value` = 'media/com_planjeagenda/images/flags/w80-webp/' WHERE `keyname` = 'flagicons_path' AND `value` = 'media/com_planjeagenda/images/flags/w20-png/';

-- update values
ALTER TABLE `#__pja_events` MODIFY `author_ip` varchar(45) DEFAULT NULL;
ALTER TABLE `#__pja_venues` MODIFY `author_ip` varchar(45) NOT NULL DEFAULT '';
ALTER TABLE `#__pja_register` MODIFY `uip` varchar(45) NOT NULL DEFAULT '';
ALTER TABLE `#__pja_events` CHANGE `fulltext` `fulltext` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `introtext`; 
