-- Step 1: Add catid column to pja_events
ALTER TABLE `#__pja_events` 
    ADD COLUMN `catid` int(11) NOT NULL DEFAULT '0' AFTER `locid`,
    ADD KEY `idx_catid` (`catid`);

-- Step 2: Migrate existing category relations to the single catid field
-- Use the first (lowest ordering) category from the relation table
UPDATE `#__pja_events` e
INNER JOIN (
    SELECT itemid, MIN(catid) as primary_catid
    FROM `#__pja_cats_event_relations`
    GROUP BY itemid
) rel ON rel.itemid = e.id
SET e.catid = rel.primary_catid
WHERE e.catid = 0;

-- Step 3: Update category default value to published=1
ALTER TABLE `#__pja_categories`
    MODIFY COLUMN `published` tinyint(1) NOT NULL DEFAULT 1;
