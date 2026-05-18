-- delete values

-- new values

-- change values
UPDATE `#__pja_venues` SET `attribs` = '{}' WHERE `attribs` IS NULL OR `attribs` = '' OR `attribs` = '""' OR `attribs` = "''" OR NOT JSON_VALID(`attribs`);
UPDATE `#__pja_events` SET `attribs` = '{}' WHERE `attribs` IS NULL OR `attribs` = '' OR `attribs` = '""' OR `attribs` = "''" OR NOT JSON_VALID(`attribs`);
UPDATE `#__pja_categories` SET `attribs` = '{}' WHERE `metadata` IS NULL OR `metadata` = '' OR `metadata` = '""' OR `metadata` = "''" OR NOT JSON_VALID(`metadata`);
UPDATE `#__pja_categories` SET `path` = NULL WHERE `id` = 1 AND `catname` = 'root' AND `path` IS NOT NULL;
  
-- update values    
ALTER TABLE `#__pja_events` MODIFY `recurrence_number` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__pja_venues` MODIFY `latitude` decimal(10,6) DEFAULT NULL;
ALTER TABLE `#__pja_venues` MODIFY `longitude` decimal(10,6) DEFAULT NULL;
ALTER TABLE `#__pja_categories` ADD KEY `idx_parent` (`parent_id`);

ALTER TABLE `#__pja_events` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__pja_venues` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__pja_categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__pja_cats_event_relations` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__pja_register` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__pja_groups` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__pja_groupmembers` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__pja_config` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__pja_attachments` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__pja_countries` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE #__pja_attachments ENGINE=InnoDB;
ALTER TABLE #__pja_categories ENGINE=InnoDB;
ALTER TABLE #__pja_cats_event_relations ENGINE=InnoDB;
ALTER TABLE #__pja_events ENGINE=InnoDB;
ALTER TABLE #__pja_groupmembers ENGINE=InnoDB;
ALTER TABLE #__pja_groups ENGINE=InnoDB;
ALTER TABLE #__pja_register ENGINE=InnoDB;
ALTER TABLE #__pja_config ENGINE=InnoDB;
ALTER TABLE #__pja_venues ENGINE=InnoDB;
ALTER TABLE #__pja_countries ENGINE=InnoDB;

-- update row order
ALTER TABLE `#__pja_events` CHANGE `fulltext` `fulltext` MEDIUMTEXT NOT NULL AFTER `introtext`;
