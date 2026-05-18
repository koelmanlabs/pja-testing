-- =============================================================================
-- KL Events (Koelman Labs) — Migration from com_jem to com_planjeagenda
-- Run this ONCE on an existing JEM installation to migrate all data.
-- Always make a database backup first!
-- =============================================================================

-- Step 1: Rename all JEM tables to KL Events tables
RENAME TABLE `#__jem_events`               TO `#__pja_events`;
RENAME TABLE `#__jem_venues`               TO `#__pja_venues`;
RENAME TABLE `#__jem_categories`           TO `#__pja_categories`;
RENAME TABLE `#__jem_register`             TO `#__pja_register`;
RENAME TABLE `#__jem_attachments`          TO `#__pja_attachments`;
RENAME TABLE `#__jem_config`               TO `#__pja_config`;
RENAME TABLE `#__jem_cats_event_relations` TO `#__pja_cats_event_relations`;
RENAME TABLE `#__jem_groups`               TO `#__pja_groups`;
RENAME TABLE `#__jem_groupmembers`         TO `#__pja_groupmembers`;
RENAME TABLE `#__jem_countries`            TO `#__pja_countries`;

-- Step 2: Update Joomla extension registry
UPDATE `#__extensions`
SET    `name`    = 'com_planjeagenda',
       `element` = 'com_planjeagenda'
WHERE  `element` = 'com_jem'
AND    `type`    = 'component';

-- Step 3: Update menu items that point to com_jem
UPDATE `#__menu`
SET    `component_id` = (
           SELECT `extension_id` FROM `#__extensions`
           WHERE  `element` = 'com_planjeagenda' AND `type` = 'component'
       )
WHERE  `component_id` = (
           SELECT `extension_id` FROM `#__extensions`
           WHERE  `element` = 'com_jem' AND `type` = 'component'
       );

-- Done.
-- After running this script, install com_planjeagenda via the Joomla installer.
