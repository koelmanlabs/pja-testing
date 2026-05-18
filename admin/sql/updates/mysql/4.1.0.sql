-- insert new config values
ALTER TABLE `#__pja_events` MODIFY `author_ip` varchar(45);
ALTER TABLE `#__pja_venues` MODIFY `author_ip` varchar(45);
ALTER TABLE `#__pja_events` ADD COLUMN `requestanswer` TINYINT(1) NOT NULL DEFAULT '0' AFTER `waitinglist`;
ALTER TABLE `#__pja_events` MODIFY `recurrence_limit_date` date NULL DEFAULT null;
ALTER TABLE `#__pja_events` MODIFY `checked_out` INT(11) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `#__pja_venues` MODIFY `checked_out` INT(11) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `#__pja_categories` MODIFY `checked_out` INT(11) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `#__pja_groups` MODIFY `checked_out` INT(11) UNSIGNED NULL DEFAULT NULL;

UPDATE `#__pja_categories` SET `modified_time` = null WHERE `modified_time` LIKE '%0000-00-00%';
UPDATE `#__pja_categories` SET `checked_out_time` = null WHERE `checked_out_time` LIKE '%0000-00-00%';
UPDATE `#__pja_categories` SET `created_time` = now() WHERE `created_time` LIKE '%0000-00-00%';
UPDATE `#__pja_events` SET `created` = now() WHERE `created` LIKE '%0000-00-00%';
UPDATE `#__pja_events` SET `modified` = null WHERE `modified` LIKE '%0000-00-00%';
UPDATE `#__pja_events` SET `checked_out_time` = null WHERE `checked_out_time` LIKE '%0000-00-00%';
UPDATE `#__pja_events` SET `recurrence_limit_date` = null WHERE `recurrence_limit_date` LIKE '%0000-00-00%';
UPDATE `#__pja_groups` SET `checked_out_time` = null WHERE `checked_out_time` LIKE '%0000-00-00%';
UPDATE `#__pja_venues` SET `created` = now() WHERE `created` LIKE '%0000-00-00%';
UPDATE `#__pja_venues` SET `modified` = null WHERE `modified` LIKE '%0000-00-00%';
UPDATE `#__pja_venues` SET `checked_out_time` = null WHERE `checked_out_time` LIKE '%0000-00-00%';
UPDATE `#__pja_venues` SET `publish_up` = null WHERE `publish_up` LIKE '%0000-00-00%';
UPDATE `#__pja_venues` SET `publish_down` = null WHERE `publish_down` LIKE '%0000-00-00%';
UPDATE `#__pja_attachments` SET `added` = null WHERE `added` LIKE '%0000-00-00%';
UPDATE `#__pja_events` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__pja_categories` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__pja_venues` SET `checked_out` = null WHERE `checked_out` = 0;
UPDATE `#__pja_groups` SET `checked_out` = null WHERE `checked_out` = 0;

UPDATE `#__pja_config` SET `value` = 'H:i' WHERE `keyname` = 'formattime';
UPDATE `#__pja_config` SET `value` = 'H'   WHERE `keyname` = 'formathour';
INSERT INTO `#__pja_config` (`keyname`, `value`, `access`) VALUES ('flyer', '0', '0');

