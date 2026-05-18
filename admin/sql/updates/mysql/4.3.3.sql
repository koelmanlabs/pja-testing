-- delete values

-- new values
UPDATE `#__pja_config` SET value = JSON_SET(value,'$.global_editevent_starttime_limit', '0', '$.global_editevent_endtime_limit', '23', '$.global_editevent_minutes_block', '1') WHERE keyname = 'globalattribs';
INSERT IGNORE INTO `#__pja_config` (`keyname`, `value`, `access`) VALUES ('recurrence_anticipation_lastday', '60', '0');
ALTER TABLE `#__pja_events` ADD `recurrence_bylastday` VARCHAR(20) NULL DEFAULT NULL AFTER `recurrence_byday`;
ALTER TABLE `#__pja_events` ADD `publish_down` DATETIME NULL DEFAULT NULL AFTER `modified_by`;
ALTER TABLE `#__pja_events` ADD `publish_up` DATETIME NULL DEFAULT NULL AFTER `modified_by`;
INSERT INTO `#__pja_config` (`keyname`, `value`, `access`) VALUES ('categories_order', '0', '0');
INSERT INTO `#__pja_config` (`keyname`, `value`, `access`) VALUES ('defaultCategory', '0', '0');
INSERT INTO `#__pja_config` (`keyname`, `value`, `access`) VALUES ('defaultVenue', '0', '0');
-- change values

-- update values
UPDATE `#__pja_events` SET publish_up = created;

